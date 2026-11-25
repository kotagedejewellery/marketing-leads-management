<script setup lang="ts">
import { CheckCircle2, CircleAlert, LoaderCircle } from '@lucide/vue';
import { computed } from 'vue';

type SaveState = 'idle' | 'pending' | 'saving' | 'saved' | 'error' | 'conflict';

const props = defineProps<{
    state?: SaveState;
    message?: string;
}>();
const emit = defineEmits<{ retry: []; resolveConflict: [] }>();

const label = computed(() => {
    if (props.state === 'pending') {
        return props.message || 'Pilih faktor Closing untuk melanjutkan.';
    }
    if (props.state === 'saving') return 'Menyimpan…';
    if (props.state === 'saved') return 'Tersimpan';
    return props.message || 'Gagal menyimpan';
});
</script>

<template>
    <div
        v-if="state && state !== 'idle'"
        class="mt-2 flex min-h-5 items-center gap-2 text-xs"
        :class="
            state === 'error' || state === 'conflict'
                ? 'text-kgj-rose'
                : state === 'pending'
                  ? 'text-kgj-gold-deep'
                  : 'text-[#62665b]'
        "
        role="status"
        aria-live="polite"
    >
        <LoaderCircle
            v-if="state === 'saving'"
            class="size-3.5 shrink-0 animate-spin"
            aria-hidden="true"
        />
        <CheckCircle2
            v-else-if="state === 'saved'"
            class="size-3.5 shrink-0 text-kgj-gold-deep"
            aria-hidden="true"
        />
        <CircleAlert v-else class="size-3.5 shrink-0" aria-hidden="true" />
        <span>{{ label }}</span>
        <button
            v-if="state === 'error'"
            class="cursor-pointer font-semibold text-kgj-gold-deep underline underline-offset-4"
            type="button"
            @click="emit('retry')"
        >
            Coba lagi
        </button>
        <button
            v-else-if="state === 'conflict'"
            class="cursor-pointer font-semibold text-kgj-gold-deep underline underline-offset-4"
            type="button"
            @click="emit('resolveConflict')"
        >
            Buka detail
        </button>
    </div>
</template>
