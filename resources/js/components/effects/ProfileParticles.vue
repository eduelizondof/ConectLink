<script setup lang="ts">
/**
 * ProfileParticles - Lightweight particle effect for profile pages
 * 
 * Optimized for mobile with reduced particle count and simplified animations
 */
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';

interface Props {
    style?: 'dots' | 'lines' | 'confetti' | 'snow';
    color?: string;
    primaryColor?: string;
    secondaryColor?: string;
    particleCount?: number;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    style: 'dots',
    color: '',
    primaryColor: '#3b82f6',
    secondaryColor: '#8b5cf6',
    particleCount: 30,
    disabled: false,
});

const canvasRef = ref<HTMLCanvasElement | null>(null);
let ctx: CanvasRenderingContext2D | null = null;
let animationFrameId: number;
let particles: Particle[] = [];

interface Particle {
    x: number;
    y: number;
    vx: number;
    vy: number;
    size: number;
    color: string;
    alpha: number;
    rotation?: number;
    rotationSpeed?: number;
}

const colors = computed(() => {
    if (props.color) {
        return [props.color];
    }
    return [props.primaryColor, props.secondaryColor];
});

function createParticle(width: number, height: number): Particle {
    const color = colors.value[Math.floor(Math.random() * colors.value.length)];
    
    const baseParticle = {
        x: Math.random() * width,
        y: Math.random() * height,
        color,
        alpha: Math.random() * 0.5 + 0.2,
    };

    switch (props.style) {
        case 'snow':
            return {
                ...baseParticle,
                vx: (Math.random() - 0.5) * 0.3,
                vy: Math.random() * 0.5 + 0.3,
                size: Math.random() * 3 + 1,
            };
        case 'confetti':
            return {
                ...baseParticle,
                vx: (Math.random() - 0.5) * 0.5,
                vy: Math.random() * 0.8 + 0.2,
                size: Math.random() * 4 + 2,
                rotation: Math.random() * 360,
                rotationSpeed: (Math.random() - 0.5) * 5,
            };
        case 'lines':
            return {
                ...baseParticle,
                vx: (Math.random() - 0.5) * 0.2,
                vy: (Math.random() - 0.5) * 0.2,
                size: Math.random() * 2 + 0.5,
            };
        default: // dots
            return {
                ...baseParticle,
                vx: (Math.random() - 0.5) * 0.15,
                vy: (Math.random() - 0.5) * 0.15,
                size: Math.random() * 2 + 0.5,
            };
    }
}

function initParticles() {
    if (!canvasRef.value || props.disabled) return;
    
    const canvas = canvasRef.value;
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Reduce particles on mobile
    const count = window.innerWidth < 768 
        ? Math.min(props.particleCount, 20)
        : props.particleCount;
    
    particles = [];
    for (let i = 0; i < count; i++) {
        particles.push(createParticle(canvas.width, canvas.height));
    }
}

function drawParticle(particle: Particle) {
    if (!ctx) return;
    
    ctx.save();
    ctx.globalAlpha = particle.alpha;
    ctx.fillStyle = particle.color;
    
    if (props.style === 'confetti' && particle.rotation !== undefined) {
        ctx.translate(particle.x, particle.y);
        ctx.rotate((particle.rotation * Math.PI) / 180);
        ctx.fillRect(-particle.size / 2, -particle.size / 4, particle.size, particle.size / 2);
    } else {
        ctx.beginPath();
        ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
        ctx.fill();
    }
    
    ctx.restore();
}

function drawLines() {
    if (!ctx || props.style !== 'lines') return;
    
    const maxDistance = 100;
    
    for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
            const dx = particles[i].x - particles[j].x;
            const dy = particles[i].y - particles[j].y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance < maxDistance) {
                const opacity = (1 - distance / maxDistance) * 0.2;
                ctx.beginPath();
                ctx.moveTo(particles[i].x, particles[i].y);
                ctx.lineTo(particles[j].x, particles[j].y);
                ctx.strokeStyle = `${particles[i].color}`;
                ctx.globalAlpha = opacity;
                ctx.lineWidth = 0.5;
                ctx.stroke();
            }
        }
    }
    ctx.globalAlpha = 1;
}

function animate() {
    if (!ctx || !canvasRef.value || props.disabled) return;
    
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
    
    if (props.style === 'lines') {
        drawLines();
    }
    
    particles.forEach(particle => {
        // Update position
        particle.x += particle.vx;
        particle.y += particle.vy;
        
        // Update rotation for confetti
        if (particle.rotation !== undefined && particle.rotationSpeed !== undefined) {
            particle.rotation += particle.rotationSpeed;
        }
        
        // Wrap around screen
        if (particle.x < 0) particle.x = canvasRef.value!.width;
        if (particle.x > canvasRef.value!.width) particle.x = 0;
        
        // For falling particles (snow, confetti)
        if (props.style === 'snow' || props.style === 'confetti') {
            if (particle.y > canvasRef.value!.height) {
                particle.y = -10;
                particle.x = Math.random() * canvasRef.value!.width;
            }
        } else {
            if (particle.y < 0) particle.y = canvasRef.value!.height;
            if (particle.y > canvasRef.value!.height) particle.y = 0;
        }
        
        drawParticle(particle);
    });
    
    animationFrameId = requestAnimationFrame(animate);
}

function handleResize() {
    initParticles();
}

onMounted(() => {
    if (!props.disabled) {
        initParticles();
        animate();
        window.addEventListener('resize', handleResize);
    }
});

onUnmounted(() => {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
    window.removeEventListener('resize', handleResize);
});

watch(() => props.disabled, (disabled) => {
    if (disabled) {
        if (animationFrameId) {
            cancelAnimationFrame(animationFrameId);
        }
    } else {
        initParticles();
        animate();
    }
});
</script>

<template>
    <canvas 
        v-if="!disabled"
        ref="canvasRef" 
        class="pointer-events-none fixed inset-0 z-0"
    />
</template>
