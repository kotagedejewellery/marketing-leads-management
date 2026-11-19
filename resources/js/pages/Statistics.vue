<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BarChart3,
    CalendarClock,
    CheckCircle2,
    ClipboardCheck,
    UsersRound,
} from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps<{
    stats: {
        total: number;
        cold: number;
        warm: number;
        hot: number;
        closing: number;
        unclassified: number;
    };
    quality: {
        total: number;
        complete: number;
        follow_up_set: number;
        needs_qualification: number;
        without_follow_up: number;
    };
    outcomes: {
        closing: {
            total: number;
            factors: {
                key: string;
                label: string;
                count: number;
                active_count: number;
            }[];
        };
        loss: {
            total: number;
            factors: {
                key: string;
                label: string;
                count: number;
                active_count: number;
            }[];
        };
    };
    outcomePeriod: { from: string | null; to: string | null };
    branches: Array<{ id: number; name: string }>;
    filters: { branch: number | null };
    workload: Array<{
        id: number | null;
        name: string;
        lead_count: number;
        is_unassigned_source_branch?: boolean;
    }>;
}>();

const isAdmin = usePage().props.auth.user?.role === 'admin';
const outcomeDateFrom = ref(props.outcomePeriod.from || '');
const outcomeDateTo = ref(props.outcomePeriod.to || '');
const branch = ref(props.filters.branch ? String(props.filters.branch) : '');
const selectedBranchName = computed(
    () =>
        props.branches.find((item) => item.id === Number(branch.value))?.name ||
        'Semua cabang',
);
const workloadTitle = computed(() =>
    branch.value ? 'Ringkasan cabang' : 'Beban per cabang',
);
const workloadDescription = computed(() =>
    branch.value
        ? 'Jumlah lead aktif dari cabang sumber yang dipilih.'
        : 'Jumlah lead aktif berdasarkan cabang sumber.',
);
const stages = computed(() => [
    { label: 'Cold', note: 'Klik saja', value: props.stats.cold },
    {
        label: 'Warm',
        note: 'Tanya-Tanya atau pricelist',
        value: props.stats.warm,
    },
    {
        label: 'Hot',
        note: 'Datang offline atau diskusi online',
        value: props.stats.hot,
    },
    {
        label: 'Closing',
        note: 'Closing / Repeat Order',
        value: props.stats.closing,
    },
    {
        label: 'Belum ditandai',
        note: 'Tahap belum dipilih saat input atau impor',
        value: props.stats.unclassified,
    },
]);
const outcomeReports = computed(() => [
    {
        key: 'closing',
        title: 'Faktor Closing',
        description: 'Kenapa customer deal.',
        total: props.outcomes.closing.total,
        factors: props.outcomes.closing.factors,
    },
    {
        key: 'loss',
        title: 'Faktor Loss',
        description: 'Kenapa customer tidak melanjutkan.',
        total: props.outcomes.loss.total,
        factors: props.outcomes.loss.factors,
    },
]);
const percentage = (value: number, total = props.stats.total) =>
    total ? `${Math.round((value / total) * 100)}%` : '0%';
const outcomePeriodText = computed(() => {
    if (outcomeDateFrom.value && outcomeDateTo.value) {
        return `${outcomeDateFrom.value} s.d. ${outcomeDateTo.value}`;
    }

    return outcomeDateFrom.value
        ? `sejak ${outcomeDateFrom.value}`
        : outcomeDateTo.value
          ? `sampai ${outcomeDateTo.value}`
          : 'seluruh periode';
});
const applyOutcomePeriod = () =>
    router.get(
        '/statistics',
        {
            branch: isAdmin && branch.value ? branch.value : undefined,
            outcome_date_from: outcomeDateFrom.value || undefined,
            outcome_date_to: outcomeDateTo.value || undefined,
        },
        { preserveScroll: true, preserveState: true, replace: true },
    );
const outcomeLeadsHref = (outcome: string, factor: string) => {
    const params = new URLSearchParams({ outcome, outcome_factor: factor });
    if (isAdmin && branch.value) {
        params.set('branch', branch.value);
    }

    return `/leads?${params.toString()}`;
};
const workloadLeadsHref = (item: (typeof props.workload)[number]) =>
    item.is_unassigned_source_branch
        ? '/leads?source_branch_missing=1'
        : `/leads?branch=${item.id}`;
</script>

