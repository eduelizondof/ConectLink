<script setup lang="ts">
import { Globe } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-gradient-to-br from-cyan-400 to-cyan-600 text-white shadow-lg"
    >
        <Globe class="size-5" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            <span class="text-cyan-500">Conect</span><span class="text-foreground">Link</span>
        </span>
    </div>
</template>
