// ============================================
// NAVEGACIÓN MÓVIL Y DESKTOP CON SUBMENÚS
// ============================================

(function() {
    'use strict';
    
    // Prevenir ejecución múltiple
    if (window.ikgNavigationInitialized) {
        return;
    }
    window.ikgNavigationInitialized = true;

    /**
     * Inicializa el sistema de navegación
     */
    function initNavigation() {
        const navToggle = document.getElementById('nav-toggle');
        const navMenu = document.getElementById('nav-menu');
        const hasSubmenuItems = document.querySelectorAll('.has-submenu');
        
        let windowWidth = window.innerWidth;
        const MOBILE_BREAKPOINT = 968;

        /**
         * Bloquea o desbloquea el scroll del body
         * @param {boolean} lock - true para bloquear, false para desbloquear
         */
        function toggleBodyScroll(lock) {
            document.body.style.overflow = lock ? 'hidden' : '';
        }

        /**
         * Cierra el menú móvil
         */
        function closeMobileMenu() {
            if (navMenu) navMenu.classList.remove('active');
            if (navToggle) navToggle.classList.remove('active');
            toggleBodyScroll(false);
        }

        /**
         * Alterna el menú móvil
         */
        function toggleMobileMenu() {
            const isActive = navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
            toggleBodyScroll(isActive);
        }

        /**
         * Configura los eventos de submenús para desktop
         */
        function setupDesktopSubmenus() {
            hasSubmenuItems.forEach(item => {
                const link = item.querySelector('.nav-link.has-children');

                // Hover para desktop
                const handleMouseEnter = () => {
                    item.classList.add('active');
                };

                const handleMouseLeave = () => {
                    item.classList.remove('active');
                };

                const handleClick = (e) => {
                    e.preventDefault();
                    item.classList.toggle('active');
                };

                item.addEventListener('mouseenter', handleMouseEnter);
                item.addEventListener('mouseleave', handleMouseLeave);
                
                if (link) {
                    link.addEventListener('click', handleClick);
                }

                // Guardar referencias para poder limpiarlas después
                item._desktopHandlers = {
                    mouseenter: handleMouseEnter,
                    mouseleave: handleMouseLeave,
                    click: handleClick,
                    link: link
                };
            });
        }

        /**
         * Configura los eventos de submenús para móvil
         */
        function setupMobileSubmenus() {
            hasSubmenuItems.forEach(item => {
                const link = item.querySelector('.nav-link.has-children');

                if (!link) return;

                const handleClick = (e) => {
                    e.preventDefault();
                    item.classList.toggle('active');
                };

                link.addEventListener('click', handleClick);

                // Guardar referencia para poder limpiarla después
                item._mobileHandler = {
                    click: handleClick,
                    link: link
                };
            });
        }

        /**
         * Limpia los event listeners de los submenús
         */
        function cleanupSubmenuHandlers() {
            hasSubmenuItems.forEach(item => {
                // Limpiar handlers de desktop
                if (item._desktopHandlers) {
                    item.removeEventListener('mouseenter', item._desktopHandlers.mouseenter);
                    item.removeEventListener('mouseleave', item._desktopHandlers.mouseleave);
                    if (item._desktopHandlers.link) {
                        item._desktopHandlers.link.removeEventListener('click', item._desktopHandlers.click);
                    }
                    delete item._desktopHandlers;
                }

                // Limpiar handlers de móvil
                if (item._mobileHandler) {
                    if (item._mobileHandler.link) {
                        item._mobileHandler.link.removeEventListener('click', item._mobileHandler.click);
                    }
                    delete item._mobileHandler;
                }

                // Limpiar clase active
                item.classList.remove('active');
            });
        }

        /**
         * Inicializa los submenús según el tamaño de pantalla
         */
        function initSubmenus() {
            cleanupSubmenuHandlers();

            if (window.innerWidth > MOBILE_BREAKPOINT) {
                setupDesktopSubmenus();
            } else {
                setupMobileSubmenus();
            }
        }

        /**
         * Configura los enlaces del menú para cerrar en móvil
         */
        function setupNavLinks() {
            const navLinks = document.querySelectorAll('.nav-menu a:not(.has-children)');
            
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= MOBILE_BREAKPOINT) {
                        closeMobileMenu();
                    }
                });
            });
        }

        /**
         * Maneja el redimensionamiento de la ventana
         */
        function handleResize() {
            const newWidth = window.innerWidth;
            
            // Si cambiamos de móvil a desktop o viceversa
            if ((windowWidth <= MOBILE_BREAKPOINT && newWidth > MOBILE_BREAKPOINT) || 
                (windowWidth > MOBILE_BREAKPOINT && newWidth <= MOBILE_BREAKPOINT)) {
                
                // Cerrar menú si está abierto
                closeMobileMenu();
                
                // Reinicializar submenús
                initSubmenus();
            }
            
            windowWidth = newWidth;
        }

        // ============================================
        // INICIALIZACIÓN DE EVENTOS
        // ============================================

        // Toggle del menú móvil
        if (navToggle && navMenu) {
            navToggle.addEventListener('click', toggleMobileMenu);
        }

        // Inicializar submenús
        if (hasSubmenuItems.length > 0) {
            initSubmenus();
        }

        // Configurar enlaces del menú
        setupNavLinks();

        // Event listener para resize con debounce
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(handleResize, 150);
        });

        // Cerrar menú al presionar ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navMenu && navMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });

        console.log('✅ Menú móvil con submenús: Configurado');
    }

    // ============================================
    // INICIALIZACIÓN AL CARGAR EL DOM
    // ============================================
    
    if (document.readyState === 'loading') {
        // DOM aún cargando
        document.addEventListener('DOMContentLoaded', initNavigation);
    } else {
        // DOM ya cargado (por si el script se carga tarde)
        initNavigation();
    }

})();