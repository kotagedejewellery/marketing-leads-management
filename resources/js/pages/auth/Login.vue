<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, LockKeyhole, Mail } from '@lucide/vue';
import { nextTick, ref } from 'vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Toaster } from '@/components/ui/sonner';

const form = useForm({ email: '', password: '', remember: false });
const emailInput = ref<HTMLInputElement | null>(null);
const passwordInput = ref<InstanceType<typeof PasswordInput> | null>(null);
const submit = () =>
    form.post('/login', {
        onError: (errors) =>
            nextTick(() => {
                if (errors.email) return emailInput.value?.focus();

                passwordInput.value?.focus();
            }),
        onFinish: () => form.reset('password'),
    });
</script>

<template>
    <Head title="Masuk | Lead Control" />
    <main
        class="relative isolate grid min-h-dvh place-items-center overflow-x-hidden bg-[#e9e0cd] p-4 text-kgj-ink sm:p-8"
    >
        <div
            aria-hidden="true"
            class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_42%,#fffdf8_0%,#f3e8d3_44%,#e3cea2_100%)]"
        />
        <section class="w-full max-w-[30rem]">
            <div
                class="w-full rounded-2xl bg-kgj-paper p-6 shadow-[0_24px_60px_rgba(69,54,29,0.18)] sm:p-10"
            >
                <img
                    class="mx-auto h-auto w-28 mix-blend-multiply sm:w-40"
                    src="/brand/kotagede-jewellery.png"
                    alt="Kotagede Jewellery"
                    width="2792"
                    height="2308"
                />
                <div class="mt-4 text-center sm:mt-5">
                    <h1
                        class="text-3xl font-semibold tracking-[-.035em] sm:text-[2rem]"
                    >
                        Masuk ke Lead Control
                    </h1>
                    <p
                        class="mx-auto mt-3 max-w-[31ch] text-sm leading-6 text-kgj-muted"
                    >
                        Gunakan akun kerja Anda untuk melanjutkan.
                    </p>
                </div>
                <form
                    class="mt-6 space-y-4 sm:mt-9 sm:space-y-5"
                    @submit.prevent="submit"
                >
                    <label class="block text-sm font-semibold">
                        Alamat email
                        <span class="relative mt-2 block">
                            <Mail
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-kgj-muted"
                            />
                            <input
                                ref="emailInput"
                                v-model="form.email"
                                class="kgj-input w-full pl-10"
                                type="email"
                                autocomplete="email"
                                required
                                :aria-invalid="Boolean(form.errors.email)"
                                :aria-describedby="
                                    form.errors.email
                                        ? 'login-error'
                                        : undefined
                                "
                            />
                        </span>
                    </label>
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-semibold"
                        >
                            Kata sandi
                        </label>
                        <span class="relative mt-2 block">
                            <LockKeyhole
                                class="pointer-events-none absolute top-1/2 left-3 z-10 size-4 -translate-y-1/2 text-kgj-muted"
                            />
                            <PasswordInput
                                ref="passwordInput"
                                id="password"
                                v-model="form.password"
                                class="kgj-input w-full pr-12 pl-10"
                                autocomplete="current-password"
                                required
                                :aria-invalid="Boolean(form.errors.password)"
                                :aria-describedby="
                                    form.errors.password
                                        ? 'login-error'
                                        : undefined
                                "
                            />
                        </span>
                    </div>
                    <label
                        class="flex min-h-11 items-center gap-2 text-sm text-kgj-muted"
                    >
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
                        {{ form.processing ? 'Memeriksa akun...' : 'Masuk' }}
                        <ArrowRight v-if="!form.processing" class="size-4" />
                    </button>
                </form>
            </div>
        </section>
        <Toaster position="top-right" rich-colors />
    </main>
</template>
