<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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
    cs_name: string | null;
    cs_email: string | null;
    google_spreadsheet_id: string | null;
    google_sheets_synced_at: string | null;
    google_sheets_sync_error: string | null;
};
defineProps<{ branches: Branch[] }>();
const form = useForm({ name: '', google_spreadsheet_id: '' });
const editingId = ref<number | null>(null);
const branchToDelete = ref<Branch | null>(null);
const syncingBranchId = ref<number | null>(null);
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
        <p class="kgj-subtitle">Satu cabang mempunyai satu akun CS aktif.</p>
        <form
            class="kgj-surface mt-7 grid max-w-2xl gap-3 p-4 sm:grid-cols-[1fr_1.4fr_auto]"
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
                <p class="mt-2 text-sm text-kgj-muted">
                    {{ branch.cs_name || 'Belum ditetapkan' }}
                </p>
                <p class="mt-1 text-sm break-all text-kgj-muted">
                    {{ branch.cs_email || 'Tambahkan akun CS dari menu Akun.' }}
                </p>
                <p class="mt-3 text-xs break-all text-kgj-muted">
                    {{
                        branch.google_spreadsheet_id ||
                        'Belum ada sumber Google Sheets.'
                    }}
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
                            {{ branch.cs_name || 'Belum ditetapkan'
                            }}<small class="block text-[#62665b]">{{
                                branch.cs_email ||
                                'Tambahkan akun CS dari menu Akun.'
                            }}</small>
                        </td>
                        <td class="p-4 text-xs">
                            <span class="break-all">{{
                                branch.google_spreadsheet_id ||
                                'Belum dikonfigurasi'
                            }}</span>
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