<template>
    <Head title="Statistik" />
    <section class="kgj-page">
        <div
            class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"
        >
            <div>
                <h1 class="kgj-title">Kesiapan tindak lanjut.</h1>
                <p class="kgj-subtitle">
                    Ringkasan pekerjaan yang perlu dituntaskan pada lead dalam
                    cakupan akses Anda.
                </p>
                <p v-if="isAdmin" class="mt-2 text-sm text-kgj-muted">
                    Cakupan statistik:
                    <strong class="text-kgj-ink">{{
                        selectedBranchName
                    }}</strong>
                </p>
            </div>
            <BarChart3 class="hidden size-7 text-kgj-gold sm:block" />
        </div>

        <form
            class="kgj-surface mt-7 flex flex-col gap-3 p-4 sm:flex-row sm:items-end"
            @submit.prevent="applyOutcomePeriod"
        >
            <label
                v-if="isAdmin"
                class="grid flex-1 gap-2 text-sm font-semibold"
                >Cabang sumber<select v-model="branch" class="kgj-input">
                    <option value="">Semua cabang</option>
                    <option
                        v-for="item in branches"
                        :key="item.id"
                        :value="String(item.id)"
                    >
                        {{ item.name }}
                    </option>
                </select></label
            >
            <label class="grid flex-1 gap-2 text-sm font-semibold"
                >Hasil akhir dari<input
                    v-model="outcomeDateFrom"
                    class="kgj-input"
                    type="date"
            /></label>
            <label class="grid flex-1 gap-2 text-sm font-semibold"
                >Hasil akhir sampai<input
                    v-model="outcomeDateTo"
                    class="kgj-input"
                    type="date"
            /></label>
            <button class="kgj-button-primary" type="submit">
                Terapkan periode
            </button>
            <Link
                v-if="outcomeDateFrom || outcomeDateTo || branch"
                class="kgj-button-secondary text-center"
                href="/statistics"
            >
                Reset
            </Link>
        </form>

        <section
            class="kgj-surface mt-8 overflow-hidden"
            aria-label="Ringkasan kualitas data"
        >
            <dl
                class="grid divide-y divide-kgj-line sm:grid-cols-3 sm:divide-x sm:divide-y-0"
            >
                <div class="px-5 py-5 sm:px-7">
                    <dt class="text-sm text-kgj-muted">Lead tersedia</dt>
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
                    <p
                        v-if="quality.needs_qualification"
                        class="mt-1 text-xs text-[#865124]"
                    >
                        {{ quality.needs_qualification }} perlu dilengkapi
                    </p>
                </div>
                <div class="px-5 py-5 sm:px-7">
                    <dt class="text-sm text-kgj-muted">Follow-up terjadwal</dt>
                    <dd class="mt-1 text-2xl font-semibold tabular-nums">
                        {{ quality.follow_up_set }}
                        <span class="text-sm font-medium text-kgj-muted">{{
                            percentage(quality.follow_up_set, quality.total)
                        }}</span>
                    </dd>
                    <p
                        v-if="quality.without_follow_up"
                        class="mt-1 text-xs text-[#865124]"
                    >
                        {{ quality.without_follow_up }} belum dijadwalkan
                    </p>
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
                        <strong
                            class="min-w-12 text-right text-2xl font-bold tabular-nums"
                            >{{ percentage(stage.value) }}</strong
                        >
                        <span
                            class="min-w-12 text-right text-xs font-medium text-kgj-muted tabular-nums"
                            >{{ stage.value }} lead</span
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
                            {{ workloadTitle }}
                        </h2>
                    </div>
                    <p class="mt-1 text-sm text-kgj-muted">
                        {{ workloadDescription }}
                    </p>
                </div>
                <ul
                    v-if="workload.length"
                    class="divide-y divide-kgj-line border-t border-kgj-line"
                >
                    <li v-for="branch in workload" :key="branch.name">
                        <Link
                            :href="workloadLeadsHref(branch)"
                            class="flex min-h-14 items-center justify-between gap-4 px-5 py-4 transition hover:bg-[#faf7ef] sm:px-7"
                        >
                            <strong class="block text-sm">{{
                                branch.name
                            }}</strong>
                            <span class="flex items-center gap-3">
                                <span class="text-xs text-kgj-muted"
                                    >Buka lead</span
                                >
                                <strong class="text-lg tabular-nums">{{
                                    branch.lead_count
                                }}</strong>
                            </span>
                        </Link>
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
                class="kgj-surface overflow-hidden"
                aria-labelledby="priority-heading"
            >
                <div class="p-5 sm:p-7">
                    <ClipboardCheck class="size-5 text-kgj-gold-deep" />
                    <h2
                        id="priority-heading"
                        class="mt-4 text-lg font-semibold"
                    >
                        Prioritas Anda
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-kgj-muted">
                        Mencakup lead yang Anda tangani, dampingi, atau tersedia
                        dari cabang Anda.
                    </p>
                </div>
                <div
                    v-if="
                        quality.needs_qualification > 0 ||
                        quality.without_follow_up > 0
                    "
                    class="divide-y divide-kgj-line border-t border-kgj-line"
                >
                    <Link
                        v-if="quality.needs_qualification > 0"
                        href="/leads?queue=needs_qualification"
                        class="flex min-h-16 items-center gap-3 px-5 py-4 transition hover:bg-[#faf7ef] sm:px-7"
                    >
                        <ClipboardCheck
                            class="size-5 shrink-0 text-kgj-gold-deep"
                        />
                        <span class="min-w-0 flex-1">
                            <strong class="block text-sm"
                                >Lengkapi kualifikasi</strong
                            >
                            <span class="mt-1 block text-xs text-kgj-muted"
                                >{{ quality.needs_qualification }} lead perlu
                                intent, waktu pakai, atau budget.</span
                            >
                        </span>
                        <ArrowRight class="size-4 shrink-0" />
                    </Link>
                    <Link
                        v-if="quality.without_follow_up > 0"
                        href="/leads?follow_up=none"
                        class="flex min-h-16 items-center gap-3 px-5 py-4 transition hover:bg-[#faf7ef] sm:px-7"
                    >
                        <CalendarClock
                            class="size-5 shrink-0 text-kgj-gold-deep"
                        />
                        <span class="min-w-0 flex-1">
                            <strong class="block text-sm"
                                >Jadwalkan follow-up</strong
                            >
                            <span class="mt-1 block text-xs text-kgj-muted"
                                >{{ quality.without_follow_up }} lead belum
                                memiliki jadwal tindak lanjut.</span
                            >
                        </span>
                        <ArrowRight class="size-4 shrink-0" />
                    </Link>
                </div>
                <p
                    v-if="
                        quality.needs_qualification === 0 &&
                        quality.without_follow_up === 0
                    "
                    class="flex items-center gap-2 border-t border-kgj-line px-5 py-4 text-sm font-semibold text-[#49654e] sm:px-7"
                >
                    <CheckCircle2 class="size-4" /> Semua lead siap
                    ditindaklanjuti.
                </p>
            </section>
        </div>

        <section class="mt-7 grid gap-7 xl:grid-cols-2">
            <section
                v-for="report in outcomeReports"
                :key="report.key"
                class="kgj-surface overflow-hidden"
                :aria-labelledby="`${report.key}-factors-heading`"
            >
                <div class="px-5 py-5 sm:px-7">
                    <div class="flex items-baseline justify-between gap-4">
                        <div>
                            <h2
                                :id="`${report.key}-factors-heading`"
                                class="text-lg font-semibold"
                            >
                                {{ report.title }}
                            </h2>
                            <p class="mt-1 text-sm text-kgj-muted">
                                {{ report.description }}
                            </p>
                            <p class="mt-1 text-xs text-kgj-muted">
                                Periode {{ outcomePeriodText }}. Termasuk lead
                                yang telah diarsipkan.
                            </p>
                        </div>
                        <strong class="text-2xl tabular-nums">
                            {{ report.total }}
                        </strong>
                    </div>
                </div>
                <dl class="divide-y divide-kgj-line border-t border-kgj-line">
                    <template
                        v-for="factor in report.factors"
                        :key="factor.key"
                    >
                        <Link
                            v-if="factor.active_count"
                            :href="outcomeLeadsHref(report.key, factor.key)"
                            class="flex items-center justify-between gap-4 px-5 py-3 transition hover:bg-[#faf7ef] sm:px-7"
                        >
                            <span class="min-w-0 text-sm leading-5">
                                {{ factor.label }}
                                <span class="block text-xs text-kgj-muted"
                                    >{{ factor.active_count }} aktif — buka
                                    daftar</span
                                >
                            </span>
                            <span
                                class="flex shrink-0 items-baseline gap-2 tabular-nums"
                            >
                                <strong class="text-sm">
                                    {{ percentage(factor.count, report.total) }}
                                </strong>
                                <span class="text-xs text-kgj-muted">
                                    {{ factor.count }} lead
                                </span>
                            </span>
                        </Link>
                        <div
                            v-else
                            class="flex items-center justify-between gap-4 px-5 py-3 sm:px-7"
                        >
                            <span class="text-sm leading-5">{{
                                factor.label
                            }}</span>
                            <span
                                class="flex shrink-0 items-baseline gap-2 tabular-nums"
                            >
                                <strong class="text-sm">
                                    {{ percentage(factor.count, report.total) }}
                                </strong>
                                <span class="text-xs text-kgj-muted">
                                    {{ factor.count }} lead
                                </span>
                            </span>
                        </div>
                    </template>
                </dl>
            </section>
        </section>
    </section>
</template>
