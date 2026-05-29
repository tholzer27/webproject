<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

class FinanceDashboardService
{
    public function forUser(User $user): array
    {
        $accountIds = $user->accounts()->pluck('id');
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $income = Transaction::whereIn('account_id', $accountIds)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $expenses = Transaction::whereIn('account_id', $accountIds)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $openInvoices = Invoice::whereHas('customer', fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('status', ['open', 'overdue'])
            ->sum('total_amount');

        return [
            'summary' => [
                'balance' => $user->accounts()->sum('balance'),
                'income' => $income,
                'expenses' => $expenses,
                'open_invoices' => $openInvoices,
            ],
            'monthly' => $this->monthlySeries($accountIds->all()),
            'categories' => $this->categoryBreakdown($accountIds->all()),
            'transactions' => Transaction::with(['account', 'category'])
                ->whereIn('account_id', $accountIds)
                ->latest('transaction_date')
                ->limit(5)
                ->get(),
            'invoices' => Invoice::with('customer')
                ->whereHas('customer', fn ($query) => $query->where('user_id', $user->id))
                ->whereIn('status', ['open', 'overdue'])
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get(),
        ];
    }

    private function monthlySeries(array $accountIds): array
    {
        return collect(range(5, 0))->map(function (int $monthsAgo) use ($accountIds) {
            $date = now()->subMonths($monthsAgo);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();

            return [
                'label' => $date->format('M'),
                'income' => (float) Transaction::whereIn('account_id', $accountIds)->where('type', 'income')->whereBetween('transaction_date', [$start, $end])->sum('amount'),
                'expenses' => (float) Transaction::whereIn('account_id', $accountIds)->where('type', 'expense')->whereBetween('transaction_date', [$start, $end])->sum('amount'),
            ];
        })->all();
    }

    private function categoryBreakdown(array $accountIds): array
    {
        return Transaction::query()
            ->selectRaw('categories.name, categories.color, SUM(transactions.amount) as total')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->whereIn('transactions.account_id', $accountIds)
            ->where('transactions.type', 'expense')
            ->where('transaction_date', '>=', Carbon::now()->subMonths(3))
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->all();
    }
}
