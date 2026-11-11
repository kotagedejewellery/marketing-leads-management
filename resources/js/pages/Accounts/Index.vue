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

type Account = {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'cs';
    branch_id: number | null;
    branch_name: string | null;
    active: boolean;
};
const props = defineProps<{
    accounts: Account[];
    branches: { id: number; name: string }[];
}>();
const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'cs' as 'admin' | 'cs',
    branch_id: '',
    active: true,
});
const editingId = ref<number | null>(null);
const accountToToggle = ref<{ account: Account; active: boolean } | null>(null);
const needsBranch = computed(() => form.role === 'cs');
const cancel = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};
const edit = (account: Account) => {
    editingId.value = account.id;
    form.name = account.name;
    form.email = account.email;
    form.password = '';
    form.role = account.role;
    form.branch_id = account.branch_id ? String(account.branch_id) : '';
    form.active = account.active;
    form.clearErrors();
};
const save = () => {
    const options = { onSuccess: cancel };
    if (editingId.value) form.patch(`/accounts/${editingId.value}`, options);
    else form.post('/accounts', options);
};
const setActive = (account: Account, active: boolean) => {
    accountToToggle.value = null;
    router.patch(`/accounts/${account.id}`, {
        name: account.name,
        email: account.email,
        password: '',
        role: account.role,
        branch_id: account.branch_id ? String(account.branch_id) : '',
        active,
    });
};
</script>

