<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronDown, History, LoaderCircle } from '@lucide/vue';
import { ref, watch } from 'vue';
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

const props = withDefaults(
    defineProps<{
        leadId: number;
        label: string;
        align?: 'start' | 'center' | 'end';
        triggerClass?: string;
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
const formatHistoryTime = (value: string | null) =>
    value ? new Date(value).toLocaleString('id-ID') : 'Waktu tidak tercatat';

const loadHistory = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/leads/${props.leadId}/history-preview`, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) throw new Error('Gagal memuat riwayat.');
        const payload = (await response.json()) as { data?: unknown };
        if (!Array.isArray(payload.data))
            throw new Error('Format riwayat tidak valid.');
        history.value = payload.data as HistoryEntry[];
    } catch {
        error.value = 'Riwayat tidak dapat dimuat. Coba lagi.';
    } finally {
        loading.value = false;
    }
};

watch(open, (isOpen) => {
    if (isOpen) void loadHistory();
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
            class="w-[min(20rem,calc(100vw-2rem))] border-[#ded5c3] bg-kgj-paper p-3 text-[#31332f]"
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
