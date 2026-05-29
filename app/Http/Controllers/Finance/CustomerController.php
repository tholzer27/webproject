<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        $customers = auth()->user()->customers()->withCount('invoices')->latest()->get();

        $customers->each(function ($customer) {
            if (! $customer->portal_token) {
                $customer->forceFill(['portal_token' => str()->random(40)])->save();
            }
        });

        return Inertia::render('Finance/Customers', [
            'customers' => $customers->map(fn ($customer) => [
                ...$customer->toArray(),
                'portal_url' => route('client.portal', $customer->portal_token),
                'admin_portal_url' => route('customers.portal', $customer),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        auth()->user()->customers()->create($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:500'],
        ]));

        return back();
    }
}
