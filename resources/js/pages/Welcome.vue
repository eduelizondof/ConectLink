<script setup lang="ts">
import { dashboard, login } from '@/routes';
import Globe3D from '@/components/landing/Globe3D.vue';
import ParticlesBackground from '@/components/landing/ParticlesBackground.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { 
    Zap, 
    Palette, 
    Share2, 
    Smartphone, 
    ShoppingBag, 
    RefreshCw, 
    Link2, 
    User, 
    Store, 
    Briefcase, 
    Users, 
    Camera,
    ChevronRight,
    Sparkles,
    Globe,
    Menu,
    X,
    Home,
    Info,
    Mail
} from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const isMenuOpen = ref(false);
const isLoaded = ref(false);
const activeSection = ref('hero');

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 100);
});

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const scrollToSection = (sectionId: string) => {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
        activeSection.value = sectionId;
        isMenuOpen.value = false;
    }
};

const benefits = [
    { icon: Zap, text: 'Presencia digital profesional en segundos' },
    { icon: Palette, text: 'Totalmente personalizable a tu estilo y marca' },
    { icon: Share2, text: 'Comparte por WhatsApp, redes sociales o QR' },
    { icon: Smartphone, text: 'Compatible con cualquier celular' },
    { icon: ShoppingBag, text: 'Ideal para catálogos, servicios o portafolios' },
    { icon: RefreshCw, text: 'Actualiza en tiempo real sin reimprimir' },
    { icon: Link2, text: 'Perfecta como link-in-bio o mini-sitio personal' },
];

const steps = [
    { number: '01', title: 'Crea tu perfil', description: 'Regístrate y crea tu página en minutos' },
    { number: '02', title: 'Personaliza', description: 'Ajusta colores, foto, enlaces y secciones' },
    { number: '03', title: 'Comparte', description: 'Usa tu link único o código QR' },
    { number: '04', title: 'Conecta', description: 'Da una experiencia moderna y actualizada' },
];

const useCases = [
    { icon: User, title: 'Profesionales', description: 'Tarjeta moderna para networking' },
    { icon: Store, title: 'Tiendas', description: 'Comparte catálogo y precios fácilmente' },
    { icon: Briefcase, title: 'Freelancers', description: 'Link ordenado y profesional' },
    { icon: Users, title: 'Equipos de ventas', description: 'Información rápida desde el celular' },
    { icon: Camera, title: 'Creativos', description: 'Muestra portafolio y redes sociales' },
];

const navLinks = [
    { id: 'hero', label: 'Inicio', icon: Home },
    { id: 'about', label: '¿Qué es?', icon: Info },
    { id: 'how', label: 'Cómo funciona', icon: Sparkles },
    { id: 'contact', label: 'Contacto', icon: Mail },
];
</script>

