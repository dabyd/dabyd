/**
 * Carrusel de Testimonios - Vanilla JavaScript
 * Funcionalidades: navegación, dots, autoplay, responsive, touch/swipe
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
        this.totalPages = Math.ceil(this.cards.length / this.itemsPerPage);
        this.autoplayInterval = null;
        this.autoplayDelay = 5000; // 5 segundos
        
        // Touch/Swipe
        this.touchStartX = 0;
        this.touchEndX = 0;
        
        this.init();
    }
    
    init() {
        if (this.cards.length <= this.itemsPerPage) {
            // Si hay menos testimonios que items por página, ocultar navegación
            this.prevBtn.style.display = 'none';
            this.nextBtn.style.display = 'none';
            return;
        }
        
        this.setupGrid();
        this.createDots();
        this.attachEvents();
        this.updateCarousel();
        this.startAutoplay();
    }
    
    getItemsPerPage() {
        const width = window.innerWidth;
        if (width < 768) return 1;
        if (width < 1024) return 2;
        return 3;
    }
    
    setupGrid() {
        // Ajustar el grid para que funcione como carrusel
        this.grid.style.display = 'flex';
        this.grid.style.flexWrap = 'nowrap';
        this.grid.style.transition = 'transform 0.5s ease-in-out';
        
        this.cards.forEach(card => {
            card.style.flex = `0 0 calc((100% - (var(--spacing-lg, 1.5rem) * ${this.itemsPerPage - 1})) / ${this.itemsPerPage})`;
            card.style.maxWidth = `calc((100% - (var(--spacing-lg, 1.5rem) * ${this.itemsPerPage - 1})) / ${this.itemsPerPage})`;
        });
    }
    
    createDots() {
        this.dotsContainer.innerHTML = '';
        
        for (let i = 0; i < this.totalPages; i++) {
            const dot = document.createElement('button');
            dot.classList.add('carousel-dot');
            dot.setAttribute('aria-label', `Ir a página ${i + 1}`);
            dot.addEventListener('click', () => this.goToPage(i));
            this.dotsContainer.appendChild(dot);
        }
        
        this.dots = Array.from(this.dotsContainer.querySelectorAll('.carousel-dot'));
    }
    
    attachEvents() {
        // Navegación con botones
        this.prevBtn.addEventListener('click', () => this.prev());
        this.nextBtn.addEventListener('click', () => this.next());
        
        // Responsive
        window.addEventListener('resize', () => this.handleResize());
        
        // Touch/Swipe para móviles
        this.grid.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: true });
        this.grid.addEventListener('touchend', (e) => this.handleTouchEnd(e), { passive: true });
        
        // Pausar autoplay al hover
        this.container.addEventListener('mouseenter', () => this.stopAutoplay());
        this.container.addEventListener('mouseleave', () => this.startAutoplay());
    }
    
    updateCarousel() {
        // Calcular el desplazamiento
        const cardWidth = this.cards[0].offsetWidth;
        const gap = parseFloat(getComputedStyle(this.grid).gap) || 0;
        const offset = -(this.currentIndex * this.itemsPerPage * (cardWidth + gap));
        
        this.grid.style.transform = `translateX(${offset}px)`;
        
        // Actualizar dots
        this.dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentIndex);
        });
        
        // Actualizar estado de botones
        this.prevBtn.disabled = this.currentIndex === 0;
        this.nextBtn.disabled = this.currentIndex === this.totalPages - 1;
    }
    
    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.updateCarousel();
            this.resetAutoplay();
        }
    }
    
    next() {
        if (this.currentIndex < this.totalPages - 1) {
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
    
    goToPage(index) {
        this.currentIndex = index;
        this.updateCarousel();
        this.resetAutoplay();
    }
    
    handleResize() {
        const newItemsPerPage = this.getItemsPerPage();
        
        if (newItemsPerPage !== this.itemsPerPage) {
            this.itemsPerPage = newItemsPerPage;
            this.totalPages = Math.ceil(this.cards.length / this.itemsPerPage);
            this.currentIndex = Math.min(this.currentIndex, this.totalPages - 1);
            
            this.setupGrid();
            this.createDots();
            this.updateCarousel();
        }
    }
    
    handleTouchStart(e) {
        this.touchStartX = e.changedTouches[0].screenX;
    }
    
    handleTouchEnd(e) {
        this.touchEndX = e.changedTouches[0].screenX;
        this.handleSwipe();
    }
    
    handleSwipe() {
        const swipeThreshold = 50; // Mínimo de píxeles para considerar swipe
        
        if (this.touchStartX - this.touchEndX > swipeThreshold) {
            // Swipe left (siguiente)
            this.next();
        }
        
        if (this.touchEndX - this.touchStartX > swipeThreshold) {
            // Swipe right (anterior)
            this.prev();
        }
    }
    
    startAutoplay() {
        this.stopAutoplay(); // Limpiar cualquier intervalo existente
        
        this.autoplayInterval = setInterval(() => {
            this.next();
        }, this.autoplayDelay);
    }
    
    stopAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }
    
    resetAutoplay() {
        this.stopAutoplay();
        this.startAutoplay();
    }
}

// Inicializar el carrusel cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    const carouselContainer = document.querySelector('[data-carousel="testimonials"]');
    
    if (carouselContainer) {
		new TestimonialsCarousel(carouselContainer);
		console.log('❓ Testimonis: Funcional');
    }
});