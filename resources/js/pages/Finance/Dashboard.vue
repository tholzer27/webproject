<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDownRight, ArrowUpRight, Banknote, FileClock } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{ dashboard: Record<string, any> }>();

const money = (value: number | string) =>
    new Intl.NumberFormat('de-CH', { style: 'currency', currency: 'CHF' }).format(Number(value ?? 0));

const maxMonth = computed(() =>
    Math.max(
        1,
        ...props.dashboard.monthly.flatMap((month: Record<string, number>) => [Number(month.income), Number(month.expenses)]),
    ),
);

const cards = computed(() => [
    { label: 'Kontostand', value: money(props.dashboard.summary.balance), icon: Banknote, tone: 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' },
    { label: 'Einnahmen diesen Monat', value: money(props.dashboard.summary.income), icon: ArrowUpRight, tone: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' },
    { label: 'Ausgaben diesen Monat', value: money(props.dashboard.summary.expenses), icon: ArrowDownRight, tone: 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' },
    { label: 'Offene Rechnungen', value: money(props.dashboard.summary.open_invoices), icon: FileClock, tone: 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300' },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <main class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6">
            <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="text-sm text-muted-foreground">FinanceHub</p>
                    <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>
                </div>
                <Link href="/transactions" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">
                    Transaktion erfassen
                </Link>
            </section>

            <section class="grid gap-4 md:grid-cols-4">
                <article v-for="card in cards" :key="card.label" class="rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">{{ card.label }}</p>
                        <div :class="['rounded-md p-2', card.tone]">
                            <component :is="card.icon" class="size-4" />
                        </div>
                    </div>
                    <p class="mt-4 text-2xl font-semibold">{{ card.value }}</p>
                </article>
            </section>

            <section class="grid gap-4 lg:grid-cols-[1.4fr_0.8fr]">
                <article class="rounded-lg border bg-card p-5">
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="font-semibold">Einnahmen vs. Ausgaben</h2>
                        <span class="text-sm text-muted-foreground">Letzte 6 Monate</span>
                    </div>
                    <div class="flex h-64 items-end gap-5">
                        <div v-for="month in dashboard.monthly" :key="month.label" class="flex flex-1 flex-col items-center gap-2">
                            <div class="flex h-52 w-full items-end justify-center gap-2">
                                <div class="w-5 rounded-t bg-emerald-500" :style="{ height: `${(Number(month.income) / maxMonth) * 100}%` }" />
                                <div class="w-5 rounded-t bg-rose-500" :style="{ height: `${(Number(month.expenses) / maxMonth) * 100}%` }" />
                            </div>
                            <span class="text-xs text-muted-foreground">{{ month.label }}</span>
                        </div>
                    </div>
                </article>

                <article class="rounded-lg border bg-card p-5">
                    <h2 class="mb-5 font-semibold">Kategorieverteilung</h2>
                    <div class="space-y-4">
                        <div v-for="category in dashboard.categories" :key="category.name">
                            <div class="mb-1 flex justify-between text-sm">
                                <span>{{ category.name }}</span>
                                <span class="text-muted-foreground">{{ money(category.total) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-muted">
                                <div class="h-2 rounded-full" :style="{ width: `${Math.max(8, (Number(category.total) / Math.max(1, dashboard.summary.expenses)) * 100)}%`, backgroundColor: category.color }" />
                            </div>
                        </div>
                        <p v-if="dashboard.categories.length === 0" class="text-sm text-muted-foreground">Noch keine Ausgaben vorhanden.</p>
                    </div>
                </article>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <article class="rounded-lg border bg-card">
                    <div class="border-b p-4 font-semibold">Letzte Transaktionen</div>
                    <div class="divide-y">
                        <div v-for="transaction in dashboard.transactions" :key="transaction.id" class="flex items-center justify-between p-4">
                            <div>
                                <p class="font-medium">{{ transaction.title }}</p>
                                <p class="text-sm text-muted-foreground">{{ transaction.category?.name ?? 'Ohne Kategorie' }}</p>
                            </div>
                            <p :class="transaction.type === 'income' ? 'text-emerald-600' : 'text-rose-600'">
                                {{ transaction.type === 'income' ? '+' : '-' }}{{ money(transaction.amount) }}
                            </p>
                        </div>
                    </div>
                </article>

                <article class="rounded-lg border bg-card">
                    <div class="border-b p-4 font-semibold">Offene Rechnungen</div>
                    <div class="divide-y">
                        <div v-for="invoice in dashboard.invoices" :key="invoice.id" class="flex items-center justify-between p-4">
                            <div>
                                <p class="font-medium">{{ invoice.invoice_no }}</p>
                                <p class="text-sm text-muted-foreground">{{ invoice.customer.name }}</p>
                            </div>
                            <p>{{ money(invoice.total_amount) }}</p>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </AppLayout>
</template>
