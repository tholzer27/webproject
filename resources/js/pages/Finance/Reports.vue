<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const reports = [
    { title: 'Monatsreport', description: 'Einnahmen, Ausgaben und Cashflow als kompakte Monatsübersicht.', action: 'CSV Export' },
    { title: 'Jahresreport', description: 'Jahresvergleich mit Kategorieauswertung und Rechnungssummen.', action: 'Excel Export' },
    { title: 'Steuerübersicht', description: 'MWST, abzugsfähige Ausgaben und offene Beträge für die Ablage.', action: 'PDF Export' },
];
</script>

<template>
    <Head title="Reports" />
    <AppLayout>
        <main class="mx-auto w-full max-w-7xl p-4 sm:p-6">
            <h1 class="mb-6 text-2xl font-semibold">Reports & Export</h1>
            <section class="grid gap-4 md:grid-cols-3">
                <article v-for="report in reports" :key="report.title" class="rounded-lg border bg-card p-5">
                    <h2 class="font-semibold">{{ report.title }}</h2>
                    <p class="mt-2 min-h-16 text-sm text-muted-foreground">{{ report.description }}</p>
                    <button class="mt-5 rounded-md border px-3 py-2 text-sm">{{ report.action }}</button>
                </article>
            </section>
        </main>
    </AppLayout>
</template>
