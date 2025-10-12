// Espera a que todo el contenido de la página esté cargado
document.addEventListener('DOMContentLoaded', function() {
    
    // Busca todos los botones que tengan la clase 'add-to-cart-btn'
    const botonesAñadir = document.querySelectorAll('.add-to-cart-btn');

    // Inicializa el carrito obteniendo los datos de sessionStorage o creando un array vacío
    let carrito = JSON.parse(sessionStorage.getItem('carrito')) || [];

    // Añade un 'escuchador' de clics a cada botón
    botonesAñadir.forEach(boton => {
        boton.addEventListener('click', function(event) {
            // Previene la acción por defecto del enlace (que es navegar a '#')
            event.preventDefault();

            // Obtiene los datos del plato desde los atributos 'data-*' del botón
            const idPlato = this.dataset.id;
            const nombrePlato = this.dataset.nombre;
            const precioPlato = parseFloat(this.dataset.precio);

            // Busca si el plato ya existe en el carrito
            const itemExistente = carrito.find(item => item.id === idPlato);

            if (itemExistente) {
                // Si existe, solo aumenta la cantidad
                itemExistente.cantidad++;
            } else {
                // Si no existe, lo añade al carrito con cantidad 1
                carrito.push({
                    id: idPlato,
                    nombre: nombrePlato,
                    precio: precioPlato,
                    cantidad: 1
                });
            }

            // Guarda el carrito actualizado en sessionStorage
            sessionStorage.setItem('carrito', JSON.stringify(carrito));

            // Notificación visual simple
            alert(`'${nombrePlato}' ha sido añadido al carrito.`);
        });
    });
});