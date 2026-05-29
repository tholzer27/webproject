<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{ invoices: any[]; customers: any[] }>();
const page = usePage();

const money = (value: number | string) => new Intl.NumberFormat('de-CH', { style: 'currency', currency: 'CHF' }).format(Number(value ?? 0));
const form = useForm({ customer_id: '', title: '', quantity: 1, unit_price: '', tax_rate: 8.1, due_date: '' });
const statusLabel: Record<string, string> = { open: 'Offen', paid: 'Bezahlt', overdue: 'Überfällig', cancelled: 'Storniert' };
const stripeError = computed(() => (page.props.errors as Record<string, string> | undefined)?.stripe);

const updateStatus = (invoiceId: number, event: Event) => {
    router.patch(`/invoices/${invoiceId}`, { status: (event.target as HTMLSelectElement).value }, { preserveScroll: true });
};

const openCheckout = (invoiceId: number) => {
    router.post(`/invoices/${invoiceId}/checkout`);
};
</script>

<template>
    <Head title="Rechnungen" />
    <AppLayout>
        <main class="mx-auto grid w-full max-w-7xl gap-6 p-4 sm:p-6 lg:grid-cols-[360px_1fr]">
            <form class="rounded-lg border bg-card p-5" @submit.prevent="form.post('/invoices', { preserveScroll: true, onSuccess: () => form.reset('title', 'unit_price') })">
                <h1 class="mb-5 text-xl font-semibold">Rechnung erstellen</h1>
                <div class="space-y-4">
                    <select v-model="form.customer_id" required class="w-full rounded-md border bg-background px-3 py-2 text-sm">
                        <option value="" disabled>Kunde wählen</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                    </select>
                    <input v-model="form.title" required placeholder="Position" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <div class="grid grid-cols-2 gap-3">
                        <input v-model="form.quantity" required type="number" step="0.01" min="0.01" placeholder="Menge" class="rounded-md border bg-background px-3 py-2 text-sm" />
                        <input v-model="form.unit_price" required type="number" step="0.01" min="0" placeholder="Preis" class="rounded-md border bg-background px-3 py-2 text-sm" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <input v-model="form.tax_rate" required type="number" step="0.1" min="0" placeholder="MWST %" class="rounded-md border bg-background px-3 py-2 text-sm" />
                        <input v-model="form.due_date" required type="date" class="rounded-md border bg-background px-3 py-2 text-sm" />
                    </div>
                    <button class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">Speichern</button>
                </div>
            </form>

            <section class="rounded-lg border bg-card">
                <div class="border-b p-5">
                    <h1 class="text-xl font-semibold">Rechnungen</h1>
                    <p v-if="stripeError" class="mt-3 rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive">
                        {{ stripeError }}
                    </p>
                </div>
                <div class="divide-y">
                    <div v-for="invoice in invoices" :key="invoice.id" class="grid gap-3 p-4 md:grid-cols-[1fr_auto_auto_auto] md:items-center">
                        <div>
                            <p class="font-medium">{{ invoice.invoice_no }}</p>
                            <p class="text-sm text-muted-foreground">{{ invoice.customer.name }} · fällig {{ invoice.due_date }}</p>
                        </div>
                        <p class="font-medium">{{ money(invoice.total_amount) }}</p>
                        <select
                            class="rounded-md border bg-background px-3 py-2 text-sm"
                            :value="invoice.status"
                            @change="updateStatus(invoice.id, $event)"
                        >
                            <option v-for="(label, status) in statusLabel" :key="status" :value="status">{{ label }}</option>
                        </select>
                        <button
                            type="button"
                            class="rounded-md border px-3 py-2 text-sm font-medium hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="invoice.status === 'paid'"
                            @click="openCheckout(invoice.id)"
                        >
                            Stripe Checkout
                        </button>
                    </div>
                    <p v-if="invoices.length === 0" class="p-5 text-sm text-muted-foreground">Noch keine Rechnungen vorhanden.</p>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
