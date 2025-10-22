// LÓGICA DEL CARRITO DE COMPRAS
document.addEventListener('DOMContentLoaded', function() {
    const botonesAñadir = document.querySelectorAll('.add-to-cart-btn');

    botonesAñadir.forEach(boton => {
        boton.addEventListener('click', function(event) {
            event.preventDefault();

            // --- INICIO DE LA CORRECCIÓN ---
            // La clave del carrito y los datos se obtienen CADA VEZ que se hace clic.
            // Esto asegura que siempre trabajamos con la información más reciente.
            const carritoKey = `carritoData_${CLIENTE_ID}`;
            let carritoData = JSON.parse(sessionStorage.getItem(carritoKey)) || { items: [], restauranteId: null };
            // --- FIN DE LA CORRECCIÓN ---

            const idPlato = this.dataset.id;
            const nombrePlato = this.dataset.nombre;
            const precioPlato = parseFloat(this.dataset.precio);
            const restauranteId = this.dataset.restauranteId;

            if (carritoData.restauranteId && carritoData.restauranteId !== restauranteId) {
                if (!confirm('Ya tienes un pedido en curso con otro restaurante. ¿Deseas empezar uno nuevo?')) {
                    return;
                }
                // Reseteamos completamente el carrito si el usuario confirma.
                carritoData = { items: [], restauranteId: restauranteId };
            }
            
            carritoData.restauranteId = restauranteId;
            const itemExistente = carritoData.items.find(item => item.id === idPlato);

            if (itemExistente) {
                itemExistente.cantidad++;
            } else {
                carritoData.items.push({
                    id: idPlato,
                    nombre: nombrePlato,
                    precio: precioPlato,
                    cantidad: 1
                });
            }

            sessionStorage.setItem(carritoKey, JSON.stringify(carritoData));
            
            // Lógica para mostrar la notificación "toast" (sin cambios)
            const toastElement = document.getElementById('cart-toast');
            const toastBody = toastElement.querySelector('.toast-body');
            const toast = new bootstrap.Toast(toastElement);
            toastBody.textContent = `'${nombrePlato}' se ha añadido a tu carrito.`;
            toast.show();
        });
    });

    // --- LÓGICA DE VOTACIÓN CON CONFIRMACIÓN ---
    const ratingModal = document.getElementById('ratingModal');
    if (ratingModal) {
        const stars = ratingModal.querySelectorAll('.rating-stars input');
        const confirmButton = ratingModal.querySelector('#confirmRatingBtn');
        const feedbackSpan = ratingModal.querySelector('.rating-feedback');
        let selectedRating = 0;

        stars.forEach(star => {
            star.addEventListener('change', function() {
                selectedRating = this.value;
                confirmButton.disabled = false;
                feedbackSpan.textContent = `Has seleccionado ${selectedRating} estrella(s).`;
            });
        });

        confirmButton.addEventListener('click', function() {
            if (selectedRating === 0) return;

            const restauranteId = ratingModal.querySelector('.rating-modal').dataset.restauranteId;
            this.disabled = true;
            feedbackSpan.textContent = 'Enviando tu voto...';

            fetch('procesos/procesar_puntuacion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_restaurante: restauranteId,
                    puntuacion: selectedRating
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    feedbackSpan.innerHTML = '<strong class="text-success">¡Gracias por tu voto!</strong>';
                    setTimeout(() => {
                        const modalInstance = bootstrap.Modal.getInstance(ratingModal);
                        if(modalInstance) modalInstance.hide();
                        window.location.reload(); 
                    }, 2000);
                } else {
                    feedbackSpan.innerHTML = `<strong class="text-danger">Error: ${data.message}</strong>`;
                    this.disabled = false;
                }
            })
            .catch(error => {
                feedbackSpan.innerHTML = '<strong class="text-danger">Error de red. Inténtalo de nuevo.</strong>';
                this.disabled = false;
                console.error('Error:', error);
            });
        });
    }
});