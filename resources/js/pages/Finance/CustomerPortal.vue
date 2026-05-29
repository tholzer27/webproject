<script setup lang="ts">
import ThemeToggle from '@/components/ThemeToggle.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ customer: any; invoices: any[]; paymentStatus?: string | null }>();
const page = usePage();

const money = (value: number | string) => new Intl.NumberFormat('de-CH', { style: 'currency', currency: 'CHF' }).format(Number(value ?? 0));
const statusLabel: Record<string, string> = { open: 'Offen', paid: 'Bezahlt', overdue: 'Überfällig', cancelled: 'Storniert' };
const stripeError = computed(() => (page.props.errors as Record<string, string> | undefined)?.stripe);

const payInvoice = (invoiceId: number) => {
    router.post(`/client/${props.customer.portal_token}/invoices/${invoiceId}/checkout`);
};
</script>

<template>
    <Head :title="`Kundenportal - ${customer.name}`" />

    <main class="min-h-screen bg-background text-foreground">
        <header class="border-b border-border">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
                <div>
                    <p class="text-sm text-muted-foreground">Kundenportal</p>
                    <h1 class="font-semibold">{{ customer.company || customer.name }}</h1>
                </div>
                <ThemeToggle />
            </div>
        </header>

        <section class="mx-auto grid max-w-5xl gap-6 px-4 py-8 sm:px-6">
            <div class="rounded-lg border bg-card p-5 text-card-foreground">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold">{{ customer.name }}</h2>
                        <p class="mt-1 text-sm text-muted-foreground">{{ customer.email || 'Keine E-Mail hinterlegt' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-muted-foreground">Offener Betrag</p>
                        <p class="text-xl font-semibold">
                            {{ money(invoices.filter((invoice) => invoice.status !== 'paid').reduce((sum, invoice) => sum + Number(invoice.total_amount), 0)) }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="stripeError" class="rounded-lg border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
                {{ stripeError }}
            </div>

            <section class="rounded-lg border bg-card text-card-foreground">
                <div class="border-b p-5">
                    <h2 class="text-lg font-semibold">Rechnungen</h2>
                </div>

                <div class="divide-y">
                    <article v-for="invoice in invoices" :key="invoice.id" class="grid gap-4 p-5 md:grid-cols-[1fr_auto_auto] md:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-medium">{{ invoice.invoice_no }}</h3>
                                <span class="rounded-md bg-muted px-2 py-1 text-xs">{{ statusLabel[invoice.status] ?? invoice.status }}</span>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">Fällig am {{ invoice.due_date }}</p>
                            <ul class="mt-3 space-y-1 text-sm text-muted-foreground">
                                <li v-for="item in invoice.items" :key="item.id">{{ item.title }} · {{ money(item.total_price) }}</li>
                            </ul>
                        </div>
                        <p class="font-semibold">{{ money(invoice.total_amount) }}</p>
                        <button
                            type="button"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="invoice.status === 'paid'"
                            @click="payInvoice(invoice.id)"
                        >
                            {{ invoice.status === 'paid' ? 'Bezahlt' : 'Mit Stripe bezahlen' }}
                        </button>
                    </article>

                    <p v-if="invoices.length === 0" class="p-5 text-sm text-muted-foreground">Es sind noch keine Rechnungen vorhanden.</p>
                </div>
            </section>
        </section>
    </main>
</template>
