// ============================================
// HEADER CON SCROLL (opcio0.php)
// ============================================

(function() {
    'use strict';
    
    // Prevenir ejecución múltiple
    if (window.ikgHeaderScrollInitialized) {
        return;
    }
    window.ikgHeaderScrollInitialized = true;

    /**
     * Inicializa el comportamiento del header con scroll
     */
    function initHeaderScroll() {
        const header = document.getElementById('header');
        if (!header) return;

        let lastScroll = 0;
        let ticking = false;

        /**
         * Actualiza el header según el scroll
         */
        function updateHeader() {
            const currentScroll = window.pageYOffset;
            
            // Añadir sombra al hacer scroll
            if (currentScroll > 50) {
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.05)';
            }
            
            lastScroll = currentScroll;
            ticking = false;
        }

        /**
         * Actualiza el efecto parallax
         */
        function updateParallax() {
            const parallax = document.querySelector('.hero-parallax');
            if (!parallax) return;
            
            const scrollPosition = window.pageYOffset;
            parallax.style.transform = `translateY(${scrollPosition * 0.3}px)`;
        }

        /**
         * Maneja el evento scroll con requestAnimationFrame para mejor performance
         */
        function handleScroll() {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    updateHeader();
                    updateParallax();
                });
                ticking = true;
            }
        }

        // Event listener para scroll
        window.addEventListener('scroll', handleScroll, { passive: true });

        console.log('✅ Header: Funcional');
    }

    // ============================================
    // INICIALIZACIÓN AL CARGAR EL DOM
    // ============================================
    
    if (document.readyState === 'loading') {
        // DOM aún cargando
        document.addEventListener('DOMContentLoaded', initHeaderScroll);
    } else {
        // DOM ya cargado (por si el script se carga tarde)
        initHeaderScroll();
    }

})();