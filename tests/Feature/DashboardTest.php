<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LoginLog;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_live_premium_sections(): void
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('secret-pass'),
        ]);

        User::query()->create([
            'id' => $admin->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret-pass'),
        ]);

        Task::query()->create([
            'user_id' => $admin->id,
            'task_key' => 'TASK-0001',
            'title' => 'Review deployment board',
            'status' => Task::STATUS_DONE,
            'priority' => Task::PRIORITY_HIGH,
            'focus_task' => true,
        ]);

        Transaction::query()->create([
            'admin_id' => $admin->id,
            'party_name' => 'Acme Client',
            'amount' => 25000,
            'type' => Transaction::TYPE_RECEIVABLE,
            'transaction_date' => now()->toDateString(),
        ]);

        LoginLog::query()->create([
            'username' => 'DevOS_admin',
            'status' => 'success',
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ]);

        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Today’s live snapshot', $html);
        $this->assertStringContainsString('Task Progress', $html);
        $this->assertStringContainsString('dashboard-realistic-clock', $html);
        $this->assertStringContainsString("Today's Schedule", $html);
        $this->assertStringContainsString('Read Documentation', $html);
        $this->assertStringContainsString('dashboard-privacy-eye', $html);
    }
}
