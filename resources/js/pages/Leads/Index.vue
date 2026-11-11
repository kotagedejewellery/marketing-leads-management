<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Lead = {
    id: number;
    name: string | null;
    contact_raw: string;
    phone: string | null;
    intent_buying: string | null;
    imported_definition: string | null;
    current_stage: string | null;
    next_follow_up_at: string | null;
    owner_name: string | null;
    branch_name: string | null;
    updated_at: string;
};
type Page = {
    data: Lead[];
    current_page: number;
    last_page: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};
const props = defineProps<{
    leads: Page;
    filters: { q?: string; branch?: string; stage?: string; queue?: string; archived?: string };
    branches: { id: number; name: string }[];
    csAccounts: { id: number; name: string; branch_name: string }[];
    archiveMode: boolean;
}>();
const page = usePage();
const isAdmin = computed(() => page.props.auth.user?.role === 'admin');
const q = ref(props.filters.q || '');
const branch = ref(props.filters.branch || '');
const stage = ref(props.filters.stage || '');
const queue = ref(props.filters.queue || '');
const isFiltering = ref(false);
const selectedLeadIds = ref<number[]>([]);
const bulkForm = useForm({ assigned_user_id: '', lead_ids: [] as number[] });
const importForm = useForm<{ csv_file: File | null }>({ csv_file: null });
const newLead = useForm({
    contact_raw: '',
    name: '',
    phone: '',
    assigned_user_id: '',
    source_date_raw: '',
    respond_time: '',
    imported_cs_name: '',
    imported_keterangan: '',
    raw_kebutuhan: '',
    greeting: '',
    chat_time: '',
    source_description: '',
    campaign_description: '',
    intent_buying: '',
    usage_date: '',
    budget: '',
    keterangan: '',
    next_follow_up_at: '',
    milestone: '',
});
const showingNewLead = ref(false);
const hasFilters = computed(() => Boolean(q.value || branch.value || stage.value || queue.value));
const selectedFile = computed(() => {
    if (!importForm.csv_file) return '';

    return `${importForm.csv_file.name} · ${Math.ceil(importForm.csv_file.size / 1024)} KB`;
});
const activeFilterText = computed(() =>
    [
        q.value ? `Pencarian: “${q.value}”` : '',
        branch.value ? 'Cabang dipilih' : '',
        stage.value ? 'Tahap dipilih' : '',
        queue.value ? 'Queue dipilih' : '',
    ]
        .filter(Boolean)
        .join(' · '),
);

const applyFilters = () =>
    router.get(
        '/leads',
        {
            q: q.value || undefined,
            branch: branch.value || undefined,
            stage: stage.value || undefined,
            queue: queue.value || undefined,
            archived: props.archiveMode ? '1' : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => (isFiltering.value = true),
            onFinish: () => {
                isFiltering.value = false;
                selectedLeadIds.value = [];
            },
        },
    );
const clearFilters = () => {
    q.value = '';
    branch.value = '';
    stage.value = '';
    queue.value = '';
    applyFilters();
};
const importCsv = () =>
    importForm.post('/leads/import', {
        forceFormData: true,
        onSuccess: () => importForm.reset(),
    });
const selectCsv = (event: Event) => {
    importForm.csv_file = (event.target as HTMLInputElement).files?.[0] || null;
};
const toggleArchive = () =>
    router.get('/leads', { archived: props.archiveMode ? undefined : '1' });
const createLead = () =>
    newLead.post('/leads', {
        onSuccess: () => {
            newLead.reset();
            showingNewLead.value = false;
        },
    });
