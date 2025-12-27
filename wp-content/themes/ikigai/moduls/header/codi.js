// ============================================
// NAVEGACIÓN MÓVIL
// ============================================
const navToggle = document.getElementById('nav-toggle');
const navMenu = document.getElementById('nav-menu');

if (navToggle) {
    navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        
        // Animación del icono hamburguesa
        navToggle.classList.toggle('active');
    });
}

// Cerrar menú al hacer click en un enlace
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        navToggle.classList.remove('active');
    });
});

// ============================================
// DETECTAR PÁGINA ACTIVA EN NAVEGACIÓN
// ============================================
const currentLocation = window.location.pathname;
const menuItems = document.querySelectorAll('.nav-link');

menuItems.forEach(item => {
    if (item.getAttribute('href') === currentLocation.split('/').pop() || 
        (currentLocation === '/' && item.getAttribute('href') === 'index.html')) {
        item.classList.add('active');
    } else {
        item.classList.remove('active');
    }
});

console.log('❓ Menú y navegación: Funcional');
