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

type Branch = {
    id: number;
    name: string;
    default_import_user_id: number | null;
    cs_accounts: { id: number; name: string; email: string }[];
    import_status: string;
    google_spreadsheet_id: string | null;
    google_sheets_synced_at: string | null;
    google_sheets_sync_error: string | null;
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
const importCsOptions = computed(
    () =>
        props.branches.find((branch) => branch.id === editingId.value)
            ?.cs_accounts || [],
);
const formatSyncTime = (value: string | null) =>
    value ? new Date(value).toLocaleString('id-ID') : '';
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
        <p class="kgj-eyebrow">Konfigurasi tim</p>
        <h1 class="mt-2 text-4xl font-semibold tracking-[-.04em] sm:text-5xl">
            Cabang.
        </h1>
        <p class="kgj-subtitle">
            Kelola sumber lead dan beberapa akun CS aktif per cabang.
        </p>
        <form
            class="kgj-surface mt-7 grid max-w-3xl gap-3 p-4 sm:grid-cols-2"
            @submit.prevent="save"
        >
            <label class="sr-only" for="branch-name">Nama cabang</label>
            <input
                id="branch-name"
                v-model="form.name"
                class="kgj-input grow"
                placeholder="Nama cabang"
                required
                maxlength="100"
                :aria-invalid="Boolean(form.errors.name)"
            />
            <label class="sr-only" for="branch-sheet"
                >Google Spreadsheet ID</label
            >
            <input
                id="branch-sheet"
                v-model="form.google_spreadsheet_id"
                class="kgj-input"
                placeholder="Google Spreadsheet ID (opsional)"
                maxlength="128"
                :aria-invalid="Boolean(form.errors.google_spreadsheet_id)"
            />
            <label
                v-if="editingId"
                class="grid gap-1 text-sm font-medium sm:col-span-2"
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
                    class="text-[#a23e2b]"
                    >{{ form.errors.default_import_user_id }}</span
                >
            </label>
            <button class="kgj-button-primary" :disabled="form.processing">
                {{
                    form.processing
                        ? 'Menyimpan…'
                        : editingId
                          ? 'Simpan'
                          : 'Tambah'
                }}
            </button>
            <button
                v-if="editingId"
                class="kgj-button-secondary"
                type="button"
                @click="cancel"
            >
                Batal
            </button>
            <p
                v-if="form.errors.name"
                class="basis-full text-sm text-[#a23e2b]"
                role="alert"
            >
                {{ form.errors.name }}
            </p>
            <p
                v-if="form.errors.google_spreadsheet_id"
                class="text-sm text-[#a23e2b] sm:col-span-3"
                role="alert"
            >
                {{ form.errors.google_spreadsheet_id }}
            </p>
        </form>
        <div class="mt-6 grid gap-3 md:hidden">
            <article
                v-for="branch in branches"
                :key="branch.id"
                class="kgj-surface p-4"
            >
                <h2 class="font-semibold">{{ branch.name }}</h2>
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
                            class="ml-2 text-xs text-kgj-gold-deep"
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
                <p class="mt-3 text-xs break-all text-kgj-muted">
                    {{
                        branch.google_spreadsheet_id ||
                        'Belum ada sumber Google Sheets.'
                    }}
                </p>
                <p
                    class="mt-2 text-xs font-semibold"
                    :class="
                        branch.import_status === 'Siap impor'
                            ? 'text-kgj-sage'
                            : 'text-kgj-rose'
                    "
                >
                    {{ branch.import_status }}
                </p>
                <p
                    v-if="branch.google_sheets_synced_at"
                    class="mt-1 text-xs text-kgj-muted"
                >
                    Sinkron terakhir:
                    {{ formatSyncTime(branch.google_sheets_synced_at) }}
                </p>
                <p
                    v-if="branch.google_sheets_sync_error"
                    class="mt-1 text-xs text-kgj-rose"
                >
                    Sinkronisasi gagal: {{ branch.google_sheets_sync_error }}
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button
                        v-if="branch.google_spreadsheet_id"
                        class="kgj-button-secondary flex-1"
                        type="button"
                        :disabled="syncingBranchId === branch.id"
                        @click="syncBranch(branch)"
                    >
                        {{
                            syncingBranchId === branch.id
                                ? 'Menyinkronkan…'
                                : 'Sinkronkan'
                        }}
                    </button>
                    <button
                        class="kgj-button-secondary flex-1"
                        type="button"
                        @click="edit(branch)"
                    >
                        Ubah</button
                    ><button
                        class="kgj-button-secondary flex-1 text-kgj-rose"
                        type="button"
                        @click="branchToDelete = branch"
                    >
                        Hapus
                    </button>
                </div>
            </article>
            <p v-if="!branches.length" class="py-10 text-center text-kgj-muted">
                Belum ada cabang.
            </p>
        </div>
        <div class="kgj-table mt-6 hidden overflow-x-auto md:block">
            <table class="w-full min-w-[820px] text-sm">
                <thead>
                    <tr class="text-left">
                        <th class="p-4">Cabang</th>
                        <th class="p-4">Akun CS aktif</th>
                        <th class="p-4">Sumber Google Sheets</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="branch in branches" :key="branch.id">
                        <td class="p-4 font-semibold">{{ branch.name }}</td>
                        <td class="p-4">
                            <template v-if="branch.cs_accounts.length">
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
                                        class="ml-2 text-xs text-kgj-gold-deep"
                                        >Default impor</span
                                    >
                                    <small class="block text-[#62665b]">{{
                                        account.email
                                    }}</small>
                                </div>
                            </template>
                            <small v-else class="block text-[#62665b]"
                                >Tambahkan akun CS dari menu Akun.</small
                            >
                        </td>
                        <td class="p-4 text-xs">
                            <span class="break-all">{{
                                branch.google_spreadsheet_id ||
                                'Belum dikonfigurasi'
                            }}</span>
                            <small
                                class="mt-1 block font-semibold"
                                :class="
                                    branch.import_status === 'Siap impor'
                                        ? 'text-kgj-sage'
                                        : 'text-kgj-rose'
                                "
                                >{{ branch.import_status }}</small
                            >
                            <small
                                v-if="branch.google_sheets_synced_at"
                                class="mt-1 block text-kgj-muted"
                                >Sinkron terakhir:
                                {{
                                    formatSyncTime(
                                        branch.google_sheets_synced_at,
                                    )
                                }}</small
                            ><small
                                v-if="branch.google_sheets_sync_error"
                                class="mt-1 block text-kgj-rose"
                                >Gagal:
                                {{ branch.google_sheets_sync_error }}</small
                            >
                        </td>
                        <td class="p-4 text-right">
                            <button
                                v-if="branch.google_spreadsheet_id"
                                class="inline-flex min-h-11 items-center text-sm font-semibold text-kgj-gold-deep underline decoration-[#c4a45d] underline-offset-4"
                                type="button"
                                :disabled="syncingBranchId === branch.id"
                                @click="syncBranch(branch)"
                            >
                                {{
                                    syncingBranchId === branch.id
                                        ? 'Menyinkronkan…'
                                        : 'Sinkronkan'
                                }}
                            </button>
                            <button
                                class="ml-3 inline-flex min-h-11 items-center text-sm font-semibold text-kgj-gold-deep underline decoration-[#c4a45d] underline-offset-4"
                                type="button"
                                @click="edit(branch)"
                            >
                                Ubah</button
                            ><button
                                class="ml-3 inline-flex min-h-11 items-center text-sm font-semibold text-kgj-rose underline decoration-[#dfafa8] underline-offset-4"
                                type="button"
                                @click="branchToDelete = branch"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!branches.length">
                        <td colspan="4" class="p-10 text-center text-[#62665b]">
                            Belum ada cabang.
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
