<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\StripeInvoiceCheckoutService;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class InvoiceCheckoutController extends Controller
{
    public function admin(Invoice $invoice, StripeInvoiceCheckoutService $checkout): Response
    {
        abort_unless($invoice->customer->user_id === auth()->id(), 403);
        abort_if($invoice->status === 'paid', 422, 'Diese Rechnung ist bereits bezahlt.');

        $session = $checkout->create($invoice);

        return Inertia::location($session->url);
    }

    public function customer(Customer $customer, Invoice $invoice, StripeInvoiceCheckoutService $checkout): Response
    {
        abort_unless($invoice->customer_id === $customer->id, 404);
        abort_if($invoice->status === 'paid', 422, 'Diese Rechnung ist bereits bezahlt.');

        $session = $checkout->create($invoice);

        return Inertia::location($session->url);
    }
}
