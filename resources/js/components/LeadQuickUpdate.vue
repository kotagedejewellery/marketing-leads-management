<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import LeadHistoryPopover from '@/components/LeadHistoryPopover.vue';
import { budgetOptions } from '@/lib/lead-options';

type Lead = {
    id: number;
    intent_buying: string | null;
    usage_date: string | null;
    budget: string | null;
    keterangan: string | null;
    outcome: string | null;
    outcome_factor: string | null;
    outcome_note: string | null;
    next_follow_up_at: string | null;
    crm_taken_over_at: string | null;
    revision: number;
    milestones: string[];
};
type QuickUpdateFields = {
    intent_buying: string;
    usage_date: string;
    budget: string;
    keterangan: string;
    outcome: string;
    outcome_factor: string;
    outcome_note: string;
    next_follow_up_at: string;
    crm_taken_over: boolean;
    revision: number;
    milestones: string[];
};
type QuickUpdatePayload = { inline: true } & Partial<QuickUpdateFields>;
type SaveState = 'idle' | 'saving' | 'saved' | 'error' | 'conflict';

const props = withDefaults(
    defineProps<{
        lead: Lead;
        outcomeFactors: Record<string, Record<string, string>>;
        variant?: 'card' | 'table';
        retrySaveVersion?: number;
        canMarkCrm?: boolean;
        showSaveFeedback?: boolean;
    }>(),
    {
        variant: 'card',
        retrySaveVersion: 0,
        canMarkCrm: false,
        showSaveFeedback: true,
    },
);
const emit = defineEmits<{
    'dirty-change': [leadId: number, isDirty: boolean];
    'save-state': [leadId: number, state: SaveState, message: string];
}>();
const toDateTimeLocal = (value: string | null) =>
    value ? value.replace(' ', 'T').slice(0, 16) : '';
