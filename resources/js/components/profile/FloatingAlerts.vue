<script setup lang="ts">
import { computed } from 'vue';
import FloatingAlertItem from './FloatingAlertItem.vue';
import type { FloatingAlert } from '@/types/profile';

const props = defineProps<{
    alerts: FloatingAlert[];
    visibleAlerts: number[];
    dismissedAlerts: number[];
}>();

const emit = defineEmits<{
    dismiss: [alertId: number];
}>();

const activeAlerts = computed(() => {
    return props.alerts.filter(
        (alert) => props.visibleAlerts.includes(alert.id) && !props.dismissedAlerts.includes(alert.id)
    );
});

function handleDismiss(alertId: number) {
    emit('dismiss', alertId);
}
</script>

<template>
    <Teleport to="body">
        <FloatingAlertItem
            v-for="alert in activeAlerts"
            :key="alert.id"
            :alert="alert"
            @dismiss="handleDismiss"
        />
    </Teleport>
</template>

