<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faWhatsapp } from '@fortawesome/free-brands-svg-icons';
import { faLink } from '@fortawesome/free-solid-svg-icons';
import type { Profile } from '@/types/profile';

const props = defineProps<{
    modelValue: boolean;
    profile: Profile;
    currentUrl: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    'copy-url': [];
}>();

function closeMenu() {
    emit('update:modelValue', false);
}

function handleCopyUrl() {
    emit('copy-url');
    closeMenu();
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="modelValue"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
            @click.self="closeMenu"
        >
            <div class="absolute inset-0 bg-black/60" @click="closeMenu" />
            <div class="relative bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-sm p-6 animate-slide-up">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Compartir perfil</h3>
                <div class="space-y-3">
                    <button
                        @click="handleCopyUrl"
                        class="flex items-center gap-3 w-full p-3 rounded-xl bg-gray-100 hover:bg-gray-200 transition-colors"
                    >
                        <font-awesome-icon :icon="faLink" class="w-5 h-5 text-gray-600" />
                        <span class="font-medium text-gray-700">Copiar enlace</span>
                    </button>
                    <a
                        :href="`https://wa.me/?text=${encodeURIComponent(`Mira el perfil de ${profile.name}: ${currentUrl}`)}`"
                        target="_blank"
                        class="flex items-center gap-3 w-full p-3 rounded-xl bg-green-50 hover:bg-green-100 transition-colors"
                    >
                        <font-awesome-icon :icon="faWhatsapp" class="w-5 h-5 text-green-600" />
                        <span class="font-medium text-green-700">Compartir en WhatsApp</span>
                    </a>
                </div>
                <button
                    @click="closeMenu"
                    class="w-full mt-4 py-3 text-gray-500 font-medium"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
@keyframes slide-up {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>

