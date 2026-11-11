<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { BarChart3 } from '@lucide/vue';
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
const rows = computed(() => [
    {
        label: 'Cold',
        note: 'Interaksi awal',
        value: props.stats.cold,
        color: 'bg-[#9caeb4]',
    },
    {
        label: 'Warm',
        note: 'Mulai mempertimbangkan',
        value: props.stats.warm,
        color: 'bg-[#caa85d]',
    },
    {
        label: 'Hot',
        note: 'Siap ditindaklanjuti',
        value: props.stats.hot,
        color: 'bg-[#b85b47]',
    },
    {
        label: 'Closing',
        note: 'Mengarah pada transaksi',
        value: props.stats.closing,
        color: 'bg-[#527260]',
    },
]);
const width = (value: number) =>
    `${Math.max(props.stats.total ? (value / props.stats.total) * 100 : 0, value ? 5 : 0)}%`;
</script>

<template>
    <Head title="Statistik" />
    <section class="kgj-page">
        <div
            class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"
        >
            <div>
                <p class="kgj-eyebrow">Monitoring funnel</p>
                <h1
                    class="mt-2 text-4xl font-semibold tracking-[-.04em] sm:text-5xl"
                >
                    Statistik.
                </h1>
                <p class="kgj-subtitle">
                    Ringkasan funnel sesuai cakupan akses akun Anda.
                </p>
            </div>
            <BarChart3 class="hidden size-7 text-kgj-gold sm:block" />
        </div>
        <div class="kgj-surface mt-8 overflow-hidden">
            <div
                class="grid border-b border-kgj-line sm:grid-cols-3 lg:grid-cols-7"
            >
                <div
                    v-for="item in [
                        { label: 'Total lead', value: stats.total },
                        ...rows.map(({ label, value }) => ({ label, value })),
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
                    class="border-b border-kgj-line px-5 py-5 last:border-b-0 sm:border-r sm:border-b-0 lg:last:border-r-0"
                >
                    <dt
                        class="text-xs font-bold tracking-[.12em] text-kgj-muted uppercase"
                    >
                        {{ item.label }}
                    </dt>
                    <dd class="mt-2 text-3xl font-semibold tabular-nums">
                        {{ item.value }}
                    </dd>
                </div>
            </div>
            <div class="p-5 sm:p-7">
                <h2 class="text-lg font-semibold">Sebaran tahapan</h2>
                <div class="mt-6 space-y-5">
                    <div
                        v-for="item in rows"
                        :key="item.label"
                        class="grid grid-cols-[82px_1fr_auto] items-center gap-3"
                    >
                        <div>
                            <strong class="block text-sm">{{
                                item.label
                            }}</strong
                            ><span class="text-xs text-kgj-muted">{{
                                item.note
                            }}</span>
                        </div>
                        <div
                            class="h-2.5 overflow-hidden rounded-full bg-[#eee9df]"
                        >
                            <div
                                class="h-full rounded-full transition-[width] duration-500"
                                :class="item.color"
                                :style="{ width: width(item.value) }"
                            />
                        </div>
                        <strong class="text-sm tabular-nums">{{
                            item.value
                        }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
