<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Crea tu Cuenta</h2>
                <p class="text-center text-muted">Regístrate para poder realizar pedidos.</p>
                <form action="procesos/procesar_registro_cliente.php" method="POST">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                     <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono (Opcional)</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Registrarme</button>
                </form>
            </div>
        </div>
        <p class="text-center mt-3">¿Ya tienes una cuenta? <a href="login_cliente.php">Inicia Sesión</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>