const values = reactive<QuickUpdateFields>({
    intent_buying: props.lead.intent_buying || '',
    usage_date: props.lead.usage_date || '',
    budget: props.lead.budget || '',
    keterangan: props.lead.keterangan || '',
    outcome: props.lead.outcome || '',
    outcome_factor: props.lead.outcome_factor || '',
    outcome_note: props.lead.outcome_note || '',
    next_follow_up_at: toDateTimeLocal(props.lead.next_follow_up_at),
    crm_taken_over: Boolean(props.lead.crm_taken_over_at),
    revision: props.lead.revision,
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
const errorId = `lead-${props.lead.id}-quick-update-error`;
const processing = ref(false);
const queued = ref(false);
const waiting = ref(false);
const hasSaved = ref(false);
const saveError = ref('');
const hasConflict = ref(false);
const invalidField = ref<string | null>(null);
let saveTimer: ReturnType<typeof setTimeout> | undefined;
let savedTimer: ReturnType<typeof setTimeout> | undefined;

const snapshot = (): QuickUpdateFields => ({
    intent_buying: values.intent_buying,
    usage_date: values.usage_date,
    budget: values.budget,
    keterangan: values.keterangan,
    outcome: values.outcome,
    outcome_factor: values.outcome_factor,
    outcome_note: values.outcome_note,
    next_follow_up_at: values.next_follow_up_at,
    crm_taken_over: values.crm_taken_over,
    revision: values.revision,
    milestones: [...values.milestones].sort(),
});
const signature = (payload: QuickUpdateFields) => JSON.stringify(payload);
const savedValues = reactive<QuickUpdateFields>(snapshot());
const isDirty = computed(
    () => signature(snapshot()) !== signature(savedValues),
);
const isInvalid = (field: string) => invalidField.value === field;
const saveState = computed<SaveState>(() => {
    if (hasConflict.value) return 'conflict';
    if (saveError.value) return 'error';
    if (processing.value || waiting.value || isDirty.value) return 'saving';
    return hasSaved.value ? 'saved' : 'idle';
});

const changedPayload = (current: QuickUpdateFields): QuickUpdatePayload => {
    const payload: QuickUpdatePayload = { inline: true };
    payload.revision = current.revision;
    if (current.intent_buying !== savedValues.intent_buying)
        payload.intent_buying = current.intent_buying;
    if (current.usage_date !== savedValues.usage_date)
        payload.usage_date = current.usage_date;
    if (current.budget !== savedValues.budget) payload.budget = current.budget;
    if (current.keterangan !== savedValues.keterangan)
        payload.keterangan = current.keterangan;
    if (
        current.outcome !== savedValues.outcome ||
        current.outcome_factor !== savedValues.outcome_factor ||
        current.outcome_note !== savedValues.outcome_note
    ) {
        payload.outcome = current.outcome;
        payload.outcome_factor = current.outcome_factor;
        payload.outcome_note = current.outcome_note;
    }
    if (current.next_follow_up_at !== savedValues.next_follow_up_at)
        payload.next_follow_up_at = current.next_follow_up_at;
    if (
        props.canMarkCrm &&
        current.crm_taken_over !== savedValues.crm_taken_over
    )
        payload.crm_taken_over = current.crm_taken_over;
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
watch(
    saveState,
    (state) => emit('save-state', props.lead.id, state, saveError.value),
    { immediate: true },
);

const save = () => {
    waiting.value = false;
    if (!isDirty.value) return;
    if (
        values.outcome &&
        (!values.outcome_factor ||
            (values.outcome_factor === 'other' && !values.outcome_note.trim()))
    ) {
        invalidField.value = values.outcome_factor
            ? 'outcome_note'
            : 'outcome_factor';
        saveError.value = values.outcome_factor
            ? 'Jelaskan faktor lainnya sebelum menyimpan.'
            : 'Pilih faktor hasil akhir sebelum menyimpan.';

        return;
    }
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
            values.revision++;
            savedValues.revision = values.revision;
            hasSaved.value = true;
            if (savedTimer) clearTimeout(savedTimer);
            savedTimer = setTimeout(() => {
                hasSaved.value = false;
            }, 3000);
            handled = true;
        },
        onError: (errors) => {
            handled = true;
            const [field, message] = Object.entries(errors)[0] || [];
            invalidField.value = field || null;
            hasConflict.value = field === 'revision';
            saveError.value = hasConflict.value
                ? 'Lead sudah diperbarui CS lain. Buka detail untuk meninjau data terbaru.'
                : message
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
    hasConflict.value = false;
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
const outcomeOptions = computed(() =>
    Object.entries(props.outcomeFactors[values.outcome] || {}),
);
const changeOutcome = () => {
    if (!values.outcome) {
        values.outcome_factor = '';
        values.outcome_note = '';
        saveImmediately();

        return;
    }
    if (!outcomeOptions.value.some(([key]) => key === values.outcome_factor)) {
        values.outcome_factor = '';
        values.outcome_note = '';
    }
};
const saveOutcome = () => {
    if (
        !values.outcome ||
        !values.outcome_factor ||
        (values.outcome_factor === 'other' && !values.outcome_note.trim())
    ) {
        return;
    }
    saveImmediately();
};
const retrySave = () => queueSave(0);
const flushPendingSave = () => {
    if (saveTimer) clearTimeout(saveTimer);
    if (!processing.value && isDirty.value) save();
};

defineExpose({ flushPendingSave });

watch(
    () => props.retrySaveVersion,
    (version, previous) => {
        if (version !== previous && saveError.value) retrySave();
    },
);

onBeforeUnmount(() => {
    flushPendingSave();
    if (savedTimer) clearTimeout(savedTimer);
    emit('save-state', props.lead.id, 'idle', '');
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
        <td class="min-w-72 p-3 align-middle">
            <div class="grid gap-2">
                <select
                    v-model="values.outcome"
                    class="kgj-input w-full"
                    aria-label="Hasil akhir lead"
                    :aria-invalid="isInvalid('outcome')"
                    :aria-describedby="saveError ? errorId : undefined"
                    @change="changeOutcome"
                >
                    <option value="">Belum ada hasil akhir</option>
                    <option value="closing">Closing</option>
                    <option value="loss">Loss</option>
                </select>
                <select
                    v-if="values.outcome"
                    v-model="values.outcome_factor"
                    class="kgj-input w-full"
                    :aria-label="
                        values.outcome === 'closing'
                            ? 'Faktor Closing'
                            : 'Faktor Loss'
                    "
                    :aria-invalid="isInvalid('outcome_factor')"
                    :aria-describedby="saveError ? errorId : undefined"
                    @change="saveOutcome"
                >
                    <option value="">
                        {{
                            values.outcome === 'closing'
                                ? 'Pilih faktor closing'
                                : 'Pilih faktor loss'
                        }}
                    </option>
                    <option
                        v-for="[key, label] in outcomeOptions"
                        :key="key"
                        :value="key"
                    >
                        {{ label }}
                    </option>
                </select>
                <input
                    v-if="values.outcome_factor === 'other'"
                    v-model="values.outcome_note"
                    class="kgj-input w-full"
                    placeholder="Jelaskan faktor lainnya"
                    aria-label="Catatan faktor lainnya"
                    :aria-invalid="isInvalid('outcome_note')"
                    :aria-describedby="saveError ? errorId : undefined"
                    @input="saveAfterTyping"
                    @blur="saveOutcome"
                />
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
        <td class="min-w-40 p-3 align-middle text-sm">
            <LeadHistoryPopover :lead-id="lead.id" label="Riwayat" />
            <label
                v-if="canMarkCrm"
                class="mt-2 flex min-h-8 cursor-pointer items-center gap-2 text-xs font-semibold text-[#665a43]"
            >
                <input
                    v-model="values.crm_taken_over"
                    class="size-4 accent-kgj-gold"
                    type="checkbox"
                    @change="saveImmediately"
                />
                Diambil alih CRM
            </label>
            <span v-if="saveError" :id="errorId" class="sr-only" role="status">
                {{ saveError }}
            </span>
        </td>
    </template>
    <section
        v-else
        class="min-w-0 lg:min-w-[34rem]"
        :aria-label="`Update cepat lead ${lead.id}`"
    >
        <div class="grid gap-4 sm:grid-cols-3">
            <fieldset
                v-for="group in groups"
                :key="group.label"
                class="min-w-0"
            >
                <legend class="text-sm font-semibold">
                    {{ group.label }}
                </legend>
                <label
                    v-for="[value, label] in group.items"
                    :key="value"
                    class="mt-2 flex min-h-9 cursor-pointer items-center gap-2 text-sm text-kgj-ink"
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

        <div
            class="mt-4 grid gap-3"
            :class="
                values.outcome ? 'rounded-xl border border-kgj-line p-4' : ''
            "
        >
            <label class="grid gap-2 text-sm font-semibold"
                >Status hasil
                <select
                    v-model="values.outcome"
                    class="kgj-input"
                    :aria-invalid="isInvalid('outcome')"
                    @change="changeOutcome"
                >
                    <option value="">Belum ada hasil akhir</option>
                    <option value="closing">Closing</option>
                    <option value="loss">Loss</option>
                </select>
            </label>
            <label
                v-if="values.outcome"
                class="grid gap-2 text-sm font-semibold"
                >{{
                    values.outcome === 'closing'
                        ? 'Faktor Closing'
                        : 'Faktor Loss'
                }}
                <select
                    v-model="values.outcome_factor"
                    class="kgj-input"
                    :aria-invalid="isInvalid('outcome_factor')"
                    @change="saveOutcome"
                >
                    <option value="">Pilih faktor utama</option>
                    <option
                        v-for="[key, label] in outcomeOptions"
                        :key="key"
                        :value="key"
                    >
                        {{ label }}
                    </option>
                </select>
            </label>
            <label
                v-if="values.outcome_factor === 'other'"
                class="grid gap-2 text-sm font-semibold"
                >Penjelasan lainnya
                <textarea
                    v-model="values.outcome_note"
                    class="kgj-input min-h-20 resize-y"
                    placeholder="Jelaskan faktor lainnya"
                    :aria-invalid="isInvalid('outcome_note')"
                    @input="saveAfterTyping"
                    @blur="saveOutcome"
                />
            </label>
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

        <p
            v-if="showSaveFeedback"
            class="mt-4 text-sm"
            :class="{
                'text-kgj-muted': saveState === 'idle',
                'text-kgj-gold-deep': saveState === 'saving',
                'text-[#49654e]': saveState === 'saved',
                'text-kgj-rose':
                    saveState === 'error' || saveState === 'conflict',
            }"
            role="status"
        >
            {{
                saveState === 'saving'
                    ? 'Menyimpan perubahan...'
                    : saveState === 'saved'
                      ? 'Tersimpan otomatis.'
                      : saveError || 'Perubahan disimpan otomatis.'
            }}
        </p>

        <div
            class="mt-4 flex flex-col gap-3 border-t border-[#e7dfcf] pt-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <LeadHistoryPopover
                :lead-id="lead.id"
                label="Lihat riwayat"
                align="start"
                content-class="z-[70]"
                trigger-class="flex min-h-11 cursor-pointer items-center gap-2 text-sm font-semibold text-[#4f514a] hover:text-kgj-gold-deep focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-kgj-gold"
            />
            <label
                v-if="canMarkCrm"
                class="flex min-h-11 cursor-pointer items-center gap-2 text-sm font-semibold text-[#665a43]"
            >
                <input
                    v-model="values.crm_taken_over"
                    class="size-4 accent-kgj-gold"
                    type="checkbox"
                    @change="saveImmediately"
                />
                Diambil alih CRM
            </label>
            <span v-if="saveError" :id="errorId" class="sr-only" role="status">
                {{ saveError }}
            </span>
        </div>
    </section>
</template>
