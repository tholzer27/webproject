<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceAccountingService
{
    public function markAsPaid(Invoice $invoice, ?string $paymentIntentId = null, string $paymentType = 'Rechnung'): Invoice
    {
        return DB::transaction(function () use ($invoice, $paymentIntentId, $paymentType) {
            $invoice = Invoice::query()->lockForUpdate()->with('customer.user')->findOrFail($invoice->id);

            $invoice->forceFill([
                'status' => 'paid',
                'stripe_payment_intent_id' => $paymentIntentId ?? $invoice->stripe_payment_intent_id,
                'paid_at' => $invoice->paid_at ?? now(),
            ])->save();

            if (! $invoice->income_transaction_id) {
                $account = $this->accountFor($invoice);
                $transaction = $account->transactions()->create([
                    'category_id' => $this->incomeCategoryIdFor($invoice),
                    'amount' => $invoice->total_amount,
                    'type' => 'income',
                    'payment_type' => $paymentType,
                    'title' => 'Zahlung '.$invoice->invoice_no,
                    'description' => 'Automatisch verbuchte Einnahme aus Rechnung '.$invoice->invoice_no,
                    'transaction_date' => $invoice->paid_at?->toDateString() ?? now()->toDateString(),
                ]);

                $account->increment('balance', $transaction->amount);
                $invoice->forceFill(['income_transaction_id' => $transaction->id])->save();
            }

            return $invoice;
        });
    }

    public function markAsUnpaid(Invoice $invoice, string $status): Invoice
    {
        return DB::transaction(function () use ($invoice, $status) {
            $invoice = Invoice::query()->lockForUpdate()->with('incomeTransaction.account')->findOrFail($invoice->id);

            if ($invoice->incomeTransaction) {
                $account = $invoice->incomeTransaction->account;
                $invoice->incomeTransaction->delete();
                $this->recalculateAccount($account);
            }

            $invoice->forceFill([
                'status' => $status,
                'paid_at' => null,
                'income_transaction_id' => null,
            ])->save();

            return $invoice;
        });
    }

    private function accountFor(Invoice $invoice): Account
    {
        return $invoice->customer->user->accounts()->orderBy('id')->firstOrFail();
    }

    private function incomeCategoryIdFor(Invoice $invoice): ?int
    {
        return Category::firstOrCreate([
            'user_id' => $invoice->customer->user_id,
            'name' => 'Rechnungen',
            'type' => 'income',
        ], [
            'color' => '#0ea5e9',
            'icon' => 'ReceiptText',
        ])->id;
    }

    private function recalculateAccount(Account $account): void
    {
        $income = $account->transactions()->where('type', 'income')->sum('amount');
        $expenses = $account->transactions()->where('type', 'expense')->sum('amount');

        $account->update(['balance' => $income - $expenses]);
    }
}
