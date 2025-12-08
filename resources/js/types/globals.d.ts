import { AppPageProps } from '@/types/index';

// QR Code Styling module declaration
declare module 'qr-code-styling' {
    interface QRCodeStylingOptions {
        width?: number;
        height?: number;
        data?: string;
        image?: string;
        dotsOptions?: {
            type?: string;
            color?: string;
            gradient?: {
                type?: string;
                rotation?: number;
                colorStops?: Array<{ offset: number; color: string }>;
            };
        };
        cornersSquareOptions?: {
            type?: string;
            color?: string;
        };
        cornersDotOptions?: {
            type?: string;
            color?: string;
        };
        backgroundOptions?: {
            color?: string;
        };
        imageOptions?: {
            crossOrigin?: string;
            margin?: number;
            imageSize?: number;
            hideBackgroundDots?: boolean;
        };
        qrOptions?: {
            errorCorrectionLevel?: 'L' | 'M' | 'Q' | 'H';
        };
    }

    export default class QRCodeStyling {
        constructor(options: QRCodeStylingOptions);
        append(container: HTMLElement): void;
        update(options: Partial<QRCodeStylingOptions>): void;
        download(options?: { name?: string; extension?: string }): void;
        getRawData(extension?: string): Promise<Blob | null>;
    }
}

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
