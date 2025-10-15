<?php include 'includes/header.php'; ?>

<div class="auth-page">
    <div class="card auth-card">
        <div class="card-body">
            <h2 class="card-title text-center mb-2">Conviértete en Repartidor</h2>
            <p class="text-center text-muted mb-4">Tu cuenta será revisada antes de ser activada.</p>
            <form action="procesos/procesar_registro_repartidor.php" method="POST">
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
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
                </div>
                <button type="submit" class="btn btn-info w-100 text-white">Enviar Solicitud</button>
            </form>
        </div>
        <p class="text-center mt-3 mb-0">¿Ya tienes una cuenta? <a href="login_repartidor.php">Inicia Sesión</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>