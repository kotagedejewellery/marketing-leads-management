<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { CheckCircle2, History, LoaderCircle } from '@lucide/vue';
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { budgetOptions } from '@/lib/lead-options';

type Lead = {
    id: number;
    intent_buying: string | null;
    usage_date: string | null;
    budget: string | null;
    keterangan: string | null;
    next_follow_up_at: string | null;
    milestones: string[];
    latest_activity: string | null;
    latest_activity_detail: string | null;
    latest_activity_at: string | null;
};
type QuickUpdateFields = {
    intent_buying: string;
    usage_date: string;
    budget: string;
    keterangan: string;
    next_follow_up_at: string;
    milestones: string[];
};
type QuickUpdatePayload = { inline: true } & Partial<QuickUpdateFields>;

const props = withDefaults(
    defineProps<{ lead: Lead; variant?: 'card' | 'table' }>(),
    { variant: 'card' },
);
const emit = defineEmits<{
    'dirty-change': [leadId: number, isDirty: boolean];
}>();
const toDateTimeLocal = (value: string | null) =>
    value ? value.replace(' ', 'T').slice(0, 16) : '';
const values = reactive<QuickUpdateFields>({
    intent_buying: props.lead.intent_buying || '',
    usage_date: props.lead.usage_date || '',
    budget: props.lead.budget || '',
    keterangan: props.lead.keterangan || '',
    next_follow_up_at: toDateTimeLocal(props.lead.next_follow_up_at),
    milestones: [...props.lead.milestones],
});
const groups = [
    {
        label: 'Cold',
        cellClass: 'min-w-36 p-3 align-middle',
        gridClass: 'grid min-h-11 grid-cols-1 gap-2',
        items: [['clicked', 'Klik saja']],
    },
    {
        label: 'Warm',
        cellClass: 'min-w-56 p-3 align-middle',
        gridClass: 'grid min-h-11 grid-cols-2 gap-3',
        items: [
            ['asked', 'Tanya-Tanya'],
            ['price_list', 'Sampai Pricelist'],
        ],
    },
    {
        label: 'Hot',
        cellClass: 'min-w-80 p-3 align-middle',
        gridClass: 'grid min-h-11 grid-cols-3 gap-3',
        items: [
            ['offline', 'Datang Offline'],
            ['online', 'Diskusi Online'],
            ['closing_repeat', 'Closing / Repeat Order'],
        ],
    },
] as const;
const latestActivityTime = computed(() =>
    props.lead.latest_activity_at
        ? new Date(props.lead.latest_activity_at).toLocaleString('id-ID')
        : '',
);
const errorId = `lead-${props.lead.id}-quick-update-error`;
const processing = ref(false);
const queued = ref(false);
const waiting = ref(false);
const hasSaved = ref(false);
const saveError = ref('');
const invalidField = ref<string | null>(null);
let saveTimer: ReturnType<typeof setTimeout> | undefined;

const snapshot = (): QuickUpdateFields => ({
    intent_buying: values.intent_buying,
    usage_date: values.usage_date,
    budget: values.budget,
    keterangan: values.keterangan,
    next_follow_up_at: values.next_follow_up_at,
    milestones: [...values.milestones].sort(),
});
const signature = (payload: QuickUpdateFields) => JSON.stringify(payload);
const savedValues = reactive<QuickUpdateFields>(snapshot());
const isDirty = computed(
    () => signature(snapshot()) !== signature(savedValues),
);
const saveStatus = computed(() => {
    if (processing.value) return 'Menyimpan…';
    if (saveError.value) return saveError.value;
    if (waiting.value || isDirty.value) return 'Menyiapkan simpan…';
    return hasSaved.value ? 'Tersimpan' : '';
});
const isInvalid = (field: string) => invalidField.value === field;

const changedPayload = (current: QuickUpdateFields): QuickUpdatePayload => {
    const payload: QuickUpdatePayload = { inline: true };
    if (current.intent_buying !== savedValues.intent_buying)
        payload.intent_buying = current.intent_buying;
    if (current.usage_date !== savedValues.usage_date)
        payload.usage_date = current.usage_date;
    if (current.budget !== savedValues.budget) payload.budget = current.budget;
    if (current.keterangan !== savedValues.keterangan)
        payload.keterangan = current.keterangan;
    if (current.next_follow_up_at !== savedValues.next_follow_up_at)
        payload.next_follow_up_at = current.next_follow_up_at;
    if (
        JSON.stringify(current.milestones) !==
        JSON.stringify(savedValues.milestones)
    )
        payload.milestones = current.milestones;

    return payload;
};

watch(isDirty, (dirty) => emit('dirty-change', props.lead.id, dirty), {
    immediate: true,
});

