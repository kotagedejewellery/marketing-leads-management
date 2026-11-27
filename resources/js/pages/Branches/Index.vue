<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

type Branch = {
    id: number;
    name: string;
    default_import_user_id: number | null;
    cs_accounts: { id: number; name: string; email: string }[];
    import_status: string;
    google_spreadsheet_id: string | null;
    google_sheets_synced_at: string | null;
    google_sheets_sync_error: string | null;
    last_sync: {
        status: 'success' | 'partial' | 'failed';
        completed_at: string | null;
        summary: {
            inserted?: number;
            enriched?: number;
            awaiting_account?: number;
            chat_time_review?: number;
            failed_tabs?: number;
            missing_source_fields?: number;
        } | null;
        error: string | null;
    } | null;
    source_events: {
        id: number;
        action:
            | 'connected'
            | 'replaced'
            | 'disconnected'
            | 'replacement_failed';
        previous_spreadsheet_id: string | null;
        spreadsheet_id: string | null;
        detail: string | null;
        created_at: string | null;
        actor_name: string | null;
    }[];
};
type SourcePreview = {
    branch_id: number;
    spreadsheet_id: string;
    rows: number;
    matching: number;
    new: number;
    updates: number;
    conflicts: number;
    missing_source_fields: number;
    failed_tabs: number;
};
type ReconciliationPreview = {
    branch_id: number;
    spreadsheet_id: string;
    spreadsheet_title: string;
    source_rows: number;
    source_comparison: {
        matching: number;
        new: number;
        conflicts: number;
    };
    archive_candidates: {
        lead_id: number;
        contact: string;
        revision: number;
    }[];
    reappeared_archived: { lead_id: number; contact: string; tab: string }[];
    coverage_errors: string[];
    changes: {
        lead_id: number;
        contact: string;
        tab: string;
        old_date: string | null;
        new_date: string;
        old_cs: string | null;
        new_cs: string | null;
        old_chat_time: string | null;
        new_chat_time: string | null;
    }[];
    conflicts: number;
    uncertain: number;
    missing: number;
    failed_tabs: number;
    matched: number;
    review: { contact: string; tab: string; reason: string }[];
};
const props = defineProps<{
    branches: Branch[];
    service_account_email: string | null;
    source_preview: SourcePreview | null;
    reconciliation_preview: ReconciliationPreview | null;
}>();
const form = useForm({
    name: '',
    google_spreadsheet_id: '',
    default_import_user_id: '',
});
const importForm = useForm<{
    csv_file: File | null;
    import_mode: 'all_leads' | 'daily_tabs';
    branch_id: string;
    return_to: 'branches';
}>({
    csv_file: null,
    import_mode: 'all_leads',
    branch_id: '',
    return_to: 'branches',
});
const fileInputKey = ref(0);
const syncingAll = ref(false);
const copyStatus = ref('');
const copyServiceAccountEmail = async () => {
    if (!props.service_account_email) return;
    try {
        await navigator.clipboard.writeText(props.service_account_email);
        copyStatus.value = 'Email berhasil disalin.';
    } catch {
        copyStatus.value =
            'Email belum dapat disalin. Pilih dan salin email secara manual.';
    }
};
const selectImportFile = (event: Event) => {
    importForm.csv_file = (event.target as HTMLInputElement).files?.[0] || null;
};
const importFile = () =>
    importForm.post('/leads/import', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
            fileInputKey.value++;
        },
    });
const syncAll = () =>
    router.post(
        '/leads/sync-google-sheet',
        { return_to: 'branches' },
        {
            preserveScroll: true,
            onStart: () => (syncingAll.value = true),
            onFinish: () => (syncingAll.value = false),
        },
    );
const editingId = ref<number | null>(null);
const branchToDelete = ref<Branch | null>(null);
const sourceBranch = ref<Branch | null>(null);
const reconciliationBranch = ref<Branch | null>(null);
const reconciliationForm = useForm({
    backup_confirmed: false,
    period_confirmed: false,
    source_coverage_confirmed: false,
});
const reconciliationPreview = computed(() =>
    reconciliationBranch.value?.id === props.reconciliation_preview?.branch_id
        ? props.reconciliation_preview
        : null,
);
const branchToDisconnect = ref<Branch | null>(null);
const syncingBranchId = ref<number | null>(null);
const sourceForm = useForm({ google_spreadsheet_id: '' });
const sourcePreview = computed(() => {
    if (!sourceBranch.value || !props.source_preview) return null;

    return props.source_preview.branch_id === sourceBranch.value.id &&
        props.source_preview.spreadsheet_id ===
            sourceForm.google_spreadsheet_id.trim()
        ? props.source_preview
        : null;
});
const readyBranches = computed(
    () =>
        props.branches.filter((branch) => branch.import_status === 'Siap impor')
            .length,
);
const importCsOptions = computed(
    () =>
        props.branches.find((branch) => branch.id === editingId.value)
            ?.cs_accounts || [],
);
const formatSyncTime = (value: string | null) =>
    value ? new Date(value).toLocaleString('id-ID') : '';
const canSync = (branch: Branch) => Boolean(branch.google_spreadsheet_id);
const statusClass = (branch: Branch) =>
    branch.import_status === 'Siap impor' ? 'text-kgj-sage' : 'text-kgj-rose';
const syncAttemptLabel = (branch: Branch) => {
    if (!branch.last_sync) return '';

    if (!branch.google_spreadsheet_id) {
        return 'Riwayat sinkronisasi terakhir';
    }

    return {
        success: 'Percobaan terakhir berhasil',
        partial: 'Percobaan terakhir selesai sebagian',
        failed: 'Percobaan terakhir gagal',
    }[branch.last_sync.status];
};
const syncAttemptClass = (branch: Branch) =>
    branch.last_sync?.status === 'failed'
        ? 'text-kgj-rose'
        : branch.last_sync?.status === 'partial'
          ? 'text-kgj-gold-deep'
          : 'text-kgj-sage';
const syncSummary = (branch: Branch) => {
    const summary = branch.last_sync?.summary;
    if (!summary) return '';

    const detail = `${summary.inserted || 0} lead baru · ${summary.enriched || 0} data diperbarui${summary.awaiting_account ? ` · ${summary.awaiting_account} menunggu akun CS` : ''}${summary.chat_time_review ? ` · ${summary.chat_time_review} waktu chat perlu diperiksa di Periksa data historis` : ''}${summary.missing_source_fields ? ` · ${summary.missing_source_fields} kolom sumber kosong` : ''}`;

    return summary.failed_tabs
        ? `${detail} · ${summary.failed_tabs} tab perlu diperbaiki`
        : detail;
};
const syncFailureDetail = (branch: Branch) =>
    branch.last_sync?.status === 'failed' ||
    branch.last_sync?.status === 'partial'
        ? branch.last_sync.error
        : !branch.last_sync
          ? branch.google_sheets_sync_error
          : null;
