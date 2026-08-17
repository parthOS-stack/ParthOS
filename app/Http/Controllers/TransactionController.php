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

        return response()->json([
            'success' => true,
            'summary' => [
                'receivable' => $receivable,
                'payable' => $payable,
                'net' => $receivable - $payable,
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
}
