<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Services\AppNotifier;
use App\Services\NotificationSettingsService;
use App\Services\SmtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    // ─── Profile Settings ────────────────────────────────────────────────────
    public function profile()
    {
        $admin = DB::table('admins')->first();
        return view('settings.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name' => 'nullable|string|max:100',
            'email'     => 'nullable|email|max:150',
            'phone'     => 'nullable|string|max:20',
            'timezone'  => 'nullable|string|max:60',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $admin = DB::table('admins')->first();
        $adminId = (int) ($admin->id ?? session('admin_id'));

        $data = [
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'timezone'  => $request->timezone ?? 'Asia/Kolkata',
        ];

        $changes = [];
        if ((string) ($admin->full_name ?? '') !== (string) ($request->full_name ?? '')) {
            $changes[] = 'name';
            AppNotifier::push($adminId, 'profile_name', 'Profile updated', 'Your **name** was changed.', $request->full_name);
        }
        if ((string) ($admin->email ?? '') !== (string) ($request->email ?? '')) {
            $changes[] = 'email';
            AppNotifier::push($adminId, 'profile_email', 'Profile updated', 'Your **email** was changed.', $request->email);
        }
        if ((string) ($admin->phone ?? '') !== (string) ($request->phone ?? '')) {
            $changes[] = 'phone';
            AppNotifier::push($adminId, 'profile_phone', 'Profile updated', 'Your **phone number** was changed.', $request->phone);
        }

        if ($request->hasFile('avatar')) {
            if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
                Storage::disk('public')->delete($admin->profile_photo);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['profile_photo'] = $path;
            $changes[] = 'photo';
            AppNotifier::push($adminId, 'profile_photo', 'Profile photo updated', 'Your profile photo was changed.');
        }

        DB::table('admins')->where('id', $admin->id)->update($data);

        $desc = $changes
            ? ('Updated: ' . implode(', ', $changes))
            : 'Profile saved.';

        return back()->with('alert', [
            'type' => 'update',
            'title' => 'done successfully :)',
            'description' => $desc,
        ]);
    }

    public function updateAvatarAjax(Request $request)
    {
        if (!$request->hasFile('avatar')) {
            return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
        }

        $admin = DB::table('admins')->first();
        $adminId = (int) ($admin->id ?? session('admin_id'));

        if ($request->hasFile('avatar')) {
            if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
                Storage::disk('public')->delete($admin->profile_photo);
            }

            $path = $request->file('avatar')->store('avatars', 'public');

            DB::table('admins')->where('id', $admin->id)->update([
                'profile_photo' => $path,
                'updated_at' => now(),
            ]);

            AppNotifier::push($adminId, 'profile_photo', 'Profile photo updated', 'Your profile photo was changed.');

            return response()->json([
                'success' => true,
                'path' => asset('storage/' . $path),
                'alert' => [
                    'type' => 'update',
                    'title' => 'done successfully :)',
                    'description' => 'Profile photo updated.',
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
    }

    // ─── Admin Settings ──────────────────────────────────────────────────────
    public function admin()
    {
        $admin = $this->sessionAdmin();

        if (!$admin) {
            abort(404, 'Admin account not found.');
        }

        $smtp = app(SmtpService::class)->publicStatus();

        return view('settings.admin', compact('admin', 'smtp'));
    }

    public function updateAdmin(Request $request)
    {
        $request->merge([
            'username' => trim((string) $request->input('username', '')),
            'current_password' => (string) $request->input('current_password', ''),
            'new_password' => (string) $request->input('new_password', ''),
            'new_password_confirmation' => (string) $request->input('new_password_confirmation', ''),
        ]);

        $changingPassword = $request->filled('new_password') || $request->filled('new_password_confirmation');

        $request->validate(
            [
                'username' => 'required|string|min:3|max:50',
                'current_password' => 'required|string',
                'new_password' => $changingPassword ? 'required|string|min:6|confirmed' : 'nullable|string',
            ],
            [
                'current_password.required' => 'Enter the password you currently use to Sign In.',
                'new_password.required' => 'Enter a new password, then confirm it.',
                'new_password.min' => 'New password must be at least 6 characters.',
                'new_password.confirmed' => 'New password and confirm password do not match.',
            ]
        );

        $admin = $this->sessionAdmin();

        if (!$admin) {
            return back()->withErrors(['username' => 'Admin account not found.']);
        }

        if (!$admin->verifyPassword((string) $request->current_password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect. Enter the password you use to Sign In.'])
                ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
        }

        $usernameTaken = Admin::query()
            ->whereRaw('LOWER(username) = ?', [mb_strtolower($request->username)])
            ->where('id', '!=', $admin->id)
            ->exists();

        if ($usernameTaken) {
            return back()
                ->withErrors(['username' => 'This username is already taken.'])
                ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
        }

        $admin->username = $request->username;

        if ($changingPassword) {
            if (hash_equals((string) $request->current_password, (string) $request->new_password)) {
                return back()
                    ->withErrors(['new_password' => 'New password and current password match. Please choose a different password.'])
                    ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
            }

            $admin->password = Hash::make((string) $request->new_password);
            $admin->failed_attempts = 0;
            $admin->locked_until = null;
            $admin->save();

            $admin->refresh();
            if (!$admin->verifyPassword((string) $request->new_password)) {
                return back()
                    ->withErrors(['new_password' => 'Password could not be saved. Please try again.'])
                    ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
            }

            return redirect()
                ->route('settings.admin')
                ->with('alert', [
                    'type' => 'update',
                    'title' => 'done successfully :)',
                    'description' => 'Login password updated. Sign In with the new password from now on.',
                ]);
        }

        $admin->save();

        return redirect()
            ->route('settings.admin')
            ->with('alert', [
                'type' => 'update',
                'title' => 'done successfully :)',
                'description' => 'Username updated. Login password was not changed.',
            ]);
    }

    private function sessionAdmin(): ?Admin
    {
        $id = session('admin_id');
        if ($id) {
            $admin = Admin::query()->find($id);
            if ($admin) {
                return $admin;
            }
        }

        return Admin::query()->first();
    }

    public function smtpStatus()
    {
        return response()->json([
            'success' => true,
            ...app(SmtpService::class)->publicStatus(),
        ]);
    }

    public function smtpToggle(Request $request, SmtpService $smtp)
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Invalid SMTP status.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $enabled = $request->boolean('enabled');
        $smtp->setEnabled($enabled);

        return response()->json([
            'success' => true,
            'enabled' => $smtp->isEnabled(),
            'message' => $enabled ? 'SMTP enabled.' : 'SMTP disabled.',
        ]);
    }

    public function smtpTest(SmtpService $smtp)
    {
        $result = $smtp->testConnection();

        return response()->json(
            $result,
            $result['success'] ? 200 : 400
        );
    }

    public function smtpSendTest(Request $request, SmtpService $smtp)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
            ],
            [
                'email.required' => 'Enter a recipient email address.',
                'email.email' => 'Enter a valid recipient email address.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Enter a valid recipient email address.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $result = $smtp->sendTestEmail((string) $request->input('email'));
        $status = 200;

        if (!($result['success'] ?? false)) {
            $status = ($result['code'] ?? null) === 'disabled' ? 403 : 400;
        }

        return response()->json($result, $status);
    }

    // ─── Notification Settings ───────────────────────────────────────────────
    public function notifications(NotificationSettingsService $settings)
    {
        return view('settings.notifications', [
            'notifPrefs' => $settings->publicPrefs(),
        ]);
    }

    public function notificationToggle(Request $request, NotificationSettingsService $settings)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|in:push,email,sounds',
            'enabled' => 'present|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Invalid notification setting.',
            ], 422);
        }

        $enabled = $request->boolean('enabled');
        $key = (string) $request->input('key');

        match ($key) {
            'push' => $settings->setPushEnabled($enabled),
            'email' => $settings->setEmailEnabled($enabled),
            'sounds' => $settings->setSoundsEnabled($enabled),
        };

        $labels = [
            'push' => 'Push notifications (bell)',
            'email' => 'Email notifications',
            'sounds' => 'App sounds',
        ];

        return response()->json([
            'success' => true,
            'message' => ($labels[$key] ?? 'Setting') . ($enabled ? ' enabled.' : ' disabled.'),
            'prefs' => $settings->publicPrefs(),
        ]);
    }

    public function notificationSoundUpload(Request $request, NotificationSettingsService $settings)
    {
        $validator = Validator::make($request->all(), [
            'sound' => 'required|file|max:2048',
        ], [
            'sound.required' => 'Choose an audio file to upload.',
            'sound.max' => 'Sound file must be 2 MB or smaller.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Unable to upload sound.',
            ], 422);
        }

        $file = $request->file('sound');
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (!in_array($ext, ['mp3', 'wav', 'ogg', 'm4a'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload an MP3, WAV, OGG, or M4A file.',
            ], 422);
        }

        $settings->storeSound($file);

        return response()->json([
            'success' => true,
            'message' => 'Custom notification sound saved.',
            'prefs' => $settings->publicPrefs(),
        ]);
    }

    public function notificationSoundDelete(NotificationSettingsService $settings)
    {
        $settings->deleteSound();

        return response()->json([
            'success' => true,
            'message' => 'Custom sound removed. Default ping will be used when sounds are on.',
            'prefs' => $settings->publicPrefs(),
        ]);
    }

    // ─── Security Locker ─────────────────────────────────────────────────────
    public function security()
    {
        return view('settings.security');
    }

    public function securityList()
    {
        return view('settings.security-list');
    }

    public function securityHigh()
    {
        return view('settings.security-high');
    }

    public function securityDetail(string $type)
    {
        $entries = [
            'devos-panel' => [
                'title'    => 'DevOS Panel',
                'type'     => 'website',
                'url'      => 'admin@devos.local',
                'username' => 'admin@devos.local',
                'note'     => 'Main admin panel for DevOS.',
                'last_used'=> 'Today',
                'color'    => 'blue',
                'icon'     => 'globe',
            ],
            'database' => [
                'title'    => 'Database — devparth_db',
                'type'     => 'database',
                'url'      => '127.0.0.1:3306',
                'username' => 'root',
                'note'     => 'MySQL production database connection.',
                'last_used'=> 'Yesterday',
                'color'    => 'green',
                'icon'     => 'database',
            ],
            'ssh-key' => [
                'title'    => 'DevOS SSH Key',
                'type'     => 'ssh',
                'url'      => 'id_rsa',
                'username' => 'RSA 4096-bit',
                'note'     => 'Primary SSH key added recently for server access.',
                'last_used'=> 'Recently added',
                'color'    => 'purple',
                'icon'     => 'key',
            ],
        ];

        $entry = $entries[$type] ?? null;

        if (!$entry) {
            return redirect()->route('settings.security.list');
        }

        return view('settings.security-detail', compact('entry', 'type'));
    }
}
