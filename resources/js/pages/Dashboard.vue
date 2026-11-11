<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Flame, ListChecks, UserRoundPlus } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        total: number;
        cold: number;
        warm: number;
        hot: number;
        closing: number;
        unassigned: number;
        follow_up_due: number;
    };
}>();
const isAdmin = usePage().props.auth.user?.role === 'admin';
const stages = computed(() => [
    { label: 'Cold', value: props.stats.cold, tone: 'bg-[#a8b9bd]' },
    { label: 'Warm', value: props.stats.warm, tone: 'bg-[#caa85d]' },
    { label: 'Hot', value: props.stats.hot, tone: 'bg-[#b85b47]' },
    { label: 'Closing', value: props.stats.closing, tone: 'bg-[#527260]' },
]);
const width = (value: number) =>
    `${Math.max(props.stats.total ? (value / props.stats.total) * 100 : 0, value ? 6 : 0)}%`;
</script>

<template>
    <Head title="Dashboard" />
    <section class="kgj-page">
        <div
            class="overflow-hidden rounded-3xl bg-kgj-ink px-6 py-8 text-[#fffdf8] shadow-[0_20px_42px_rgba(47,39,25,0.17)] sm:px-8 lg:px-10 lg:py-10"
        >
            <div
                class="flex flex-col justify-between gap-7 lg:flex-row lg:items-end"
            >
                <div class="max-w-2xl">
                    <p class="kgj-eyebrow text-[#e6c17e]">
                        Ruang kendali leads
                    </p>
                    <h1
                        class="mt-3 text-4xl font-semibold tracking-[-0.04em] sm:text-5xl"
                    >
                        Kerja yang jelas, tindak lanjut yang terjaga.
                    </h1>
                    <p
                        class="mt-4 max-w-xl text-sm leading-6 text-white/65 sm:text-base"
                    >
                        Semua percakapan, tahap minat, dan penanggung jawab
                        berada dalam satu alur kerja yang mudah dibaca.
                    </p>
                </div>
                <Link
                    href="/leads"
                    class="inline-flex min-h-11 w-fit items-center gap-2 rounded-xl bg-[#fffdf8] px-4 text-sm font-bold text-kgj-ink transition hover:bg-[#f1e6cb]"
                    ><ListChecks class="size-4" />Buka workspace
                    leads<ArrowRight class="size-4"
                /></Link>
            </div>
            <div
                class="mt-9 grid gap-0 border-t border-white/12 pt-5 sm:grid-cols-3 lg:grid-cols-7"
            >
                <div
                    v-for="item in [
                        { label: 'Total lead', value: stats.total },
                        { label: 'Cold', value: stats.cold },
                        { label: 'Warm', value: stats.warm },
                        { label: 'Hot', value: stats.hot },
                        { label: 'Closing', value: stats.closing },
                        ...(isAdmin
                            ? [
                                  {
                                      label: 'Belum ditugaskan',
                                      value: stats.unassigned,
                                  },
                              ]
                            : []),
                        {
                            label: 'Follow-up jatuh tempo',
                            value: stats.follow_up_due,
                        },
                    ]"
                    :key="item.label"
                    class="border-b border-white/12 py-4 last:border-b-0 sm:border-r sm:border-b-0 sm:px-4 sm:first:pl-0 lg:last:border-r-0"
                >
                    <dt
                        class="text-xs font-semibold tracking-wide text-white/55"
                    >
                        {{ item.label }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold tabular-nums">
                        {{ item.value }}
                    </dd>
                </div>
            </div>
        </div>

        <div
            class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(300px,0.55fr)]"
        >
            <section class="kgj-surface p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold tracking-[-0.025em]">
                            Funnel saat ini
                        </h2>
                        <p class="mt-1 text-sm text-kgj-muted">
                            Komposisi tahap aktif dari seluruh lead yang
                            terlihat.
                        </p>
                    </div>
                    <Flame class="mt-1 size-5 text-kgj-gold" />
                </div>
                <div class="mt-7 space-y-5">
                    <div
                        v-for="stage in stages"
                        :key="stage.label"
                        class="grid grid-cols-[74px_1fr_auto] items-center gap-3 text-sm"
                    >
                        <span class="font-semibold">{{ stage.label }}</span>
                        <div
                            class="h-2 overflow-hidden rounded-full bg-[#eee9df]"
                        >
                            <div
                                class="h-full rounded-full transition-[width] duration-500"
                                :class="stage.tone"
                                :style="{ width: width(stage.value) }"
                            />
                        </div>
                        <strong class="min-w-7 text-right tabular-nums">{{
                            stage.value
                        }}</strong>
                    </div>
                </div>
            </section>

            <aside
                v-if="isAdmin"
                class="rounded-2xl border border-[#dccd9f] bg-[#f8f1df] p-5 sm:p-6"
            >
                <UserRoundPlus class="size-5 text-kgj-gold-deep" />
                <h2 class="mt-5 text-xl font-semibold tracking-[-0.025em]">
                    Distribusi perlu diperiksa
                </h2>
                <p class="mt-2 text-sm leading-6 text-[#665a43]">
                    {{
                        stats.unassigned
                            ? `${stats.unassigned} lead belum memiliki CS penanggung jawab.`
                            : 'Semua lead sudah memiliki penanggung jawab.'
                    }}
                </p>
                <Link
                    href="/leads?queue=unassigned"
                    class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-kgj-gold-deep underline decoration-[#c4a45d] underline-offset-4"
                    >Tinjau daftar leads<ArrowRight class="size-4"
                /></Link>
            </aside>
        </div>
    </section>
</template>
