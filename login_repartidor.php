<?php include 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <?php if(isset($_GET['status']) && $_GET['status'] == 'solicitud_enviada'): ?>
            <div class="alert alert-success">Tu solicitud ha sido enviada. Se te notificará cuando sea aprobada.</div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h2 class="text-center">Acceso para Repartidores</h2>
                <form action="procesos/procesar_login_repartidor.php" method="POST">
                    <div class="mb-3">
                        <label>Correo Electrónico</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label>Contraseña</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Ingresar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>