<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class DocsController extends Controller
{
    public function index()
    {
        $sections = array_map(function (array $section) {
            $section['html'] = Str::markdown($this->contentFor($section['slug']));

            return $section;
        }, $this->sections());

        return view('docs.index', [
            'sections' => $sections,
        ]);
    }

    public function show(string $section)
    {
        abort_if(collect($this->sections())->firstWhere('slug', $section) === null, 404);

        return redirect()->route('docs.index');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sections(): array
    {
        return [
            [
                'slug' => 'overview',
                'index' => '01',
                'title' => 'Overview',
                'description' => 'DevOS is the personal admin workspace for one operator: tasks, projects, money, credentials, and settings.',
                'preview' => [
                    ['method' => 'APP', 'path' => 'Laravel 13 + Blade + Vite', 'note' => 'Custom session auth, not Laravel auth()'],
                    ['method' => 'USER', 'path' => 'DevOS_admin', 'note' => 'Single admin account'],
                    ['method' => 'GATE', 'path' => '/secure-access', 'note' => 'Login before any workspace page'],
                ],
            ],
            [
                'slug' => 'login',
                'index' => '02',
                'title' => 'Login',
                'description' => 'Sign in at /secure-access. Failed attempts lock the account; every try is written to the audit log.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/secure-access', 'note' => 'Admin sign-in form'],
                    ['method' => 'POST', 'path' => '/secure-access', 'note' => 'Username + password → session'],
                    ['method' => 'POST', 'path' => '/logout', 'note' => 'Ends admin_logged_in session'],
                ],
            ],
            [
                'slug' => 'forgot-password',
                'index' => '03',
                'title' => 'Password',
                'description' => 'Email OTP recovery: reset the password or jump straight to the dashboard after verify.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/forgot-password', 'note' => 'Email → OTP → reset or dashboard'],
                    ['method' => 'POST', 'path' => '/forgot-password/send-otp', 'note' => 'Hashed OTP mail, SMTP required'],
                    ['method' => 'POST', 'path' => '/forgot-password/verify-otp', 'note' => '6-digit code, 10 minute expiry'],
                ],
            ],
            [
                'slug' => 'dashboard',
                'index' => '04',
                'title' => 'Dashboard',
                'description' => 'Live snapshot: wallet totals, task completion, analog clock, locker counts, and a docs shortcut.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/dashboard', 'note' => 'Home after login'],
                    ['method' => 'LIVE', 'path' => 'Receivable / payable / net', 'note' => 'From transactions table'],
                    ['method' => 'LIVE', 'path' => 'Security locker counts', 'note' => 'High Security stays blurred until revealed'],
                ],
            ],
            [
                'slug' => 'dailyops',
                'index' => '05',
                'title' => 'DailyOps',
                'description' => 'Daily task list with status, priority, focus, bulk actions, and optional project linking.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/tasks', 'note' => 'Task workspace'],
                    ['method' => 'GET', 'path' => '/task-daily', 'note' => 'Same DailyOps view, legacy URL'],
                    ['method' => 'PATCH', 'path' => '/tasks/{id}/status', 'note' => 'Todo, backlog, in progress, done, canceled'],
                ],
            ],
            [
                'slug' => 'projects',
                'index' => '06',
                'title' => 'Projects',
                'description' => 'Personal project workspace: create, open, archive, or delete projects and attach DailyOps tasks.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/projects', 'note' => 'Project list'],
                    ['method' => 'GET', 'path' => '/projects/{id}', 'note' => 'Single project page'],
                    ['method' => 'POST', 'path' => '/projects/{id}/archive', 'note' => 'Archive without deleting'],
                ],
            ],
            [
                'slug' => 'transactions',
                'index' => '07',
                'title' => 'Transactions',
                'description' => 'Receivables, payables, and net balance with a live wallet hero on /transaction.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/transaction', 'note' => 'Wallet + ledger UI'],
                    ['method' => 'GET', 'path' => '/transactions/data', 'note' => 'JSON list and summary totals'],
                    ['method' => 'POST', 'path' => '/transactions', 'note' => 'Add receivable or payable'],
                ],
            ],
            [
                'slug' => 'notifications',
                'index' => '08',
                'title' => 'Notifications',
                'description' => 'Header bell for login audit and task events, plus push / email / sound preferences.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/notifications', 'note' => 'In-app notification list'],
                    ['method' => 'GET', 'path' => '/settings/notifications', 'note' => 'Bell, email, and sound toggles'],
                    ['method' => 'POST', 'path' => '/notifications/read-all', 'note' => 'Clear unread badge'],
                ],
            ],
            [
                'slug' => 'audit-log',
                'index' => '09',
                'title' => 'Audit Log',
                'description' => 'Login, logout, and failed attempt history stored in login_logs and shown at /audit-log.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/audit-log', 'note' => 'Sidebar → Audit Log'],
                    ['method' => 'DATA', 'path' => 'login_logs', 'note' => 'Success, fail, and logout rows'],
                    ['method' => 'BELL', 'path' => 'Login audit', 'note' => 'Also pushes an in-app notification'],
                ],
            ],
            [
                'slug' => 'settings',
                'index' => '10',
                'title' => 'Settings',
                'description' => 'Profile, admin username/password, read-only SMTP from .env, and notification preferences.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/settings/profile', 'note' => 'Name, email, phone, timezone, avatar'],
                    ['method' => 'GET', 'path' => '/settings/admin', 'note' => 'Credentials + SMTP test tools'],
                    ['method' => 'GET', 'path' => '/settings/notifications', 'note' => 'Push, email, and app sounds'],
                ],
            ],
            [
                'slug' => 'security',
                'index' => '11',
                'title' => 'Security',
                'description' => 'Standard locker for saved credentials, plus High Security vault behind HIGH_SECURITY_PASSWORD.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/settings/security', 'note' => 'Standard credential vault'],
                    ['method' => 'GET', 'path' => '/settings/security-high', 'note' => 'Unlock with master password'],
                    ['method' => 'GET', 'path' => '/settings/security-credentials/{id}/password', 'note' => 'Reveal decrypted password'],
                ],
            ],
            [
                'slug' => 'pages',
                'index' => '12',
                'title' => 'Routes',
                'description' => 'Every DevOS web route: module, method, auth, status, and a one-line description.',
                'preview' => [
                    ['method' => 'GET', 'path' => '/dashboard', 'note' => 'Home after login'],
                    ['method' => 'GET', 'path' => '/tasks', 'note' => 'DailyOps'],
                    ['method' => 'GET', 'path' => '/projects', 'note' => 'Project workspace'],
                    ['method' => 'GET', 'path' => '/transaction', 'note' => 'Receivables and payables'],
                    ['method' => 'GET', 'path' => '/settings/security', 'note' => 'Security Locker'],
                ],
            ],
        ];
    }

    private function contentFor(string $slug): string
    {
        $readme = $this->fileContents(base_path('README.md'));
        $pages = $this->fileContents(base_path('docs/PAGES.md'));
        $flows = $this->fileContents(base_path('docs/FLOWS.md'));

        return match ($slug) {
            'overview' => $this->sliceUntil($readme, '## Environment variables'),
            'login' => trim(
                $this->sliceBetween($flows, '## 1. Login', '## 3. Forgot password')
            ),
            'forgot-password' => trim(
                $this->sliceBetween($flows, '## 3. Forgot password', '## 4. SMTP')
            ),
            'dashboard' => <<<'MD'
# Dashboard

`/dashboard` is the home view after login. Numbers come from live tables, not placeholders.

## What it shows

- **Wallet** — receivable, payable, and net from the transactions table for the signed-in admin
- **Task completion** — done tasks vs all tasks
- **Security Locker count** — saved standard credentials
- **High Security count** — blurred until the eye icon is clicked
- **Analog clock** — live day, date, and time
- **Read Documentation** — opens `/docs`

## Related routes

| URL | Why it is here |
|-----|----------------|
| `/dashboard` | Main home |
| `/transaction` | Full wallet + ledger |
| `/settings/security` | Open locker from the dashboard shortcut |
| `/docs` | Documentation landing |

**Code:** `DashboardController`, `resources/views/welcome.blade.php`, `resources/js/dashboard.js`
MD,
            'dailyops' => <<<'MD'
# DailyOps

Daily task workspace for the signed-in admin user.

## Pages

| URL | Page |
|-----|------|
| `/tasks` | Main DailyOps list |
| `/task-daily` | Same view, older URL |

## Task behaviour

- Status: `backlog`, `todo`, `in_progress`, `done`, `canceled`
- Priority: low, medium, high, urgent
- Create, edit, duplicate, delete one task
- Bulk status, priority, duplicate, notification, and delete
- Optional link to a project
- Focus tasks can be marked and later appear on the dashboard

**Code:** `TaskController`, `App\Models\Task`, `resources/views/task-daily.blade.php`
MD,
            'projects' => <<<'MD'
# Projects

Personal project list and a detail page for one project.

## Pages

| URL | Page |
|-----|------|
| `/projects` | List and create |
| `/projects/{id}` | Single project |
| `/project-based` | Legacy URL, redirects to `/projects` |

## Project behaviour

- Status: `planning`, `active`, `on_hold`, `completed`, `archived`
- Archive keeps the project out of the active list
- Delete removes it permanently
- DailyOps tasks can be attached to a project
- Dashboard counts active, completed, and not-started projects from this same data

**Code:** `ProjectController`, `App\Models\Project`
MD,
            'transactions' => <<<'MD'
# Transactions

Track money in and out for the signed-in admin.

## Pages

| URL | Page |
|-----|------|
| `/transaction` | Wallet hero + ledger |
| `/transactions/data` | JSON list and summary |
| `/transactions` | Create receivable or payable |
| `/cards`, `/invoice` | Placeholder UI, not fully wired |

## Wallet

- **Receivable** — money expected
- **Payable** — money owed
- **Net** — receivable minus payable

The dashboard wallet uses the same totals.

**Code:** `TransactionController`, `App\Models\Transaction`, `resources/js/transactions.js`
MD,
            'notifications' => trim(
                "## Notifications\n\n"
                ."Header bell and `/notifications` list login audit and task events.\n\n"
                .$this->sliceBetween($flows, '## 6. Notification settings', '## 7. Security Locker')
            ),
            'audit-log' => trim(
                $this->sliceBetween($flows, '## 8. What is audited today', '## 9. Documentation')
            ),
            'settings' => trim(
                $this->sliceBetween($flows, '## 4. SMTP', '## 7. Security Locker')
            ),
            'security' => trim(
                $this->sliceBetween($flows, '## 7. Security Locker', '## 8. What is audited today')
                ."\n\n"
                .$this->sliceFrom($readme, '## Security notes')
            ),
            'pages' => $pages,
            default => '# Not found',
        };
    }

    private function fileContents(string $path): string
    {
        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    private function sliceUntil(string $markdown, string $heading): string
    {
        $pos = strpos($markdown, $heading);

        return trim($pos === false ? $markdown : substr($markdown, 0, $pos));
    }

    private function sliceFrom(string $markdown, string $heading): string
    {
        $pos = strpos($markdown, $heading);

        return trim($pos === false ? '' : substr($markdown, $pos));
    }

    private function sliceBetween(string $markdown, string $start, string $end): string
    {
        $from = strpos($markdown, $start);
        if ($from === false) {
            return '';
        }

        $to = strpos($markdown, $end, $from + strlen($start));

        return trim($to === false ? substr($markdown, $from) : substr($markdown, $from, $to - $from));
    }
}
