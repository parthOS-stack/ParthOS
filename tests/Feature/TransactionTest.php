<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): Admin
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('secret-pass'),
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ]);

        return $admin;
    }

    public function test_transactions_page_renders_ui(): void
    {
        $this->actingAsAdmin();

        $html = $this->get('/transaction')->assertOk()->getContent();

        $this->assertStringContainsString('Transactions', $html);
        $this->assertStringContainsString('Add Transaction', $html);
        $this->assertStringContainsString('Transaction Wallet', $html);
        $this->assertStringContainsString('Premium Overview', $html);
        $this->assertStringContainsString('dailyops-task-table', $html);
        $this->assertStringContainsString('transactionModalOverlay', $html);
    }

    public function test_can_create_and_list_transactions_with_summary(): void
    {
        $admin = $this->actingAsAdmin();

        $this->postJson('/transactions', [
            'party_name' => 'Ramesh Patel',
            'amount' => 15000,
            'type' => 'receivable',
            'category' => 'Client Payment - Consulting',
            'transaction_date' => '2023-10-24',
            'note' => 'Invoice #12',
        ])->assertCreated()->assertJsonPath('success', true);

        $this->postJson('/transactions', [
            'party_name' => 'Amazon Web Services',
            'amount' => 2400,
            'type' => 'payable',
            'category' => 'Software Subscription',
            'transaction_date' => '2023-10-24',
        ])->assertCreated();

        $response = $this->getJson('/transactions/data')->assertOk();
        $response->assertJsonPath('summary.receivable', 15000);
        $response->assertJsonPath('summary.payable', 2400);
        $response->assertJsonPath('summary.net', 12600);
        $response->assertJsonPath('summary.total_transactions', 2);
        $response->assertJsonCount(3, 'hero.cards');
        $response->assertJsonPath('hero.cards.2.brand', 'Recent');
        $response->assertJsonPath('hero.cards.2.type_label', 'Payable');
        $this->assertNotEmpty($response->json('hero.cards.2.date_label'));
        $this->assertNotEmpty($response->json('hero.cards.2.amount_label'));
        $response->assertJsonCount(2, 'transactions');

        $this->getJson('/transactions/data?filter=receivable')
            ->assertOk()
            ->assertJsonCount(1, 'transactions')
            ->assertJsonPath('transactions.0.party_name', 'Ramesh Patel');

        $this->assertSame(2, Transaction::query()->where('admin_id', $admin->id)->count());
    }

    public function test_guest_cannot_access_transactions_api(): void
    {
        $this->get('/transaction')->assertRedirect('/secure-access');
        $this->getJson('/transactions/data')->assertRedirect('/secure-access');
        $this->postJson('/transactions', [])->assertRedirect('/secure-access');
    }
}
