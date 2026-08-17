# DevOS — Pages & Routes

Overview of every web route in DevOS.

## How to read this doc

- **Auth = Yes** → requires admin session (`admin.auth` middleware)
- **Status = placeholder** → UI exists but backend not fully built
- **Status = new** → route exists but missing entry in `config/docs.php`

## Quick map (main pages)

| URL | Page |
|-----|------|
| `/secure-access` | Login |
| `/forgot-password` | Forgot password (OTP) |
| `/dashboard` | Dashboard home |
| `/tasks` | Daily tasks |
| `/projects` | Projects |
| `/settings/profile` | Profile settings |
| `/settings/admin` | Admin + SMTP settings |
| `/settings/security` | Security Locker |
| `/settings/security-high` | High Security Locker |
| `/transaction`, `/invoice` | Finance placeholders |

---

<!-- docs:pages:start -->
### Route index (auto-generated)

| Module | Methods | URI | Route name | Page | Auth | Status | Description |
|--------|---------|-----|------------|------|------|--------|-------------|
| Audit | GET | `/audit-log` | `audit-log.index` | Audit Log | Yes | active | Login, logout, and failed attempt history from login_logs table. |
| Auth | GET | `/forgot-password` | `password.forgot` | Forgot Password | No | active | Email → OTP → reset password or go to dashboard. |
| Auth | POST | `/forgot-password/dashboard` | `password.forgot.dashboard` | Dashboard Login (API) | No | active | Logs in without password change after OTP verify. |
| Auth | POST | `/forgot-password/reset` | `password.forgot.reset` | Reset Password (API) | No | active | Sets new admin password after OTP verification. |
| Auth | POST | `/forgot-password/send-otp` | `password.forgot.send` | Send OTP (API) | No | active | Sends hashed OTP email. Requires SMTP enabled. |
| Auth | POST | `/forgot-password/verify-otp` | `password.forgot.verify` | Verify OTP (API) | No | active | Validates 6-digit OTP from email. |
| Auth | POST | `/logout` | `logout` | Logout | No | active | Ends admin session and redirects to login. |
| Auth | GET | `/secure-access` | `login` | Login | No | active | Admin sign-in at /secure-access. Custom session auth (not Laravel auth()). |
| Auth | POST | `/secure-access` | `—` | Login Submit | No | active | POST handler for admin login form. |
| DailyOps | GET | `/task-daily` | `—` | Task Daily | Yes | active | Daily task view (legacy/alternate route). |
| DailyOps | GET | `/tasks` | `tasks.index` | Tasks | Yes | active | Daily task list with create, edit, bulk actions. |
| DailyOps | POST | `/tasks` | `tasks.store` | Create Task (API) | Yes | active | Creates a new task. |
| DailyOps | POST | `/tasks/bulk-delete` | `tasks.bulkDestroy` | Bulk Delete (API) | Yes | active | Deletes multiple tasks. |
| DailyOps | POST | `/tasks/bulk-duplicate` | `tasks.bulkDuplicate` | Bulk Duplicate (API) | Yes | active | Duplicates multiple tasks. |
| DailyOps | POST | `/tasks/bulk-notification` | `tasks.bulkNotification` | Bulk Notification (API) | Yes | active | Updates notification setting for multiple tasks. |
| DailyOps | POST | `/tasks/bulk-priority` | `tasks.bulkPriority` | Bulk Priority (API) | Yes | active | Updates priority for multiple tasks. |
| DailyOps | PATCH | `/tasks/bulk-status` | `tasks.bulkStatus` | Bulk Status (API) | Yes | active | Updates status for multiple tasks. |
| DailyOps | GET | `/tasks/{id}` | `tasks.show` | Task Detail (API) | Yes | active | Returns single task JSON. |
| DailyOps | PUT | `/tasks/{id}` | `tasks.update` | Update Task (API) | Yes | active | Updates task fields. |
| DailyOps | POST | `/tasks/{id}/delete` | `tasks.destroy` | Delete Task (API) | Yes | active | Deletes one task. |
| DailyOps | POST | `/tasks/{id}/duplicate` | `tasks.duplicate` | Duplicate Task (API) | Yes | active | Clones one task. |
| DailyOps | PATCH | `/tasks/{id}/status` | `tasks.updateStatus` | Update Status (API) | Yes | active | Changes task status only. |
| Dashboard | GET | `/dashboard` | `—` | Dashboard | Yes | active | Main home view after login. |
| Finance | GET | `/cards` | `—` | Cards | Yes | placeholder | Placeholder UI — not fully wired yet. |
| Finance | GET | `/invoice` | `—` | Invoice | Yes | placeholder | Placeholder UI — not fully wired yet. |
| Finance | GET | `/transaction` | `transactions.page` | Transactions | Yes | active | Track receivables, payables, and net balance. |
| Notifications | GET | `/notifications` | `notifications.index` | Notifications | Yes | active | In-app notification list (login audit, tasks, etc.). |
| Notifications | POST | `/notifications/read-all` | `notifications.readAll` | Mark All Read (API) | Yes | active | Marks every notification as read. |
| Notifications | GET | `/notifications/unread-count` | `notifications.unreadCount` | Unread Count (API) | Yes | active | JSON unread notification count for header badge. |
| Notifications | POST | `/notifications/{id}/read` | `notifications.read` | Mark Read (API) | Yes | active | Marks a single notification as read. |
| Other | POST | `/transactions` | `transactions.store` | — | Yes | new | Add description in `config/docs.php` (route: `transactions.store`). |
| Other | GET | `/transactions/data` | `transactions.index` | — | Yes | new | Add description in `config/docs.php` (route: `transactions.index`). |
| Projects | GET, POST, PUT, PATCH, DELETE, OPTIONS | `/project-based` | `—` | Legacy Redirect | Yes | active | Redirects old URL to /projects. |
| Projects | GET | `/projects` | `projects.page` | Projects | Yes | active | Personal project list and management UI. |
| Projects | POST | `/projects` | `projects.store` | Create Project (API) | Yes | active | Creates a new project. |
| Projects | GET | `/projects/data` | `projects.index` | Projects Data (API) | Yes | active | JSON list of projects for the page. |
| Projects | GET | `/projects/{id}` | `projects.show` | Project Page | Yes | active | Single project detail view. |
| Projects | POST | `/projects/{id}/archive` | `projects.archive` | Archive Project (API) | Yes | active | Archives a project. |
| Projects | GET | `/projects/{id}/data` | `projects.show.data` | Project Data (API) | Yes | active | JSON data for one project. |
| Projects | POST | `/projects/{id}/delete` | `projects.destroy` | Delete Project (API) | Yes | active | Permanently deletes a project. |
| Projects | POST | `/projects/{id}/update` | `projects.update` | Update Project (API) | Yes | active | Updates project fields. |
| Settings | GET | `/settings` | `—` | Settings Redirect | Yes | active | Redirects to profile settings. |
| Settings | GET | `/settings/admin` | `settings.admin` | Admin Settings | Yes | active | Username/password, SMTP toggle, read-only mail config from .env. |
| Settings | POST | `/settings/admin` | `settings.admin.update` | Update Admin (API) | Yes | active | Changes admin username and/or password. |
| Settings | GET | `/settings/notifications` | `settings.notifications` | Notification Settings | Yes | active | Bell, security OTP email, and app sound preferences. |
| Settings | POST | `/settings/notifications/sound` | `settings.notifications.sound` | Upload Notification Sound (API) | Yes | active | Stores a custom sound used for bell pings and toasts. |
| Settings | DELETE | `/settings/notifications/sound` | `settings.notifications.sound.delete` | Delete Notification Sound (API) | Yes | active | Removes the uploaded custom sound. |
| Settings | POST | `/settings/notifications/toggle` | `settings.notifications.toggle` | Toggle Notification Setting (API) | Yes | active | Enables or disables push, email, or sounds. |
| Settings | GET | `/settings/profile` | `settings.profile` | Profile | Yes | active | Admin display name, email, phone, timezone, avatar. |
| Settings | POST | `/settings/profile` | `settings.profile.update` | Update Profile (API) | Yes | active | Saves profile form changes. |
| Settings | POST | `/settings/profile/avatar` | `settings.avatar.upload` | Upload Avatar (API) | Yes | active | AJAX profile photo upload. |
| Settings | GET | `/settings/security` | `settings.security` | Security Locker | Yes | active | Stored credentials vault (standard tier). |
| Settings | POST | `/settings/security-credentials` | `settings.security.store` | Add Credential (API) | Yes | active | Stores a new credential entry. |
| Settings | PUT | `/settings/security-credentials/{id}` | `settings.security.update` | Update Credential (API) | Yes | active | Updates a credential entry. |
| Settings | DELETE | `/settings/security-credentials/{id}` | `settings.security.destroy` | Delete Credential (API) | Yes | active | Removes a credential entry. |
| Settings | GET | `/settings/security-credentials/{id}/password` | `settings.security.password` | Reveal Password (API) | Yes | active | Returns decrypted password for a credential. |
| Settings | POST | `/settings/security-credentials/{id}/pin` | `settings.security.pin` | Pin Credential (API) | Yes | active | Pins or unpins a credential. |
| Settings | GET | `/settings/security-high` | `settings.security.high` | High Security Locker | Yes | active | Extra-protected vault; requires HIGH_SECURITY_PASSWORD unlock. |
| Settings | POST | `/settings/security-high-credentials` | `settings.security.high.store` | Add High Credential (API) | Yes | active | Stores credential in high-security vault. |
| Settings | PUT | `/settings/security-high-credentials/{id}` | `settings.security.high.update` | Update High Credential (API) | Yes | active | Updates high-security credential. |
| Settings | DELETE | `/settings/security-high-credentials/{id}` | `settings.security.high.destroy` | Delete High Credential (API) | Yes | active | Deletes high-security credential. |
| Settings | GET | `/settings/security-high-credentials/{id}/password` | `settings.security.high.password` | Reveal High Password (API) | Yes | active | Returns high-security decrypted password. |
| Settings | POST | `/settings/security-high-credentials/{id}/pin` | `settings.security.high.pin` | Pin High Credential (API) | Yes | active | Pins high-security credential. |
| Settings | POST | `/settings/security-high/unlock` | `settings.security.high.unlock` | Unlock High Security (API) | Yes | active | Unlocks high-security session with master password. |
| Settings | GET | `/settings/security-list` | `settings.security.list` | Security List (API) | Yes | active | Lists saved credentials. |
| Settings | GET | `/settings/smtp` | `settings.smtp.status` | SMTP Status (API) | Yes | active | Returns whether SMTP is enabled (no secrets). |
| Settings | POST | `/settings/smtp/enabled` | `settings.smtp.toggle` | SMTP Toggle (API) | Yes | active | Enables or disables outbound email. |
| Settings | POST | `/settings/smtp/test` | `settings.smtp.test` | SMTP Test (API) | Yes | active | Tests SMTP connection. |
| Settings | POST | `/settings/smtp/test-email` | `settings.smtp.test-email` | Send Test Email (API) | Yes | active | Sends DevOS-branded OTP test email (OTP not stored in DB). |

#### Undocumented routes

Add entries to `config/docs.php` for: `transactions.store`, `transactions.index`.
<!-- docs:pages:end -->

---

## Adding a new page or route

1. Add the route in `routes/web.php`
2. Add metadata in `config/docs.php` under `routes`:

```php
'my.new.route' => [
    'module' => 'MyModule',
    'page' => 'My Page',
    'description' => 'What this route does.',
    'status' => 'active',
],
```

3. Run:

```bash
php artisan docs:sync
```

4. If the flow is non-trivial, add a section to [FLOWS.md](FLOWS.md)
