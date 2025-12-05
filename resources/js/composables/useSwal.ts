import Swal from 'sweetalert2';

export function useSwal() {
    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        },
    });

    const success = (message: string) => {
        toast.fire({
            icon: 'success',
            title: message,
        });
    };

    const error = (message: string) => {
        toast.fire({
            icon: 'error',
            title: message,
        });
    };

    const warning = (message: string) => {
        toast.fire({
            icon: 'warning',
            title: message,
        });
    };

    const info = (message: string) => {
        toast.fire({
            icon: 'info',
            title: message,
        });
    };

    const confirm = async (options: {
        title?: string;
        text?: string;
        confirmText?: string;
        cancelText?: string;
        icon?: 'warning' | 'error' | 'success' | 'info' | 'question';
    }) => {
        const result = await Swal.fire({
            title: options.title || '¿Estás seguro?',
            text: options.text || 'Esta acción no se puede deshacer.',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: '#06b6d4',
            cancelButtonColor: '#6b7280',
            confirmButtonText: options.confirmText || 'Sí, continuar',
            cancelButtonText: options.cancelText || 'Cancelar',
        });

        return result.isConfirmed;
    };

    const confirmDelete = async (itemName?: string) => {
        return confirm({
            title: '¿Eliminar?',
            text: itemName ? `"${itemName}" será eliminado permanentemente.` : 'Este elemento será eliminado permanentemente.',
            icon: 'warning',
            confirmText: 'Sí, eliminar',
        });
    };

    return {
        toast,
        success,
        error,
        warning,
        info,
        confirm,
        confirmDelete,
    };
}


