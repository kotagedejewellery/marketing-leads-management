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
        summary: { inserted?: number; enriched?: number } | null;
        error: string | null;
    } | null;
};
const props = defineProps<{ branches: Branch[] }>();
const form = useForm({
    name: '',
    google_spreadsheet_id: '',
    default_import_user_id: '',
});
const editingId = ref<number | null>(null);
const branchToDelete = ref<Branch | null>(null);
const syncingBranchId = ref<number | null>(null);
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
const canSync = (branch: Branch) => branch.import_status === 'Siap impor';
const statusClass = (branch: Branch) =>
    canSync(branch) ? 'text-kgj-sage' : 'text-kgj-rose';
const syncAttemptLabel = (branch: Branch) => {
    if (!branch.last_sync) return '';

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

    return `${summary.inserted || 0} lead baru · ${summary.enriched || 0} data diperbarui`;
};
const syncFailureDetail = (branch: Branch) =>
    branch.last_sync?.status === 'failed'
        ? branch.last_sync.error
        : !branch.last_sync
          ? branch.google_sheets_sync_error
          : null;
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
        <form
            class="kgj-surface mt-7 max-w-4xl p-5 sm:p-6"
            @submit.prevent="save"
        >
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-kgj-ink">
                        {{ editingId ? 'Perbarui cabang' : 'Tambah cabang' }}
                    </h2>
                    <p class="mt-1 text-sm text-kgj-muted">
                        Hubungkan spreadsheet ketika cabang siap disinkronkan.
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
                        placeholder="Tempel ID dari URL spreadsheet"
                        maxlength="128"
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
                </label>
            </div>
            <label
                v-if="editingId"
                class="mt-4 grid gap-1.5 text-sm font-medium"
                >CS default impor
                <select
                    v-model="form.default_import_user_id"
                    class="kgj-input"
                    :aria-invalid="Boolean(form.errors.default_import_user_id)"
                >
                    <option value="">Pilih CS default impor</option>
                    <option
                        v-for="account in importCsOptions"
                        :key="account.id"
                        :value="String(account.id)"
                    >
                        {{ account.name }} · {{ account.email }}
                    </option>
                </select>
                <span class="text-xs font-normal text-kgj-muted"
                    >Lead baru dari spreadsheet cabang akan menjadi tanggung
                    jawab CS ini.</span
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
                            >Default impor</span
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
                        Ubah cabang untuk menyambungkan sumber lead.
                    </p>
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
                    class="mt-2 text-xs text-kgj-rose"
                >
                    <summary class="cursor-pointer font-semibold">
                        Lihat alasan kegagalan
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
                            !canSync(branch) || syncingBranchId === branch.id
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
                                v-if="syncFailureDetail(branch)"
                                class="mt-2 text-xs text-kgj-rose"
                            >
                                <summary class="cursor-pointer font-semibold">
                                    Lihat alasan kegagalan
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
                                        syncingBranchId === branch.id
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
    </section>
</template>