const save = () => {
    waiting.value = false;
    if (!isDirty.value) return;
    if (processing.value) {
        queued.value = true;

        return;
    }

    const requestValues = snapshot();
    const payload = changedPayload(requestValues);
    processing.value = true;
    saveError.value = '';
    invalidField.value = null;
    let handled = false;
    router.patch(`/leads/${props.lead.id}`, payload, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            Object.assign(savedValues, requestValues);
            hasSaved.value = true;
            handled = true;
        },
        onError: (errors) => {
            handled = true;
            const [field, message] = Object.entries(errors)[0] || [];
            invalidField.value = field || null;
            saveError.value = message
                ? `${field === 'budget' ? 'Budget' : 'Data'}: ${message}`
                : 'Gagal menyimpan. Ubah kembali data untuk mencoba lagi.';
        },
        onFinish: () => {
            processing.value = false;
            if (!handled) {
                saveError.value =
                    'Simpan gagal. Periksa koneksi lalu coba lagi.';
            }
            if (queued.value) {
                queued.value = false;
                queueSave(0);
            }
        },
    });
};
const queueSave = (delay = 0) => {
    saveError.value = '';
    invalidField.value = null;
    if (saveTimer) clearTimeout(saveTimer);
    if (processing.value) {
        queued.value = true;

        return;
    }
    waiting.value = true;
    saveTimer = setTimeout(save, delay);
};
const saveImmediately = () => queueSave();
const saveAfterTyping = () => queueSave(650);
const retrySave = () => queueSave(0);

onBeforeUnmount(() => {
    if (saveTimer) clearTimeout(saveTimer);
});
</script>

