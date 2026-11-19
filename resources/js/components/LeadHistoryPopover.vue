<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronDown, History, LoaderCircle } from '@lucide/vue';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';

type HistoryEntry = {
    action: string;
    detail: string | null;
    created_at: string | null;
    actor_name: string | null;
};
type HistoryMeta = {
    lead_created_on: string | null;
    created_at: string | null;
    last_activity_at: string | null;
    next_follow_up_at: string | null;
    crm_taken_over_at: string | null;
};

const props = withDefaults(
    defineProps<{
        leadId: number;
        label: string;
        align?: 'start' | 'center' | 'end';
        triggerClass?: string;
        contentClass?: string;
    }>(),
    {
        align: 'end',
        triggerClass:
            'flex min-h-11 w-full cursor-pointer items-center justify-between gap-2 font-semibold text-[#4f514a] hover:text-kgj-gold-deep focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-kgj-gold',
    },
);

const open = ref(false);
const loading = ref(false);
const error = ref('');
const history = ref<HistoryEntry[]>([]);
const meta = ref<HistoryMeta | null>(null);
const now = ref(Date.now());
let clock: number | undefined;
const formatHistoryTime = (value: string | null) =>
    value ? new Date(value).toLocaleString('id-ID') : 'Waktu tidak tercatat';
const elapsed = (value: string | null) => {
    if (!value) return 'Belum ada aktivitas';
    const milliseconds = Math.max(0, now.value - new Date(value).getTime());
    const minutes = Math.floor(milliseconds / 60000);
    if (minutes < 1) return 'Baru saja';
    if (minutes < 60) return `${minutes} menit lalu`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} jam lalu`;

    return `${Math.floor(hours / 24)} hari lalu`;
};
const followUpLabel = computed(() => {
    const dueAt = meta.value?.next_follow_up_at;
    if (!dueAt) return 'Belum dijadwalkan';
    const minutes = Math.round((new Date(dueAt).getTime() - now.value) / 60000);
    if (minutes === 0) return 'Jatuh tempo sekarang';
    const duration = minutes < 0 ? Math.abs(minutes) : minutes;
    const label =
        duration < 60
            ? `${duration} menit`
            : `${Math.floor(duration / 60)} jam`;

    return minutes < 0 ? `Terlambat ${label}` : `${label} lagi`;
});
const leadAge = computed(() =>
    elapsed(meta.value?.lead_created_on || meta.value?.created_at || null),
);

const loadHistory = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/leads/${props.leadId}/history-preview`, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) throw new Error('Gagal memuat riwayat.');
        const payload = (await response.json()) as {
            data?: unknown;
            meta?: HistoryMeta;
        };
        if (!Array.isArray(payload.data))
            throw new Error('Format riwayat tidak valid.');
        history.value = payload.data as HistoryEntry[];
        meta.value = payload.meta || null;
    } catch {
        error.value = 'Riwayat tidak dapat dimuat. Coba lagi.';
    } finally {
        loading.value = false;
    }
};

watch(open, (isOpen) => {
    if (clock) window.clearInterval(clock);
    if (isOpen) {
        now.value = Date.now();
        clock = window.setInterval(() => (now.value = Date.now()), 60000);
        void loadHistory();
    }
});
onBeforeUnmount(() => {
    if (clock) window.clearInterval(clock);
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <button :class="triggerClass" type="button">
                <span class="flex items-center gap-2">
                    <History class="size-4 shrink-0" aria-hidden="true" />
                    {{ label }}
                </span>
                <ChevronDown class="size-4 shrink-0" aria-hidden="true" />
            </button>
        </PopoverTrigger>
        <PopoverContent
            :align="align"
            :class="[
                'w-[min(20rem,calc(100vw-2rem))] border-[#ded5c3] bg-kgj-paper p-3 text-[#31332f]',
                contentClass,
            ]"
        >
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold">Riwayat terbaru</p>
                <Link
                    :href="`/leads/${leadId}`"
                    class="text-xs font-semibold text-kgj-gold-deep underline underline-offset-4"
                >
                    Detail lengkap
                </Link>
            </div>
            <div
                v-if="!loading && meta"
                class="mt-3 grid gap-2 border-y border-[#e7dfcf] py-3 text-xs text-[#62665b]"
                aria-live="polite"
            >
                <p>
                    Aktivitas terakhir:
                    <strong class="text-[#31332f]">{{
                        elapsed(meta.last_activity_at)
                    }}</strong>
                </p>
                <p>
                    Follow-up:
                    <strong class="text-[#31332f]">{{ followUpLabel }}</strong>
                </p>
                <p>
                    Usia lead:
                    <strong class="text-[#31332f]">{{ leadAge }}</strong>
                </p>
                <p
                    v-if="meta.crm_taken_over_at"
                    class="font-semibold text-[#8a6b2d]"
                >
                    Ditandai untuk CRM
                </p>
            </div>
            <div v-if="loading" class="py-5 text-center text-sm text-[#62665b]">
                <LoaderCircle
                    class="mx-auto mb-2 size-4 animate-spin"
                    aria-hidden="true"
                />
                Memuat riwayat…
            </div>
            <div
                v-else-if="error"
                class="py-3 text-sm text-kgj-rose"
                role="alert"
            >
                {{ error }}
                <button
                    class="mt-2 block cursor-pointer font-semibold text-kgj-gold-deep underline underline-offset-4"
                    type="button"
                    @click="loadHistory"
                >
                    Coba lagi
                </button>
            </div>
            <ol v-else-if="history.length" class="mt-3 space-y-3">
                <li
                    v-for="item in history"
                    :key="`${item.action}-${item.created_at}`"
                    class="border-t border-[#e7dfcf] pt-3 first:border-t-0 first:pt-0"
                >
                    <p class="text-sm font-semibold">{{ item.action }}</p>
                    <p
                        v-if="item.detail"
                        class="mt-1 text-xs leading-relaxed text-[#62665b]"
                    >
                        {{ item.detail }}
                    </p>
                    <p class="mt-1 text-xs text-[#7a7d73]">
                        {{ item.actor_name || 'Sistem' }} ·
                        {{ formatHistoryTime(item.created_at) }}
                    </p>
                </li>
            </ol>
            <p v-else class="py-3 text-sm text-[#62665b]">Belum ada riwayat.</p>
        </PopoverContent>
    </Popover>
</template>
