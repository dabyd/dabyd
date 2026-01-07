document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    
    // Navegación móvil - Toggle del menú principal
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
            
            // Bloquear scroll del body cuando el menú está abierto
            if (navMenu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
    }
    
    // Gestión de submenús en DESKTOP
    const hasSubmenuItems = document.querySelectorAll('.has-submenu');
    
    if (window.innerWidth > 968) {
        hasSubmenuItems.forEach(item => {
            const link = item.querySelector('.nav-link.has-children');
            
            // Hover para desktop
            item.addEventListener('mouseenter', () => {
                item.classList.add('active');
            });
            
            item.addEventListener('mouseleave', () => {
                item.classList.remove('active');
            });
            
            // Click en el enlace padre en desktop - prevenir navegación
            if (link) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    item.classList.toggle('active');
                });
            }
        });
    }
    
    // Gestión de submenús en MÓVIL
    if (window.innerWidth <= 968) {
        hasSubmenuItems.forEach(item => {
            const link = item.querySelector('.nav-link.has-children');
            
            if (link) {
                // En móvil, el click en el padre alterna la visibilidad del submenú
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    item.classList.toggle('active');
                });
            }
        });
    }
    
    // Cerrar menú al hacer click en cualquier enlace del menú (excepto padres con hijos)
    const navLinks = document.querySelectorAll('.nav-menu a:not(.has-children)');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu) navMenu.classList.remove('active');
            if (navToggle) navToggle.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
    
    // Recalcular eventos en resize (de móvil a desktop y viceversa)
    let windowWidth = window.innerWidth;
    window.addEventListener('resize', () => {
        const newWidth = window.innerWidth;
        
        // Si cambiamos de móvil a desktop o viceversa
        if ((windowWidth <= 968 && newWidth > 968) || (windowWidth > 968 && newWidth <= 968)) {
            location.reload(); // Recargamos para reaplicar eventos correctamente
        }
        
        windowWidth = newWidth;
    });
    
    console.log('✅ Menú móvil con submenús: Configurado');
});