<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route documentation metadata
    |--------------------------------------------------------------------------
    |
    | Used by `php artisan docs:sync` to build docs/PAGES.md.
    | When you add a new route, add an entry here with route name as key.
    |
    */
    'routes' => [
        'login' => [
            'module' => 'Auth',
            'page' => 'Login',
            'description' => 'Admin sign-in at /secure-access. Custom session auth (not Laravel auth()).',
            'status' => 'active',
        ],
        'password.forgot' => [
            'module' => 'Auth',
            'page' => 'Forgot Password',
            'description' => 'Email → OTP → reset password or go to dashboard.',
            'status' => 'active',
        ],
        'password.forgot.send' => [
            'module' => 'Auth',
            'page' => 'Send OTP (API)',
            'description' => 'Sends hashed OTP email. Requires SMTP enabled.',
            'status' => 'active',
        ],
        'password.forgot.verify' => [
            'module' => 'Auth',
            'page' => 'Verify OTP (API)',
            'description' => 'Validates 6-digit OTP from email.',
            'status' => 'active',
        ],
        'password.forgot.reset' => [
            'module' => 'Auth',
            'page' => 'Reset Password (API)',
            'description' => 'Sets new admin password after OTP verification.',
            'status' => 'active',
        ],
        'password.forgot.dashboard' => [
            'module' => 'Auth',
            'page' => 'Dashboard Login (API)',
            'description' => 'Logs in without password change after OTP verify.',
            'status' => 'active',
        ],
        'logout' => [
            'module' => 'Auth',
            'page' => 'Logout',
            'description' => 'Ends admin session and redirects to login.',
            'status' => 'active',
        ],

        'notifications.index' => [
            'module' => 'Notifications',
            'page' => 'Notifications',
            'description' => 'In-app notification list (login audit, tasks, etc.).',
            'status' => 'active',
        ],
        'notifications.unreadCount' => [
            'module' => 'Notifications',
            'page' => 'Unread Count (API)',
            'description' => 'JSON unread notification count for header badge.',
            'status' => 'active',
        ],
        'notifications.readAll' => [
            'module' => 'Notifications',
            'page' => 'Mark All Read (API)',
            'description' => 'Marks every notification as read.',
            'status' => 'active',
        ],
        'notifications.read' => [
            'module' => 'Notifications',
            'page' => 'Mark Read (API)',
            'description' => 'Marks a single notification as read.',
            'status' => 'active',
        ],

        'audit-log.index' => [
            'module' => 'Audit',
            'page' => 'Audit Log',
            'description' => 'Login, logout, and failed attempt history from login_logs table.',
            'status' => 'active',
        ],

        'docs.index' => [
            'module' => 'Dashboard',
            'page' => 'Documentation',
            'description' => 'Single /docs landing: 12 hover panels with watermark expand. No inner article pages.',
            'status' => 'active',
        ],
        'docs.show' => [
            'module' => 'Dashboard',
            'page' => 'Documentation Section',
            'description' => 'Legacy section URL; redirects to the /docs landing accordion.',
            'status' => 'active',
        ],

        'tasks.index' => [
            'module' => 'DailyOps',
            'page' => 'Tasks',
            'description' => 'Daily task list with create, edit, bulk actions.',
            'status' => 'active',
        ],
        'tasks.store' => [
            'module' => 'DailyOps',
            'page' => 'Create Task (API)',
            'description' => 'Creates a new task.',
            'status' => 'active',
        ],
        'tasks.show' => [
            'module' => 'DailyOps',
            'page' => 'Task Detail (API)',
            'description' => 'Returns single task JSON.',
            'status' => 'active',
        ],
        'tasks.update' => [
            'module' => 'DailyOps',
            'page' => 'Update Task (API)',
            'description' => 'Updates task fields.',
            'status' => 'active',
        ],
        'tasks.updateStatus' => [
            'module' => 'DailyOps',
            'page' => 'Update Status (API)',
            'description' => 'Changes task status only.',
            'status' => 'active',
        ],
        'tasks.duplicate' => [
            'module' => 'DailyOps',
            'page' => 'Duplicate Task (API)',
            'description' => 'Clones one task.',
            'status' => 'active',
        ],
        'tasks.destroy' => [
            'module' => 'DailyOps',
            'page' => 'Delete Task (API)',
            'description' => 'Deletes one task.',
            'status' => 'active',
        ],
        'tasks.bulkStatus' => [
            'module' => 'DailyOps',
            'page' => 'Bulk Status (API)',
            'description' => 'Updates status for multiple tasks.',
            'status' => 'active',
        ],
        'tasks.bulkPriority' => [
            'module' => 'DailyOps',
            'page' => 'Bulk Priority (API)',
            'description' => 'Updates priority for multiple tasks.',
            'status' => 'active',
        ],
        'tasks.bulkDuplicate' => [
            'module' => 'DailyOps',
            'page' => 'Bulk Duplicate (API)',
            'description' => 'Duplicates multiple tasks.',
            'status' => 'active',
        ],
        'tasks.bulkNotification' => [
            'module' => 'DailyOps',
            'page' => 'Bulk Notification (API)',
            'description' => 'Updates notification setting for multiple tasks.',
            'status' => 'active',
        ],
        'tasks.bulkDestroy' => [
            'module' => 'DailyOps',
            'page' => 'Bulk Delete (API)',
            'description' => 'Deletes multiple tasks.',
            'status' => 'active',
        ],

        'projects.page' => [
            'module' => 'Projects',
            'page' => 'Projects',
            'description' => 'Personal project list and management UI.',
            'status' => 'active',
        ],
        'projects.index' => [
            'module' => 'Projects',
            'page' => 'Projects Data (API)',
            'description' => 'JSON list of projects for the page.',
            'status' => 'active',
        ],
        'projects.store' => [
            'module' => 'Projects',
            'page' => 'Create Project (API)',
            'description' => 'Creates a new project.',
            'status' => 'active',
        ],
        'projects.show' => [
            'module' => 'Projects',
            'page' => 'Project Page',
            'description' => 'Single project detail view.',
            'status' => 'active',
        ],
        'projects.show.data' => [
            'module' => 'Projects',
            'page' => 'Project Data (API)',
            'description' => 'JSON data for one project.',
            'status' => 'active',
        ],
        'projects.update' => [
            'module' => 'Projects',
            'page' => 'Update Project (API)',
            'description' => 'Updates project fields.',
            'status' => 'active',
        ],
        'projects.archive' => [
            'module' => 'Projects',
            'page' => 'Archive Project (API)',
            'description' => 'Archives a project.',
            'status' => 'active',
        ],
        'projects.destroy' => [
            'module' => 'Projects',
            'page' => 'Delete Project (API)',
            'description' => 'Permanently deletes a project.',
            'status' => 'active',
        ],

        'settings.profile' => [
            'module' => 'Settings',
            'page' => 'Profile',
            'description' => 'Admin display name, email, phone, timezone, avatar.',
            'status' => 'active',
        ],
        'settings.profile.update' => [
            'module' => 'Settings',
            'page' => 'Update Profile (API)',
            'description' => 'Saves profile form changes.',
            'status' => 'active',
        ],
        'settings.avatar.upload' => [
            'module' => 'Settings',
            'page' => 'Upload Avatar (API)',
            'description' => 'AJAX profile photo upload.',
            'status' => 'active',
        ],
        'settings.admin' => [
            'module' => 'Settings',
            'page' => 'Admin Settings',
            'description' => 'Username/password, read-only mail config from .env, and SMTP test tools.',
            'status' => 'active',
        ],
        'settings.admin.update' => [
            'module' => 'Settings',
            'page' => 'Update Admin (API)',
            'description' => 'Changes admin username and/or password.',
            'status' => 'active',
        ],
        'settings.smtp.status' => [
            'module' => 'Settings',
            'page' => 'SMTP Status (API)',
            'description' => 'Returns SMTP configuration status (no secrets).',
            'status' => 'active',
        ],
        'settings.smtp.toggle' => [
            'module' => 'Settings',
            'page' => 'SMTP Compatibility Status (API)',
            'description' => 'Returns SMTP readiness for older clients that still call the toggle endpoint.',
            'status' => 'active',
        ],
        'settings.smtp.test' => [
            'module' => 'Settings',
            'page' => 'SMTP Test (API)',
            'description' => 'Tests SMTP connection.',
            'status' => 'active',
        ],
        'settings.smtp.test-email' => [
            'module' => 'Settings',
            'page' => 'Send Test Email (API)',
            'description' => 'Sends DevOS-branded OTP test email (OTP not stored in DB).',
            'status' => 'active',
        ],
        'settings.notifications' => [
            'module' => 'Settings',
            'page' => 'Notification Settings',
            'description' => 'Bell, security OTP email, and app sound preferences.',
            'status' => 'active',
        ],
        'settings.notifications.toggle' => [
            'module' => 'Settings',
            'page' => 'Toggle Notification Setting (API)',
            'description' => 'Enables or disables push, email, or sounds.',
            'status' => 'active',
        ],
        'settings.notifications.sound' => [
            'module' => 'Settings',
            'page' => 'Upload Notification Sound (API)',
            'description' => 'Stores a custom sound used for bell pings and toasts.',
            'status' => 'active',
        ],
        'settings.notifications.sound.delete' => [
            'module' => 'Settings',
            'page' => 'Delete Notification Sound (API)',
            'description' => 'Removes the uploaded custom sound.',
            'status' => 'active',
        ],
        'settings.security' => [
            'module' => 'Settings',
            'page' => 'Security Locker',
            'description' => 'Stored credentials vault (standard tier).',
            'status' => 'active',
        ],
        'settings.security.list' => [
            'module' => 'Settings',
            'page' => 'Security List (API)',
            'description' => 'Lists saved credentials.',
            'status' => 'active',
        ],
        'settings.security.store' => [
            'module' => 'Settings',
            'page' => 'Add Credential (API)',
            'description' => 'Stores a new credential entry.',
            'status' => 'active',
        ],
        'settings.security.update' => [
            'module' => 'Settings',
            'page' => 'Update Credential (API)',
            'description' => 'Updates a credential entry.',
            'status' => 'active',
        ],
        'settings.security.destroy' => [
            'module' => 'Settings',
            'page' => 'Delete Credential (API)',
            'description' => 'Removes a credential entry.',
            'status' => 'active',
        ],
        'settings.security.pin' => [
            'module' => 'Settings',
            'page' => 'Pin Credential (API)',
            'description' => 'Pins or unpins a credential.',
            'status' => 'active',
        ],
        'settings.security.password' => [
            'module' => 'Settings',
            'page' => 'Reveal Password (API)',
            'description' => 'Returns decrypted password for a credential.',
            'status' => 'active',
        ],
        'settings.security.high' => [
            'module' => 'Settings',
            'page' => 'High Security Locker',
            'description' => 'Extra-protected vault; requires HIGH_SECURITY_PASSWORD unlock.',
            'status' => 'active',
        ],
        'settings.security.high.unlock' => [
            'module' => 'Settings',
            'page' => 'Unlock High Security (API)',
            'description' => 'Unlocks high-security session with master password.',
            'status' => 'active',
        ],
        'settings.security.high.store' => [
            'module' => 'Settings',
            'page' => 'Add High Credential (API)',
            'description' => 'Stores credential in high-security vault.',
            'status' => 'active',
        ],
        'settings.security.high.update' => [
            'module' => 'Settings',
            'page' => 'Update High Credential (API)',
            'description' => 'Updates high-security credential.',
            'status' => 'active',
        ],
        'settings.security.high.destroy' => [
            'module' => 'Settings',
            'page' => 'Delete High Credential (API)',
            'description' => 'Deletes high-security credential.',
            'status' => 'active',
        ],
        'settings.security.high.pin' => [
            'module' => 'Settings',
            'page' => 'Pin High Credential (API)',
            'description' => 'Pins high-security credential.',
            'status' => 'active',
        ],
        'settings.security.high.password' => [
            'module' => 'Settings',
            'page' => 'Reveal High Password (API)',
            'description' => 'Returns high-security decrypted password.',
            'status' => 'active',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Undocumented route fallbacks (matched by URI prefix)
    |--------------------------------------------------------------------------
    */
    'uri_fallbacks' => [
        '/secure-access' => [
            'module' => 'Auth',
            'page' => 'Login Submit',
            'description' => 'POST handler for admin login form.',
            'status' => 'active',
        ],
        '/settings' => [
            'module' => 'Settings',
            'page' => 'Settings Redirect',
            'description' => 'Redirects to profile settings.',
            'status' => 'active',
        ],
        '/project-based' => [
            'module' => 'Projects',
            'page' => 'Legacy Redirect',
            'description' => 'Redirects old URL to /projects.',
            'status' => 'active',
        ],
        '/dashboard' => [
            'module' => 'Dashboard',
            'page' => 'Dashboard',
            'description' => 'Main home view after login.',
            'status' => 'active',
        ],
        '/task-daily' => [
            'module' => 'DailyOps',
            'page' => 'Task Daily',
            'description' => 'Daily task view (legacy/alternate route).',
            'status' => 'active',
        ],
        '/cards' => [
            'module' => 'Finance',
            'page' => 'Cards',
            'description' => 'Placeholder UI — not fully wired yet.',
            'status' => 'placeholder',
        ],
        '/transaction' => [
            'module' => 'Finance',
            'page' => 'Transactions',
            'description' => 'Track receivables, payables, and net balance.',
            'status' => 'active',
        ],
        'transactions.index' => [
            'module' => 'Finance',
            'page' => 'Transactions Data (API)',
            'description' => 'JSON list and summary totals for transactions.',
            'status' => 'active',
        ],
        'transactions.store' => [
            'module' => 'Finance',
            'page' => 'Create Transaction (API)',
            'description' => 'Adds a receivable or payable transaction.',
            'status' => 'active',
        ],
        '/invoice' => [
            'module' => 'Finance',
            'page' => 'Invoice',
            'description' => 'Placeholder UI — not fully wired yet.',
            'status' => 'placeholder',
        ],
    ],

];