const sourceEventLabel = (action: Branch['source_events'][number]['action']) =>
    ({
        connected: 'Sumber dihubungkan',
        replaced: 'Sumber diganti',
        disconnected: 'Koneksi diputus',
        replacement_failed: 'Penggantian dibatalkan',
    })[action];
const sourceEventActor = (event: Branch['source_events'][number]) =>
    event.actor_name || 'Sistem';
const save = () => {
    const options = {
        onSuccess: () => {
            form.reset();
            editingId.value = null;
        },
    };
    if (editingId.value) form.patch(`/branches/${editingId.value}`, options);
    else form.post('/branches', options);
};
const edit = (branch: Branch) => {
    editingId.value = branch.id;
    form.name = branch.name;
    form.google_spreadsheet_id = branch.google_spreadsheet_id || '';
    form.default_import_user_id = branch.default_import_user_id
        ? String(branch.default_import_user_id)
        : '';
    form.clearErrors();
};
const cancel = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};
const remove = () => {
    if (!branchToDelete.value) return;
    router.delete(`/branches/${branchToDelete.value.id}`, {
        onFinish: () => (branchToDelete.value = null),
    });
};
const openSourceChange = (branch: Branch) => {
    sourceBranch.value = branch;
    sourceForm.reset();
    sourceForm.clearErrors();
};
const replaceSource = () => {
    if (!sourceBranch.value || !sourcePreview.value) return;
    sourceForm.post(`/branches/${sourceBranch.value.id}/replace-google-sheet`, {
        preserveScroll: true,
        onSuccess: () => {
            sourceBranch.value = null;
            sourceForm.reset();
        },
    });
};
const previewSource = () => {
    if (!sourceBranch.value) return;
    sourceForm.post(`/branches/${sourceBranch.value.id}/preview-google-sheet`, {
        preserveScroll: true,
        preserveState: true,
    });
};
const previewReconciliation = (branch: Branch) => {
    reconciliationBranch.value = branch;
    reconciliationForm.reset();
    reconciliationForm.post(`/branches/${branch.id}/preview-reconciliation`, {
        preserveScroll: true,
        preserveState: true,
    });
};
const applyReconciliation = () => {
    if (!reconciliationBranch.value || !reconciliationPreview.value) return;
    reconciliationForm.post(
        `/branches/${reconciliationBranch.value.id}/apply-reconciliation`,
        {
            preserveScroll: true,
            onSuccess: () => (reconciliationBranch.value = null),
        },
    );
};
const archiveMissing = () => {
    if (!reconciliationBranch.value || !reconciliationPreview.value) return;
    reconciliationForm.post(
        `/branches/${reconciliationBranch.value.id}/archive-missing-leads`,
        {
            preserveScroll: true,
            onSuccess: () => (reconciliationBranch.value = null),
        },
    );
};
const disconnectSource = () => {
    if (!branchToDisconnect.value) return;
    router.delete(`/branches/${branchToDisconnect.value.id}/google-sheet`, {
        preserveScroll: true,
        onSuccess: () => (branchToDisconnect.value = null),
    });
};
const syncBranch = (branch: Branch) =>
    router.post(
        `/branches/${branch.id}/sync-google-sheet`,
        {},
        {
            preserveScroll: true,
            onStart: () => (syncingBranchId.value = branch.id),
            onFinish: () => (syncingBranchId.value = null),
        },
    );
</script>

