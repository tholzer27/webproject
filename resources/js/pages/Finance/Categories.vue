<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{ categories: any[] }>();

const form = useForm({ name: '', type: 'expense', color: '#2563eb', icon: 'CircleDollarSign' });
const colors = ['#2563eb', '#059669', '#dc2626', '#7c3aed', '#ea580c', '#0891b2'];
</script>

<template>
    <Head title="Kategorien" />
    <AppLayout>
        <main class="mx-auto grid w-full max-w-7xl gap-6 p-4 sm:p-6 lg:grid-cols-[340px_1fr]">
            <form class="rounded-lg border bg-card p-5" @submit.prevent="form.post('/categories', { preserveScroll: true, onSuccess: () => form.reset('name') })">
                <h1 class="mb-5 text-xl font-semibold">Kategorie erstellen</h1>
                <div class="space-y-4">
                    <input v-model="form.name" required placeholder="Name" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <select v-model="form.type" class="w-full rounded-md border bg-background px-3 py-2 text-sm">
                        <option value="income">Einnahme</option>
                        <option value="expense">Ausgabe</option>
                    </select>
                    <input v-model="form.icon" required placeholder="Icon Name" class="w-full rounded-md border bg-background px-3 py-2 text-sm" />
                    <div class="flex gap-2">
                        <button v-for="color in colors" :key="color" type="button" class="size-8 rounded-md border" :style="{ backgroundColor: color }" @click="form.color = color" />
                    </div>
                    <button class="w-full rounded-md bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Speichern</button>
                </div>
            </form>

            <section class="grid content-start gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <article v-for="category in categories" :key="category.id" class="rounded-lg border bg-card p-4">
                    <div class="mb-4 size-3 rounded-full" :style="{ backgroundColor: category.color }" />
                    <p class="font-medium">{{ category.name }}</p>
                    <p class="text-sm text-muted-foreground">{{ category.type === 'income' ? 'Einnahme' : 'Ausgabe' }} · {{ category.icon }}</p>
                </article>
            </section>
        </main>
    </AppLayout>
</template>
