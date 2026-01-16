<?php
/**
 * Module: Engineering of Wellness
 * Description: Hardcoded block regarding technical authority and balance.
 */
?>
<section class="engineering-wellness">
    <div class="engineering-wellness__bg">
        <!-- Connecting Dots Abstract SVG -->
        <svg class="engineering-wellness__constellation" viewBox="0 0 800 400" xmlns="http://www.w3.org/2000/svg">
            <style>
                .constellation-line { stroke: var(--color-primary-light); stroke-width: 1; opacity: 0.3; }
                .constellation-dot { fill: var(--color-primary); }
                .pulsing-dot { fill: var(--color-accent); animation: pulse 3s infinite; }
                @keyframes pulse { 0% { r: 3; opacity: 1; } 50% { r: 6; opacity: 0.5; } 100% { r: 3; opacity: 1; } }
            </style>
            <!-- Lines -->
            <line x1="100" y1="300" x2="200" y2="150" class="constellation-line" />
            <line x1="200" y1="150" x2="350" y2="100" class="constellation-line" />
            <line x1="350" y1="100" x2="500" y2="200" class="constellation-line" />
            <line x1="500" y1="200" x2="650" y2="120" class="constellation-line" />
            
            <!-- Dots -->
            <circle cx="100" cy="300" r="4" class="constellation-dot" />
            <circle cx="200" cy="150" r="4" class="constellation-dot" />
            <circle cx="350" cy="100" r="4" class="constellation-dot" />
            <circle cx="500" cy="200" r="6" class="pulsing-dot" /> <!-- Central Pulse -->
            <circle cx="650" cy="120" r="4" class="constellation-dot" />
        </svg>
    </div>
    
    <div class="container">
        <div class="engineering-wellness__grid">
            <div class="engineering-wellness__content">
                <span class="section-subtitle">Del Código al Cuerpo</span>
                <h2 class="engineering-wellness__title">La Ingeniería del Bienestar</h2>
                <div class="engineering-wellness__text">
                    <p>He construido arquitecturas digitales complejas para grandes marcas como <strong>Montibello</strong> y <strong>Eurofragance</strong>.</p>
                    <p>Hoy, aplico esa misma precisión lógica y pensamiento sistémico para reconstruir tu equilibrio personal.</p>
                </div>
                <a href="/sobre-mi" class="btn btn-primary">Conoce mi perfil híbrido</a>
            </div>
            <div class="engineering-wellness__visual-col">
                <!-- Abstract Representation of Body/System balance -->
                <div class="system-visual">
                    <div class="system-layer layer-1"></div>
                    <div class="system-layer layer-2"></div>
                    <div class="system-layer layer-3"></div>
                </div>
            </div>
        </div>
    </div>
</section>
