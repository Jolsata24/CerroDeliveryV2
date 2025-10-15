document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.navbar.fixed-top');
    if (!navbar) return; // Si no hay navbar, no hacer nada

    let lastScrollTop = 0;

    // Función para manejar la visibilidad de la barra de navegación
    function handleNavbarVisibility() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        // Si el scroll es mayor a 10px (para evitar saltos en algunos navegadores)
        if (scrollTop > 10) {
            navbar.style.top = '0'; // Muestra el header
        } else {
            // Si está en la cima, lo oculta
            navbar.style.top = `-${navbar.offsetHeight}px`;
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }

    // Ocultar el header al cargar la página
    navbar.style.top = `-${navbar.offsetHeight}px`;

    // Escuchar el evento de scroll
    window.addEventListener('scroll', handleNavbarVisibility);
});

// --- LÓGICA DEL PRELOADER ---
window.addEventListener('load', function () {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        // Añade la clase 'loaded' al body para activar la transición de ocultado
        document.body.classList.add('loaded');
    }
});