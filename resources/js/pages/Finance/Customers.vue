<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ customers: any[] }>();

const form = useForm({ name: '', company: '', email: '', phone: '', address: '' });
</script>

<template>
    <Head title="Kunden" />
    <AppLayout>
        <main class="mx-auto grid w-full max-w-7xl gap-6 p-4 sm:p-6 lg:grid-cols-[360px_1fr]">
            <form class="rounded-lg border bg-card p-5" @submit.prevent="form.post('/customers', { preserveScroll: true, onSuccess: () => form.reset() })">
                <h1 class="mb-5 text-xl font-semibold">Kunde erstellen</h1>
                <div class="space-y-4">
                    <input v-model="form.name" required placeholder="Name" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <input v-model="form.company" placeholder="Firma" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <input v-model="form.email" type="email" placeholder="E-Mail" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <input v-model="form.phone" placeholder="Telefon" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <textarea v-model="form.address" rows="3" placeholder="Adresse" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <button class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">Speichern</button>
                </div>
            </form>

            <section class="grid content-start gap-3 md:grid-cols-2">
                <article v-for="customer in customers" :key="customer.id" class="rounded-lg border bg-card p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold">{{ customer.name }}</p>
                            <p class="text-sm text-muted-foreground">{{ customer.company || 'Privatkunde' }}</p>
                        </div>
                        <span class="rounded-md bg-muted px-2 py-1 text-xs">{{ customer.invoices_count }} Rechnungen</span>
                    </div>
                    <div class="mt-4 text-sm text-muted-foreground">
                        <p>{{ customer.email }}</p>
                        <p>{{ customer.phone }}</p>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <Link :href="customer.admin_portal_url" class="rounded-md border px-3 py-2 text-sm font-medium hover:bg-muted">
                            Kundenportal öffnen
                        </Link>
                        <a :href="customer.portal_url" target="_blank" rel="noopener noreferrer" class="rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground">
                            Kundenlink
                        </a>
                    </div>
                </article>
            </section>
        </main>
    </AppLayout>
</template>
