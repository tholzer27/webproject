<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('portal_token')->nullable()->unique()->after('address');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->index()->after('due_date');
            $table->string('stripe_payment_intent_id')->nullable()->index()->after('stripe_checkout_session_id');
            $table->text('stripe_checkout_url')->nullable()->after('stripe_payment_intent_id');
            $table->timestamp('stripe_checkout_expires_at')->nullable()->after('stripe_checkout_url');
            $table->timestamp('paid_at')->nullable()->after('stripe_checkout_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'stripe_checkout_url',
                'stripe_checkout_expires_at',
                'paid_at',
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('portal_token');
        });
    }
};
