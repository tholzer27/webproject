<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceAccountingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Cashier;

class CustomerPortalController extends Controller
{
    public function show(Customer $customer): Response
    {
        $customer->load(['invoices.items' => fn ($query) => $query->latest()]);

        return Inertia::render('Finance/CustomerPortal', [
            'customer' => $customer,
            'invoices' => $customer->invoices()
                ->with('items')
                ->latest('due_date')
                ->get(),
            'paymentStatus' => request('payment'),
        ]);
    }

    public function admin(Customer $customer): RedirectResponse
    {
        abort_unless($customer->user_id === auth()->id(), 403);

        if (! $customer->portal_token) {
            $customer->forceFill(['portal_token' => str()->random(40)])->save();
        }

        return to_route('client.portal', $customer->portal_token);
    }

    public function success(Customer $customer, Invoice $invoice, InvoiceAccountingService $accounting): RedirectResponse
    {
        abort_unless($invoice->customer_id === $customer->id, 404);

        $sessionId = request('session_id');

        if ($sessionId && $sessionId === $invoice->stripe_checkout_session_id) {
            $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $accounting->markAsPaid($invoice, is_string($session->payment_intent) ? $session->payment_intent : null, 'Stripe');
            }
        }

        return redirect()->route('client.portal', $customer->portal_token, 303);
    }
}
