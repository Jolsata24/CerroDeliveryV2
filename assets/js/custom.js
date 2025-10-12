document.addEventListener('DOMContentLoaded', function() {
    const botonesAñadir = document.querySelectorAll('.add-to-cart-btn');
    // Ahora guardaremos un objeto que contiene los items Y el id del restaurante
    let carritoData = JSON.parse(sessionStorage.getItem('carritoData')) || { items: [], restauranteId: null };

    botonesAñadir.forEach(boton => {
        boton.addEventListener('click', function(event) {
            event.preventDefault();

            const idPlato = this.dataset.id;
            const nombrePlato = this.dataset.nombre;
            const precioPlato = parseFloat(this.dataset.precio);
            const restauranteId = this.dataset.restauranteId;

            // Lógica para limpiar el carrito si se cambia de restaurante
            if (carritoData.restauranteId && carritoData.restauranteId !== restauranteId) {
                if (!confirm('Ya tienes un pedido en curso con otro restaurante. ¿Deseas empezar uno nuevo?')) {
                    return; // Cancela si el usuario no quiere limpiar el carrito
                }
                carritoData.items = []; // Limpia los items
            }
            
            carritoData.restauranteId = restauranteId; // Asigna el ID del restaurante actual

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

            sessionStorage.setItem('carritoData', JSON.stringify(carritoData));
            
            const totalItems = carritoData.items.reduce((sum, item) => sum + item.cantidad, 0);
            alert(`'${nombrePlato}' añadido. Tienes ${totalItems} item(s) en tu carrito.`);
        });
    });
});