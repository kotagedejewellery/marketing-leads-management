<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    ClipboardList,
    LayoutDashboard,
    LogOut,
    Network,
    UsersRound,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Toaster } from '@/components/ui/sonner';

const page = usePage();
const isLoggingOut = ref(false);
const isLogoutDialogOpen = ref(false);
const user = computed(
    () =>
        page.props.auth.user as {
            name: string;
            role: string;
            branch?: { name: string } | null;
        },
);
const nav = [
    { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { href: '/leads', label: 'Leads', icon: ClipboardList },
    {
        href: '/statistics',
        label: user.value.role === 'admin' ? 'Statistik' : 'Statistik Saya',
        icon: BarChart3,
    },
];
if (user.value.role === 'admin')
    nav.push(
        { href: '/branches', label: 'Cabang', icon: Network },
        { href: '/accounts', label: 'Akun', icon: UsersRound },
    );

const logout = () =>
    router.post(
        '/logout',
        {},
        {
            onStart: () => {
                isLoggingOut.value = true;
                isLogoutDialogOpen.value = false;
            },
            onFinish: () => (isLoggingOut.value = false),
        },
    );
</script>

<template>
    <div class="min-h-screen bg-kgj-canvas text-kgj-ink">
        <header
            class="border-b border-black/10 bg-kgj-ink px-5 py-3 text-[#fffdf8] lg:px-10 xl:px-12 2xl:px-16"
        >
            <div class="flex w-full items-center justify-between gap-3">
                <Link href="/dashboard" class="group flex items-center gap-3"
                    ><img
                        src="/brand/kotagede-jewellery.png"
                        alt="Kotagede Jewellery"
                        class="h-12 w-[68px] rounded-lg object-contain transition group-hover:brightness-105 lg:h-14 lg:w-[72px]"
                    /><span
                        class="hidden text-sm font-semibold tracking-tight text-white/65 sm:block"
                        >Leads Workspace</span
                    ></Link
                >
                <nav
                    class="hidden gap-1 lg:flex lg:rounded-xl lg:border lg:border-white/10 lg:bg-white/5 lg:p-1"
                    aria-label="Navigasi utama"
                >
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="flex min-h-11 shrink-0 items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-white/60 transition hover:bg-white/10 hover:text-white"
                        :aria-current="
                            page.url.startsWith(item.href) ? 'page' : undefined
                        "
                        :class="{
                            'bg-[#fffdf8] !text-[#201f1c] shadow-[0_6px_14px_rgba(0,0,0,0.16)]':
                                page.url.startsWith(item.href),
                        }"
                        ><component :is="item.icon" class="size-3.5" />{{
                            item.label
                        }}</Link
                    >
                </nav>
                <div class="flex items-center gap-3 text-sm text-white/65">
                    <span
                        v-if="user.branch?.name"
                        class="rounded-full bg-[#dce7dd] px-2 py-1 text-xs font-bold text-[#36503a]"
                        >{{ user.branch.name }}</span
                    ><span class="hidden sm:inline"
                        >{{ user.name }} · {{ user.role.toUpperCase() }}</span
                    ><Dialog v-model:open="isLogoutDialogOpen">
                        <DialogTrigger as-child>
                            <button
                                class="inline-flex min-h-11 items-center gap-1 rounded-lg px-2 text-white/75 transition hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-60"
                                type="button"
                                :disabled="isLoggingOut"
                            >
                                <LogOut class="size-3.5" />{{
                                    isLoggingOut ? 'Keluar…' : 'Keluar'
                                }}
                            </button>
                        </DialogTrigger>
                        <DialogContent :show-close-button="!isLoggingOut">
                            <DialogHeader>
                                <DialogTitle
                                    >Keluar dari dashboard?</DialogTitle
                                >
                                <DialogDescription>
                                    Sesi Anda akan diakhiri pada perangkat ini.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <DialogClose as-child>
                                    <button
                                        class="kgj-button-secondary"
                                        type="button"
                                        :disabled="isLoggingOut"
                                    >
                                        Batalkan
                                    </button>
                                </DialogClose>
                                <button
                                    class="kgj-button-primary"
                                    type="button"
                                    :disabled="isLoggingOut"
                                    @click="logout"
                                >
                                    {{
                                        isLoggingOut
                                            ? 'Mengeluarkan…'
                                            : 'Ya, keluar'
                                    }}
                                </button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </header>
        <main
            class="w-full px-5 py-8 pb-[calc(6.5rem+env(safe-area-inset-bottom))] lg:px-10 lg:py-10 xl:px-12 2xl:px-16"
        >
            <slot />
        </main>
        <nav
            class="fixed inset-x-0 bottom-0 z-50 grid border-t border-white/10 bg-kgj-ink px-2 pt-2 shadow-[0_-10px_26px_rgba(32,31,28,0.16)] lg:hidden"
            :style="{
                gridTemplateColumns: `repeat(${nav.length}, minmax(0, 1fr))`,
                paddingBottom: 'max(0.5rem, env(safe-area-inset-bottom))',
            }"
            aria-label="Navigasi utama"
        >
            <Link
                v-for="item in nav"
                :key="item.href"
                :href="item.href"
                class="flex min-h-12 min-w-0 flex-col items-center justify-center gap-1 rounded-lg px-1 py-1 text-[10px] leading-3 font-semibold whitespace-nowrap text-white/65 transition active:scale-[0.97]"
                :aria-current="
                    page.url.startsWith(item.href) ? 'page' : undefined
                "
                :class="{
                    'bg-white/10 text-[#f3d38f]': page.url.startsWith(
                        item.href,
                    ),
                }"
            >
                <component :is="item.icon" class="size-4 shrink-0" />
                <span class="max-w-full truncate">{{ item.label }}</span>
            </Link>
        </nav>
        <Toaster position="top-right" rich-colors />
    </div>
</template>
