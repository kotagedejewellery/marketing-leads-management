import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

export function initializeFlashToast(): void {
    router.on('flash', (event) => {
        const flash = (event as CustomEvent).detail?.flash;
        const data = flash?.toast as FlashToast | undefined;

        if (!data) {
            return;
        }

        toast[data.type](data.message);
    });

    router.on('error', (event) => {
        if (Object.keys(event.detail.errors).length) {
            toast.error('Periksa kembali isian yang ditandai.');
        }
    });

    router.on('httpException', (event) => {
        event.preventDefault();
        toast.error('Terjadi masalah pada server. Silakan coba lagi.');
    });

    router.on('networkError', (event) => {
        event.preventDefault();
        toast.error('Koneksi terputus. Periksa jaringan lalu coba lagi.');
    });
}
