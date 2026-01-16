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
        /**
         * Actualiza el efecto parallax (Soporte Multi-instancia y Posición Relativa)
         */
        function updateParallax() {
            const parallaxes = document.querySelectorAll('.hero-parallax');
            
            parallaxes.forEach(element => {
                const container = element.closest('.services-hero') || element.closest('.hero-bg-wrapper').parentElement;
                if (!container) return;

                const rect = container.getBoundingClientRect();
                const viewportHeight = window.innerHeight;

                // Solo calcular si está visible (o cerca)
                if (rect.top < viewportHeight && rect.bottom > 0) {
                    // Cálculo relativo: 0 cuando el elemento está en la parte superior del viewport
                    // Para que esté centrado visualmente cuando el módulo está en pantalla.
                    
                    // Fórmula: (Scroll Global - Offset del Elemento) * Velocidad
                    // Podemos usar rect.top para obtener la posición relativa al viewport sin recalcular global scroll
                    // rect.top es positivo si está abajo, negativo si hemos hecho scroll más allá.
                    
                    // Queremos que cuando rect.top sea 0 (top viewport), el desplazamiento sea 0 (o ajustado).
                    // Pero para suavidad, usamos una variación continua.
                    
                    // Invertimos rect.top porque al scrollear hacia abajo, rect.top disminuye, 
                    // y queremos que el background baje (translate positivo) para efecto profundidad.
                    // Espera, parallax standard: background se mueve + lento.
                    // Si contenido sube 100px, fondo sube 70px (si translateY es +30px).
                    
                    // Queremos que el parallax esté centrado (translateY=0) cuando el módulo está en el centro de la pantalla.
                    // rect.top es la posición del borde superior del contenedor respecto al viewport.
                    
                    const center = (viewportHeight - rect.height) / 2;
                    const delta = rect.top - center;
                    
                    // Velocidad reducida para asegurar que no se salga de los límites del 130% de altura
                    // Con speed 0.15 y altura extra del 30% (+/- 15%), cubrimos la mayoría de pantallas.
                    const speed = 0.15;
                    
                    element.style.transform = `translateY(${delta * speed}px)`;
                }
            });
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