<template>
    <Head title="Akun" />
    <section class="kgj-page">
        <p class="kgj-eyebrow">Kontrol akses</p>
        <h1 class="mt-2 text-4xl font-semibold tracking-[-.04em] sm:text-5xl">
            Akun.
        </h1>
        <p class="kgj-subtitle">
            Buat akun admin atau satu akun CS aktif untuk setiap cabang.
        </p>
        <form
            class="kgj-surface mt-7 grid gap-4 p-4 sm:p-5 md:grid-cols-2"
            @submit.prevent="save"
        >
            <label class="grid gap-1 text-sm font-medium"
                >Nama<input
                    id="account-name"
                    v-model="form.name"
                    class="kgj-input"
                    required
                    :aria-invalid="Boolean(form.errors.name)"
                /><span v-if="form.errors.name" class="text-[#a23e2b]">{{
                    form.errors.name
                }}</span></label
            >
            <label class="grid gap-1 text-sm font-medium"
                >Email<input
                    id="account-email"
                    v-model="form.email"
                    type="email"
                    class="kgj-input"
                    required
                    :aria-invalid="Boolean(form.errors.email)"
                /><span v-if="form.errors.email" class="text-[#a23e2b]">{{
                    form.errors.email
                }}</span></label
            >
            <label class="grid gap-1 text-sm font-medium"
                >{{ editingId ? 'Kata sandi baru (opsional)' : 'Kata sandi'
                }}<input
                    id="account-password"
                    v-model="form.password"
                    type="password"
                    minlength="12"
                    class="kgj-input"
                    :required="!editingId"
                    :aria-invalid="Boolean(form.errors.password)"
                /><span v-if="form.errors.password" class="text-[#a23e2b]">{{
                    form.errors.password
                }}</span></label
            >
            <label class="grid gap-1 text-sm font-medium"
                >Peran<select
                    id="account-role"
                    v-model="form.role"
                    class="kgj-input"
                >
                    <option value="cs">CS</option>
                    <option value="admin">Admin</option></select
                ><span v-if="form.errors.role" class="text-[#a23e2b]">{{
                    form.errors.role
                }}</span></label
            >
            <label v-if="needsBranch" class="grid gap-1 text-sm font-medium"
                >Cabang<select
                    id="account-branch"
                    v-model="form.branch_id"
                    class="kgj-input"
                    required
                    :aria-invalid="Boolean(form.errors.branch_id)"
                >
                    <option value="">Pilih cabang</option>
                    <option
                        v-for="branch in props.branches"
                        :key="branch.id"
                        :value="String(branch.id)"
                    >
                        {{ branch.name }}
                    </option></select
                ><span v-if="form.errors.branch_id" class="text-[#a23e2b]">{{
                    form.errors.branch_id
                }}</span></label
            >
            <label
                v-if="editingId"
                class="flex min-h-11 items-center gap-2 text-sm font-medium"
                ><input
                    v-model="form.active"
                    class="size-4 accent-[#8a6b2d]"
                    type="checkbox"
                />
                Akun aktif</label
            >
            <div class="flex items-end gap-2">
                <button class="kgj-button-primary" :disabled="form.processing">
                    {{
                        form.processing
                            ? 'Menyimpan…'
                            : editingId
                              ? 'Simpan perubahan'
                              : 'Tambah akun'
                    }}</button
                ><button
                    v-if="editingId"
                    class="kgj-button-secondary"
                    type="button"
                    @click="cancel"
                >
                    Batal
                </button>
            </div>
        </form>
        <div class="mt-6 grid gap-3 md:hidden">
            <article
                v-for="account in accounts"
                :key="account.id"
                class="kgj-surface p-4"
                :class="{ 'opacity-60': !account.active }"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ account.name }}</h2>
                        <p class="mt-1 text-sm break-all text-kgj-muted">
                            {{ account.email }}
                        </p>
                    </div>
                    <span
                        class="kgj-status"
                        :class="
                            account.active ? 'text-kgj-sage' : 'text-kgj-muted'
                        "
                        >{{ account.active ? 'Aktif' : 'Nonaktif' }}</span
                    >
                </div>
                <p class="mt-4 text-sm">
                    {{ account.role.toUpperCase() }} ·
                    {{ account.branch_name || 'Tanpa cabang' }}
                </p>
                <div class="mt-4 flex gap-2">
                    <button
                        class="kgj-button-secondary flex-1"
                        type="button"
                        @click="edit(account)"
                    >
                        Ubah</button
                    ><button
                        class="kgj-button-secondary flex-1"
                        type="button"
                        @click="
                            accountToToggle = {
                                account,
                                active: !account.active,
                            }
                        "
                    >
                        {{ account.active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </div>
            </article>
            <p v-if="!accounts.length" class="py-10 text-center text-kgj-muted">
                Belum ada akun.
            </p>
        </div>
        <div class="kgj-table mt-6 hidden overflow-x-auto md:block">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="text-left">
                        <th class="p-4">Nama</th>
                        <th class="p-4">Peran</th>
                        <th class="p-4">Cabang</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="account in accounts"
                        :key="account.id"
                        :class="{ 'opacity-60': !account.active }"
                    >
                        <td class="p-4 font-semibold">
                            {{ account.name
                            }}<small class="block font-normal text-[#62665b]">{{
                                account.email
                            }}</small>
                        </td>
                        <td class="p-4">{{ account.role.toUpperCase() }}</td>
                        <td class="p-4">{{ account.branch_name || '—' }}</td>
                        <td class="p-4">
                            {{ account.active ? 'Aktif' : 'Nonaktif' }}
                        </td>
                        <td class="p-4 text-right">
                            <button
                                class="inline-flex min-h-11 items-center text-sm font-semibold text-kgj-gold-deep underline decoration-[#c4a45d] underline-offset-4"
                                type="button"
                                @click="edit(account)"
                            >
                                Ubah</button
                            ><button
                                class="ml-3 inline-flex min-h-11 items-center text-sm font-semibold underline underline-offset-4"
                                :class="
                                    account.active
                                        ? 'text-kgj-rose decoration-[#dfafa8]'
                                        : 'text-kgj-gold-deep decoration-[#c4a45d]'
                                "
                                type="button"
                                @click="
                                    accountToToggle = {
                                        account,
                                        active: !account.active,
                                    }
                                "
                            >
                                {{
                                    account.active ? 'Nonaktifkan' : 'Aktifkan'
                                }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!accounts.length">
                        <td colspan="5" class="p-10 text-center text-[#62665b]">
                            Belum ada akun.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Dialog
            :open="Boolean(accountToToggle)"
            @update:open="(open) => !open && (accountToToggle = null)"
        >
            <DialogContent
                ><DialogHeader
                    ><DialogTitle>{{
                        accountToToggle?.active
                            ? 'Aktifkan akun?'
                            : 'Nonaktifkan akun?'
                    }}</DialogTitle
                    ><DialogDescription>{{
                        accountToToggle?.active
                            ? `Akun ${accountToToggle?.account.name} dapat kembali masuk ke dashboard.`
                            : `Akun ${accountToToggle?.account.name} tidak dapat masuk lagi dan lead yang ditangani akan dilepas.`
                    }}</DialogDescription></DialogHeader
                ><DialogFooter
                    ><DialogClose as-child
                        ><button class="kgj-button-secondary" type="button">
                            Batalkan
                        </button></DialogClose
                    ><button
                        class="min-h-11 rounded-lg px-4 text-sm font-semibold text-white"
                        :class="
                            accountToToggle?.active
                                ? 'bg-kgj-sage hover:bg-[#3f5e4d]'
                                : 'bg-kgj-rose hover:bg-[#84362e]'
                        "
                        type="button"
                        @click="
                            accountToToggle &&
                            setActive(
                                accountToToggle.account,
                                accountToToggle.active,
                            )
                        "
                    >
                        {{
                            accountToToggle?.active
                                ? 'Aktifkan akun'
                                : 'Nonaktifkan akun'
                        }}
                    </button></DialogFooter
                ></DialogContent
            >
        </Dialog>
    </section>
</template>
