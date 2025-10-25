<footer class="text-center mt-auto py-3 bg-light">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> CerroDelivery. Todos los derechos reservados.</p>
        </div>
    </footer>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="cart-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto"><i class="bi bi-cart-check-fill me-2"></i>Añadido al Carrito</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                </div>
        </div>
    </div>
    

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Selecciona el header
    const navbar = document.querySelector('.navbar.sticky-top');

    // 2. Escucha el evento de "scroll" en la ventana
    window.addEventListener('scroll', function() {
        
        // 3. Comprueba qué tan abajo ha hecho scroll el usuario
        if (window.scrollY > 50) {
            // Si ha bajado más de 50px, añade la clase "navbar-scrolled"
            navbar.classList.add('navbar-scrolled');
        } else {
            // Si está arriba, quita la clase
            navbar.classList.remove('navbar-scrolled');
        }
    });
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/cerrodeliveryv2/assets/js/custom.js"></script>
    <script src="/cerrodeliveryv2/assets/js/main.js"></script>
</body>
</html>