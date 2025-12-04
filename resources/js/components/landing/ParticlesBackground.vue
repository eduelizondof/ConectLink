<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';

const canvasRef = ref<HTMLCanvasElement | null>(null);
let ctx: CanvasRenderingContext2D | null = null;
let animationFrameId: number;
let particles: Particle[] = [];
let mouseX = 0;
let mouseY = 0;

interface Particle {
    x: number;
    y: number;
    vx: number;
    vy: number;
    radius: number;
    color: string;
    alpha: number;
    targetAlpha: number;
}

const colors = [
    'rgba(6, 182, 212,',   // cyan
    'rgba(34, 211, 238,',  // cyan-bright
    'rgba(168, 85, 247,',  // purple
    'rgba(16, 185, 129,',  // emerald
];

const createParticle = (width: number, height: number): Particle => {
    const color = colors[Math.floor(Math.random() * colors.length)];
    return {
        x: Math.random() * width,
        y: Math.random() * height,
        vx: (Math.random() - 0.5) * 0.3,
        vy: (Math.random() - 0.5) * 0.3,
        radius: Math.random() * 2 + 0.5,
        color: color,
        alpha: Math.random() * 0.5 + 0.1,
        targetAlpha: Math.random() * 0.5 + 0.2,
    };
};

const initParticles = () => {
    if (!canvasRef.value) return;
    
    const canvas = canvasRef.value;
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Create particles based on screen size (fewer on mobile)
    const particleCount = window.innerWidth < 768 ? 40 : 80;
    particles = [];
    
    for (let i = 0; i < particleCount; i++) {
        particles.push(createParticle(canvas.width, canvas.height));
    }
};

const drawParticle = (particle: Particle) => {
    if (!ctx) return;
    
    ctx.beginPath();
    ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
    ctx.fillStyle = `${particle.color} ${particle.alpha})`;
    ctx.fill();
    
    // Add glow effect
    ctx.shadowBlur = 10;
    ctx.shadowColor = `${particle.color} 0.5)`;
};

const drawConnections = () => {
    if (!ctx) return;
    
    const maxDistance = 150;
    
    for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
            const dx = particles[i].x - particles[j].x;
            const dy = particles[i].y - particles[j].y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance < maxDistance) {
                const opacity = (1 - distance / maxDistance) * 0.15;
                ctx.beginPath();
                ctx.moveTo(particles[i].x, particles[i].y);
                ctx.lineTo(particles[j].x, particles[j].y);
                ctx.strokeStyle = `rgba(6, 182, 212, ${opacity})`;
                ctx.lineWidth = 0.5;
                ctx.stroke();
            }
        }
        
        // Connect to mouse if close
        const mouseDistance = Math.sqrt(
            Math.pow(particles[i].x - mouseX, 2) + 
            Math.pow(particles[i].y - mouseY, 2)
        );
        
        if (mouseDistance < 200 && mouseX > 0 && mouseY > 0) {
            const opacity = (1 - mouseDistance / 200) * 0.3;
            ctx.beginPath();
            ctx.moveTo(particles[i].x, particles[i].y);
            ctx.lineTo(mouseX, mouseY);
            ctx.strokeStyle = `rgba(6, 182, 212, ${opacity})`;
            ctx.lineWidth = 1;
            ctx.stroke();
        }
    }
};

const animate = () => {
    if (!ctx || !canvasRef.value) return;
    
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
    ctx.shadowBlur = 0;
    
    // Draw connections first (behind particles)
    drawConnections();
    
    // Update and draw particles
    particles.forEach(particle => {
        // Update position
        particle.x += particle.vx;
        particle.y += particle.vy;
        
        // Wrap around screen
        if (particle.x < 0) particle.x = canvasRef.value!.width;
        if (particle.x > canvasRef.value!.width) particle.x = 0;
        if (particle.y < 0) particle.y = canvasRef.value!.height;
        if (particle.y > canvasRef.value!.height) particle.y = 0;
        
        // Smooth alpha transition (twinkling effect)
        if (Math.abs(particle.alpha - particle.targetAlpha) < 0.01) {
            particle.targetAlpha = Math.random() * 0.5 + 0.2;
        }
        particle.alpha += (particle.targetAlpha - particle.alpha) * 0.02;
        
        drawParticle(particle);
    });
    
    animationFrameId = requestAnimationFrame(animate);
};

const handleResize = () => {
    initParticles();
};

const handleMouseMove = (e: MouseEvent) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
};

const handleTouchMove = (e: TouchEvent) => {
    if (e.touches.length > 0) {
        mouseX = e.touches[0].clientX;
        mouseY = e.touches[0].clientY;
    }
};

onMounted(() => {
    initParticles();
    animate();
    
    window.addEventListener('resize', handleResize);
    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('touchmove', handleTouchMove);
});

onUnmounted(() => {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('touchmove', handleTouchMove);
});
</script>

<template>
    <canvas 
        ref="canvasRef" 
        class="pointer-events-none fixed inset-0 z-0"
    />
</template>

