<script setup lang="ts">
import ThemeToggle from '@/components/ThemeToggle.vue';
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { BarChart3, FileText, WalletCards } from 'lucide-vue-next';

const features = [
    { title: 'Finanzen im Blick', text: 'Monatsübersicht, Kontostand und Cashflow auf einem Dashboard.', icon: WalletCards },
    { title: 'Rechnungen', text: 'Kunden verwalten, Rechnungen erstellen und Status nachführen.', icon: FileText },
    { title: 'Reports', text: 'Grundlage für Monatsreport, Jahresreport und Steuerübersicht.', icon: BarChart3 },
];
</script>

<template>
    <Head title="Finovo" />

    <main class="min-h-screen bg-background text-foreground transition-colors">
        <header class="mx-auto flex h-16 max-w-7xl items-center justify-between border-b border-border/70 px-4 sm:px-6">
            <Link href="/" class="text-lg font-semibold">Finovo</Link>
            <nav class="flex items-center gap-2">
                <ThemeToggle />

                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link :href="login()" class="rounded-md px-4 py-2 text-sm font-medium hover:bg-muted">Login</Link>
                    <Link :href="register()" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground">
                        Registrieren
                    </Link>
                </template>
            </nav>
        </header>

        <section class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1fr_480px] lg:py-24">
            <div class="max-w-2xl">
                <p class="mb-3 text-sm font-medium text-muted-foreground">Persönliche Buchhaltung für Alltag und Freelance</p>
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl">Finovo macht deine Finanzen ruhig, klar und greifbar.</h1>
                <p class="mt-5 text-lg text-muted-foreground">
                    Erfasse Einnahmen und Ausgaben, verwalte Kunden und Rechnungen und behalte deine wichtigsten Zahlen ohne Ballast im Blick.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link :href="register()" class="rounded-md bg-primary px-5 py-3 text-sm font-medium text-primary-foreground">
                        Kostenlos starten
                    </Link>
                    <Link :href="login()" class="rounded-md border border-border px-5 py-3 text-sm font-medium hover:bg-muted">Einloggen</Link>
                </div>
            </div>

            <div class="rounded-lg border border-border bg-card p-5 text-card-foreground shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Mai 2026</p>
                        <p class="text-2xl font-semibold">CHF 12'840.50</p>
                    </div>
                    <span class="rounded-md bg-emerald-100 px-2 py-1 text-sm text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">+18%</span>
                </div>
                <div class="space-y-3">
                    <div class="h-3 rounded-full bg-muted">
                        <div class="h-3 w-3/4 rounded-full bg-emerald-500" />
                    </div>
                    <div class="h-3 rounded-full bg-muted">
                        <div class="h-3 w-1/2 rounded-full bg-sky-500" />
                    </div>
                    <div class="h-3 rounded-full bg-muted">
                        <div class="h-3 w-1/3 rounded-full bg-rose-500" />
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl gap-4 px-4 pb-16 sm:px-6 md:grid-cols-3">
            <article v-for="feature in features" :key="feature.title" class="rounded-lg border border-border bg-card p-5 text-card-foreground">
                <component :is="feature.icon" class="mb-4 size-5" />
                <h2 class="font-semibold">{{ feature.title }}</h2>
                <p class="mt-2 text-sm text-muted-foreground">{{ feature.text }}</p>
            </article>
        </section>
    </main>
</template>
