<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $account = $user->accounts()->create([
            'name' => 'Geschäftskonto',
            'balance' => 12840.50,
            'currency' => 'CHF',
        ]);

        $categories = collect([
            ['name' => 'Lohn', 'type' => 'income', 'color' => '#059669', 'icon' => 'Briefcase'],
            ['name' => 'Freelancer', 'type' => 'income', 'color' => '#2563eb', 'icon' => 'Laptop'],
            ['name' => 'Rechnungen', 'type' => 'income', 'color' => '#0ea5e9', 'icon' => 'ReceiptText'],
            ['name' => 'Miete', 'type' => 'expense', 'color' => '#dc2626', 'icon' => 'Home'],
            ['name' => 'Lebensmittel', 'type' => 'expense', 'color' => '#ea580c', 'icon' => 'ShoppingCart'],
            ['name' => 'Transport', 'type' => 'expense', 'color' => '#0891b2', 'icon' => 'Train'],
        ])->map(fn (array $category) => $user->categories()->create($category));

        collect([
            ['title' => 'Webdesign Projekt', 'type' => 'income', 'amount' => 4200, 'category' => 'Freelancer', 'transaction_date' => now()->subDays(4)],
            ['title' => 'Monatslohn', 'type' => 'income', 'amount' => 5600, 'category' => 'Lohn', 'transaction_date' => now()->subDays(12)],
            ['title' => 'Büromiete', 'type' => 'expense', 'amount' => 1250, 'category' => 'Miete', 'transaction_date' => now()->subDays(8)],
            ['title' => 'Migros Einkauf', 'type' => 'expense', 'amount' => 186.40, 'category' => 'Lebensmittel', 'transaction_date' => now()->subDays(2)],
            ['title' => 'SBB Business', 'type' => 'expense', 'amount' => 92.20, 'category' => 'Transport', 'transaction_date' => now()->subDays(1)],
        ])->each(function (array $transaction) use ($account, $categories) {
            $account->transactions()->create([
                'category_id' => $categories->firstWhere('name', $transaction['category'])->id,
                'amount' => $transaction['amount'],
                'type' => $transaction['type'],
                'payment_type' => 'Bank',
                'title' => $transaction['title'],
                'transaction_date' => $transaction['transaction_date'],
            ]);
        });

        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => 'Mara Keller',
            'company' => 'Keller Studio GmbH',
            'email' => 'mara@example.test',
            'phone' => '+41 44 555 12 34',
            'address' => 'Bahnhofstrasse 10, 8001 Zürich',
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_no' => 'INV-'.now()->format('Ymd').'-0001',
            'status' => 'open',
            'subtotal' => 1800,
            'tax_amount' => 145.80,
            'total_amount' => 1945.80,
            'due_date' => now()->addDays(14),
        ]);

        $invoice->items()->create([
            'title' => 'Branding und Landing Page',
            'quantity' => 1,
            'unit_price' => 1800,
            'total_price' => 1800,
        ]);
    }
}
