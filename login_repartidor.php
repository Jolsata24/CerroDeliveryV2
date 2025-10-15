<?php include 'includes/header.php'; ?>

<div class="auth-page">
    <div class="card auth-card">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Acceso Repartidores</h2>

            <?php if(isset($_GET['status']) && $_GET['status'] == 'solicitud_enviada'): ?>
                <div class="alert alert-success">Tu solicitud ha sido enviada. Se te notificará cuando sea aprobada.</div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <form action="procesos/procesar_login_repartidor.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-info w-100 text-white">Ingresar</button>
            </form>
        </div>
        <p class="text-center mt-3 mb-0">¿Aún no te unes? <a href="registro_repartidor.php">Conviértete en repartidor</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>