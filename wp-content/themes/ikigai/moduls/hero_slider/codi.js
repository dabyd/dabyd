(function() {
    'use strict';
    
    // Evitar múltiples inicializaciones
    if (window.ikgHeroSliderInitialized) return;
    window.ikgHeroSliderInitialized = true;

    function initHeroSlider() {
        const sliders = document.querySelectorAll('.hero-slider');
        
        sliders.forEach(slider => {
            if (slider.hasAttribute('data-initialized')) return;
            slider.setAttribute('data-initialized', 'true');

            const wrapper = slider.querySelector('.hero-slider__bg-wrapper');
            const slides = slider.querySelectorAll('.hero-slider__slide');
            
            if (slides.length < 2) return; 

            let currentIndex = 0;
            
            // Intervalo desde ACF o default 5s
            const intervalAttr = slider.getAttribute('data-interval');
            const intervalDuration = (intervalAttr ? parseInt(intervalAttr) : 5) * 1000;
            
            // Función para cambiar slide (Scroll horizontal)
            const nextSlide = () => {
                currentIndex++;
                
                // Si llegamos al final, volvemos al principio
                if (currentIndex >= slides.length) {
                    currentIndex = 0;
                }
                
                // Mover el wrapper con translateX
                const percentage = currentIndex * 100;
                wrapper.style.transform = `translateX(-${percentage}%)`;
            };

            // Iniciar intervalo
            setInterval(nextSlide, intervalDuration);
        });
        
        console.log('✅ Hero Slider: Inicializado (Modo Carousel)');
    }

    // Inicialización al cargar el DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroSlider);
    } else {
        initHeroSlider();
    }
})();