<template>
    <template v-if="variant === 'table'">
        <td v-for="group in groups" :key="group.label" :class="group.cellClass">
            <div :class="group.gridClass">
                <label
                    v-for="[value, label] in group.items"
                    :key="value"
                    class="flex min-h-11 cursor-pointer items-center gap-2 text-sm leading-tight"
                >
                    <input
                        v-model="values.milestones"
                        class="size-4 shrink-0 accent-kgj-gold"
                        type="checkbox"
                        :value="value"
                        :aria-label="label"
                        @change="saveImmediately"
                    />
                    <span>{{ label }}</span>
                </label>
            </div>
        </td>
        <td class="min-w-40 p-3 align-middle">
            <select
                v-model="values.intent_buying"
                class="kgj-input w-full"
                aria-label="Intent buying"
                :aria-invalid="isInvalid('intent_buying')"
                :aria-describedby="saveError ? errorId : undefined"
                @change="saveImmediately"
            >
                <option value="">Pilih kebutuhan</option>
                <option>Tunangan</option>
                <option>Lamaran</option>
                <option>Nikahan</option>
                <option>Anniversary</option>
                <option>Lainnya</option>
            </select>
        </td>
        <td class="min-w-40 p-3 align-middle">
            <input
                v-model="values.usage_date"
                class="kgj-input w-full"
                type="date"
                aria-label="Waktu pemakaian"
                :aria-invalid="isInvalid('usage_date')"
                :aria-describedby="saveError ? errorId : undefined"
                @change="saveImmediately"
            />
        </td>
        <td class="min-w-48 p-3 align-middle">
            <select
                v-model="values.budget"
                class="kgj-input w-full"
                aria-label="Budget"
                :aria-invalid="isInvalid('budget')"
                :aria-describedby="saveError ? errorId : undefined"
                @change="saveImmediately"
            >
                <option value="">Pilih budget</option>
                <option
                    v-for="option in budgetOptions"
                    :key="option"
                    :value="option"
                >
                    {{ option }}
                </option>
            </select>
        </td>
        <td class="min-w-64 p-3 align-middle">
            <input
                v-model="values.keterangan"
                class="kgj-input w-full"
                placeholder="Keterangan"
                aria-label="Keterangan"
                :title="values.keterangan || 'Belum ada keterangan'"
                :aria-invalid="isInvalid('keterangan')"
                :aria-describedby="saveError ? errorId : undefined"
                @input="saveAfterTyping"
                @blur="saveImmediately"
            />
        </td>
        <td class="min-w-48 p-3 align-middle">
            <input
                v-model="values.next_follow_up_at"
                class="kgj-input w-full"
                type="datetime-local"
                aria-label="Tindak lanjut berikutnya"
                :aria-invalid="isInvalid('next_follow_up_at')"
                :aria-describedby="saveError ? errorId : undefined"
                @change="saveImmediately"
            />
        </td>
        <td class="min-w-36 p-3 align-middle text-sm">
            <div class="flex items-center gap-2 text-[#62665b]">
                <History class="size-4 shrink-0" aria-hidden="true" />
                <Link
                    :href="`/leads/${lead.id}`"
                    class="font-semibold text-kgj-gold-deep underline underline-offset-4"
                >
                    Detail
                </Link>
            </div>
            <p
                class="mt-1 truncate text-xs text-[#62665b]"
                :title="`${lead.latest_activity || 'Belum ada aktivitas'} · ${latestActivityTime || '—'}`"
            >
                {{ lead.latest_activity || 'Belum ada aktivitas' }}
            </p>
        </td>
        <td
            class="sticky right-0 z-10 min-w-36 bg-kgj-paper p-3 align-middle group-hover:bg-[#fcfaf5]"
        >
            <p
                :id="saveError ? errorId : undefined"
                class="flex min-h-11 items-center gap-2 text-xs"
                :class="saveError ? 'text-kgj-rose' : 'text-[#62665b]'"
                role="status"
                aria-live="polite"
            >
                <LoaderCircle
                    v-if="processing || waiting"
                    class="size-4 shrink-0 animate-spin"
                    aria-hidden="true"
                />
                <CheckCircle2
                    v-else-if="hasSaved && !saveError"
                    class="size-4 shrink-0 text-kgj-gold-deep"
                    aria-hidden="true"
                />
                {{ saveStatus || '—' }}
            </p>
            <button
                v-if="saveError"
                class="mt-1 text-xs font-semibold text-kgj-gold-deep underline underline-offset-4"
                type="button"
                @click="retrySave"
            >
                Coba lagi
            </button>
        </td>
    </template>
    <section
        v-else
        class="min-w-0 lg:min-w-[34rem]"
        :aria-label="`Update cepat lead ${lead.id}`"
    >
        <div class="grid gap-3 lg:grid-cols-3">
            <fieldset
                v-for="group in groups"
                :key="group.label"
                class="rounded-lg bg-kgj-canvas p-3"
            >
                <legend class="px-1 text-sm font-semibold">
                    {{ group.label }}
                </legend>
                <label
                    v-for="[value, label] in group.items"
                    :key="value"
                    class="mt-2 flex min-h-11 cursor-pointer items-center gap-2 text-sm text-kgj-ink"
                >
                    <input
                        v-model="values.milestones"
                        class="size-4 accent-kgj-gold"
                        type="checkbox"
                        :value="value"
                        @change="saveImmediately"
                    />
                    {{ label }}
                </label>
            </fieldset>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2 text-sm font-semibold"
                >Intent buying
                <select
                    v-model="values.intent_buying"
                    class="kgj-input"
                    :aria-invalid="isInvalid('intent_buying')"
                    @change="saveImmediately"
                >
                    <option value="">Pilih kebutuhan</option>
                    <option>Tunangan</option>
                    <option>Lamaran</option>
                    <option>Nikahan</option>
                    <option>Anniversary</option>
                    <option>Lainnya</option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Waktu pemakaian
                <input
                    v-model="values.usage_date"
                    class="kgj-input"
                    type="date"
                    :aria-invalid="isInvalid('usage_date')"
                    @change="saveImmediately"
                />
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Budget
                <select
                    v-model="values.budget"
                    class="kgj-input"
                    :aria-invalid="isInvalid('budget')"
                    @change="saveImmediately"
                >
                    <option value="">Pilih budget</option>
                    <option
                        v-for="option in budgetOptions"
                        :key="option"
                        :value="option"
                    >
                        {{ option }}
                    </option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold"
                >Tindak lanjut berikutnya
                <input
                    v-model="values.next_follow_up_at"
                    class="kgj-input"
                    type="datetime-local"
                    :aria-invalid="isInvalid('next_follow_up_at')"
                    @change="saveImmediately"
                />
            </label>
        </div>

        <label class="mt-4 grid gap-2 text-sm font-semibold"
            >Keterangan
            <textarea
                v-model="values.keterangan"
                class="kgj-input min-h-24 resize-y"
                placeholder="Catatan percakapan atau kebutuhan lead"
                :aria-invalid="isInvalid('keterangan')"
                @input="saveAfterTyping"
                @blur="saveImmediately"
            />
        </label>

        <div
            class="mt-4 flex flex-col gap-3 border-t border-[#e7dfcf] pt-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-start gap-2 text-sm text-[#62665b]">
                <History class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                <p>
                    <span class="font-semibold text-[#31332f]">Riwayat:</span>
                    {{ lead.latest_activity || 'Belum ada aktivitas.' }}
                    <span v-if="latestActivityTime">
                        · {{ latestActivityTime }}</span
                    >
                    <span v-if="lead.latest_activity_detail" class="block">
                        {{ lead.latest_activity_detail }}
                    </span>
                    <Link
                        :href="`/leads/${lead.id}`"
                        class="ml-1 font-semibold text-kgj-gold-deep underline underline-offset-4"
                    >
                        Detail lengkap
                    </Link>
                </p>
            </div>
            <p
                class="flex min-h-11 items-center gap-2 text-sm"
                :class="saveError ? 'text-kgj-rose' : 'text-[#62665b]'"
                role="status"
                aria-live="polite"
            >
                <LoaderCircle
                    v-if="processing || waiting"
                    class="size-4 animate-spin"
                    aria-hidden="true"
                />
                <CheckCircle2
                    v-else-if="hasSaved && !saveError"
                    class="size-4 text-kgj-gold-deep"
                    aria-hidden="true"
                />
                {{ saveStatus || 'Perubahan tersimpan otomatis.' }}
            </p>
        </div>
    </section>
</template>
