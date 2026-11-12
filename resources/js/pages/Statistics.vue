<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { BarChart3, CheckCircle2, UsersRound } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        total: number;
        cold: number;
        warm: number;
        hot: number;
        closing: number;
        unclassified: number;
    };
    quality: { total: number; complete: number; follow_up_set: number };
    workload: Array<{
        name: string;
        cs_name: string | null;
        lead_count: number;
    }>;
}>();

const isAdmin = usePage().props.auth.user?.role === 'admin';
const stages = computed(() => [
    { label: 'Cold', note: 'Klik saja', value: props.stats.cold },
    {
        label: 'Warm',
        note: 'Tanya-tanya atau pricelist',
        value: props.stats.warm,
    },
    {
        label: 'Hot',
        note: 'Offline, online, atau belum transfer',
        value: props.stats.hot,
    },
    {
        label: 'Closing',
        note: 'Closing atau repeat order',
        value: props.stats.closing,
    },
    {
        label: 'Belum ditandai',
        note: 'Tahap belum dipilih saat input atau impor',
        value: props.stats.unclassified,
    },
]);
const percentage = (value: number, total = props.stats.total) =>
    total ? `${Math.round((value / total) * 100)}%` : '0%';
</script>

<template>
    <Head title="Statistik" />
    <section class="kgj-page">
        <div
            class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"
        >
            <div>
                <h1 class="kgj-title">Kualitas dan distribusi.</h1>
                <p class="kgj-subtitle">
                    Laporan kondisi data lead dalam cakupan akses akun Anda.
                </p>
            </div>
            <BarChart3 class="hidden size-7 text-kgj-gold sm:block" />
        </div>

        <section
            class="kgj-surface mt-8 overflow-hidden"
            aria-label="Ringkasan kualitas data"
        >
            <dl
                class="grid divide-y divide-kgj-line sm:grid-cols-3 sm:divide-x sm:divide-y-0"
            >
                <div class="px-5 py-5 sm:px-7">
                    <dt class="text-sm text-kgj-muted">Lead aktif</dt>
                    <dd class="mt-1 text-2xl font-semibold tabular-nums">
                        {{ quality.total }}
                    </dd>
                </div>
                <div class="px-5 py-5 sm:px-7">
                    <dt class="text-sm text-kgj-muted">Kualifikasi lengkap</dt>
                    <dd class="mt-1 text-2xl font-semibold tabular-nums">
                        {{ quality.complete }}
                        <span class="text-sm font-medium text-kgj-muted">{{
                            percentage(quality.complete, quality.total)
                        }}</span>
                    </dd>
                </div>
                <div class="px-5 py-5 sm:px-7">
                    <dt class="text-sm text-kgj-muted">Follow-up terjadwal</dt>
                    <dd class="mt-1 text-2xl font-semibold tabular-nums">
                        {{ quality.follow_up_set }}
                        <span class="text-sm font-medium text-kgj-muted">{{
                            percentage(quality.follow_up_set, quality.total)
                        }}</span>
                    </dd>
                </div>
            </dl>
        </section>

        <div
            class="mt-7 grid gap-7 xl:grid-cols-[minmax(0,1fr)_minmax(340px,0.72fr)]"
        >
            <section
                class="kgj-surface overflow-hidden"
                aria-labelledby="stages-heading"
            >
                <div class="px-5 py-5 sm:px-7">
                    <h2 id="stages-heading" class="text-lg font-semibold">
                        Sebaran tahap aktif
                    </h2>
                    <p class="mt-1 text-sm text-kgj-muted">
                        Komposisi tahap terakhir, bukan perhitungan konversi
                        antar tahap.
                    </p>
                </div>
                <div class="border-t border-kgj-line">
                    <div
                        v-for="stage in stages"
                        :key="stage.label"
                        class="grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-4 border-b border-kgj-line px-5 py-4 last:border-b-0 sm:px-7"
                    >
                        <span>
                            <strong class="block text-sm">{{
                                stage.label
                            }}</strong>
                            <span class="mt-1 block text-xs text-kgj-muted">{{
                                stage.note
                            }}</span>
                        </span>
                        <span class="text-sm text-kgj-muted tabular-nums">{{
                            percentage(stage.value)
                        }}</span>
                        <strong
                            class="min-w-9 text-right text-lg tabular-nums"
                            >{{ stage.value }}</strong
                        >
                    </div>
                </div>
            </section>

            <section
                v-if="isAdmin"
                class="kgj-surface overflow-hidden"
                aria-labelledby="workload-heading"
            >
                <div class="px-5 py-5 sm:px-7">
                    <div class="flex items-center gap-2">
                        <UsersRound class="size-5 text-kgj-gold-deep" />
                        <h2 id="workload-heading" class="text-lg font-semibold">
                            Beban per cabang
                        </h2>
                    </div>
                    <p class="mt-1 text-sm text-kgj-muted">
                        Jumlah lead aktif berdasarkan CS aktif di tiap cabang.
                    </p>
                </div>
                <ul
                    v-if="workload.length"
                    class="divide-y divide-kgj-line border-t border-kgj-line"
                >
                    <li
                        v-for="branch in workload"
                        :key="branch.name"
                        class="flex items-center justify-between gap-4 px-5 py-4 sm:px-7"
                    >
                        <span>
                            <strong class="block text-sm">{{
                                branch.name
                            }}</strong>
                            <span class="mt-1 block text-xs text-kgj-muted">{{
                                branch.cs_name || 'Belum ada CS aktif'
                            }}</span>
                        </span>
                        <strong class="text-lg tabular-nums">{{
                            branch.lead_count
                        }}</strong>
                    </li>
                </ul>
                <p
                    v-else
                    class="border-t border-kgj-line px-5 py-10 text-center text-sm text-kgj-muted sm:px-7"
                >
                    Belum ada cabang untuk dianalisis.
                </p>
            </section>

            <section
                v-else
                class="kgj-surface p-5 sm:p-7"
                aria-labelledby="scope-heading"
            >
                <CheckCircle2 class="size-5 text-kgj-gold-deep" />
                <h2 id="scope-heading" class="mt-4 text-lg font-semibold">
                    Fokus pada lead Anda
                </h2>
                <p class="mt-2 text-sm leading-6 text-kgj-muted">
                    Laporan ini hanya menampilkan lead yang menjadi tanggung
                    jawab Anda.
                </p>
            </section>
        </div>
    </section>
</template>
