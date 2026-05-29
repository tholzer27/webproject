<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceAccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('Finance/Invoices', [
            'invoices' => Invoice::with('customer')
                ->whereHas('customer', fn ($query) => $query->where('user_id', $user->id))
                ->latest('due_date')
                ->get(),
            'customers' => $user->customers()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'title' => ['required', 'string', 'max:120'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
        ]);

        $customer = Customer::where('user_id', auth()->id())->findOrFail($data['customer_id']);
        $subtotal = round($data['quantity'] * $data['unit_price'], 2);
        $tax = round($subtotal * ($data['tax_rate'] / 100), 2);

        $invoice = $customer->invoices()->create([
            'invoice_no' => 'INV-'.now()->format('Ymd').'-'.str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT),
            'status' => 'open',
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $subtotal + $tax,
            'due_date' => $data['due_date'],
        ]);

        $invoice->items()->create([
            'title' => $data['title'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'total_price' => $subtotal,
        ]);

        return back();
    }

    public function update(Request $request, Invoice $invoice, InvoiceAccountingService $accounting): RedirectResponse
    {
        abort_unless($invoice->customer->user_id === auth()->id(), 403);

        $data = $request->validate([
            'status' => ['required', 'in:open,paid,overdue,cancelled'],
        ]);

        if ($data['status'] === 'paid') {
            $accounting->markAsPaid($invoice, paymentType: 'Manuell');
        } elseif ($invoice->status === 'paid' || $invoice->income_transaction_id) {
            $accounting->markAsUnpaid($invoice, $data['status']);
        } else {
            $invoice->update($data);
        }

        return back();
    }
}