<template>
    <Head title="Cabang" />
    <section class="kgj-page">
        <h1 class="kgj-title">Cabang.</h1>
        <p class="kgj-subtitle">
            Kelola sumber lead dan beberapa akun CS aktif per cabang.
        </p>
        <p class="mt-4 text-sm text-kgj-muted">
            <span class="font-semibold text-kgj-ink tabular-nums">{{
                branches.length
            }}</span>
            cabang
            <span class="mx-2 text-kgj-line">/</span>
            <span class="font-semibold text-kgj-sage tabular-nums">{{
                readyBranches
            }}</span>
            siap impor
        </p>
        <div class="mt-6 grid items-start gap-6 xl:grid-cols-2">
            <section
                class="kgj-surface min-w-0 p-5 sm:p-6"
                aria-labelledby="source-management-heading"
            >
                <div>
                    <div>
                        <h2
                            id="source-management-heading"
                            class="font-semibold"
                        >
                            Sumber data lead
                        </h2>
                        <p class="mt-1 text-sm text-kgj-muted">
                            Sinkronkan Google Sheets per cabang di bawah, atau
                            semua cabang yang terhubung. Cabang tanpa CS
                            fallback dapat disinkronkan jika setiap lead
                            memiliki nama CS sumber.
                        </p>
                        <p
                            v-if="service_account_email"
                            class="mt-4 text-sm text-kgj-muted"
                        >
                            Bagikan setiap spreadsheet sumber kepada akun
                            berikut dengan akses <strong>Pelihat</strong>:
                        </p>
                        <div
                            v-if="service_account_email"
                            class="mt-2 flex flex-wrap items-center gap-3"
                        >
                            <p
                                class="min-w-0 flex-1 basis-64 text-sm font-medium break-all text-kgj-ink select-all"
                            >
                                {{ service_account_email }}
                            </p>
                            <button
                                type="button"
                                class="kgj-button-secondary shrink-0"
                                @click="copyServiceAccountEmail"
                            >
                                Salin email
                            </button>
                        </div>
                        <p
                            v-if="service_account_email"
                            class="mt-1 text-xs text-kgj-muted"
                            role="status"
                            aria-live="polite"
                        >
                            {{ copyStatus }}
                        </p>
                        <p v-else class="mt-4 text-sm text-kgj-rose">
                            Email service account belum tersedia. Periksa
                            konfigurasi kredensial Google Sheets di server.
                        </p>
                    </div>
                    <div class="mt-4 border-t border-kgj-line pt-4">
                        <button
                            type="button"
                            class="kgj-button-secondary"
                            :disabled="
                                syncingAll ||
                                importForm.processing ||
                                syncingBranchId !== null ||
                                !branches.some(
                                    (branch) => branch.google_spreadsheet_id,
                                )
                            "
                            @click="syncAll"
                        >
                            {{
                                syncingAll
                                    ? 'Menyinkronkan…'
                                    : 'Sinkronkan semua cabang'
                            }}
                        </button>
                    </div>
                </div>
            </section>
            <form class="kgj-surface min-w-0 p-5 sm:p-6" @submit.prevent="save">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-kgj-ink">
                            {{
                                editingId ? 'Perbarui cabang' : 'Tambah cabang'
                            }}
                        </h2>
                        <p class="mt-1 text-sm text-kgj-muted">
                            Hubungkan spreadsheet ketika cabang siap
                            disinkronkan.
                        </p>
                    </div>
                    <button
                        v-if="editingId"
                        class="text-sm font-semibold text-kgj-gold-deep underline decoration-[#c4a45d] underline-offset-4"
                        type="button"
                        @click="cancel"
                    >
                        Batal ubah
                    </button>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label
                        class="grid gap-1.5 text-sm font-medium"
                        for="branch-name"
                        >Nama cabang
                        <input
                            id="branch-name"
                            v-model="form.name"
                            class="kgj-input"
                            placeholder="Contoh: Palagan"
                            required
                            maxlength="100"
                            :aria-invalid="Boolean(form.errors.name)"
                        />
                        <span
                            v-if="form.errors.name"
                            class="text-sm font-normal text-[#a23e2b]"
                            role="alert"
                            >{{ form.errors.name }}</span
                        >
                    </label>
                    <label
                        class="grid gap-1.5 text-sm font-medium"
                        for="branch-sheet"
                        ><span
                            >ID Google Spreadsheet
                            <span class="font-normal text-kgj-muted"
                                >(opsional)</span
                            ></span
                        >
                        <input
                            id="branch-sheet"
                            v-model="form.google_spreadsheet_id"
                            class="kgj-input"
                            :placeholder="
                                editingId
                                    ? 'Gunakan tindakan Ganti spreadsheet sumber'
                                    : 'Tempel ID dari URL spreadsheet'
                            "
                            maxlength="128"
                            :readonly="Boolean(editingId)"
                            :aria-invalid="
                                Boolean(form.errors.google_spreadsheet_id)
                            "
                        />
                        <span
                            v-if="form.errors.google_spreadsheet_id"
                            class="text-sm font-normal text-[#a23e2b]"
                            role="alert"
                            >{{ form.errors.google_spreadsheet_id }}</span
                        >
                        <span
                            v-if="editingId"
                            class="text-xs font-normal text-kgj-muted"
                            >Gunakan menu Lainnya untuk mengganti atau
                            memutuskan sumber spreadsheet.</span
                        >
                    </label>
                </div>
                <label
                    v-if="editingId"
                    class="mt-4 grid gap-1.5 text-sm font-medium"
                    >CS fallback impor
                    <select
                        v-model="form.default_import_user_id"
                        class="kgj-input"
                        :aria-invalid="
                            Boolean(form.errors.default_import_user_id)
                        "
                    >
                        <option value="">Pilih CS fallback impor</option>
                        <option
                            v-for="account in importCsOptions"
                            :key="account.id"
                            :value="String(account.id)"
                        >
                            {{ account.name }} · {{ account.email }}
                        </option>
                    </select>
                    <span class="text-xs font-normal text-kgj-muted"
                        >Dipakai hanya bila Nama CS sumber kosong.</span
                    >
                    <span
                        v-if="form.errors.default_import_user_id"
                        class="text-sm font-normal text-[#a23e2b]"
                        role="alert"
                        >{{ form.errors.default_import_user_id }}</span
                    >
                </label>
                <div
                    class="mt-5 flex flex-wrap items-center gap-3 border-t border-kgj-line pt-4"
                >
                    <button
                        class="kgj-button-primary min-w-40"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? 'Menyimpan…'
                                : editingId
                                  ? 'Simpan perubahan'
                                  : 'Tambah cabang'
                        }}
                    </button>
                    <p class="text-xs text-kgj-muted">
                        Pengaturan dapat diubah kembali kapan saja.
                    </p>
                </div>
            </form>
        </div>
        <form
            class="kgj-surface mt-6 min-w-0 p-5 sm:p-6"
            @submit.prevent="importFile"
        >
            <h2 class="font-semibold">Impor berkas</h2>
            <p class="mt-1 text-sm text-kgj-muted">
                {{
                    importForm.import_mode === 'daily_tabs'
                        ? 'Unggah XLSX monitoring dan pilih cabang sumber untuk tab 1–31.'
                        : 'Unggah CSV atau XLSX dari tab All Leads. Tetapkan cabang sumber di daftar Leads agar tim cabang dapat menindaklanjutinya.'
                }}
            </p>
            <div
                class="mt-4 grid items-end gap-3 sm:grid-cols-2"
                :class="
                    importForm.import_mode === 'daily_tabs'
                        ? 'xl:grid-cols-[minmax(10rem,1fr)_minmax(10rem,1fr)_minmax(16rem,2fr)_auto]'
                        : 'xl:grid-cols-[minmax(10rem,1fr)_minmax(16rem,3fr)_auto]'
                "
            >
                <label class="grid min-w-0 gap-1.5 text-sm font-medium"
                    >Mode impor
                    <select
                        v-model="importForm.import_mode"
                        class="kgj-input"
                        :disabled="importForm.processing"
                        @change="importForm.branch_id = ''"
                    >
                        <option value="all_leads">All Leads</option>
                        <option value="daily_tabs">Tab harian</option>
                    </select>
                </label>
                <label
                    v-if="importForm.import_mode === 'daily_tabs'"
                    class="grid min-w-0 gap-1.5 text-sm font-medium"
                    >Cabang sumber
                    <select
                        v-model="importForm.branch_id"
                        class="kgj-input"
                        :disabled="importForm.processing"
                        required
                    >
                        <option value="">Pilih cabang</option>
                        <option
                            v-for="branch in branches"
                            :key="branch.id"
                            :value="String(branch.id)"
                        >
                            {{ branch.name }}
                        </option>
                    </select>
                </label>
                <label
                    class="grid min-w-0 gap-1.5 text-sm font-medium sm:col-span-2 xl:col-span-1"
                    >Berkas CSV / XLSX
                    <input
                        :key="fileInputKey"
                        type="file"
                        class="kgj-input w-full min-w-0 p-0 text-sm text-kgj-muted file:mr-3 file:h-full file:cursor-pointer file:border-0 file:border-r file:border-kgj-line file:bg-[#f6f2ea] file:px-4 file:font-semibold file:text-kgj-ink"
                        accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                        :disabled="importForm.processing"
                        @change="selectImportFile"
                    />
                </label>
                <button
                    class="kgj-button-gold justify-self-start"
                    :disabled="
                        !importForm.csv_file ||
                        importForm.processing ||
                        syncingAll ||
                        syncingBranchId !== null
                    "
                >
                    {{ importForm.processing ? 'Mengimpor…' : 'Impor berkas' }}
                </button>
            </div>
            <p
                v-if="importForm.errors.csv_file"
                class="mt-2 text-sm text-kgj-rose"
                role="alert"
            >
                {{ importForm.errors.csv_file }}
            </p>
            <p
                v-if="importForm.errors.branch_id"
                class="mt-2 text-sm text-kgj-rose"
                role="alert"
            >
                {{ importForm.errors.branch_id }}
            </p>
        </form>
        <div class="mt-6 grid gap-3 md:hidden">
            <article
                v-for="branch in branches"
                :key="branch.id"
                class="kgj-surface p-4"
            >
                <div class="flex items-start justify-between gap-3">
                    <h2 class="font-semibold">{{ branch.name }}</h2>
                    <span
                        class="kgj-status max-w-48 shrink-0 text-right leading-4 whitespace-normal"
                        :class="statusClass(branch)"
                    >
                        {{ branch.import_status }}
                    </span>
                </div>
                <div v-if="branch.cs_accounts.length" class="mt-3 space-y-2">
                    <p
                        class="text-xs font-semibold tracking-[.08em] text-kgj-muted uppercase"
                    >
                        CS aktif ({{ branch.cs_accounts.length }})
                    </p>
                    <p
                        v-for="account in branch.cs_accounts"
                        :key="account.id"
                        class="text-sm text-kgj-muted"
                    >
                        <span class="font-medium text-kgj-ink">{{
                            account.name
                        }}</span>
                        <span
                            v-if="branch.default_import_user_id === account.id"
                            class="ml-2 rounded-full bg-[#f3ead1] px-2 py-0.5 text-xs font-semibold text-kgj-gold-deep"
                            >Fallback impor</span
                        >
                        <small class="block break-all">{{
                            account.email
                        }}</small>
                    </p>
                </div>
                <p v-else class="mt-2 text-sm text-kgj-muted">
                    Tambahkan akun CS dari menu Akun.
                </p>
                <div class="mt-4 border-t border-kgj-line pt-3">
                    <p class="text-sm font-medium text-kgj-ink">
                        {{
                            branch.google_spreadsheet_id
                                ? 'Google Sheets terhubung'
                                : 'Google Sheets belum terhubung'
                        }}
                    </p>
                    <details
                        v-if="branch.google_spreadsheet_id"
                        class="mt-1 text-xs text-kgj-muted"
                    >
                        <summary
                            class="cursor-pointer font-semibold text-kgj-gold-deep"
                        >
                            Lihat ID spreadsheet
                        </summary>
                        <p class="mt-1 break-all">
                            {{ branch.google_spreadsheet_id }}
                        </p>
                    </details>
                    <p v-else class="mt-1 text-xs text-kgj-muted">
                        Hubungkan spreadsheet sumber dari menu Lainnya.
                    </p>
                    <details
                        v-if="branch.source_events.length"
                        class="mt-2 text-xs text-kgj-muted"
                    >
                        <summary
                            class="cursor-pointer font-semibold text-kgj-gold-deep"
                        >
                            Riwayat konfigurasi sumber
                        </summary>
                        <ol
                            class="mt-2 grid gap-2 border-l border-kgj-line pl-3"
                        >
                            <li
                                v-for="event in branch.source_events"
                                :key="event.id"
                            >
                                <span class="font-medium text-kgj-ink">{{
                                    sourceEventLabel(event.action)
                                }}</span>
                                <span> · {{ sourceEventActor(event) }}</span>
                                <span v-if="event.created_at">
                                    ·
                                    {{ formatSyncTime(event.created_at) }}</span
                                >
                                <span
                                    v-if="event.detail"
                                    class="mt-0.5 block leading-relaxed"
                                    >{{ event.detail }}</span
                                >
                            </li>
                        </ol>
                    </details>
                </div>
                <p
                    v-if="branch.google_sheets_synced_at"
                    class="mt-1 text-xs text-kgj-muted"
                >
                    Berhasil terakhir:
                    {{ formatSyncTime(branch.google_sheets_synced_at) }}
                </p>
                <p
                    v-if="branch.last_sync"
                    class="mt-2 text-xs font-medium"
                    :class="syncAttemptClass(branch)"
                >
                    {{ syncAttemptLabel(branch) }}:
                    {{ formatSyncTime(branch.last_sync.completed_at) }}
                </p>
                <p
                    v-if="
                        branch.last_sync?.status !== 'failed' &&
                        syncSummary(branch)
                    "
                    class="mt-1 text-xs text-kgj-muted"
                >
                    {{ syncSummary(branch) }}
                </p>
                <details
                    v-if="syncFailureDetail(branch)"
                    class="mt-2 text-xs"
                    :class="
                        branch.last_sync?.status === 'partial'
                            ? 'text-kgj-gold-deep'
                            : 'text-kgj-rose'
                    "
                >
                    <summary class="cursor-pointer font-semibold">
                        {{
                            branch.last_sync?.status === 'partial'
                                ? 'Lihat tab yang perlu diperbaiki'
                                : 'Lihat alasan kegagalan'
                        }}
                    </summary>
                    <p class="mt-1 leading-relaxed break-words">
                        {{ syncFailureDetail(branch) }}
                    </p>
                </details>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button
                        v-if="branch.google_spreadsheet_id"
                        class="kgj-button-gold flex-1"
                        type="button"
                        :disabled="
                            !canSync(branch) ||
                            syncingAll ||
                            importForm.processing ||
                            syncingBranchId !== null
                        "
                        @click="syncBranch(branch)"
                    >
                        {{
                            syncingBranchId === branch.id
                                ? 'Menyinkronkan…'
                                : canSync(branch)
                                  ? 'Sinkronkan'
                                  : 'Belum siap impor'
                        }}
                    </button>
                    <button
                        class="kgj-button-secondary flex-1"
                        type="button"
                        @click="edit(branch)"
                    >
                        Ubah</button
                    ><DropdownMenu
                        ><DropdownMenuTrigger as-child
                            ><button
                                class="kgj-button-secondary flex-1"
                                type="button"
                            >
                                Lainnya
                            </button></DropdownMenuTrigger
                        ><DropdownMenuContent
                            align="end"
                            class="border-kgj-line bg-kgj-paper text-kgj-ink"
                            ><DropdownMenuItem
                                @select="openSourceChange(branch)"
                                >{{
                                    branch.google_spreadsheet_id
                                        ? 'Ganti spreadsheet sumber'
                                        : 'Hubungkan spreadsheet sumber'
                                }}</DropdownMenuItem
                            ><DropdownMenuItem
                                v-if="branch.google_spreadsheet_id"
                                @select="previewReconciliation(branch)"
                                >Periksa data historis</DropdownMenuItem
                            ><DropdownMenuItem
                                v-if="branch.google_spreadsheet_id"
                                variant="destructive"
                                @select="branchToDisconnect = branch"
                                >Putuskan koneksi Google
                                Sheets</DropdownMenuItem
                            ><DropdownMenuItem
                                variant="destructive"
                                @select="branchToDelete = branch"
                                >Hapus cabang</DropdownMenuItem
                            ></DropdownMenuContent
                        ></DropdownMenu
                    >
                </div>
            </article>
            <p v-if="!branches.length" class="py-10 text-center text-kgj-muted">
                Belum ada cabang.
            </p>
        </div>
        <div class="kgj-table mt-6 hidden overflow-x-auto md:block">
            <table class="w-full min-w-[900px] text-sm">
                <colgroup>
                    <col class="w-[13%]" />
                    <col class="w-[28%]" />
                    <col class="w-[34%]" />
                    <col class="w-[25%]" />
                </colgroup>
                <thead>
                    <tr class="text-left">
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Tim CS</th>
                        <th class="px-4 py-3">Sumber lead</th>
                        <th class="px-4 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="branch in branches" :key="branch.id">
                        <td class="px-4 py-3 align-top">
                            <p class="font-semibold">{{ branch.name }}</p>
                            <span
                                class="kgj-status mt-2"
                                :class="statusClass(branch)"
                            >
                                {{ branch.import_status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <template v-if="branch.cs_accounts.length">
                                <p
                                    class="mb-2 text-xs font-semibold tracking-[.08em] text-kgj-muted uppercase"
                                >
                                    {{ branch.cs_accounts.length }} CS aktif
                                </p>
                                <div
                                    v-for="account in branch.cs_accounts"
                                    :key="account.id"
                                    class="mb-2 last:mb-0"
                                >
                                    <span class="font-medium">{{
                                        account.name
                                    }}</span>
                                    <span
                                        v-if="
                                            branch.default_import_user_id ===
                                            account.id
                                        "
                                        class="ml-2 rounded-full bg-[#f3ead1] px-2 py-0.5 text-xs font-semibold text-kgj-gold-deep"
                                        >Default impor</span
                                    >
                                    <small
                                        class="mt-0.5 block text-kgj-muted"
                                        >{{ account.email }}</small
                                    >
                                </div>
                            </template>
                            <small v-else class="block text-kgj-muted"
                                >Tambahkan akun CS dari menu Akun.</small
                            >
                        </td>
                        <td class="px-4 py-3 align-top">
                            <p class="font-medium text-kgj-ink">
                                {{
                                    branch.google_spreadsheet_id
                                        ? 'Google Sheets terhubung'
                                        : 'Belum ada sumber Google Sheets'
                                }}
                            </p>
                            <small
                                v-if="branch.google_sheets_synced_at"
                                class="mt-1 block text-kgj-muted"
                                >Berhasil terakhir:
                                {{
                                    formatSyncTime(
                                        branch.google_sheets_synced_at,
                                    )
                                }}</small
                            >
                            <small
                                v-if="branch.last_sync"
                                class="mt-2 block font-semibold"
                                :class="syncAttemptClass(branch)"
                            >
                                {{ syncAttemptLabel(branch) }}:
                                {{
                                    formatSyncTime(
                                        branch.last_sync.completed_at,
                                    )
                                }}
                            </small>
                            <small
                                v-if="
                                    branch.last_sync?.status !== 'failed' &&
                                    syncSummary(branch)
                                "
                                class="mt-1 block text-kgj-muted"
                            >
                                {{ syncSummary(branch) }}
                            </small>
                            <details
                                v-if="branch.google_spreadsheet_id"
                                class="mt-2 text-xs text-kgj-muted"
                            >
                                <summary
                                    class="cursor-pointer font-semibold text-kgj-gold-deep"
                                >
                                    Lihat ID spreadsheet
                                </summary>
                                <p class="mt-1 break-all">
                                    {{ branch.google_spreadsheet_id }}
                                </p>
                            </details>
                            <details
                                v-if="branch.source_events.length"
                                class="mt-2 text-xs text-kgj-muted"
                            >
                                <summary
                                    class="cursor-pointer font-semibold text-kgj-gold-deep"
                                >
                                    Riwayat konfigurasi sumber
                                </summary>
                                <ol
                                    class="mt-2 grid gap-2 border-l border-kgj-line pl-3"
                                >
                                    <li
                                        v-for="event in branch.source_events"
                                        :key="event.id"
                                    >
                                        <span
                                            class="font-medium text-kgj-ink"
                                            >{{
                                                sourceEventLabel(event.action)
                                            }}</span
                                        >
                                        <span>
                                            ·
                                            {{ sourceEventActor(event) }}</span
                                        >
                                        <span v-if="event.created_at">
                                            ·
                                            {{
                                                formatSyncTime(event.created_at)
                                            }}</span
                                        >
                                        <span
                                            v-if="event.detail"
                                            class="mt-0.5 block leading-relaxed"
                                            >{{ event.detail }}</span
                                        >
                                    </li>
                                </ol>
                            </details>
                            <details
                                v-if="syncFailureDetail(branch)"
                                class="mt-2 text-xs"
                                :class="
                                    branch.last_sync?.status === 'partial'
                                        ? 'text-kgj-gold-deep'
                                        : 'text-kgj-rose'
                                "
                            >
                                <summary class="cursor-pointer font-semibold">
                                    {{
                                        branch.last_sync?.status === 'partial'
                                            ? 'Lihat tab yang perlu diperbaiki'
                                            : 'Lihat alasan kegagalan'
                                    }}
                                </summary>
                                <p class="mt-1 leading-relaxed break-words">
                                    {{ syncFailureDetail(branch) }}
                                </p>
                            </details>
                        </td>
                        <td class="px-4 py-3 text-right align-middle">
                            <div class="flex flex-wrap justify-end gap-2">
                                <button
                                    v-if="branch.google_spreadsheet_id"
                                    class="kgj-button-gold"
                                    type="button"
                                    :disabled="
                                        !canSync(branch) ||
                                        syncingAll ||
                                        importForm.processing ||
                                        syncingBranchId !== null
                                    "
                                    @click="syncBranch(branch)"
                                >
                                    {{
                                        syncingBranchId === branch.id
                                            ? 'Menyinkronkan…'
                                            : canSync(branch)
                                              ? 'Sinkronkan'
                                              : 'Belum siap impor'
                                    }}
                                </button>
                                <button
                                    class="kgj-button-secondary"
                                    type="button"
                                    @click="edit(branch)"
                                >
                                    Ubah
                                </button>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <button
                                            class="kgj-button-secondary"
                                            type="button"
                                        >
                                            Lainnya
                                        </button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="border-kgj-line bg-kgj-paper text-kgj-ink"
                                    >
                                        <DropdownMenuItem
                                            v-if="branch.google_spreadsheet_id"
                                            @select="
                                                previewReconciliation(branch)
                                            "
                                            >Periksa data
                                            historis</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            @select="openSourceChange(branch)"
                                            >{{
                                                branch.google_spreadsheet_id
                                                    ? 'Ganti spreadsheet sumber'
                                                    : 'Hubungkan spreadsheet sumber'
                                            }}</DropdownMenuItem
                                        ><DropdownMenuItem
                                            v-if="branch.google_spreadsheet_id"
                                            variant="destructive"
                                            @select="
                                                branchToDisconnect = branch
                                            "
                                            >Putuskan koneksi Google
                                            Sheets</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            variant="destructive"
                                            @select="branchToDelete = branch"
                                        >
                                            Hapus cabang
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!branches.length">
                        <td colspan="4" class="p-10 text-center text-kgj-muted">
                            Belum ada cabang. Tambahkan cabang pertama untuk
                            memulai.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Dialog
            :open="Boolean(branchToDelete)"
            @update:open="(open) => !open && (branchToDelete = null)"
        >
            <DialogContent
                ><DialogHeader
                    ><DialogTitle>Hapus cabang?</DialogTitle
                    ><DialogDescription
                        >Cabang {{ branchToDelete?.name }} hanya dapat dihapus
                        bila tidak memiliki akun atau lead dari sumber cabang
                        ini.</DialogDescription
                    ></DialogHeader
                ><DialogFooter
                    ><DialogClose as-child
                        ><button class="kgj-button-secondary" type="button">
                            Batalkan
                        </button></DialogClose
                    ><button
                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-kgj-rose px-4 text-sm font-semibold text-white transition hover:bg-[#84362e]"
                        type="button"
                        @click="remove"
                    >
                        Hapus cabang
                    </button></DialogFooter
                ></DialogContent
            >
        </Dialog>
        <Dialog
            :open="Boolean(sourceBranch)"
            @update:open="(open) => !open && (sourceBranch = null)"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{
                            sourceBranch?.google_spreadsheet_id
                                ? 'Ganti spreadsheet sumber?'
                                : 'Hubungkan spreadsheet sumber?'
                        }}
                    </DialogTitle>
                    <DialogDescription>
                        Lead dengan kontak yang cocok tetap satu record. Data
                        asal akan diperbarui saat sinkronisasi ini, sedangkan
                        penugasan, milestone, catatan, dan hasil kerja CS tetap
                        dipertahankan.
                    </DialogDescription>
                </DialogHeader>
                <label class="grid gap-1.5 text-sm font-medium">
                    ID Google Spreadsheet baru
                    <input
                        v-model="sourceForm.google_spreadsheet_id"
                        class="kgj-input"
                        placeholder="Tempel ID dari URL spreadsheet"
                        maxlength="128"
                        required
                        :aria-invalid="
                            Boolean(sourceForm.errors.google_spreadsheet_id)
                        "
                    />
                    <span
                        v-if="sourceForm.errors.google_spreadsheet_id"
                        class="text-sm font-normal text-[#a23e2b]"
                        role="alert"
                        >{{ sourceForm.errors.google_spreadsheet_id }}</span
                    >
                </label>
                <p class="text-sm text-kgj-muted">
                    Lead yang tidak ada di spreadsheet baru tidak dihapus atau
                    diarsipkan otomatis.
                </p>
                <div
                    v-if="sourcePreview"
                    class="grid gap-3 rounded-xl border border-kgj-line bg-kgj-canvas p-4 text-sm sm:grid-cols-2"
                >
                    <p class="font-semibold text-kgj-ink sm:col-span-2">
                        Dampak spreadsheet yang diperiksa
                    </p>
                    <p>
                        <span class="block text-xs text-kgj-muted"
                            >Baris lead</span
                        >
                        <span class="font-semibold tabular-nums">{{
                            sourcePreview.rows
                        }}</span>
                    </p>
                    <p>
                        <span class="block text-xs text-kgj-muted"
                            >Kontak cocok</span
                        >
                        <span class="font-semibold tabular-nums">{{
                            sourcePreview.matching
                        }}</span>
                    </p>
                    <p>
                        <span class="block text-xs text-kgj-muted"
                            >Lead baru</span
                        >
                        <span class="font-semibold tabular-nums">{{
                            sourcePreview.new
                        }}</span>
                    </p>
                    <p>
                        <span class="block text-xs text-kgj-muted"
                            >Data asal diperbarui</span
                        >
                        <span class="font-semibold tabular-nums">{{
                            sourcePreview.updates
                        }}</span>
                    </p>
                    <p
                        v-if="sourcePreview.conflicts"
                        class="text-kgj-rose sm:col-span-2"
                    >
                        {{ sourcePreview.conflicts }} kontak berasal dari cabang
                        lain dan akan ditandai untuk ditinjau, bukan
                        dipindahkan.
                    </p>
                    <p
                        v-if="sourcePreview.missing_source_fields"
                        class="text-kgj-gold-deep sm:col-span-2"
                    >
                        {{ sourcePreview.missing_source_fields }} kolom sumber
                        kosong perlu dilengkapi di Google Sheets.
                    </p>
                    <p
                        v-if="sourcePreview.failed_tabs"
                        class="text-kgj-gold-deep sm:col-span-2"
                    >
                        {{ sourcePreview.failed_tabs }} tab perlu diperbaiki;
                        tab valid tetap dapat disinkronkan.
                    </p>
                </div>
                <p v-else class="text-xs text-kgj-muted">
                    Periksa dampak terlebih dahulu. Spreadsheet tidak akan
                    diganti sebelum pemeriksaan selesai.
                </p>
                <DialogFooter>
                    <DialogClose as-child>
                        <button class="kgj-button-secondary" type="button">
                            Batalkan
                        </button>
                    </DialogClose>
                    <button
                        class="kgj-button-secondary"
                        type="button"
                        :disabled="
                            sourceForm.processing ||
                            !sourceForm.google_spreadsheet_id.trim()
                        "
                        @click="previewSource"
                    >
                        {{
                            sourceForm.processing
                                ? 'Memeriksa…'
                                : 'Periksa dampak'
                        }}
                    </button>
                    <button
                        class="kgj-button-primary"
                        type="button"
                        :disabled="sourceForm.processing || !sourcePreview"
                        @click="replaceSource"
                    >
                        {{
                            sourceForm.processing
                                ? 'Menyinkronkan…'
                                : 'Simpan dan sinkronkan'
                        }}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <Dialog
            :open="Boolean(reconciliationBranch)"
            @update:open="(open) => !open && (reconciliationBranch = null)"
        >
            <DialogContent class="max-h-[85vh] overflow-y-auto sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle
                        >Periksa data historis
                        {{ reconciliationBranch?.name }}</DialogTitle
                    >
                    <DialogDescription>
                        Bandingkan tanggal, nama CS, dan waktu chat dengan tab
                        sumber. Data kerja dan penugasan CS tidak diubah.
                    </DialogDescription>
                </DialogHeader>
                <p
                    v-if="reconciliationForm.processing"
                    role="status"
                    class="text-sm text-kgj-muted"
                >
                    Memeriksa spreadsheet…
                </p>
                <template v-else-if="reconciliationPreview">
                    <p class="text-sm">
                        {{ reconciliationPreview.source_rows }} baris sumber ·
                        {{ reconciliationPreview.source_comparison.matching }}
                        cocok dengan lead tersimpan ·
                        {{ reconciliationPreview.source_comparison.new }} belum
                        memiliki lead ·
                        {{ reconciliationPreview.source_comparison.conflicts }}
                        tercatat di cabang lain.
                    </p>
                    <p class="text-sm text-kgj-muted">
                        Hitungan berdasarkan baris, sehingga kontak yang muncul
                        beberapa kali dapat dihitung lebih dari sekali.
                        Sinkronisasi terjadwal dapat menambahkan lead yang belum
                        tersimpan.
                    </p>
                    <p class="text-sm">
                        {{ reconciliationPreview.matched }} lead sudah sesuai ·
                        {{ reconciliationPreview.changes.length }} lead dapat
                        dikoreksi · {{ reconciliationPreview.uncertain }} perlu
                        diperiksa ·
                        {{ reconciliationPreview.conflicts }} konflik cabang ·
                        {{ reconciliationPreview.missing }} lead aktif tidak
                        ditemukan di spreadsheet aktif.
                    </p>
                    <p
                        v-if="
                            !reconciliationPreview.changes.length &&
                            !reconciliationPreview.failed_tabs
                        "
                        class="text-sm text-kgj-muted"
                    >
                        Tidak ada koreksi yang dapat diterapkan. Lead yang tidak
                        ditemukan atau berkonflik perlu diperiksa secara
                        terpisah.
                    </p>
                    <p class="text-sm text-kgj-muted">
                        Spreadsheet:
                        {{
                            reconciliationPreview.spreadsheet_title ||
                            'Judul tidak tersedia'
                        }}. Bulan dan tahun tanggal lead diambil dari judul ini;
                        pastikan sesuai periode tab sebelum menerapkan koreksi.
                    </p>
                    <p class="text-sm break-all text-kgj-muted">
                        ID sumber aktif:
                        {{ reconciliationPreview.spreadsheet_id }}
                    </p>
                    <details
                        v-if="reconciliationPreview.archive_candidates.length"
                        class="text-sm"
                    >
                        <summary
                            class="cursor-pointer font-semibold text-kgj-gold-deep"
                        >
                            {{
                                reconciliationPreview.archive_candidates.length
                            }}
                            lead impor dapat diarsipkan
                        </summary>
                        <p class="mt-2 text-kgj-muted">
                            Lead impor ini tidak ditemukan di spreadsheet aktif.
                            Lead manual dan konflik cabang tidak disertakan;
                            lead yang diarsipkan dapat dipulihkan.
                        </p>
                        <ul class="mt-2 max-h-48 overflow-y-auto">
                            <li
                                v-for="candidate in reconciliationPreview.archive_candidates"
                                :key="candidate.lead_id"
                            >
                                {{ candidate.contact }}
                            </li>
                        </ul>
                    </details>
                    <p
                        v-if="reconciliationPreview.failed_tabs"
                        class="text-sm text-kgj-rose"
                        role="alert"
                    >
                        {{ reconciliationPreview.failed_tabs }} tab gagal
                        dibaca. Perbaiki sumber dan periksa kembali sebelum
                        menerapkan koreksi.
                    </p>
                    <div
                        v-if="reconciliationPreview.coverage_errors.length"
                        class="text-sm text-red-700"
                    >
                        <p
                            v-for="error in reconciliationPreview.coverage_errors"
                            :key="error"
                        >
                            {{ error }}
                        </p>
                    </div>
                    <div
                        v-if="reconciliationPreview.reappeared_archived.length"
                        class="space-y-2 text-sm"
                    >
                        <p>
                            {{
                                reconciliationPreview.reappeared_archived.length
                            }}
                            lead arsip ditemukan kembali di spreadsheet. Periksa
                            sebelum memulihkan.
                        </p>
                        <ul class="max-h-32 overflow-auto">
                            <li
                                v-for="lead in reconciliationPreview.reappeared_archived"
                                :key="lead.lead_id"
                            >
                                {{ lead.contact }} · Tab {{ lead.tab }}
                            </li>
                        </ul>
                        <a href="/leads?archived=1" class="underline"
                            >Buka arsip lead</a
                        >
                    </div>
                    <div
                        v-if="reconciliationPreview.changes.length"
                        class="max-h-72 overflow-auto rounded-lg border border-kgj-line"
                    >
                        <table class="w-full min-w-[620px] text-left text-sm">
                            <thead>
                                <tr>
                                    <th class="p-3">Lead / tab</th>
                                    <th class="p-3">Tanggal lama → sumber</th>
                                    <th class="p-3">CS lama → sumber</th>
                                    <th class="p-3">Waktu chat lama → hasil</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="change in reconciliationPreview.changes"
                                    :key="change.lead_id"
                                    class="border-t border-kgj-line"
                                >
                                    <td class="p-3">
                                        {{ change.contact }}
                                        <span class="text-kgj-muted"
                                            >· Tab {{ change.tab }}</span
                                        >
                                    </td>
                                    <td class="p-3">
                                        {{ change.old_date || '—' }} →
                                        {{ change.new_date }}
                                    </td>
                                    <td class="p-3">
                                        {{ change.old_cs || '—' }} →
                                        {{ change.new_cs || '—' }}
                                    </td>
                                    <td class="p-3">
                                        {{ change.old_chat_time || '—' }} →
                                        {{ change.new_chat_time || '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <details
                        v-if="reconciliationPreview.review.length"
                        class="text-sm"
                    >
                        <summary
                            class="cursor-pointer font-semibold text-kgj-gold-deep"
                        >
                            Lihat data yang perlu diperiksa
                        </summary>
                        <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto">
                            <li
                                v-for="(
                                    item, index
                                ) in reconciliationPreview.review"
                                :key="index"
                            >
                                {{ item.contact }} · Tab {{ item.tab || '—' }} —
                                {{ item.reason }}
                            </li>
                        </ul>
                    </details>
                    <label
                        v-if="
                            reconciliationPreview.changes.length ||
                            reconciliationPreview.archive_candidates.length
                        "
                        class="flex items-start gap-2 text-sm"
                    >
                        <input
                            v-model="reconciliationForm.backup_confirmed"
                            type="checkbox"
                            class="mt-1"
                        />
                        Saya telah memastikan backup database tersedia sebelum
                        menerapkan koreksi atau mengarsipkan lead.
                    </label>
                    <label
                        v-if="
                            reconciliationPreview.changes.length ||
                            reconciliationPreview.archive_candidates.length
                        "
                        class="flex items-start gap-2 text-sm"
                    >
                        <input
                            v-model="reconciliationForm.period_confirmed"
                            type="checkbox"
                            class="mt-1"
                        />
                        Saya telah memeriksa bulan dan tahun pada judul
                        spreadsheet sesuai dengan tab sumber.
                    </label>
                    <p
                        v-if="reconciliationForm.errors.period_confirmed"
                        class="text-sm text-kgj-rose"
                        role="alert"
                    >
                        {{ reconciliationForm.errors.period_confirmed }}
                    </p>
                    <label
                        v-if="reconciliationPreview.archive_candidates.length"
                        class="flex items-start gap-2 text-sm"
                    >
                        <input
                            v-model="
                                reconciliationForm.source_coverage_confirmed
                            "
                            type="checkbox"
                            class="mt-1"
                        />
                        Saya telah memastikan semua lead yang harus tetap aktif
                        berada pada tab harian 1–31, dalam area A1:AZ5000.
                    </label>
                    <p
                        v-if="
                            reconciliationForm.errors.source_coverage_confirmed
                        "
                        class="text-sm text-kgj-rose"
                        role="alert"
                    >
                        {{
                            reconciliationForm.errors.source_coverage_confirmed
                        }}
                    </p>
                    <p
                        v-if="reconciliationForm.errors.backup_confirmed"
                        class="text-sm text-kgj-rose"
                        role="alert"
                    >
                        {{ reconciliationForm.errors.backup_confirmed }}
                    </p>
                </template>
                <p v-else class="text-sm text-kgj-muted">
                    Pratinjau belum tersedia. Periksa kembali sumber cabang.
                </p>
                <DialogFooter>
                    <DialogClose as-child
                        ><button class="kgj-button-secondary" type="button">
                            Tutup
                        </button></DialogClose
                    >
                    <button
                        v-if="reconciliationPreview?.archive_candidates.length"
                        class="kgj-button-secondary"
                        type="button"
                        :disabled="
                            reconciliationForm.processing ||
                            !reconciliationForm.backup_confirmed ||
                            !reconciliationForm.period_confirmed ||
                            !reconciliationForm.source_coverage_confirmed ||
                            !reconciliationPreview.source_rows ||
                            Boolean(reconciliationPreview.failed_tabs) ||
                            Boolean(
                                reconciliationPreview.coverage_errors.length,
                            )
                        "
                        @click="archiveMissing"
                    >
                        Arsipkan
                        {{ reconciliationPreview.archive_candidates.length }}
                        lead impor
                    </button>
                    <button
                        class="kgj-button-primary"
                        type="button"
                        :disabled="
                            reconciliationForm.processing ||
                            !reconciliationForm.backup_confirmed ||
                            !reconciliationForm.period_confirmed ||
                            !reconciliationPreview?.changes.length ||
                            Boolean(reconciliationPreview?.failed_tabs)
                        "
                        @click="applyReconciliation"
                    >
                        Terapkan koreksi
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <Dialog
            :open="Boolean(branchToDisconnect)"
            @update:open="(open) => !open && (branchToDisconnect = null)"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Putuskan koneksi Google Sheets?</DialogTitle>
                    <DialogDescription>
                        Sinkronisasi baru untuk cabang
                        {{ branchToDisconnect?.name }} akan berhenti. Lead,
                        penugasan CS, dan riwayat yang sudah tersimpan tidak
                        akan dihapus.
                    </DialogDescription>
                </DialogHeader>
                <p class="text-sm text-kgj-muted">
                    Untuk mencabut akses Google sepenuhnya, hapus email Service
                    Account dari pengaturan Share spreadsheet.
                </p>
                <DialogFooter>
                    <DialogClose as-child>
                        <button class="kgj-button-secondary" type="button">
                            Batalkan
                        </button>
                    </DialogClose>
                    <button
                        class="inline-flex min-h-11 items-center justify-center rounded-lg bg-kgj-rose px-4 text-sm font-semibold text-white transition hover:bg-[#84362e]"
                        type="button"
                        @click="disconnectSource"
                    >
                        Putuskan koneksi
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </section>
</template>
