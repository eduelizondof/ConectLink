<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import * as THREE from 'three';

const props = withDefaults(
    defineProps<{
        size?: number;
        autoRotate?: boolean;
        wireframe?: boolean;
    }>(),
    {
        size: 300,
        autoRotate: true,
        wireframe: true,
    }
);

const containerRef = ref<HTMLDivElement | null>(null);
let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let renderer: THREE.WebGLRenderer;
let globe: THREE.Mesh;
let points: THREE.Points;
let connectionLines: THREE.LineSegments;
let animationFrameId: number;

const createGlobe = () => {
    if (!containerRef.value) return;

    // Scene setup
    scene = new THREE.Scene();
    
    // Camera setup
    camera = new THREE.PerspectiveCamera(45, 1, 0.1, 1000);
    camera.position.z = 2.5;

    // Renderer setup
    renderer = new THREE.WebGLRenderer({ 
        alpha: true, 
        antialias: true,
        powerPreference: 'high-performance'
    });
    renderer.setSize(props.size, props.size);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    containerRef.value.appendChild(renderer.domElement);

    // Globe geometry with wireframe
    const globeGeometry = new THREE.SphereGeometry(1, 48, 48);
    
    // Main globe material
    const globeMaterial = new THREE.MeshBasicMaterial({
        color: 0x06b6d4,
        wireframe: props.wireframe,
        transparent: true,
        opacity: 0.15,
    });

    globe = new THREE.Mesh(globeGeometry, globeMaterial);
    scene.add(globe);

    // Inner glow sphere
    const innerGlobeGeometry = new THREE.SphereGeometry(0.98, 32, 32);
    const innerGlobeMaterial = new THREE.MeshBasicMaterial({
        color: 0x0891b2,
        transparent: true,
        opacity: 0.05,
    });
    const innerGlobe = new THREE.Mesh(innerGlobeGeometry, innerGlobeMaterial);
    scene.add(innerGlobe);

    // Create random points on the globe surface (representing locations)
    const pointsGeometry = new THREE.BufferGeometry();
    const pointsCount = 150;
    const positions = new Float32Array(pointsCount * 3);
    const colors = new Float32Array(pointsCount * 3);

    for (let i = 0; i < pointsCount; i++) {
        // Random spherical coordinates
        const phi = Math.acos(-1 + (2 * Math.random()));
        const theta = 2 * Math.PI * Math.random();
        
        const x = Math.sin(phi) * Math.cos(theta);
        const y = Math.sin(phi) * Math.sin(theta);
        const z = Math.cos(phi);
        
        positions[i * 3] = x;
        positions[i * 3 + 1] = y;
        positions[i * 3 + 2] = z;

        // Cyan to teal gradient colors
        const colorChoice = Math.random();
        if (colorChoice > 0.7) {
            colors[i * 3] = 0.024; // R
            colors[i * 3 + 1] = 0.714; // G
            colors[i * 3 + 2] = 0.831; // B (cyan)
        } else if (colorChoice > 0.4) {
            colors[i * 3] = 0.063;
            colors[i * 3 + 1] = 0.725;
            colors[i * 3 + 2] = 0.506; // (emerald)
        } else {
            colors[i * 3] = 0.659;
            colors[i * 3 + 1] = 0.333;
            colors[i * 3 + 2] = 0.969; // (purple)
        }
    }

    pointsGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    pointsGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    const pointsMaterial = new THREE.PointsMaterial({
        size: 0.04,
        vertexColors: true,
        transparent: true,
        opacity: 0.9,
        sizeAttenuation: true,
    });

    points = new THREE.Points(pointsGeometry, pointsMaterial);
    scene.add(points);

    // Create connection lines between random points
    const lineGeometry = new THREE.BufferGeometry();
    const linePositions: number[] = [];
    const lineColors: number[] = [];
    const connectionCount = 30;

    for (let i = 0; i < connectionCount; i++) {
        const idx1 = Math.floor(Math.random() * pointsCount);
        const idx2 = Math.floor(Math.random() * pointsCount);
        
        // Start point
        linePositions.push(positions[idx1 * 3]);
        linePositions.push(positions[idx1 * 3 + 1]);
        linePositions.push(positions[idx1 * 3 + 2]);
        
        // End point
        linePositions.push(positions[idx2 * 3]);
        linePositions.push(positions[idx2 * 3 + 1]);
        linePositions.push(positions[idx2 * 3 + 2]);

        // Line colors (cyan gradient)
        lineColors.push(0.024, 0.714, 0.831);
        lineColors.push(0.024, 0.714, 0.831);
    }

    lineGeometry.setAttribute('position', new THREE.BufferAttribute(new Float32Array(linePositions), 3));
    lineGeometry.setAttribute('color', new THREE.BufferAttribute(new Float32Array(lineColors), 3));

    const lineMaterial = new THREE.LineBasicMaterial({
        vertexColors: true,
        transparent: true,
        opacity: 0.3,
    });

    connectionLines = new THREE.LineSegments(lineGeometry, lineMaterial);
    scene.add(connectionLines);

    // Outer ring
    const ringGeometry = new THREE.RingGeometry(1.15, 1.17, 64);
    const ringMaterial = new THREE.MeshBasicMaterial({
        color: 0x06b6d4,
        transparent: true,
        opacity: 0.3,
        side: THREE.DoubleSide,
    });
    const ring = new THREE.Mesh(ringGeometry, ringMaterial);
    ring.rotation.x = Math.PI / 2.5;
    scene.add(ring);

    // Animation loop
    const animate = () => {
        animationFrameId = requestAnimationFrame(animate);

        if (props.autoRotate) {
            globe.rotation.y += 0.002;
            points.rotation.y += 0.002;
            connectionLines.rotation.y += 0.002;
            innerGlobe.rotation.y += 0.002;
        }

        // Subtle floating motion
        const time = Date.now() * 0.001;
        globe.position.y = Math.sin(time * 0.5) * 0.02;
        points.position.y = globe.position.y;
        connectionLines.position.y = globe.position.y;
        innerGlobe.position.y = globe.position.y;

        renderer.render(scene, camera);
    };

    animate();
};

const handleResize = () => {
    if (!containerRef.value || !renderer) return;
    
    const size = Math.min(containerRef.value.offsetWidth, props.size);
    camera.aspect = 1;
    camera.updateProjectionMatrix();
    renderer.setSize(size, size);
};

onMounted(() => {
    createGlobe();
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
    }
    if (renderer) {
        renderer.dispose();
    }
});

watch(() => props.size, () => {
    handleResize();
});
</script>

<template>
    <div 
        ref="containerRef" 
        class="globe-container relative flex items-center justify-center"
        :style="{ width: `${size}px`, height: `${size}px` }"
    >
        <!-- Glow effect behind globe -->
        <div 
            class="absolute inset-0 rounded-full opacity-50 blur-3xl"
            style="background: radial-gradient(circle, rgba(6, 182, 212, 0.3) 0%, rgba(6, 182, 212, 0.1) 40%, transparent 70%);"
        />
    </div>
</template>

<style scoped>
.globe-container {
    filter: drop-shadow(0 0 30px rgba(6, 182, 212, 0.3));
}

.globe-container canvas {
    cursor: grab;
}

.globe-container canvas:active {
    cursor: grabbing;
}
</style>