<template>
    <Head title="Tu tarjeta digital inteligente">
        <meta name="description" content="ConectLink - La forma más fácil, moderna y profesional de compartir toda tu información, enlaces y catálogos desde un solo lugar." />
    </Head>

    <!-- Background -->
    <div class="fixed inset-0 animated-gradient" />
    <ParticlesBackground />

    <!-- Main Container -->
    <div class="relative min-h-screen text-white">
        
        <!-- Desktop Navbar (Glass Effect) -->
        <header 
            class="fixed left-1/2 top-4 z-50 hidden w-full max-w-4xl -translate-x-1/2 px-4 lg:block"
            :class="{ 'opacity-0 -translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
            style="transition: all 0.6s ease-out;"
        >
            <nav class="glass flex items-center justify-between rounded-2xl px-6 py-3">
                <!-- Logo -->
                <button @click="scrollToSection('hero')" class="flex items-center gap-2 group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-600 shadow-lg group-hover:scale-105 transition-transform">
                        <Globe class="h-5 w-5 text-white" />
                    </div>
                    <span class="font-display text-xl font-bold">
                        <span class="gradient-text-connect">Conect</span><span class="text-white">Link</span>
                    </span>
                </button>

                <!-- Nav Links -->
                <div class="flex items-center gap-6">
                    <button 
                        v-for="link in navLinks" 
                        :key="link.id"
                        @click="scrollToSection(link.id)"
                        class="text-sm font-medium text-white/70 transition-colors hover:text-cyan-400"
                        :class="{ 'text-cyan-400': activeSection === link.id }"
                    >
                        {{ link.label }}
                    </button>
                </div>

                <!-- Auth Buttons -->
                <div class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="btn-glow rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="px-4 py-2 text-sm font-medium text-white/80 transition-colors hover:text-white"
                        >
                            Iniciar sesión
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="login()"
                            class="btn-glow rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg"
                        >
                            Crear cuenta
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <!-- Mobile Logo Header -->
        <header 
            class="fixed left-0 right-0 top-0 z-50 flex items-center justify-center px-4 py-4 lg:hidden safe-top"
            :class="{ 'opacity-0 -translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
            style="transition: all 0.6s ease-out;"
        >
            <button @click="scrollToSection('hero')" class="flex items-center gap-2 group">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-600 shadow-lg group-hover:scale-105 transition-transform glow-cyan-sm">
                    <Globe class="h-5 w-5 text-white" />
                </div>
                <span class="font-display text-xl font-bold">
                    <span class="gradient-text-connect">Conect</span><span class="text-white">Link</span>
                </span>
            </button>
        </header>

        <!-- Mobile Footer Menu (Glass Effect) -->
        <nav 
            class="fixed bottom-0 left-0 right-0 z-50 lg:hidden safe-bottom"
            :class="{ 'opacity-0 translate-y-4': !isLoaded, 'opacity-100 translate-y-0': isLoaded }"
            style="transition: all 0.6s ease-out 0.3s;"
        >
            <div class="glass mx-4 mb-4 flex items-center justify-around rounded-2xl px-2 py-3">
                <button
                    v-for="link in navLinks"
                    :key="link.id"
                    @click="scrollToSection(link.id)"
                    class="flex flex-col items-center gap-1 px-4 py-1 transition-all"
                    :class="activeSection === link.id ? 'text-cyan-400 scale-110' : 'text-white/60'"
                >
                    <component :is="link.icon" class="h-5 w-5" />
                    <span class="text-[10px] font-medium">{{ link.label }}</span>
                </button>
                
                <!-- Auth Button for Mobile -->
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="flex flex-col items-center gap-1 px-4 py-1 text-cyan-400"
                >
                    <User class="h-5 w-5" />
                    <span class="text-[10px] font-medium">Mi cuenta</span>
                </Link>
                <Link
                    v-else
                    :href="login()"
                    class="flex flex-col items-center gap-1 px-4 py-1 text-white/60 transition-colors hover:text-cyan-400"
                >
                    <User class="h-5 w-5" />
                    <span class="text-[10px] font-medium">Entrar</span>
                </Link>
            </div>
        </nav>

        <!-- Content -->
        <main class="relative z-10">
            
            <!-- Hero Section -->
            <section id="hero" class="flex min-h-screen flex-col items-center justify-center px-4 pb-24 pt-20 lg:pt-28 overflow-visible">
                <div class="mx-auto max-w-6xl text-center overflow-visible">
                    
                    <!-- Badge -->
                    <div 
                        class="mb-6 inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-2"
                        :class="{ 'fade-in-up': isLoaded }"
                    >
                        <Sparkles class="h-4 w-4 text-cyan-400" />
                        <span class="text-sm font-medium text-cyan-300">Potenciado por Conectiva ITS</span>
                    </div>

                    <!-- Main Title -->
                    <h1 
                        class="mb-6 font-display text-4xl font-bold leading-tight sm:text-5xl lg:text-7xl"
                        :class="{ 'fade-in-up fade-in-up-delay-1': isLoaded }"
                    >
                        <span class="gradient-text-connect">Conect</span><span class="text-white">Link</span>
                        <br />
                        <span class="text-glow-white">Tu tarjeta digital</span>
                        <br />
                        <span class="gradient-text">inteligente</span>
                    </h1>

                    <!-- Subtitle -->
                    <p 
                        class="mx-auto mb-8 max-w-2xl text-lg text-white/70 sm:text-xl"
                        :class="{ 'fade-in-up fade-in-up-delay-2': isLoaded }"
                    >
                        La forma más fácil, moderna y profesional de compartir toda tu información, 
                        enlaces y catálogos desde <span class="font-semibold text-cyan-400">un solo lugar</span>.
                    </p>

                    <!-- CTA Buttons -->
                    <div 
                        class="mb-12 flex flex-col items-center justify-center gap-4 sm:flex-row"
                        :class="{ 'fade-in-up fade-in-up-delay-3': isLoaded }"
                    >
                        <Link
                            v-if="!$page.props.auth.user && canRegister"
                            :href="login()"
                            class="btn-glow group flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg"
                        >
                            <Sparkles class="h-5 w-5" />
                            Empieza gratis
                            <ChevronRight class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                        </Link>
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="btn-glow group flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg"
                        >
                            Ir al Dashboard
                            <ChevronRight class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                        </Link>
                        <button
                            @click="scrollToSection('about')"
                            class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-8 py-4 text-lg font-medium text-white backdrop-blur-sm transition-all hover:border-cyan-500/50 hover:bg-white/10"
                        >
                            Conocer más
                        </button>
                    </div>

                    <!-- Globe 3D -->
                    <div 
                        class="relative mx-auto overflow-visible"
                        :class="{ 'scale-in': isLoaded }"
                        style="animation-delay: 0.5s; min-height: 360px; padding: 20px 0;"
                    >
                        <Globe3D :size="300" class="mx-auto" />
                        
                        <!-- Text under globe -->
                        <div class="mt-4 flex items-center justify-center gap-2">
                            <div class="h-px w-12 bg-gradient-to-r from-transparent to-cyan-500/50" />
                            <p class="text-center text-sm font-medium text-cyan-400/80">
                                <span class="gradient-text-connect font-bold">Conecta</span> con el mundo
                            </p>
                            <div class="h-px w-12 bg-gradient-to-l from-transparent to-cyan-500/50" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- About Section -->
            <section id="about" class="px-4 py-20">
                <div class="mx-auto max-w-6xl">
                    <!-- Section Header -->
                    <div class="mb-16 text-center">
                        <span class="mb-4 inline-block rounded-full border border-cyan-500/30 bg-cyan-500/10 px-4 py-1 text-sm font-medium text-cyan-400">
                            ¿Qué es ConectLink?
                        </span>
                        <h2 class="mb-6 font-display text-3xl font-bold text-white sm:text-4xl lg:text-5xl">
                            Tu presencia digital
                            <span class="gradient-text"> profesional</span>
                        </h2>
                        <p class="mx-auto max-w-3xl text-lg text-white/70">
                            ConectLink es una tarjeta de presentación digital diseñada por 
                            <span class="font-semibold text-cyan-400">Conectiva ITS</span>, creada para que profesionales, 
                            emprendedores y negocios compartan toda su información con un solo clic. 
                            Personaliza tu página, organiza tus enlaces, muestra tu catálogo, comparte tu contacto 
                            y haz que tus clientes te encuentren rápido, desde cualquier dispositivo.
                        </p>
                    </div>

                    <!-- Benefits Grid -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div 
                            v-for="(benefit, index) in benefits" 
                            :key="index"
                            class="card-hover glass group flex items-start gap-4 rounded-2xl p-5"
                        >
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500/20 to-cyan-600/20 transition-all group-hover:from-cyan-500/30 group-hover:to-cyan-600/30">
                                <component :is="benefit.icon" class="h-6 w-6 text-cyan-400" />
                            </div>
                            <p class="text-sm font-medium text-white/90">{{ benefit.text }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How it Works Section -->
            <section id="how" class="px-4 py-20">
                <div class="mx-auto max-w-6xl">
                    <!-- Section Header -->
                    <div class="mb-16 text-center">
                        <span class="mb-4 inline-block rounded-full border border-purple-500/30 bg-purple-500/10 px-4 py-1 text-sm font-medium text-purple-400">
                            Cómo funciona
                        </span>
                        <h2 class="mb-6 font-display text-3xl font-bold text-white sm:text-4xl lg:text-5xl">
                            Cuatro pasos para
                            <span class="gradient-text"> conectar</span>
                        </h2>
                    </div>

                    <!-- Steps -->
                    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        <div 
                            v-for="(step, index) in steps" 
                            :key="index"
                            class="card-hover group relative rounded-2xl bg-gradient-to-br from-white/5 to-white/[0.02] p-6 text-center"
                        >
                            <!-- Step Number -->
                            <div class="mb-4 font-display text-5xl font-bold text-cyan-500/20 transition-colors group-hover:text-cyan-500/40">
                                {{ step.number }}
                            </div>
                            <h3 class="mb-2 font-display text-xl font-bold text-white">{{ step.title }}</h3>
                            <p class="text-sm text-white/60">{{ step.description }}</p>
                            
                            <!-- Connector Line (hidden on last item and mobile) -->
                            <div 
                                v-if="index < steps.length - 1" 
                                class="absolute right-0 top-1/2 hidden h-0.5 w-8 -translate-y-1/2 translate-x-full bg-gradient-to-r from-cyan-500/50 to-transparent lg:block"
                            />
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="mt-12 text-center">
                        <Link
                            v-if="!$page.props.auth.user && canRegister"
                            :href="login()"
                            class="btn-glow inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg"
                        >
                            <Sparkles class="h-5 w-5" />
                            Crea tu página ahora
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Use Cases Section -->
            <section class="px-4 py-20">
                <div class="mx-auto max-w-6xl">
                    <!-- Section Header -->
                    <div class="mb-16 text-center">
                        <span class="mb-4 inline-block rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1 text-sm font-medium text-emerald-400">
                            Ejemplos de uso
                        </span>
                        <h2 class="mb-6 font-display text-3xl font-bold text-white sm:text-4xl lg:text-5xl">
                            Perfecto para
                            <span class="gradient-text-connect"> cualquiera</span>
                        </h2>
                    </div>

                    <!-- Use Cases Grid -->
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                        <div 
                            v-for="(useCase, index) in useCases" 
                            :key="index"
                            class="card-hover glass group flex flex-col items-center rounded-2xl p-6 text-center"
                        >
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500/20 to-purple-500/20 transition-all group-hover:from-cyan-500/30 group-hover:to-purple-500/30 group-hover:scale-110">
                                <component :is="useCase.icon" class="h-8 w-8 text-cyan-400" />
                            </div>
                            <h3 class="mb-2 font-display text-lg font-bold text-white">{{ useCase.title }}</h3>
                            <p class="text-sm text-white/60">{{ useCase.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final CTA Section -->
            <section id="contact" class="px-4 py-20">
                <div class="mx-auto max-w-4xl">
                    <div class="glass glow-cyan relative overflow-hidden rounded-3xl p-8 text-center sm:p-12">
                        <!-- Background decoration -->
                        <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-cyan-500/20 blur-3xl" />
                        <div class="absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-purple-500/20 blur-3xl" />
                        
                        <div class="relative">
                            <h2 class="mb-4 font-display text-3xl font-bold text-white sm:text-4xl lg:text-5xl">
                                <span class="gradient-text-connect">Conecta</span> mejor,
                                <br />comparte más fácil
                            </h2>
                            <p class="mx-auto mb-8 max-w-xl text-lg text-white/70">
                                ConectLink te ayuda a destacar. Haz que tus clientes te encuentren rápido 
                                y ofrece una experiencia moderna. Todo desde un solo enlace.
                            </p>
                            
                            <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                                <Link
                                    v-if="!$page.props.auth.user && canRegister"
                                    :href="login()"
                                    class="btn-glow group flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg"
                                >
                                    <Sparkles class="h-5 w-5" />
                                    Empieza gratis en segundos
                                    <ChevronRight class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                                </Link>
                                <Link
                                    v-if="$page.props.auth.user"
                                    :href="dashboard()"
                                    class="btn-glow group flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg"
                                >
                                    Ir a mi Dashboard
                                    <ChevronRight class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="border-t border-white/10 px-4 py-8 pb-32 lg:pb-8">
                <div class="mx-auto max-w-6xl">
                    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <!-- Logo -->
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-400 to-cyan-600">
                                <Globe class="h-4 w-4 text-white" />
                            </div>
                            <span class="font-display text-lg font-bold">
                                <span class="gradient-text-connect">Conect</span><span class="text-white">Link</span>
                            </span>
                        </div>

                        <!-- Copyright -->
                        <p class="text-center text-sm text-white/50">
                            © {{ new Date().getFullYear() }} ConectLink. Un producto de
                            <a href="https://conectiva-its.com" target="_blank" class="font-medium text-cyan-400 hover:underline">
                                Conectiva ITS
                            </a>
                        </p>

                        <!-- Social / Links -->
                        <div class="flex items-center gap-4">
                            <a href="#" class="text-white/50 transition-colors hover:text-cyan-400">
                                Privacidad
                            </a>
                            <a href="#" class="text-white/50 transition-colors hover:text-cyan-400">
                                Términos
                            </a>
                        </div>
                    </div>
                </div>
            </footer>

        </main>
    </div>
</template>

<style scoped>
/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Custom font-display class */
.font-display {
    font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
}
</style>
