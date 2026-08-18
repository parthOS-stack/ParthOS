# DevOS — Application Flows

How the important parts of DevOS work. Constants in the **Auto-synced** sections are refreshed by `php artisan docs:sync`.

<!-- docs:sync-time:start -->
Last auto-sync: **Aug 18, 2026 10:52 AM UTC**
<!-- docs:sync-time:end -->

---

## 1. Login (`/secure-access`)

```
User → POST username + password
     → Admin::findByUsername() (case-insensitive)
     → Admin::verifyPassword()
     → session: admin_logged_in, admin_id
     → redirect /dashboard
```

**Session keys:** `admin_logged_in`, `admin_id`  
**Middleware:** `admin.auth` protects all dashboard routes  
**Audit:** Each attempt written to `login_logs`; success/fail also pushes in-app notification ("Login audit")

---

## 2. Login lockout (auto-synced)

<!-- docs:lockout:start -->
- Reset password prompt after: **3** failed login attempts
- Account lock after: **6** failed login attempts
- Lock duration: **24 hours**
- Successful login or password reset clears lock and attempt counter
<!-- docs:lockout:end -->

### UI behaviour

| Attempts | What the user sees |
|----------|-------------------|
| 1–2 | "Invalid credentials" + attempts remaining |
| 3–5 | Amber banner + **Reset Password** button |
| 6+ | Red "Account blocked for 24 hours" banner; Sign In disabled |

Locked accounts cannot sign in even with the correct password. Use **Forgot Password** (OTP) to recover immediately.

**Code:** `App\Http\Controllers\AdminAuthController`

---

## 3. Forgot password (`/forgot-password`)

```
Step 1  Email (default: admin email or MAIL_FROM_ADDRESS)
Step 2  Send OTP → email via SmtpService (requires SMTP to be fully configured)
Step 3  Verify 6-digit OTP
Step 4  Modal: Reset Password  OR  Go to Dashboard
Step 5a Reset → new password → redirect login (clears lockout)
Step 5b Dashboard → session login without password change
```

### OTP rules (auto-synced)

<!-- docs:otp:start -->
- OTP expiry: **10 minutes**
- Max wrong OTP tries: **5**
- Post-verify session: **15 minutes**
- OTP stored **hashed** in `password_reset_otps` (not plain text)
<!-- docs:otp:end -->

**Code:** `ForgotPasswordService`, `ForgotPasswordController`

---

## 4. SMTP

| What | Where |
|------|-------|
| Host, port, username, password | `.env` only |
| Test connection | Admin Settings → Test SMTP |
| Test email | Sends DevOS-branded OTP template (OTP **not** stored in DB) |

Forgot password and test emails require SMTP to be fully configured in environment variables.

**Code:** `SmtpService`, `SettingsController`

---

## 5. Admin settings (`/settings/admin`)

- **Username / password** — uses session `admin_id` (not first DB row)
- **Current password** required only when setting a new password
- **SMTP section** — read-only `.env` fields + test actions
- **Audit Log** — Open Audit Log button links to `/audit-log`

---

## 6. Notification settings (`/settings/notifications`)

| Toggle | When ON | When OFF |
|--------|---------|----------|
| **Push Notifications** | Header bell shows in-app alerts | Bell hidden; new in-app alerts are not stored |
| **Email Notifications** | General email notifications can send when configured | General email notifications are blocked |
| **App Sounds** | Upload a custom sound; it plays for new bell pings and success/error toasts | Silent; upload panel hidden |

Custom sound (MP3 / WAV / OGG / M4A, max 2 MB) plays **only while App Sounds is enabled**. If no file is uploaded, a default ping is used.

**Code:** `NotificationSettingsService`, `SettingsController`

---

## 7. Security Locker

**Standard** (`/settings/security`) — store and reveal credentials  
**High security** (`/settings/security-high`) — requires `HIGH_SECURITY_PASSWORD` unlock first

---

## 8. What is audited today

| Event | Storage | View in app |
|-------|---------|-------------|
| Login success / fail | `login_logs` table | **Sidebar → Audit Log** (`/audit-log`) |
| Logout | `login_logs` table | **Sidebar → Audit Log** |
| Login / logout notifications | In-app notifications | Header bell |
| SMTP toggle, settings changes | Not audited | — |

**Code:** `AuditLogController`, `LoginLog` model, `AdminAuthController::logAttempt()`

---

## 9. Documentation (`/docs`)

`/docs` is the only documentation page. Twelve vertical panels sit in a row. Overview starts open with a watermark and blue border. Hovering another panel closes the open one and expands the hovered section in place — there is no inner article page.

| Panel | Source |
|-------|--------|
| Overview | README intro and quick start |
| Login | Login + lockout flow |
| Password | OTP recovery flow |
| Dashboard | Live home snapshot |
| DailyOps | Tasks workspace |
| Projects | Project workspace |
| Transactions | Wallet and ledger |
| Notifications | Bell, list, and notification settings |
| Audit Log | `login_logs` history |
| Settings | Profile, admin, SMTP |
| Security | Standard + High Security lockers |
| Routes | `docs/PAGES.md` route table |

`/docs/{section}` redirects back to `/docs`.

---

## Updating this file

- **Auto:** `php artisan docs:sync` refreshes lockout and OTP constant blocks
- **Manual:** Edit flow descriptions here when behaviour changes
- **New routes:** Add entry to `config/docs.php`, then run `docs:sync`
