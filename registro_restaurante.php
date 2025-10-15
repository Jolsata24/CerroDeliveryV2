<?php include 'includes/header.php'; ?>

<div class="auth-page">
    <div class="card auth-card">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Registra tu Negocio</h2>
            <form action="procesos/procesar_registro.php" method="POST">
                <div class="mb-3">
                    <label for="nombre_restaurante" class="form-label">Nombre del Restaurante</label>
                    <input type="text" class="form-control" id="nombre_restaurante" name="nombre_restaurante" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico de Contacto</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Crea una Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Crear Cuenta</button>
            </form>
        </div>
         <p class="text-center mt-3 mb-0">¿Ya tienes una cuenta? <a href="login_restaurante.php">Inicia Sesión aquí</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>