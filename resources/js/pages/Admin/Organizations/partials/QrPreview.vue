<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import QRCodeStyling from 'qr-code-styling';

interface QrSettings {
    foreground_color: string;
    background_color: string;
    dot_style: string;
    corner_style: string;
    corner_color: string | null;
    show_logo: boolean;
    logo_source?: string;
    logo_size: number;
    logo_shape: string;
    logo_background_color: string;
    logo_background_enabled: boolean;
    logo_margin: number;
    error_correction: string;
    size: number;
    use_gradient: boolean;
    gradient_start_color: string | null;
    gradient_end_color: string | null;
    gradient_type: string;
    gradient_rotation: number;
}

const props = defineProps<{
    url: string;
    settings: QrSettings;
    logoUrl?: string | null;
}>();

const qrContainer = ref<HTMLDivElement | null>(null);
const qrCode = ref<QRCodeStyling | null>(null);

// Map dot styles to qr-code-styling options
const dotStyleMap: Record<string, string> = {
    'square': 'square',
    'rounded': 'rounded',
    'dots': 'dots',
    'classy': 'classy',
    'classy-rounded': 'classy-rounded',
    'extra-rounded': 'extra-rounded',
};

// Map corner styles
const cornerStyleMap: Record<string, string> = {
    'square': 'square',
    'rounded': 'dot',
    'extra-rounded': 'extra-rounded',
};

// Map error correction
const errorCorrectionMap: Record<string, 'L' | 'M' | 'Q' | 'H'> = {
    'L': 'L',
    'M': 'M',
    'Q': 'Q',
    'H': 'H',
};

// Generate QR configuration
const qrConfig = computed(() => {
    const config: any = {
        width: props.settings.size || 300,
        height: props.settings.size || 300,
        data: props.url,
        dotsOptions: {
            type: dotStyleMap[props.settings.dot_style] || 'square',
        },
        cornersSquareOptions: {
            type: cornerStyleMap[props.settings.corner_style] || 'square',
        },
        cornersDotOptions: {
            type: cornerStyleMap[props.settings.corner_style] || 'square',
        },
        backgroundOptions: {
            color: props.settings.background_color || '#FFFFFF',
        },
        qrOptions: {
            errorCorrectionLevel: errorCorrectionMap[props.settings.error_correction] || 'H',
        },
    };

    // Apply gradient or solid color
    if (props.settings.use_gradient && props.settings.gradient_start_color && props.settings.gradient_end_color) {
        config.dotsOptions.gradient = {
            type: props.settings.gradient_type || 'linear',
            rotation: (props.settings.gradient_rotation || 0) * (Math.PI / 180),
            colorStops: [
                { offset: 0, color: props.settings.gradient_start_color },
                { offset: 1, color: props.settings.gradient_end_color },
            ],
        };
    } else {
        config.dotsOptions.color = props.settings.foreground_color || '#000000';
    }

    // Apply corner color
    if (props.settings.corner_color) {
        config.cornersSquareOptions.color = props.settings.corner_color;
        config.cornersDotOptions.color = props.settings.corner_color;
    } else if (props.settings.use_gradient && props.settings.gradient_start_color) {
        config.cornersSquareOptions.color = props.settings.gradient_start_color;
        config.cornersDotOptions.color = props.settings.gradient_start_color;
    } else {
        config.cornersSquareOptions.color = props.settings.foreground_color || '#000000';
        config.cornersDotOptions.color = props.settings.foreground_color || '#000000';
    }

    // Add logo if enabled
    if (props.settings.show_logo && props.logoUrl) {
        config.image = props.logoUrl;
        config.imageOptions = {
            crossOrigin: props.logoUrl.startsWith('blob:') ? undefined : 'anonymous',
            margin: props.settings.logo_margin || 5,
            imageSize: (props.settings.logo_size || 20) / 100,
            hideBackgroundDots: true,
        };
    }

    return config;
});

// Initialize QR code
function initQr() {
    if (!qrContainer.value) return;

    // Clear previous QR
    qrContainer.value.innerHTML = '';

    qrCode.value = new QRCodeStyling(qrConfig.value);
    qrCode.value.append(qrContainer.value);
}

// Update QR code
function updateQr() {
    if (!qrCode.value) {
        initQr();
        return;
    }

    qrCode.value.update(qrConfig.value);
}

// Download QR
function download(format: 'png' | 'svg' = 'png') {
    if (!qrCode.value) return;

    const filename = `qr-code.${format}`;
    qrCode.value.download({
        name: filename.replace(`.${format}`, ''),
        extension: format,
    });
}

// Expose download method
defineExpose({ download });

// Watch for changes
watch(
    () => [props.url, props.settings, props.logoUrl],
    () => {
        updateQr();
    },
    { deep: true }
);

onMounted(() => {
    initQr();
});
</script>

<template>
    <div class="qr-preview">
        <div
            ref="qrContainer"
            class="qr-container flex items-center justify-center rounded-xl p-4"
            :style="{
                backgroundColor: settings.background_color,
                borderRadius: settings.logo_shape === 'circle' ? '16px' : '8px',
            }"
        />

        <!-- Logo shape overlay for styling -->
        <div
            v-if="settings.show_logo && logoUrl && settings.logo_background_enabled"
            class="logo-background-indicator"
        />
    </div>
</template>

<style scoped>
.qr-preview {
    position: relative;
    display: inline-block;
}

.qr-container {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.qr-container :deep(canvas) {
    display: block;
}
</style>
