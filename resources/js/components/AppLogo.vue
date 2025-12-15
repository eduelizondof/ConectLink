<script setup lang="ts">
import { Globe } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-gradient-to-br from-cyan-400 to-cyan-600 text-white shadow-lg"
    >
        <img src="/icono-bco.png" alt="Logo" class="h-8 w-8" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            <span class="text-cyan-500">Link</span><span class="text-foreground text-sm"> by CNVA</span>
        </span>
    </div>
</template>
