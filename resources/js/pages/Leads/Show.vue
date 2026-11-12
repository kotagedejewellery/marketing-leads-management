<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import { leadSchema } from '@/lib/lead-schema';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Lead = Record<string, string | number | null> & {
    id: number;
    contact_raw: string;
    name: string | null;
    phone: string | null;
    assigned_user_id: number | null;
    archived_at: string | null;
};
const props = defineProps<{
    lead: Lead;
    milestones: string[];
    history: {
        data: {
            action: string;
            detail: string | null;
            created_at: string;
            actor_name: string | null;
        }[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    csAccounts: { id: number; name: string; branch_name: string }[];
}>();
const page = usePage();
const isAdmin = page.props.auth.user?.role === 'admin';
const form = useForm({
    intent_buying: String(props.lead.intent_buying || ''),
    usage_date: String(props.lead.usage_date || ''),
    budget: String(props.lead.budget || ''),
    keterangan: String(props.lead.keterangan || ''),
    next_follow_up_at: String(props.lead.next_follow_up_at || '').slice(0, 16),
    assigned_user_id: props.lead.assigned_user_id
        ? String(props.lead.assigned_user_id)
        : '',
    milestones: [...props.milestones],
});
const clientError = ref('');
const archiveDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const lifecycleProcessing = ref(false);
const groups = {
    Cold: [['clicked', 'Klik saja']],
    Warm: [
        ['asked', 'Tanya-tanya saja'],
        ['price_list', 'Sampai Pricelist'],
    ],
    Hot: [
        ['offline', 'Datang Offline'],
        ['online', 'Diskusi Online'],
        ['closing_pending_transfer', 'Closing tapi belum Transfer'],
        ['closing', 'Closing'],
        ['repeat_order', 'Repeat Order'],
    ],
};
const milestoneCount = computed(() => form.milestones.length);
const formFields = [
    'intent_buying',
    'usage_date',
    'budget',
    'keterangan',
    'next_follow_up_at',
    'assigned_user_id',
    'milestones',
] as const;
const stageLabel = (value: string | null) =>
    ({
        clicked: 'Klik saja',
        asked: 'Tanya-tanya saja',
        price_list: 'Sampai Pricelist',
        offline: 'Datang Offline',
        online: 'Diskusi Online',
        closing_pending_transfer: 'Closing belum transfer',
        closing: 'Closing',
        repeat_order: 'Repeat Order',
    })[value || ''];
const focusFirstError = () => {
    const field = formFields.find((key) => form.errors[key]);

    if (field) nextTick(() => document.getElementById(field)?.focus());
};
function save() {
    const result = leadSchema.safeParse(form.data());
    if (!result.success) {
        clientError.value = 'Periksa kembali data lead sebelum menyimpan.';
        nextTick(() => document.getElementById('intent_buying')?.focus());
        return;
    }
    clientError.value = '';
    form.patch(`/leads/${props.lead.id}`, {
        preserveScroll: true,
        onError: focusFirstError,
    });
}
function archiveLead() {
    archiveDialogOpen.value = false;
    lifecycleProcessing.value = true;
    router.patch(`/leads/${props.lead.id}/archive`, {
        onFinish: () => (lifecycleProcessing.value = false),
    });
}
function restoreLead() {
    lifecycleProcessing.value = true;
    router.patch(`/leads/${props.lead.id}/restore`, {
        onFinish: () => (lifecycleProcessing.value = false),
    });
}
function destroyLead() {
    deleteDialogOpen.value = false;
    lifecycleProcessing.value = true;
    router.delete(`/leads/${props.lead.id}`, {
        onFinish: () => (lifecycleProcessing.value = false),
    });
}
</script>

<template>
    <Head :title="lead.name || lead.contact_raw" />
    <section class="kgj-page">
        <Link
            href="/leads"
            class="text-sm font-semibold text-kgj-muted underline decoration-kgj-gold/60 underline-offset-4"
            >Kembali ke leads</Link
        >
        <div
            class="mt-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-end"
        >
            <div>
                <p class="kgj-eyebrow">Profil lead</p>
                <h1
                    class="mt-2 text-4xl font-semibold tracking-[-.04em] sm:text-5xl"
                >
                    {{ lead.name || lead.contact_raw }}
                </h1>
                <p class="mt-2 text-[#62665b]">
                    {{ lead.phone || lead.contact_raw }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    v-if="!lead.archived_at"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-[#d8aaa2] px-3 text-sm font-semibold text-kgj-rose transition hover:bg-[#fff3f1]"
                    type="button"
                    :disabled="lifecycleProcessing"
                    @click="archiveDialogOpen = true"
                >
                    Arsipkan
                </button>
                <button
                    v-if="lead.archived_at"
                    class="kgj-button-primary"
                    type="button"
                    :disabled="lifecycleProcessing"
                    @click="restoreLead"
                >
                    Pulihkan
                </button>
                <button
                    v-if="lead.archived_at"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-[#d8aaa2] px-3 text-sm font-semibold text-kgj-rose transition hover:bg-[#fff3f1]"
                    type="button"
                    :disabled="lifecycleProcessing"
                    @click="deleteDialogOpen = true"
                >
                    Hapus permanen
                </button>
                <span
                    class="kgj-status w-fit border-[#d8c891] bg-[#fbf4e2] text-kgj-gold-deep"
                    >{{
                        lead.archived_at
                            ? 'Diarsipkan'
                            : stageLabel(String(lead.current_stage || '')) ||
                              lead.imported_definition ||
                              'Belum dikualifikasi'
                    }}</span
                >
            </div>
        </div>

        <p
            v-if="lead.archived_at"
            class="mt-8 rounded-2xl border border-[#dccd9f] bg-[#f8f1df] p-4 text-sm text-[#665a43]"
        >
            Lead ini berada di arsip dan tidak dapat diperbarui sampai
            dipulihkan.
        </p>
        <form v-else class="mt-8 grid gap-6" @submit.prevent="save">
            <div class="grid gap-4 md:grid-cols-2">
                <label class="grid gap-2 text-sm font-semibold"
                    >Intent Buying<select
                        id="intent_buying"
                        v-model="form.intent_buying"
                        class="kgj-input"
                        :aria-invalid="Boolean(form.errors.intent_buying)"
                    >
                        <option value="">Pilih kebutuhan</option>
                        <option
                            v-for="item in [
                                'Tunangan',
                                'Lamaran',
                                'Nikahan',
                                'Anniversary',
                                'Lainnya',
                            ]"
                            :key="item"
                        >
                            {{ item }}
                        </option></select
                    ><span
                        v-if="form.errors.intent_buying"
                        class="font-normal text-[#a23e2b]"
                        >{{ form.errors.intent_buying }}</span
                    ></label
                ><label class="grid gap-2 text-sm font-semibold"
                    >Waktu pemakaian<input
                        id="usage_date"
                        v-model="form.usage_date"
                        type="date"
                        class="kgj-input"
                        :aria-invalid="Boolean(form.errors.usage_date)"
                    /><span
                        v-if="form.errors.usage_date"
                        class="font-normal text-[#a23e2b]"
                        >{{ form.errors.usage_date }}</span
                    ></label
                ><label class="grid gap-2 text-sm font-semibold"
                    >Budget<input
                        id="budget"
                        v-model="form.budget"
                        maxlength="120"
                        class="kgj-input"
                        :aria-invalid="Boolean(form.errors.budget)"
                    /><span
                        v-if="form.errors.budget"
                        class="font-normal text-[#a23e2b]"
                        >{{ form.errors.budget }}</span
                    ></label
                ><label class="grid gap-2 text-sm font-semibold"
                    >Tindak lanjut berikutnya<input
                        id="next_follow_up_at"
                        v-model="form.next_follow_up_at"
                        type="datetime-local"
                        class="kgj-input"
                        :aria-invalid="Boolean(form.errors.next_follow_up_at)"
                    /><span
                        v-if="form.errors.next_follow_up_at"
                        class="font-normal text-[#a23e2b]"
                        >{{ form.errors.next_follow_up_at }}</span
                    ></label
                ><label v-if="isAdmin" class="grid gap-2 text-sm font-semibold"
                    >CS penanggung jawab<select
                        id="assigned_user_id"
                        v-model="form.assigned_user_id"
                        class="kgj-input"
                        :aria-invalid="Boolean(form.errors.assigned_user_id)"
                    >
                        <option value="">Belum ditugaskan</option>
                        <option
                            v-for="account in csAccounts"
                            :key="account.id"
                            :value="String(account.id)"
                        >
                            {{ account.branch_name }} — {{ account.name }}
                        </option></select
                    ><span
                        v-if="form.errors.assigned_user_id"
                        class="font-normal text-[#a23e2b]"
                        >{{ form.errors.assigned_user_id }}</span
                    ></label
                >
            </div>
            <label class="grid gap-2 text-sm font-semibold"
                >Keterangan<textarea
                    id="keterangan"
                    v-model="form.keterangan"
                    rows="3"
                    class="kgj-input"
                    :aria-invalid="Boolean(form.errors.keterangan)"
                /><span
                    v-if="form.errors.keterangan"
                    class="font-normal text-[#a23e2b]"
                    >{{ form.errors.keterangan }}</span
                >
            </label>
            <fieldset class="kgj-surface grid gap-4 p-5 md:grid-cols-3">
                <legend class="px-1 font-semibold">Perjalanan lead</legend>
                <p class="col-span-full -mt-2 text-sm text-[#62665b]">
                    {{ milestoneCount }} tahap aktif
                </p>
                <div v-for="(items, group) in groups" :key="group">
                    <h2
                        class="text-xs font-bold tracking-wider text-[#62665b] uppercase"
                    >
                        Lead {{ group }}
                    </h2>
                    <label
                        v-for="item in items"
                        :key="item[0]"
                        class="mt-3 flex min-h-8 items-center gap-2 text-sm"
                        ><input
                            v-model="form.milestones"
                            :id="item[0]"
                            class="size-4 accent-[#8a6b2d]"
                            type="checkbox"
                            :value="item[0]"
                        />{{ item[1] }}</label
                    >
                </div>
            </fieldset>
            <p v-if="clientError" class="text-sm text-[#a23e2b]" role="alert">
                {{ clientError }}
            </p>
            <p
                v-if="form.errors.milestones"
                class="text-sm text-[#a23e2b]"
                role="alert"
            >
                {{ form.errors.milestones }}
            </p>
            <p class="text-sm text-[#62665b]" aria-live="polite">
                {{
                    form.processing
                        ? 'Menyimpan perubahan…'
                        : form.isDirty
                          ? 'Ada perubahan yang belum disimpan.'
                          : 'Semua perubahan sudah tersimpan.'
                }}
            </p>
            <button
                class="kgj-button-primary sticky bottom-3 w-fit shadow-[0_12px_24px_rgba(32,31,28,0.22)]"
                :disabled="form.processing || !form.isDirty"
            >
                {{ form.processing ? 'Menyimpan…' : 'Simpan perubahan' }}
            </button>
        </form>

        <section class="mt-10 border-t border-kgj-line pt-6">
            <h2 class="text-lg font-semibold">Data dari import</h2>
            <dl class="mt-4 grid gap-x-8 gap-y-4 text-sm md:grid-cols-2">
                <div
                    v-for="item in [
                        {
                            label: 'Tanggal sumber',
                            value: lead.source_date_raw,
                        },
                        {
                            label: 'Keterangan awal',
                            value: lead.imported_keterangan,
                        },
                        { label: 'Kebutuhan awal', value: lead.raw_kebutuhan },
                        { label: 'Respond time', value: lead.respond_time },
                        {
                            label: 'Nama CS di CSV',
                            value: lead.imported_cs_name,
                        },
                        { label: 'Waktu chat', value: lead.chat_time },
                        { label: 'Sumber', value: lead.source_description },
                        { label: 'Campaign', value: lead.campaign_description },
                        { label: 'Greeting', value: lead.greeting },
                    ]"
                    :key="item.label"
                    class="border-b border-[#e7eae5] pb-3"
                >
                    <dt
                        class="text-xs font-bold tracking-wider text-[#62665b] uppercase"
                    >
                        {{ item.label }}
                    </dt>
                    <dd class="mt-1 leading-6 whitespace-pre-wrap">
                        {{ item.value || '—' }}
                    </dd>
                </div>
            </dl>
        </section>
        <section class="mt-10 border-t border-kgj-line pt-6">
            <h2 class="text-lg font-semibold">Riwayat</h2>
            <article
                v-for="item in history.data"
                :key="`${item.created_at}-${item.action}`"
                class="mt-4 border-l border-[#d7dcd5] pl-4"
            >
                <strong class="text-sm">{{ item.action }}</strong>
                <p class="text-sm text-[#62665b]">{{ item.detail || '—' }}</p>
                <small class="text-[#62665b]"
                    >{{ item.actor_name || 'Sistem' }} ·
                    {{
                        new Date(item.created_at).toLocaleString('id-ID')
                    }}</small
                >
            </article>
            <p v-if="!history.data.length" class="mt-4 text-sm text-[#62665b]">
                Belum ada riwayat.
            </p>
            <nav
                v-if="history.last_page > 1"
                class="mt-5 flex items-center justify-between text-sm"
                aria-label="Halaman riwayat"
            >
                <span class="text-[#62665b]"
                    >Halaman {{ history.current_page }} dari
                    {{ history.last_page }}</span
                >
                <div class="flex gap-2">
                    <Link
                        v-if="history.prev_page_url"
                        :href="history.prev_page_url"
                        class="min-h-11 border border-[#cfc6b5] px-3 py-3"
                        >Lebih baru</Link
                    >
                    <Link
                        v-if="history.next_page_url"
                        :href="history.next_page_url"
                        class="min-h-11 border border-[#cfc6b5] px-3 py-3"
                        >Lebih lama</Link
                    >
                </div>
            </nav>
        </section>
        <Dialog v-model:open="archiveDialogOpen">
            <DialogContent>
                <DialogHeader
                    ><DialogTitle>Arsipkan lead ini?</DialogTitle
                    ><DialogDescription
                        >Lead tidak lagi muncul di daftar kerja aktif, tetapi
                        data dan riwayatnya tetap tersimpan.</DialogDescription
                    ></DialogHeader
                >
                <DialogFooter
                    ><DialogClose as-child
                        ><button class="kgj-button-secondary" type="button">
                            Batalkan
                        </button></DialogClose
                    ><button
                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-kgj-rose px-4 text-sm font-semibold text-white transition hover:bg-[#84362e]"
                        type="button"
                        :disabled="lifecycleProcessing"
                        @click="archiveLead"
                    >
                        Arsipkan lead
                    </button></DialogFooter
                >
            </DialogContent>
        </Dialog>
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader
                    ><DialogTitle>Hapus lead permanen?</DialogTitle
                    ><DialogDescription
                        >Lead, milestone, dan seluruh riwayatnya akan dihapus
                        tanpa dapat dipulihkan.</DialogDescription
                    ></DialogHeader
                >
                <DialogFooter
                    ><DialogClose as-child
                        ><button class="kgj-button-secondary" type="button">
                            Batalkan
                        </button></DialogClose
                    ><button
                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-kgj-rose px-4 text-sm font-semibold text-white transition hover:bg-[#84362e]"
                        type="button"
                        :disabled="lifecycleProcessing"
                        @click="destroyLead"
                    >
                        Hapus permanen
                    </button></DialogFooter
                >
            </DialogContent>
        </Dialog>
    </section>
</template>
