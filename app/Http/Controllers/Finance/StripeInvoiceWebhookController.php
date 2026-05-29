<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceAccountingService;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class StripeInvoiceWebhookController extends Controller
{
    public function __invoke(Request $request, InvoiceAccountingService $accounting): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('cashier.webhook.secret');

        try {
            $event = $secret
                ? Webhook::constructEvent($payload, $signature, $secret)
                : Cashier::stripe()->events->retrieve(json_decode($payload, true)['id']);
        } catch (SignatureVerificationException|UnexpectedValueException) {
            return response('Invalid Stripe webhook signature.', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $invoiceId = $session->metadata->invoice_id ?? null;

            if ($invoiceId && $session->payment_status === 'paid') {
                $invoice = Invoice::whereKey($invoiceId)
                    ->where('stripe_checkout_session_id', $session->id)
                    ->first();

                if ($invoice) {
                    $accounting->markAsPaid($invoice, is_string($session->payment_intent) ? $session->payment_intent : null, 'Stripe');
                }
            }
        }

        return response('Webhook handled.');
    }
}
