<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';

defineProps<{ transactions: any[]; accounts: any[]; categories: any[] }>();

const money = (value: number | string) => new Intl.NumberFormat('de-CH', { style: 'currency', currency: 'CHF' }).format(Number(value ?? 0));
const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    account_id: '',
    category_id: '',
    amount: '',
    type: 'expense',
    payment_type: 'Bank',
    title: '',
    description: '',
    transaction_date: today,
});

const submit = () => {
    form.post('/transactions', {
        preserveScroll: true,
        onSuccess: () => form.reset('amount', 'title', 'description'),
    });
};
</script>

<template>
    <Head title="Transaktionen" />
    <AppLayout>
        <main class="mx-auto grid w-full max-w-7xl gap-6 p-4 sm:p-6 lg:grid-cols-[360px_1fr]">
            <form class="rounded-lg border bg-card p-5" @submit.prevent="submit">
                <h1 class="mb-5 text-xl font-semibold">Transaktion erfassen</h1>
                <div class="space-y-4">
                    <input v-model="form.title" required placeholder="Beschreibung" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <div class="grid grid-cols-2 gap-3">
                        <input v-model="form.amount" required type="number" step="0.01" min="0.01" placeholder="Betrag" class="rounded-md border bg-background px-3 py-2 text-sm" />
                        <select v-model="form.type" class="rounded-md border bg-background px-3 py-2 text-sm">
                            <option value="income">Einnahme</option>
                            <option value="expense">Ausgabe</option>
                        </select>
                    </div>
                    <select v-model="form.account_id" required class="w-full rounded-md border bg-background px-3 py-2 text-sm">
                        <option value="" disabled>Konto wählen</option>
                        <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                    </select>
                    <select v-model="form.category_id" class="w-full rounded-md border bg-background px-3 py-2 text-sm">
                        <option value="">Keine Kategorie</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <div class="grid grid-cols-2 gap-3">
                        <input v-model="form.payment_type" required placeholder="Zahlungsart" class="rounded-md border bg-background px-3 py-2 text-sm" />
                        <input v-model="form.transaction_date" required type="date" class="rounded-md border bg-background px-3 py-2 text-sm" />
                    </div>
                    <textarea v-model="form.description" rows="3" placeholder="Notiz" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <button class="w-full rounded-md bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Speichern</button>
                </div>
            </form>

            <section class="rounded-lg border bg-card">
                <div class="border-b p-5">
                    <h1 class="text-xl font-semibold">Transaktionen</h1>
                </div>
                <div class="divide-y">
                    <div v-for="transaction in transactions" :key="transaction.id" class="grid gap-3 p-4 sm:grid-cols-[1fr_auto_auto] sm:items-center">
                        <div>
                            <p class="font-medium">{{ transaction.title }}</p>
                            <p class="text-sm text-muted-foreground">{{ transaction.account.name }} · {{ transaction.category?.name ?? 'Ohne Kategorie' }} · {{ transaction.transaction_date }}</p>
                        </div>
                        <p :class="['font-medium', transaction.type === 'income' ? 'text-emerald-600' : 'text-rose-600']">
                            {{ transaction.type === 'income' ? '+' : '-' }}{{ money(transaction.amount) }}
                        </p>
                        <button class="rounded-md border p-2" @click="router.delete(`/transactions/${transaction.id}`, { preserveScroll: true })">
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                    <p v-if="transactions.length === 0" class="p-5 text-sm text-muted-foreground">Noch keine Transaktionen vorhanden.</p>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
