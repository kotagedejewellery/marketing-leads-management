<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, LockKeyhole, LogIn, Mail } from '@lucide/vue';
import { nextTick, ref } from 'vue';
import { Toaster } from '@/components/ui/sonner';

const form = useForm({ email: '', password: '', remember: false });
const emailInput = ref<HTMLInputElement | null>(null);
const submit = () =>
    form.post('/login', {
        onError: () => nextTick(() => emailInput.value?.focus()),
        onFinish: () => form.reset('password'),
    });
</script>

<template>
    <Head title="Masuk" />
    <main class="relative isolate flex min-h-screen overflow-hidden bg-[#e9e0cd] px-4 py-6 text-kgj-ink sm:p-8">
        <div
            aria-hidden="true"
            class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_0%,#fffdf8_0%,#f2e7cf_47%,#dfcb9d_100%)]"
        />
        <header class="absolute inset-x-0 top-0 z-10 p-6 sm:p-8">
            <img
                src="/brand/kotagede-jewellery.png"
                alt="Kotagede Jewellery"
                class="h-14 w-28 object-contain"
            />
        </header>
        <section class="flex w-full flex-1 items-center justify-center py-24 sm:py-28">
            <div class="w-full max-w-[25rem] rounded-2xl bg-kgj-paper p-6 shadow-[0_24px_60px_rgba(69,54,29,0.18)] sm:p-8">
                <div class="mx-auto flex size-12 items-center justify-center rounded-xl bg-[#f1e3c4] text-kgj-gold-deep shadow-[0_8px_18px_rgba(115,80,30,0.14)]">
                    <LogIn class="size-5" />
                </div>
                <div class="mt-6 text-center">
                    <h1 class="text-3xl font-semibold tracking-[-.035em] sm:text-[2rem]">
                        Masuk ke Leads Workspace
                    </h1>
                    <p class="mx-auto mt-3 max-w-[31ch] text-sm leading-6 text-kgj-muted">
                        Gunakan akun CS atau admin untuk melanjutkan pekerjaan hari ini.
                    </p>
                </div>
                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <label class="block text-sm font-semibold">
                        Alamat email
                        <span class="relative mt-2 block">
                            <Mail class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-kgj-muted" />
                            <input
                                ref="emailInput"
                                v-model="form.email"
                                class="kgj-input w-full pl-10"
                                type="email"
                                autocomplete="email"
                                required
                                autofocus
                                :aria-invalid="Boolean(form.errors.email)"
                                :aria-describedby="form.errors.email ? 'login-error' : undefined"
                            />
                        </span>
                    </label>
                    <label class="block text-sm font-semibold">
                        Kata sandi
                        <span class="relative mt-2 block">
                            <LockKeyhole class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-kgj-muted" />
                            <input
                                v-model="form.password"
                                class="kgj-input w-full pl-10"
                                type="password"
                                autocomplete="current-password"
                                required
                                :aria-invalid="Boolean(form.errors.password)"
                                :aria-describedby="form.errors.password ? 'login-error' : undefined"
                            />
                        </span>
                    </label>
                    <label class="flex min-h-11 items-center gap-2 text-sm text-kgj-muted">
                        <input
                            v-model="form.remember"
                            class="size-4 accent-kgj-gold"
                            type="checkbox"
                        />
                        Ingat saya di perangkat ini
                    </label>
                    <p
                        v-if="form.errors.email || form.errors.password"
                        id="login-error"
                        class="rounded-lg bg-[#f9e8e5] px-3 py-2 text-sm leading-6 text-kgj-rose"
                        role="alert"
                    >
                        {{ form.errors.email || form.errors.password }}
                    </p>
                    <button
                        class="kgj-button-primary w-full"
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Memeriksa akun...' : 'Masuk ke dashboard' }}
                        <ArrowRight v-if="!form.processing" class="size-4" />
                    </button>
                </form>
            </div>
        </section>
        <Toaster position="top-right" rich-colors />
    </main>
</template>
