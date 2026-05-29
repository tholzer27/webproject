<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Checkout;
use Stripe\Checkout\Session;

class StripeInvoiceCheckoutService
{
    public function create(Invoice $invoice): Session
    {
        if (blank(config('cashier.secret'))) {
            throw ValidationException::withMessages([
                'stripe' => 'Stripe ist noch nicht konfiguriert. Bitte STRIPE_SECRET in der .env setzen und den Config-Cache leeren.',
            ]);
        }

        $invoice->loadMissing(['customer', 'items']);

        if (! $invoice->customer->portal_token) {
            $invoice->customer->forceFill(['portal_token' => str()->random(40)])->save();
        }

        $sessionOptions = [
            'client_reference_id' => (string) $invoice->id,
            'metadata' => [
                'invoice_id' => (string) $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'customer_id' => (string) $invoice->customer_id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'invoice_id' => (string) $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'customer_id' => (string) $invoice->customer_id,
                ],
            ],
            'success_url' => route('client.invoices.success', [$invoice->customer->portal_token, $invoice], absolute: true).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('client.portal', $invoice->customer->portal_token, absolute: true),
        ];

        if ($invoice->customer->email) {
            $sessionOptions['customer_email'] = $invoice->customer->email;
        }

        $checkout = Checkout::guest()->create([
            [
                'price_data' => [
                    'currency' => strtolower(config('services.stripe.currency', 'chf')),
                    'product_data' => [
                        'name' => 'Rechnung '.$invoice->invoice_no,
                        'description' => $invoice->items->pluck('title')->join(', '),
                    ],
                    'unit_amount' => (int) round((float) $invoice->total_amount * 100),
                ],
                'quantity' => 1,
            ],
        ], $sessionOptions)->asStripeCheckoutSession();

        $invoice->forceFill([
            'stripe_checkout_session_id' => $checkout->id,
            'stripe_checkout_url' => $checkout->url,
            'stripe_checkout_expires_at' => isset($checkout->expires_at) ? Carbon::createFromTimestamp($checkout->expires_at) : null,
        ])->save();

        return $checkout;
    }
}
