// ============================================
// CARRUSEL DE TESTIMONIOS
// ============================================

(function() {
    'use strict';
    
    // Prevenir ejecución múltiple
    if (window.ikgTestimonialsCarouselInitialized) {
        return;
    }
    window.ikgTestimonialsCarouselInitialized = true;

    /**
     * Clase para manejar el carrusel de testimonios
     */
    class TestimonialsCarousel {
        constructor(container) {
            this.container = container;
            this.grid = container.querySelector('.testimonials-grid');
            this.cards = Array.from(this.grid.querySelectorAll('.testimonial-card'));
            this.prevBtn = container.querySelector('.carousel-nav.prev');
            this.nextBtn = container.querySelector('.carousel-nav.next');
            this.dotsContainer = document.querySelector('[data-dots="testimonials"]');
            
            // Configuración
            this.currentIndex = 0;
            this.itemsPerPage = this.getItemsPerPage();
            this.totalSlides = this.cards.length; // Total de cards individuales
            this.autoplayInterval = null;
            this.autoplayDelay = 5000; // 5 segundos
            
            // Touch/Swipe
            this.touchStartX = 0;
            this.touchEndX = 0;
            
            // Resize debounce
            this.resizeTimeout = null;
            
            this.init();
        }
        
        init() {
            if (this.cards.length === 0) {
                console.warn('No hay testimonios para mostrar');
                return;
            }

            if (this.cards.length <= this.itemsPerPage) {
                // Si hay menos testimonios que items por página, ocultar navegación
                if (this.prevBtn) this.prevBtn.style.display = 'none';
                if (this.nextBtn) this.nextBtn.style.display = 'none';
                if (this.dotsContainer) this.dotsContainer.style.display = 'none';
                return;
            }
            
            this.setupGrid();
            this.createDots();
            this.attachEvents();
            this.updateCarousel();
            this.startAutoplay();
        }
        
        /**
         * Obtiene el número de items visibles según el ancho de pantalla
         */
        getItemsPerPage() {
            const width = window.innerWidth;
            if (width < 768) return 1;
            if (width < 1024) return 2;
            return 3;
        }
        
        /**
         * Configura el grid como carrusel
         */
        setupGrid() {
            this.grid.style.display = 'flex';
            this.grid.style.flexWrap = 'nowrap';
            this.grid.style.transition = 'transform 0.5s ease-in-out';
            
            this.cards.forEach(card => {
                card.style.flex = `0 0 calc((100% - (var(--spacing-lg, 1.5rem) * ${this.itemsPerPage - 1})) / ${this.itemsPerPage})`;
                card.style.maxWidth = `calc((100% - (var(--spacing-lg, 1.5rem) * ${this.itemsPerPage - 1})) / ${this.itemsPerPage})`;
            });
        }
        
        /**
         * Crea los dots de navegación
         */
        createDots() {
            if (!this.dotsContainer) return;
            
            this.dotsContainer.innerHTML = '';
            
            // Crear un dot por cada slide (ahora se mueve de 1 en 1)
            const maxDots = this.totalSlides - this.itemsPerPage + 1;
            
            for (let i = 0; i < maxDots; i++) {
                const dot = document.createElement('button');
                dot.classList.add('carousel-dot');
                dot.setAttribute('aria-label', `Ir a testimonio ${i + 1}`);
                dot.addEventListener('click', () => this.goToSlide(i));
                this.dotsContainer.appendChild(dot);
            }
            
            this.dots = Array.from(this.dotsContainer.querySelectorAll('.carousel-dot'));
        }
        
        /**
         * Añade todos los event listeners
         */
        attachEvents() {
            // Navegación con botones
            if (this.prevBtn) {
                this.prevBtn.addEventListener('click', () => this.prev());
            }
            
            if (this.nextBtn) {
                this.nextBtn.addEventListener('click', () => this.next());
            }
            
            // Responsive con debounce
            window.addEventListener('resize', () => this.handleResize());
            
            // Touch/Swipe para móviles
            this.grid.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: true });
            this.grid.addEventListener('touchend', (e) => this.handleTouchEnd(e), { passive: true });
            
            // Pausar autoplay al hover
            this.container.addEventListener('mouseenter', () => this.stopAutoplay());
            this.container.addEventListener('mouseleave', () => this.startAutoplay());
            
            // Navegación con teclado
            this.container.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.prev();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.next();
                }
            });
        }
        
        /**
         * Actualiza la posición del carrusel y los indicadores
         */
        updateCarousel() {
            // Calcular el desplazamiento (ahora de 1 en 1 card)
            const cardWidth = this.cards[0].offsetWidth;
            const gap = parseFloat(getComputedStyle(this.grid).gap) || 0;
            const offset = -(this.currentIndex * (cardWidth + gap));
            
            this.grid.style.transform = `translateX(${offset}px)`;
            
            // Actualizar dots
            if (this.dots && this.dots.length > 0) {
                this.dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === this.currentIndex);
                });
            }
            
            // Actualizar estado de botones
            const maxIndex = this.totalSlides - this.itemsPerPage;
            
            if (this.prevBtn) {
                this.prevBtn.disabled = this.currentIndex === 0;
                this.prevBtn.setAttribute('aria-disabled', this.currentIndex === 0);
            }
            
            if (this.nextBtn) {
                this.nextBtn.disabled = this.currentIndex >= maxIndex;
                this.nextBtn.setAttribute('aria-disabled', this.currentIndex >= maxIndex);
            }
        }
        
        /**
         * Navega al slide anterior (1 card hacia atrás)
         */
        prev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                this.updateCarousel();
                this.resetAutoplay();
            }
        }
        
        /**
         * Navega al siguiente slide (1 card hacia adelante)
         */
        next() {
            const maxIndex = this.totalSlides - this.itemsPerPage;
            
            if (this.currentIndex < maxIndex) {
                this.currentIndex++;
                this.updateCarousel();
                this.resetAutoplay();
            } else {
                // Volver al inicio cuando llega al final
                this.currentIndex = 0;
                this.updateCarousel();
                this.resetAutoplay();
            }
        }
        
        /**
         * Navega a un slide específico
         * @param {number} index - Índice del slide
         */
        goToSlide(index) {
            const maxIndex = this.totalSlides - this.itemsPerPage;
            this.currentIndex = Math.max(0, Math.min(index, maxIndex));
            this.updateCarousel();
            this.resetAutoplay();
        }
        
        /**
         * Maneja el redimensionamiento de la ventana
         */
        handleResize() {
            clearTimeout(this.resizeTimeout);
            
            this.resizeTimeout = setTimeout(() => {
                const newItemsPerPage = this.getItemsPerPage();
                
                if (newItemsPerPage !== this.itemsPerPage) {
                    this.itemsPerPage = newItemsPerPage;
                    const maxIndex = this.totalSlides - this.itemsPerPage;
                    this.currentIndex = Math.min(this.currentIndex, maxIndex);
                    
                    this.setupGrid();
                    this.createDots();
                    this.updateCarousel();
                }
            }, 150);
        }
        
        /**
         * Maneja el inicio del touch/swipe
         */
        handleTouchStart(e) {
            this.touchStartX = e.changedTouches[0].screenX;
        }
        
        /**
         * Maneja el fin del touch/swipe
         */
        handleTouchEnd(e) {
            this.touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe();
        }
        
        /**
         * Procesa el gesto de swipe
         */
        handleSwipe() {
            const swipeThreshold = 50; // Mínimo de píxeles para considerar swipe
            const diff = this.touchStartX - this.touchEndX;
            
            if (Math.abs(diff) < swipeThreshold) return;
            
            if (diff > 0) {
                // Swipe left (siguiente)
                this.next();
            } else {
                // Swipe right (anterior)
                this.prev();
            }
        }
        
        /**
         * Inicia el autoplay
         */
        startAutoplay() {
            this.stopAutoplay(); // Limpiar cualquier intervalo existente
            
            this.autoplayInterval = setInterval(() => {
                this.next();
            }, this.autoplayDelay);
        }
        
        /**
         * Detiene el autoplay
         */
        stopAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
                this.autoplayInterval = null;
            }
        }
        
        /**
         * Reinicia el autoplay
         */
        resetAutoplay() {
            this.stopAutoplay();
            this.startAutoplay();
        }
        
        /**
         * Destruye el carrusel y limpia los event listeners
         */
        destroy() {
            this.stopAutoplay();
            // Aquí podrías añadir más limpieza si fuera necesario
        }
    }

    /**
     * Inicializa el carrusel de testimonios
     */
    function initTestimonialsCarousel() {
        const carouselContainer = document.querySelector('[data-carousel="testimonials"]');
        
        if (!carouselContainer) {
            return;
        }

        try {
            new TestimonialsCarousel(carouselContainer);
            console.log('✅ Testimonios: Funcional');
        } catch (error) {
            console.error('Error inicializando carrusel de testimonios:', error);
        }
    }

    // ============================================
    // INICIALIZACIÓN AL CARGAR EL DOM
    // ============================================
    
    if (document.readyState === 'loading') {
        // DOM aún cargando
        document.addEventListener('DOMContentLoaded', initTestimonialsCarousel);
    } else {
        // DOM ya cargado (por si el script se carga tarde)
        initTestimonialsCarousel();
    }

})();