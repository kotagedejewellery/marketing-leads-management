<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarClock,
    ClipboardList,
    FileWarning,
    UserRoundPlus,
} from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    priorities: {
        total: number;
        unassigned: number;
        follow_up_due: number;
        needs_qualification: number;
    };
    recent_leads: Array<{
        id: number;
        name: string | null;
        current_stage: string | null;
        next_follow_up_at: string | null;
        updated_at: string | null;
        owner_name: string | null;
    }>;
}>();

const isAdmin = usePage().props.auth.user?.role === 'admin';
const priorityRows = computed(() => [
    {
        label: 'Tindak lanjut perlu dicek',
        detail: 'Jadwal follow-up sudah melewati waktunya.',
        value: props.priorities.follow_up_due,
        href: '/leads?queue=follow_up_due',
        icon: CalendarClock,
    },
    {
        label: 'Data kualifikasi belum lengkap',
        detail: 'Kebutuhan, waktu pemakaian, atau budget belum terisi.',
        value: props.priorities.needs_qualification,
        href: '/leads?queue=needs_qualification',
        icon: FileWarning,
    },
    ...(isAdmin
        ? [
              {
                  label: 'Lead belum memiliki penanggung jawab',
                  detail: 'Bagikan ke CS agar percakapan dapat ditindaklanjuti.',
                  value: props.priorities.unassigned,
                  href: '/leads?queue=unassigned',
                  icon: UserRoundPlus,
              },
          ]
        : []),
]);

const stageLabel = (stage: string | null) =>
    ({
        clicked: 'Klik saja',
        asked: 'Tanya-tanya',
        price_list: 'Sampai pricelist',
        offline: 'Datang offline',
        online: 'Diskusi online',
        closing_pending_transfer: 'Closing, belum transfer',
        closing: 'Closing',
        repeat_order: 'Repeat order',
    })[stage ?? ''] ?? 'Belum ditandai';

const formatUpdatedAt = (updatedAt: string | null) =>
    updatedAt
        ? new Intl.DateTimeFormat('id-ID', {
              day: 'numeric',
              month: 'short',
              hour: '2-digit',
              minute: '2-digit',
          }).format(new Date(updatedAt))
        : 'Waktu pembaruan tidak tersedia';
</script>

<template>
    <Head title="Dashboard" />
    <section class="kgj-page">
        <div
            class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"
        >
            <div>
                <h1 class="kgj-title">Prioritas kerja.</h1>
                <p class="kgj-subtitle">
                    Mulai dari pekerjaan yang membutuhkan perhatian, lalu buka
                    daftar lead untuk menyelesaikannya.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link href="/leads" class="kgj-button-primary"
                    ><ClipboardList class="size-4" />Buka leads</Link
                >
                <Link href="/statistics" class="kgj-button-secondary"
                    >Lihat laporan<ArrowRight class="size-4"
                /></Link>
            </div>
        </div>

        <section
            class="mt-8 overflow-hidden rounded-2xl bg-kgj-ink text-[#fffdf8] shadow-[0_18px_38px_rgba(47,39,25,0.16)]"
            aria-labelledby="priority-heading"
        >
            <div class="flex flex-col gap-3 px-5 py-6 sm:px-7 sm:py-7">
                <h2 id="priority-heading" class="text-xl font-semibold">
                    Yang perlu dikerjakan sekarang
                </h2>
                <p class="max-w-2xl text-sm leading-6 text-white/65">
                    {{ priorities.total }} lead aktif berada dalam cakupan akun
                    Anda. Urutkan pekerjaan dari daftar berikut.
                </p>
            </div>
            <div class="divide-y divide-white/12 border-t border-white/12">
                <Link
                    v-for="item in priorityRows"
                    :key="item.label"
                    :href="item.href"
                    class="group grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-4 px-5 py-4 transition hover:bg-white/6 sm:px-7"
                >
                    <component :is="item.icon" class="size-5 text-[#e6c17e]" />
                    <span>
                        <strong class="block text-sm font-semibold">{{
                            item.label
                        }}</strong>
                        <span
                            class="mt-1 block text-xs leading-5 text-white/60"
                            >{{ item.detail }}</span
                        >
                    </span>
                    <span class="flex items-center gap-3">
                        <strong class="text-2xl font-semibold tabular-nums">{{
                            item.value
                        }}</strong>
                        <ArrowRight
                            class="size-4 text-white/55 transition-transform group-hover:translate-x-0.5 group-focus-visible:translate-x-0.5"
                        />
                    </span>
                </Link>
            </div>
        </section>

        <section
            class="kgj-surface mt-7 overflow-hidden"
            aria-labelledby="recent-heading"
        >
            <div
                class="flex items-center justify-between gap-4 px-5 py-5 sm:px-7"
            >
                <div>
                    <h2 id="recent-heading" class="text-lg font-semibold">
                        Update terakhir
                    </h2>
                    <p class="mt-1 text-sm text-kgj-muted">
                        Lead yang terakhir diperbarui dalam cakupan Anda.
                    </p>
                </div>
                <Link
                    href="/leads"
                    class="text-sm font-semibold text-kgj-gold-deep underline decoration-[#c4a45d] underline-offset-4"
                    >Semua lead</Link
                >
            </div>
            <ul
                v-if="recent_leads.length"
                class="divide-y divide-kgj-line border-t border-kgj-line"
            >
                <li v-for="lead in recent_leads" :key="lead.id">
                    <Link
                        :href="`/leads/${lead.id}`"
                        class="group grid gap-2 px-5 py-4 transition hover:bg-[#fcfaf5] sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center sm:gap-6 sm:px-7"
                    >
                        <span class="min-w-0">
                            <strong class="block truncate text-sm">{{
                                lead.name || 'Tanpa nama'
                            }}</strong>
                            <span class="mt-1 block text-xs text-kgj-muted">{{
                                lead.owner_name || 'Belum ditugaskan'
                            }}</span>
                        </span>
                        <span class="text-xs text-kgj-muted sm:text-right"
                            ><strong
                                class="block text-sm font-medium text-kgj-ink"
                                >{{ stageLabel(lead.current_stage) }}</strong
                            >Diperbarui
                            {{ formatUpdatedAt(lead.updated_at) }}</span
                        >
                        <ArrowRight
                            class="hidden size-4 text-kgj-gold-deep transition-transform group-hover:translate-x-0.5 sm:block"
                        />
                    </Link>
                </li>
            </ul>
            <p
                v-else
                class="border-t border-kgj-line px-5 py-10 text-center text-sm text-kgj-muted sm:px-7"
            >
                Belum ada lead aktif untuk ditampilkan.
            </p>
        </section>
    </section>
</template>
