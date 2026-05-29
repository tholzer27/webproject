<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('Finance/Transactions', [
            'transactions' => Transaction::with(['account', 'category'])
                ->whereHas('account', fn ($query) => $query->where('user_id', $user->id))
                ->latest('transaction_date')
                ->get(),
            'accounts' => $user->accounts()->get(),
            'categories' => $user->categories()->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'in:income,expense'],
            'payment_type' => ['required', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date'],
        ]);

        $account = Account::where('user_id', auth()->id())->findOrFail($data['account_id']);
        $transaction = $account->transactions()->create($data);
        $this->syncAccountBalance($transaction);

        return back();
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        abort_unless($transaction->account->user_id === auth()->id(), 403);

        $account = $transaction->account;
        $transaction->delete();
        $this->recalculateAccount($account);

        return back();
    }

    private function syncAccountBalance(Transaction $transaction): void
    {
        $account = $transaction->account;
        $account->increment('balance', $transaction->type === 'income' ? $transaction->amount : -$transaction->amount);
    }

    private function recalculateAccount(Account $account): void
    {
        $income = $account->transactions()->where('type', 'income')->sum('amount');
        $expenses = $account->transactions()->where('type', 'expense')->sum('amount');

        $account->update(['balance' => $income - $expenses]);
    }
}
