// --- LÓGICA DEL PRELOADER Y HEADER DINÁMICO ---

// Usamos el evento 'load', que espera a que TODO (imágenes, CSS, etc.) esté cargado.
window.addEventListener('load', function () {
    
    // 1. Ocultar el preloader
    const preloader = document.getElementById('preloader');
    if (preloader) {
        document.body.classList.add('loaded');
    }

    // 2. Manejar la visibilidad del header
    const navbar = document.querySelector('.navbar.fixed-top');
    if (navbar) {
        
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
        };

        // Ocultar el header inmediatamente después de que todo cargue.
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

// --- LÓGICA PARA EL CARRUSEL INFINITO DE CATEGORÍAS ---
document.addEventListener('DOMContentLoaded', () => {
    const scrollers = document.querySelectorAll(".scroller");

    // Si el usuario no prefiere movimiento, no añadimos la animación
    if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        addAnimation();
    }

    function addAnimation() {
        scrollers.forEach((scroller) => {
            scroller.setAttribute("data-animated", true);
        });
    }
});


// --- LÓGICA PARA EL TEXTO ANIMADO DEL HERO ---
document.addEventListener("DOMContentLoaded", function() {
    const typedTextContainer = document.getElementById("typed-text-container");

    const words = [
        { text: "favoritos", colorClass: "typed-text-color-1" },
        { text: "de casa", colorClass: "typed-text-color-2" },
        { text: "del día", colorClass: "typed-text-color-3" },
        { text: "locales", colorClass: "typed-text-color-4" }
    ];

    let wordIndex = 0;
    let charIndex = 0;
    let isDeleting = false;

    function type() {
        const currentWord = words[wordIndex];
        const fullText = currentWord.text;
        
        // Aplica la clase de color actual
        typedTextContainer.className = 'typed-text-container ' + currentWord.colorClass;

        if (isDeleting) {
            // Borrando
            typedTextContainer.textContent = fullText.substring(0, charIndex - 1);
            charIndex--;
        } else {
            // Escribiendo
            typedTextContainer.textContent = fullText.substring(0, charIndex + 1);
            charIndex++;
        }

        let typeSpeed = isDeleting ? 100 : 200;

        if (!isDeleting && charIndex === fullText.length) {
            // Pausa al final de la palabra
            typeSpeed = 2000;
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            wordIndex = (wordIndex + 1) % words.length;
            typeSpeed = 500;
        }

        setTimeout(type, typeSpeed);
    }

    if (typedTextContainer) {
        type();
    }
});