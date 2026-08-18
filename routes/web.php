<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\ForgotPasswordController;

// Authentication Pages
Route::get('/secure-access', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/secure-access', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.forgot');
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('password.forgot.send');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.forgot.verify');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.forgot.reset');
Route::post('/forgot-password/dashboard', [ForgotPasswordController::class, 'dashboard'])->name('password.forgot.dashboard');

Route::redirect('/', '/dashboard');

// Protected Dashboard Routes
Route::middleware(['admin.auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
    Route::get('/docs', [\App\Http\Controllers\DocsController::class, 'index'])->name('docs.index');
    Route::get('/docs/{section}', [\App\Http\Controllers\DocsController::class, 'show'])->name('docs.show');

    Route::get('/audit-log', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-log.index');

    Route::get('/notifications', [\App\Http\Controllers\AppNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\AppNotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('/notifications/read-all', [\App\Http\Controllers\AppNotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\AppNotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/task-daily', function () {
        return view('task-daily');
    });
    
    // DailyOps Tasks
    Route::get('/tasks', [\App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/bulk-status', [\App\Http\Controllers\TaskController::class, 'bulkUpdateStatus'])->name('tasks.bulkStatus');
    Route::post('/tasks/bulk-priority', [\App\Http\Controllers\TaskController::class, 'bulkUpdatePriority'])->name('tasks.bulkPriority');
    Route::post('/tasks/bulk-duplicate', [\App\Http\Controllers\TaskController::class, 'bulkDuplicate'])->name('tasks.bulkDuplicate');
    Route::post('/tasks/bulk-notification', [\App\Http\Controllers\TaskController::class, 'bulkUpdateNotification'])->name('tasks.bulkNotification');
    Route::post('/tasks/bulk-delete', [\App\Http\Controllers\TaskController::class, 'bulkDestroy'])->name('tasks.bulkDestroy');
    Route::get('/tasks/{id}', [\App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{id}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{id}/status', [\App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::post('/tasks/{id}/duplicate', [\App\Http\Controllers\TaskController::class, 'duplicate'])->name('tasks.duplicate');
    Route::post('/tasks/{id}/delete', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');

    // Projects (personal)
    Route::get('/projects', [\App\Http\Controllers\ProjectController::class, 'page'])->name('projects.page');
    Route::get('/projects/data', [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [\App\Http\Controllers\ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{id}', [\App\Http\Controllers\ProjectController::class, 'showPage'])->name('projects.show');
    Route::get('/projects/{id}/data', [\App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show.data');
    Route::post('/projects/{id}/update', [\App\Http\Controllers\ProjectController::class, 'update'])->name('projects.update');
    Route::post('/projects/{id}/archive', [\App\Http\Controllers\ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('/projects/{id}/delete', [\App\Http\Controllers\ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::redirect('/project-based', '/projects');
    Route::get('/settings', function () {
        return redirect()->route('settings.profile');
    });
    Route::get('/settings/profile', [\App\Http\Controllers\SettingsController::class, 'profile'])->name('settings.profile');
    Route::post('/settings/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::post('/settings/profile/avatar', [\App\Http\Controllers\SettingsController::class, 'updateAvatarAjax'])->name('settings.avatar.upload');
    Route::get('/settings/admin', [\App\Http\Controllers\SettingsController::class, 'admin'])->name('settings.admin');
    Route::post('/settings/admin', [\App\Http\Controllers\SettingsController::class, 'updateAdmin'])->name('settings.admin.update');
    Route::get('/settings/smtp', [\App\Http\Controllers\SettingsController::class, 'smtpStatus'])->name('settings.smtp.status');
    Route::post('/settings/smtp/enabled', [\App\Http\Controllers\SettingsController::class, 'smtpToggle'])->name('settings.smtp.toggle');
    Route::post('/settings/smtp/test', [\App\Http\Controllers\SettingsController::class, 'smtpTest'])->name('settings.smtp.test');
    Route::post('/settings/smtp/test-email', [\App\Http\Controllers\SettingsController::class, 'smtpSendTest'])->name('settings.smtp.test-email');
    Route::get('/settings/notifications', [\App\Http\Controllers\SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::post('/settings/notifications/toggle', [\App\Http\Controllers\SettingsController::class, 'notificationToggle'])->name('settings.notifications.toggle');
    Route::post('/settings/notifications/sound', [\App\Http\Controllers\SettingsController::class, 'notificationSoundUpload'])->name('settings.notifications.sound');
    Route::delete('/settings/notifications/sound', [\App\Http\Controllers\SettingsController::class, 'notificationSoundDelete'])->name('settings.notifications.sound.delete');
    Route::get('/settings/security', [\App\Http\Controllers\SettingsController::class, 'security'])->name('settings.security');
    
    // Security Locker Features
    Route::get('/settings/security-list', [\App\Http\Controllers\SecurityLockerController::class, 'index'])->name('settings.security.list');
    Route::post('/settings/security-credentials', [\App\Http\Controllers\SecurityLockerController::class, 'store'])->name('settings.security.store');
    Route::put('/settings/security-credentials/{id}', [\App\Http\Controllers\SecurityLockerController::class, 'update'])->name('settings.security.update');
    Route::delete('/settings/security-credentials/{id}', [\App\Http\Controllers\SecurityLockerController::class, 'destroy'])->name('settings.security.destroy');
    Route::post('/settings/security-credentials/{id}/pin', [\App\Http\Controllers\SecurityLockerController::class, 'togglePin'])->name('settings.security.pin');
    Route::get('/settings/security-credentials/{id}/password', [\App\Http\Controllers\SecurityLockerController::class, 'getPassword'])->name('settings.security.password');
    
    // High Security Features
    Route::get('/settings/security-high', [\App\Http\Controllers\SecurityLockerController::class, 'highIndex'])->name('settings.security.high');
    Route::post('/settings/security-high/unlock', [\App\Http\Controllers\SecurityLockerController::class, 'unlockHighSecurity'])->name('settings.security.high.unlock');
    Route::post('/settings/security-high-credentials', [\App\Http\Controllers\SecurityLockerController::class, 'storeHigh'])->name('settings.security.high.store');
    Route::put('/settings/security-high-credentials/{id}', [\App\Http\Controllers\SecurityLockerController::class, 'updateHigh'])->name('settings.security.high.update');
    Route::delete('/settings/security-high-credentials/{id}', [\App\Http\Controllers\SecurityLockerController::class, 'destroyHigh'])->name('settings.security.high.destroy');
    Route::post('/settings/security-high-credentials/{id}/pin', [\App\Http\Controllers\SecurityLockerController::class, 'togglePinHigh'])->name('settings.security.high.pin');
    Route::get('/settings/security-high-credentials/{id}/password', [\App\Http\Controllers\SecurityLockerController::class, 'getPasswordHigh'])->name('settings.security.high.password');

    Route::get('/cards', function () {
        return view('cards');
    });
    Route::get('/transaction', [\App\Http\Controllers\TransactionController::class, 'page'])->name('transactions.page');
    Route::get('/transactions/data', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [\App\Http\Controllers\TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/invoice', function () {
        return view('invoice');
    });
});