const stageLabel = (value: string | null) => ({
    clicked: 'Klik saja',
    asked: 'Tanya-tanya saja',
    price_list: 'Sampai Pricelist',
    offline: 'Datang Offline',
    online: 'Diskusi Online',
    closing_pending_transfer: 'Closing belum transfer',
    closing: 'Closing',
    repeat_order: 'Repeat Order',
}[value || '']);
const assignSelected = () => {
    bulkForm.lead_ids = selectedLeadIds.value;
    bulkForm.post('/leads/assign', {
        onSuccess: () => {
            selectedLeadIds.value = [];
            bulkForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Leads" />

    <section class="kgj-page">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div>
                <p class="kgj-eyebrow">Workspace harian</p>
                <h1
                    class="mt-2 text-4xl font-semibold tracking-[-0.04em] sm:text-5xl"
                >
                    Leads.
                </h1>
                <p class="kgj-subtitle">Daftar kerja sesuai akses akun Anda.</p>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <button
                    v-if="isAdmin"
                    class="text-sm font-semibold text-kgj-gold-deep underline decoration-[#c4a45d] underline-offset-4"
                    type="button"
                    @click="toggleArchive"
                >
                    {{ archiveMode ? 'Kembali ke aktif' : 'Lihat arsip' }}
                </button>
                <button
                    v-if="isAdmin && !archiveMode"
                    class="kgj-button-primary"
                    type="button"
                    @click="showingNewLead = !showingNewLead"
                >
                    {{ showingNewLead ? 'Tutup' : 'Tambah lead' }}
                </button>
                <p class="text-[#62665b]">
                    <strong class="text-[#22231f]">{{ leads.total }}</strong>
                    lead {{ archiveMode ? 'diarsipkan' : 'ditemukan' }}
                </p>
            </div>
        </div>

        <form
            v-if="showingNewLead"
            class="kgj-surface mt-6 grid gap-4 p-4 sm:p-5 lg:grid-cols-2"
            @submit.prevent="createLead"
        >
            <label class="sr-only" for="new-lead-contact"
                >Nama atau nomor lead</label
            >
            <input
                id="new-lead-contact"
                v-model="newLead.contact_raw"
                class="kgj-input min-w-[220px] flex-1"
                placeholder="Nama / nomor WhatsApp"
                required
                :aria-invalid="Boolean(newLead.errors.contact_raw)"
            />
            <button
                class="kgj-button-gold shrink-0"
                :disabled="newLead.processing"
            >
                {{ newLead.processing ? 'Menyimpan…' : 'Simpan' }}
            </button>
            <p
                v-if="newLead.errors.contact_raw"
                class="basis-full text-sm text-[#a23e2b]"
                role="alert"
            >
                {{ newLead.errors.contact_raw }}
            </p>
            <details class="col-span-full rounded-xl border border-kgj-line bg-[#fffdf8] p-4">
                <summary class="cursor-pointer text-sm font-semibold text-kgj-gold-deep">Tambahkan data sumber dan kualifikasi</summary>
                <p class="mt-2 text-sm text-[#62665b]">Opsional untuk lead baru; gunakan saat data berasal dari chat, spreadsheet, atau kampanye.</p>
                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <label class="grid gap-2 text-sm font-semibold">Nama<input v-model="newLead.name" class="kgj-input" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Nomor WhatsApp<input v-model="newLead.phone" class="kgj-input" /></label>
                    <label class="grid gap-2 text-sm font-semibold">CS penanggung jawab<select v-model="newLead.assigned_user_id" class="kgj-input"><option value="">Belum ditugaskan</option><option v-for="account in csAccounts" :key="account.id" :value="String(account.id)">{{ account.branch_name }} — {{ account.name }}</option></select></label>
                    <label class="grid gap-2 text-sm font-semibold">Tanggal sumber<input v-model="newLead.source_date_raw" class="kgj-input" placeholder="Contoh: 31" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Respond time<input v-model="newLead.respond_time" class="kgj-input" placeholder="Contoh: +-1 jam" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Nama CS di sumber<input v-model="newLead.imported_cs_name" class="kgj-input" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Waktu chat<input v-model="newLead.chat_time" class="kgj-input" placeholder="Contoh: 10.38" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Sumber<select v-model="newLead.source_description" class="kgj-input"><option value="">Pilih sumber</option><option>Iklan</option><option>Instagram</option><option>Google</option><option>Website</option><option>Organik</option><option>Teman</option><option>Lainnya</option></select></label>
                    <label class="grid gap-2 text-sm font-semibold">Campaign<input v-model="newLead.campaign_description" class="kgj-input" placeholder="Contoh: Bio atau DM" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Keterangan awal<input v-model="newLead.imported_keterangan" class="kgj-input" placeholder="Contoh: tanya-tanya" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Kebutuhan awal<input v-model="newLead.raw_kebutuhan" class="kgj-input" placeholder="Contoh: cincin lamaran" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Tahap awal<select v-model="newLead.milestone" class="kgj-input"><option value="">Belum dikualifikasi</option><option value="clicked">Klik saja</option><option value="asked">Tanya-tanya saja</option><option value="price_list">Sampai Pricelist</option><option value="offline">Datang Offline</option><option value="online">Diskusi Online</option><option value="closing_pending_transfer">Closing tapi belum Transfer</option><option value="closing">Closing</option><option value="repeat_order">Repeat Order</option></select></label>
                    <label class="grid gap-2 text-sm font-semibold">Intent Buying<select v-model="newLead.intent_buying" class="kgj-input"><option value="">Pilih kebutuhan</option><option>Tunangan</option><option>Lamaran</option><option>Nikahan</option><option>Anniversary</option><option>Lainnya</option></select></label>
                    <label class="grid gap-2 text-sm font-semibold">Waktu pemakaian<input v-model="newLead.usage_date" type="date" class="kgj-input" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Budget<input v-model="newLead.budget" class="kgj-input" /></label>
                    <label class="grid gap-2 text-sm font-semibold">Tindak lanjut berikutnya<input v-model="newLead.next_follow_up_at" type="datetime-local" class="kgj-input" /></label>
                    <label class="grid gap-2 text-sm font-semibold lg:col-span-2">Greeting<textarea v-model="newLead.greeting" rows="3" class="kgj-input" /></label>
                    <label class="grid gap-2 text-sm font-semibold lg:col-span-2">Catatan internal<textarea v-model="newLead.keterangan" rows="3" class="kgj-input" /></label>
                </div>
            </details>
        </form>

        <div
            class="kgj-surface mt-7 grid gap-3 p-4 lg:grid-cols-[1fr_auto_auto_auto_auto]"
        >
            <form class="flex gap-2" @submit.prevent="applyFilters">
                <label class="sr-only" for="lead-search">Cari lead</label
                ><input
                    id="lead-search"
                    v-model="q"
                    class="kgj-input w-full"
                    placeholder="Cari nama atau nomor"
                /><button
                    class="kgj-button-primary shrink-0"
                    type="submit"
                    :disabled="isFiltering"
                >
                    {{ isFiltering ? 'Memuat…' : 'Cari' }}
                </button>
            </form>
            <label v-if="isAdmin" class="text-sm font-medium"
                ><span class="sr-only">Filter cabang</span
                ><select
                    v-model="branch"
                    class="kgj-input w-full"
                    :disabled="isFiltering"
                    @change="applyFilters"
                >
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
            <label class="text-sm font-medium"
                ><span class="sr-only">Filter tahap lead</span
                ><select v-model="stage" class="kgj-input w-full" :disabled="isFiltering" @change="applyFilters">
                    <option value="">Semua tahap</option>
                    <option value="clicked">Klik saja</option>
                    <option value="asked">Tanya-tanya</option>
                    <option value="price_list">Sampai Pricelist</option>
                    <option value="offline">Datang Offline</option>
                    <option value="online">Diskusi Online</option>
                    <option value="closing_pending_transfer">Belum transfer</option>
                    <option value="closing">Closing</option>
                    <option value="repeat_order">Repeat Order</option>
                </select></label
            >
            <label v-if="isAdmin" class="text-sm font-medium"
                ><span class="sr-only">Queue kerja admin</span
                ><select v-model="queue" class="kgj-input w-full" :disabled="isFiltering" @change="applyFilters">
                    <option value="">Semua queue</option>
                    <option value="unassigned">Belum ditugaskan</option>
                    <option value="needs_qualification">Perlu kualifikasi</option>
                    <option value="follow_up_due">Follow-up jatuh tempo</option>
                </select></label
            >
            <form
                v-if="isAdmin"
                class="flex items-center gap-2"
                @submit.prevent="importCsv"
            >
                <label
                    class="inline-flex min-h-11 cursor-pointer items-center rounded-lg border border-[#c9aa6a] bg-white px-3 py-3 text-sm font-semibold text-kgj-gold-deep transition hover:bg-[#fbf6e8]"
                    >{{ importForm.csv_file?.name || 'Pilih CSV / XLSX'
                    }}<input
                        class="sr-only"
                        type="file"
                        accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                        @change="selectCsv" /></label
                ><button
                    class="kgj-button-gold"
                    :disabled="!importForm.csv_file || importForm.processing"
                >
                    {{ importForm.processing ? 'Mengimpor…' : 'Import' }}
                </button>
            </form>
        </div>
        <div
            class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-[#62665b]"
            aria-live="polite"
        >
            <span v-if="hasFilters">Filter aktif: {{ activeFilterText }}</span>
            <button
                v-if="hasFilters"
                class="font-semibold text-[#6c511e] underline underline-offset-4"
                type="button"
                @click="clearFilters"
            >
                Hapus filter
            </button>
            <span v-if="selectedFile">Siap diimpor: {{ selectedFile }}</span>
            <span v-if="isAdmin && !selectedFile">Gunakan export CSV atau XLSX dari tab All Leads.</span>
        </div>
        <p
            v-if="importForm.errors.csv_file"
            class="mt-2 text-sm text-[#a23e2b]"
            role="alert"
        >
            {{ importForm.errors.csv_file }}
        </p>
        <form
            v-if="isAdmin && selectedLeadIds.length"
            class="kgj-surface mt-4 flex flex-col gap-3 border border-[#dccd9f] p-4 sm:flex-row sm:items-center"
            @submit.prevent="assignSelected"
        >
            <p class="text-sm font-semibold">{{ selectedLeadIds.length }} lead dipilih</p>
            <label class="sr-only" for="bulk-assignee">CS penanggung jawab</label>
            <select id="bulk-assignee" v-model="bulkForm.assigned_user_id" class="kgj-input sm:ml-auto sm:max-w-xs" required>
                <option value="">Pilih CS penanggung jawab</option>
                <option v-for="account in csAccounts" :key="account.id" :value="String(account.id)">{{ account.branch_name }} — {{ account.name }}</option>
            </select>
            <button class="kgj-button-gold" :disabled="bulkForm.processing || !bulkForm.assigned_user_id">
                {{ bulkForm.processing ? 'Menugaskan…' : 'Tugaskan' }}
            </button>
            <button class="kgj-button-secondary" type="button" @click="selectedLeadIds = []">Batal</button>
        </form>

        <div class="mt-6 grid gap-3 md:hidden">
            <article
                v-for="lead in leads.data"
                :key="lead.id"
                class="rounded-2xl border border-kgj-line bg-kgj-paper p-4 shadow-[0_8px_22px_rgba(58,48,31,0.06)] transition hover:-translate-y-0.5 hover:border-[#c3a463]"
                ><label v-if="isAdmin" class="mb-3 flex min-h-11 items-center gap-2 text-sm font-semibold"><input v-model="selectedLeadIds" class="size-4 accent-kgj-gold" type="checkbox" :disabled="!selectedLeadIds.includes(lead.id) && selectedLeadIds.length >= 100" :value="lead.id" />Pilih lead</label>
                ><Link :href="`/leads/${lead.id}`" class="block"><strong class="block">{{
                    lead.name || lead.contact_raw
                }}</strong
                ><span class="mt-1 block text-sm text-[#62665b]">{{
                    lead.phone || lead.contact_raw
                }}</span
                ><span class="mt-3 block text-sm"
                    >{{ lead.branch_name || 'Belum ditugaskan' }} ·
                    {{ lead.owner_name || '—' }}</span
                ><span
                    class="mt-1 block text-xs tracking-wide text-[#8a6b2d] uppercase"
                    >{{
                        stageLabel(lead.current_stage) || lead.intent_buying ||
                        lead.imported_definition ||
                        'Belum dikualifikasi'
                    }}</span><span v-if="lead.next_follow_up_at" class="mt-2 block text-xs text-kgj-muted">Follow-up: {{ new Date(lead.next_follow_up_at).toLocaleString('id-ID') }}</span>
                ></Link></article
            >
            <p
                v-if="!leads.data.length"
                class="py-12 text-center text-[#62665b]"
            >
                {{
                    hasFilters
                        ? 'Tidak ada lead yang cocok dengan filter ini.'
                        : 'Belum ada lead. Import CSV untuk memulai.'
                }}
                <button
                    v-if="hasFilters"
                    class="mt-3 block w-full font-semibold text-[#6c511e] underline underline-offset-4"
                    type="button"
                    @click="clearFilters"
                >
                    Tampilkan semua lead
                </button>
            </p>
        </div>

        <div class="kgj-table mt-6 hidden overflow-x-auto md:block">
            <table class="w-full min-w-[760px] text-sm">
                <thead
                    class="text-left text-xs tracking-wider text-[#62665b] uppercase"
                >
                    <tr>
                        <th v-if="isAdmin" class="p-4"><span class="sr-only">Pilih lead</span></th>
                        <th class="p-4">Lead</th>
                        <th class="p-4">Cabang / CS</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Update</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="lead in leads.data"
                        :key="lead.id"
                        class="border-t border-[#e7eae5]"
                    >
                        <td v-if="isAdmin" class="p-4"><input v-model="selectedLeadIds" class="size-4 accent-kgj-gold" type="checkbox" :disabled="!selectedLeadIds.includes(lead.id) && selectedLeadIds.length >= 100" :aria-label="`Pilih ${lead.name || lead.contact_raw}`" :value="lead.id" /></td>
                        <td class="p-4 font-semibold">
                            <Link
                                :href="`/leads/${lead.id}`"
                                class="hover:underline"
                                >{{ lead.name || lead.contact_raw }}</Link
                            ><small
                                class="mt-1 block font-normal text-[#62665b]"
                                >{{ lead.phone || lead.contact_raw }}</small
                            >
                        </td>
                        <td class="p-4">
                            {{ lead.branch_name || 'Belum ditugaskan'
                            }}<small class="mt-1 block text-[#62665b]">{{
                                lead.owner_name || '—'
                            }}</small>
                        </td>
                        <td class="p-4">
                            {{
                                stageLabel(lead.current_stage) || lead.intent_buying ||
                                lead.imported_definition ||
                                'Belum dikualifikasi'
                            }}<small v-if="lead.next_follow_up_at" class="mt-1 block text-[#62665b]">Follow-up: {{ new Date(lead.next_follow_up_at).toLocaleString('id-ID') }}</small>
                        </td>
                        <td class="p-4">
                            {{
                                new Date(lead.updated_at).toLocaleDateString(
                                    'id-ID',
                                )
                            }}
                        </td>
                    </tr>
                    <tr v-if="!leads.data.length">
                        <td :colspan="isAdmin ? 5 : 4" class="p-12 text-center text-[#62665b]">
                            {{
                                hasFilters
                                    ? 'Tidak ada lead yang cocok dengan filter ini.'
                                    : 'Belum ada lead. Import CSV untuk memulai.'
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav
            v-if="leads.last_page > 1"
            class="mt-6 flex items-center justify-between text-sm"
            aria-label="Halaman leads"
        >
            <span class="text-[#62665b]"
                >Halaman {{ leads.current_page }} dari
                {{ leads.last_page }}</span
            >
            <div class="flex gap-2">
                <Link
                    v-if="leads.prev_page_url"
                    :href="leads.prev_page_url"
                    class="min-h-11 border border-[#cfc6b5] px-3 py-3"
                    >Sebelumnya</Link
                ><Link
                    v-if="leads.next_page_url"
                    :href="leads.next_page_url"
                    class="min-h-11 border border-[#cfc6b5] px-3 py-3"
                    >Berikutnya</Link
                >
            </div>
        </nav>
    </section>
</template>
