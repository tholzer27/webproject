<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_no',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'due_date',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_checkout_url',
        'stripe_checkout_expires_at',
        'paid_at',
        'income_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'stripe_checkout_expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function markAsPaid(?string $paymentIntentId = null): void
    {
        $this->forceFill([
            'status' => 'paid',
            'stripe_payment_intent_id' => $paymentIntentId ?? $this->stripe_payment_intent_id,
            'paid_at' => $this->paid_at ?? now(),
        ])->save();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function incomeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'income_transaction_id');
    }
}
