<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    private function adminId(): int
    {
        $adminId = (int) session('admin_id');
        if ($adminId <= 0) {
            abort(401);
        }

        return $adminId;
    }

    public function page()
    {
        return view('transaction');
    }

    public function index(Request $request)
    {
        $adminId = $this->adminId();
        $filter = (string) $request->query('filter', 'all');

        $query = Transaction::query()
            ->forAdmin($adminId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if (in_array($filter, [Transaction::TYPE_RECEIVABLE, Transaction::TYPE_PAYABLE], true)) {
            $query->where('type', $filter);
        }

        $transactions = $query->get();

        $receivable = (float) Transaction::query()
            ->forAdmin($adminId)
            ->where('type', Transaction::TYPE_RECEIVABLE)
            ->sum('amount');

        $payable = (float) Transaction::query()
            ->forAdmin($adminId)
            ->where('type', Transaction::TYPE_PAYABLE)
            ->sum('amount');

        $recent = Transaction::query()
            ->forAdmin($adminId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $largestReceivable = Transaction::query()
            ->forAdmin($adminId)
            ->where('type', Transaction::TYPE_RECEIVABLE)
            ->orderByDesc('amount')
            ->orderByDesc('id')
            ->first();

        $largestPayable = Transaction::query()
            ->forAdmin($adminId)
            ->where('type', Transaction::TYPE_PAYABLE)
            ->orderByDesc('amount')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'summary' => [
                'receivable' => $receivable,
                'payable' => $payable,
                'net' => $receivable - $payable,
                'total_transactions' => $transactions->count(),
            ],
            'hero' => [
                'cards' => $this->heroCards($receivable, $payable, $recent, $largestReceivable, $largestPayable),
                'balance_label' => $receivable - $payable >= 0 ? 'Net Balance' : 'Net Outflow',
                'recent_activity' => $recent->map(fn (Transaction $tx) => $this->format($tx))->values(),
            ],
            'transactions' => $transactions->map(fn (Transaction $tx) => $this->format($tx))->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_name' => 'required|string|max:120',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'type' => ['required', Rule::in([Transaction::TYPE_RECEIVABLE, Transaction::TYPE_PAYABLE])],
            'category' => 'nullable|string|max:120',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $transaction = Transaction::query()->create([
            'admin_id' => $this->adminId(),
            'party_name' => trim($validated['party_name']),
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'category' => isset($validated['category']) ? trim((string) $validated['category']) : null,
            'transaction_date' => $validated['transaction_date'],
            'note' => isset($validated['note']) ? trim((string) $validated['note']) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction saved successfully.',
            'transaction' => $this->format($transaction),
        ], 201);
    }

    private function format(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'key' => 'TX-' . str_pad((string) $transaction->id, 4, '0', STR_PAD_LEFT),
            'party_name' => $transaction->party_name,
            'initials' => $transaction->initials(),
            'amount' => (float) $transaction->amount,
            'type' => $transaction->type,
            'category' => $transaction->category,
            'transaction_date' => $transaction->transaction_date?->format('Y-m-d'),
            'transaction_date_label' => $transaction->transaction_date?->format('d M Y'),
            'note' => $transaction->note,
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, Transaction> $recent
     * @return array<int, array<string, mixed>>
     */
    private function heroCards(
        float $receivable,
        float $payable,
        $recent,
        ?Transaction $largestReceivable,
        ?Transaction $largestPayable
    ): array {
        $variants = ['stripe', 'wise', 'paypal'];
        $latest = $recent->first();

        $cards = [
            [
                'variant' => $variants[0],
                'brand' => 'Receivable',
                'label' => 'Top client',
                'value' => $largestReceivable?->party_name ?: 'No receivables yet',
                'masked' => $this->maskToken('RCV', $largestReceivable?->id),
                'visible' => $this->tokenNumber('RCV', $largestReceivable?->id, $receivable),
                'amount' => $receivable,
            ],
            [
                'variant' => $variants[1],
                'brand' => 'Payable',
                'label' => 'Top payout',
                'value' => $largestPayable?->party_name ?: 'No payables yet',
                'masked' => $this->maskToken('PAY', $largestPayable?->id),
                'visible' => $this->tokenNumber('PAY', $largestPayable?->id, $payable),
                'amount' => $payable,
            ],
            [
                'variant' => $variants[2],
                'brand' => 'Recent',
                'label' => 'Latest entry',
                'value' => $latest?->party_name ?: 'No transactions yet',
                'masked' => $this->maskToken('TX', $latest?->id),
                'visible' => $this->tokenNumber('TX', $latest?->id, (float) ($latest?->amount ?? 0)),
                'amount' => (float) ($latest?->amount ?? 0),
                'type' => $latest?->type,
                'type_label' => $latest
                    ? ($latest->type === Transaction::TYPE_RECEIVABLE ? 'Receivable' : 'Payable')
                    : '—',
                'date_label' => $latest?->transaction_date?->format('d M Y') ?: '—',
            ],
        ];

        return array_map(function (array $card) {
            $card['amount_label'] = $card['amount'] > 0 ? $this->formatCurrency($card['amount']) : 'No amount';
            return $card;
        }, $cards);
    }

    private function maskToken(string $prefix, ?int $id): string
    {
        $tail = str_pad((string) ($id ?? 0), 4, '0', STR_PAD_LEFT);

        return sprintf('%s •••• %s', $prefix, $tail);
    }

    private function tokenNumber(string $prefix, ?int $id, float $amount): string
    {
        $idPart = str_pad((string) ($id ?? 0), 4, '0', STR_PAD_LEFT);
        $amountPart = str_pad((string) round(abs($amount)), 4, '0', STR_PAD_LEFT);

        return sprintf('%s %s %s', $prefix, substr($idPart, 0, 2) . substr($amountPart, 0, 2), substr($idPart, 2) . substr($amountPart, 2));
    }

    private function formatCurrency(float $value): string
    {
        return '₹' . number_format($value, 0, '.', ',');
    }
}
