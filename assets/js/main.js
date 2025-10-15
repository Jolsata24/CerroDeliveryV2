// --- LÓGICA DEL PRELOADER Y HEADER DINÁMICO ---

// Usamos el evento 'load' que espera a que TODO (imágenes incluidas) esté cargado.
window.addEventListener('load', function () {
    
    // 1. Ocultar el preloader
    const preloader = document.getElementById('preloader');
    if (preloader) {
        document.body.classList.add('loaded');
    }

    // 2. Manejar la visibilidad del header
    const navbar = document.querySelector('.navbar.fixed-top');
    if (navbar) {
        let lastScrollTop = 0;

        // Función que se ejecutará cada vez que el usuario haga scroll
        const handleScroll = () => {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > 10) {
                // Si el scroll es mayor a 10px, muestra el header
                navbar.style.top = '0';
            } else {
                // Si está en la cima de la página, lo oculta
                navbar.style.top = `-${navbar.offsetHeight}px`;
            }
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        };

        // Ocultar el header inmediatamente después de que todo cargue
        // Ahora sí 'offsetHeight' tendrá el valor correcto.
        if (window.pageYOffset <= 10) {
             navbar.style.top = `-${navbar.offsetHeight}px`;
        }

        // Añadir el listener para el evento de scroll
        window.addEventListener('scroll', handleScroll);
    }
});
// --- LÓGICA DEL PRELOADER ---
window.addEventListener('load', function () {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        // Añade la clase 'loaded' al body para activar la transición de ocultado
        document.body.classList.add('loaded');
    }
});