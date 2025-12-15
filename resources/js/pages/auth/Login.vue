<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/login';
import { Form, Head } from '@inertiajs/vue3';
import { Zap, Palette, Share2, RefreshCw, Sparkles } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Inicia sesión en tu cuenta"
        description="Ingresa tu correo electrónico y contraseña para iniciar sesión"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Correo electrónico</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Contraseña</Label>
                        <a
                            v-if="canResetPassword"
                            href="https://wa.me/5213322428574"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-sm text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            :tabindex="5"
                        >
                            Olvidaste tu contraseña?
                        </a>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Contraseña"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Recuérdame</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Iniciar sesión
                </Button>
            </div>

            <!-- Benefits Section -->
            <div class="mt-8 space-y-4">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <div class="h-px flex-1 bg-gradient-to-r from-transparent via-border to-transparent"></div>
                    <Sparkles class="h-4 w-4 text-muted-foreground" />
                    <span class="text-xs font-medium text-muted-foreground">Descubre lo que puedes lograr</span>
                    <Sparkles class="h-4 w-4 text-muted-foreground" />
                    <div class="h-px flex-1 bg-gradient-to-r from-transparent via-border to-transparent"></div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div class="group flex items-start gap-2.5 rounded-lg border border-border/50 bg-card/50 p-3 transition-all hover:border-cyan-500/30 hover:bg-card/80 hover:shadow-sm">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/10 to-cyan-600/10 transition-colors group-hover:from-cyan-500/20 group-hover:to-cyan-600/20">
                            <Zap class="h-4 w-4 text-cyan-600 dark:text-cyan-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-foreground">Control total</p>
                            <p class="mt-0.5 text-[10px] leading-tight text-muted-foreground">Sin limitaciones ni restricciones</p>
                        </div>
                    </div>
                    
                    <div class="group flex items-start gap-2.5 rounded-lg border border-border/50 bg-card/50 p-3 transition-all hover:border-purple-500/30 hover:bg-card/80 hover:shadow-sm">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500/10 to-purple-600/10 transition-colors group-hover:from-purple-500/20 group-hover:to-purple-600/20">
                            <Palette class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-foreground">Catálogo personalizable</p>
                            <p class="mt-0.5 text-[10px] leading-tight text-muted-foreground">Cámbialo cuando quieras</p>
                        </div>
                    </div>
                    
                    <div class="group flex items-start gap-2.5 rounded-lg border border-border/50 bg-card/50 p-3 transition-all hover:border-emerald-500/30 hover:bg-card/80 hover:shadow-sm">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 transition-colors group-hover:from-emerald-500/20 group-hover:to-emerald-600/20">
                            <Share2 class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-foreground">Comparte fácil</p>
                            <p class="mt-0.5 text-[10px] leading-tight text-muted-foreground">WhatsApp, QR o link único</p>
                        </div>
                    </div>
                    
                    <div class="group flex items-start gap-2.5 rounded-lg border border-border/50 bg-card/50 p-3 transition-all hover:border-blue-500/30 hover:bg-card/80 hover:shadow-sm">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500/10 to-blue-600/10 transition-colors group-hover:from-blue-500/20 group-hover:to-blue-600/20">
                            <RefreshCw class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-foreground">Actualiza en tiempo real</p>
                            <p class="mt-0.5 text-[10px] leading-tight text-muted-foreground">Productos y precios al instante</p>
                        </div>
                    </div>
                </div>
            </div>
        </Form>
    </AuthBase>
</template>
