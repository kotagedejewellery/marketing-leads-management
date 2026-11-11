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
};
defineProps<{ branches: Branch[] }>();
const form = useForm({ name: '' });
const editingId = ref<number | null>(null);
const branchToDelete = ref<Branch | null>(null);
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
            class="kgj-surface mt-7 flex max-w-xl flex-wrap gap-2 p-4"
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
                <div class="mt-4 flex gap-2">
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
            <table class="w-full min-w-[620px] text-sm">
                <thead>
                    <tr class="text-left">
                        <th class="p-4">Cabang</th>
                        <th class="p-4">Akun CS aktif</th>
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
                        <td class="p-4 text-right">
                            <button
                                class="inline-flex min-h-11 items-center text-sm font-semibold text-kgj-gold-deep underline decoration-[#c4a45d] underline-offset-4"
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
                        <td colspan="3" class="p-10 text-center text-[#62665b]">
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
                        bila tidak memiliki akun.</DialogDescription
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
