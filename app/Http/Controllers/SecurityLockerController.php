<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SecurityCredential;
use App\Models\HighSecurityCredential;
use App\Services\AppNotifier;
use Illuminate\Support\Facades\Crypt;

class SecurityLockerController extends Controller
{
    // ==========================================
    // NORMAL SECURITY
    // ==========================================

    public function index()
    {
        $adminId = session('admin_id');

        $credentials = SecurityCredential::where('admin_id', $adminId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return view('settings.security-list', compact('credentials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        SecurityCredential::create([
            'admin_id' => session('admin_id'),
            'name' => $request->name,
            'login_id' => $request->login_id,
            'password' => Crypt::encryptString($request->password),
        ]);

        AppNotifier::forSessionAdmin(
            'security_created',
            'Security locker',
            'New entry **' . $request->name . '** was added.',
            'Security locker'
        );

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $credential = SecurityCredential::where('admin_id', session('admin_id'))->findOrFail($id);

        $credential->update([
            'name' => $request->name,
            'login_id' => $request->login_id,
            'password' => Crypt::encryptString($request->password),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $credential = SecurityCredential::where('admin_id', session('admin_id'))->findOrFail($id);
        $name = $credential->name;
        $credential->delete();

        AppNotifier::forSessionAdmin(
            'security_deleted',
            'Security locker',
            'Entry **' . $name . '** was deleted.',
            'Security locker'
        );

        return response()->json(['success' => true]);
    }

    public function togglePin($id)
    {
        $credential = SecurityCredential::where('admin_id', session('admin_id'))->findOrFail($id);
        $credential->update([
            'is_pinned' => !$credential->is_pinned
        ]);

        return response()->json(['success' => true, 'is_pinned' => $credential->is_pinned]);
    }

    public function getPassword($id)
    {
        $credential = SecurityCredential::where('admin_id', session('admin_id'))->findOrFail($id);
        $decryptedPassword = Crypt::decryptString($credential->password);

        return response()->json([
            'success' => true,
            'password' => $decryptedPassword
        ]);
    }

    // ==========================================
    // HIGH SECURITY
    // ==========================================

    public function highIndex()
    {
        if (!$this->isHighSecurityUnlocked()) {
            return view('settings.security-high-auth');
        }

        $adminId = session('admin_id');

        $credentials = HighSecurityCredential::where('admin_id', $adminId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return view('settings.security-high-list', compact('credentials'));
    }

    private function isHighSecurityUnlocked()
    {
        if (!session('high_security_unlocked')) {
            return false;
        }

        if (now()->timestamp - session('high_security_unlocked_at', 0) > 86400) {
            session()->forget(['high_security_unlocked', 'high_security_unlocked_at']);
            return false;
        }

        return true;
    }

    public function unlockHighSecurity(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $correctPassword = env('HIGH_SECURITY_PASSWORD');

        if ($request->password === $correctPassword) {
            session([
                'high_security_unlocked' => true,
                'high_security_unlocked_at' => now()->timestamp
            ]);

            AppNotifier::forSessionAdmin(
                'high_security_unlock',
                'High security',
                'High security locker unlocked successfully.',
                'Access granted'
            );

            return response()->json(['success' => true]);
        }

        AppNotifier::forSessionAdmin(
            'high_security_failed',
            'High security',
            'Wrong password for high security locker.',
            'Access denied'
        );

        return response()->json(['success' => false, 'message' => 'Incorrect security password.'], 401);
    }

    private function checkHighSecurityUnlock()
    {
        if (!$this->isHighSecurityUnlocked()) {
            abort(403, 'Unauthorized. High security locker is locked.');
        }
    }

    public function storeHigh(Request $request)
    {
        $this->checkHighSecurityUnlock();

        $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        HighSecurityCredential::create([
            'admin_id' => session('admin_id'),
            'name' => $request->name,
            'login_id' => $request->login_id,
            'password' => Crypt::encryptString($request->password),
        ]);

        AppNotifier::forSessionAdmin(
            'security_created',
            'High security locker',
            'New entry **' . $request->name . '** was added.',
            'High security'
        );

        return response()->json(['success' => true]);
    }

    public function updateHigh(Request $request, $id)
    {
        $this->checkHighSecurityUnlock();

        $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $credential = HighSecurityCredential::where('admin_id', session('admin_id'))->findOrFail($id);

        $credential->update([
            'name' => $request->name,
            'login_id' => $request->login_id,
            'password' => Crypt::encryptString($request->password),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroyHigh($id)
    {
        $this->checkHighSecurityUnlock();

        $credential = HighSecurityCredential::where('admin_id', session('admin_id'))->findOrFail($id);
        $name = $credential->name;
        $credential->delete();

        AppNotifier::forSessionAdmin(
            'security_deleted',
            'High security locker',
            'Entry **' . $name . '** was deleted.',
            'High security'
        );

        return response()->json(['success' => true]);
    }

    public function togglePinHigh($id)
    {
        $this->checkHighSecurityUnlock();

        $credential = HighSecurityCredential::where('admin_id', session('admin_id'))->findOrFail($id);
        $credential->update([
            'is_pinned' => !$credential->is_pinned
        ]);

        return response()->json(['success' => true, 'is_pinned' => $credential->is_pinned]);
    }

    public function getPasswordHigh($id)
    {
        $this->checkHighSecurityUnlock();

        $credential = HighSecurityCredential::where('admin_id', session('admin_id'))->findOrFail($id);
        $decryptedPassword = Crypt::decryptString($credential->password);

        return response()->json([
            'success' => true,
            'password' => $decryptedPassword
        ]);
    }
}
