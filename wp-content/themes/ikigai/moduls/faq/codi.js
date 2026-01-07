// ============================================
// FAQ ACCORDION
// ============================================

(function() {
    'use strict';
    
    // Prevenir ejecución múltiple
    if (window.ikgFaqAccordionInitialized) {
        return;
    }
    window.ikgFaqAccordionInitialized = true;

    /**
     * Inicializa el accordion de FAQ
     */
    function initFaqAccordion() {
        const faqItems = document.querySelectorAll('.faq-item');
        
        if (!faqItems.length) return;

        /**
         * Cierra todos los items del FAQ
         */
        function closeAllItems() {
            faqItems.forEach(item => {
                item.classList.remove('active');
                const question = item.querySelector('.faq-question');
                if (question) {
                    question.setAttribute('aria-expanded', 'false');
                }
            });
        }

        /**
         * Abre un item específico del FAQ
         * @param {HTMLElement} item - El item a abrir
         */
        function openItem(item) {
            item.classList.add('active');
            const question = item.querySelector('.faq-question');
            if (question) {
                question.setAttribute('aria-expanded', 'true');
            }
        }

        /**
         * Maneja el click en una pregunta del FAQ
         * @param {HTMLElement} item - El item clickeado
         */
        function handleFaqClick(item) {
            const isActive = item.classList.contains('active');
            
            // Cerrar todos los items
            closeAllItems();
            
            // Si el item no estaba activo, abrirlo
            if (!isActive) {
                openItem(item);
            }
        }

        // Añadir event listeners a cada item
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            if (!question) return;

            // Click event
            question.addEventListener('click', () => {
                handleFaqClick(item);
            });

            // Soporte para teclado (Enter y Space)
            question.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    handleFaqClick(item);
                }
            });

            // Asegurar que sea accesible con teclado
            if (!question.hasAttribute('tabindex')) {
                question.setAttribute('tabindex', '0');
            }

            // Asegurar que tenga role button para accesibilidad
            if (!question.hasAttribute('role')) {
                question.setAttribute('role', 'button');
            }

            // Inicializar aria-expanded si no existe
            if (!question.hasAttribute('aria-expanded')) {
                question.setAttribute('aria-expanded', 'false');
            }
        });

        console.log('✅ FAQ: Funcional');
    }

    // ============================================
    // INICIALIZACIÓN AL CARGAR EL DOM
    // ============================================
    
    if (document.readyState === 'loading') {
        // DOM aún cargando
        document.addEventListener('DOMContentLoaded', initFaqAccordion);
    } else {
        // DOM ya cargado (por si el script se carga tarde)
        initFaqAccordion();
    }

})();