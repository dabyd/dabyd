// ============================================
// CARRUSEL DE TESTIMONIOS (CALM TECH / NATIVE SCROLL)
// ============================================

(function() {
    'use strict';
    
    function initTestimonialsCarousel() {
        const container = document.querySelector('[data-carousel="testimonials"]');
        if (!container) return;

        const grid = container.querySelector('.testimonials-grid');
        const prevBtn = container.querySelector('.carousel-nav.prev');
        const nextBtn = container.querySelector('.carousel-nav.next');
        const dotsContainer = document.querySelector('[data-dots="testimonials"]');
        
        if (!grid) return;

        // Variables de estado
        let scrollTimeout;

        // --- FUNCIONES DE SCROLL ---

        const getCardWidth = () => {
            const card = grid.querySelector('.testimonial-card');
            if (!card) return 0;
            // Incluir el gap (asumimos 2rem o 32px standard si no se puede calcular)
            const gap = 32; // var(--space-md)
            return card.offsetWidth + gap;
        };

        const scrollTo = (direction) => {
            const scrollAmount = getCardWidth();
            const targetScroll = grid.scrollLeft + (direction * scrollAmount);
            
            grid.scrollTo({
                left: targetScroll,
                behavior: 'smooth'
            });
        };

        // --- EVENT LISTENERS ---

        if (prevBtn) {
            prevBtn.addEventListener('click', () => scrollTo(-1));
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => scrollTo(1));
        }

        // --- INDICADORES (DOTS) ---
        // Generar dots basados en la cantidad de items
        const cards = grid.querySelectorAll('.testimonial-card');
        if (dotsContainer && cards.length > 0) {
            dotsContainer.innerHTML = '';
            
            cards.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.classList.add('carousel-dot');
                dot.setAttribute('aria-label', `Ver testimonio ${index + 1}`);
                if (index === 0) dot.classList.add('active');
                
                dot.addEventListener('click', () => {
                    const cardWidth = getCardWidth();
                    grid.scrollTo({
                        left: index * cardWidth,
                        behavior: 'smooth'
                    });
                });
                
                dotsContainer.appendChild(dot);
            });
        }

        // Actualizar dots on scroll active
        const updateActiveDot = () => {
            if (!dotsContainer) return;
            
            const scrollCenter = grid.scrollLeft + (grid.offsetWidth / 2);
            const cardWidth = getCardWidth();
            
            // Índice aproximado
            let activeIndex = Math.round(grid.scrollLeft / cardWidth);
            // Asegurar límites
            activeIndex = Math.max(0, Math.min(activeIndex, cards.length - 1));

            const dots = dotsContainer.querySelectorAll('.carousel-dot');
            dots.forEach(d => d.classList.remove('active'));
            if (dots[activeIndex]) dots[activeIndex].classList.add('active');
            
            // Actualizar estado botones
            if (prevBtn) prevBtn.style.opacity = grid.scrollLeft <= 10 ? '0.5' : '1';
            // Simple check para el final: (scrollWidth - clientWidth) <= scrollLeft + pequeño margen
            const maxScroll = grid.scrollWidth - grid.clientWidth;
            if (nextBtn) nextBtn.style.opacity = grid.scrollLeft >= maxScroll - 10 ? '0.5' : '1';
        };

        grid.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(updateActiveDot, 50); // Debounce visual updates
        });
        
        // Init state
        updateActiveDot();

        console.log('✅ Testimonios: Carousel Nativo Inicializado');
    }

    // Inicialización
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTestimonialsCarousel);
    } else {
        initTestimonialsCarousel();
    }

})();