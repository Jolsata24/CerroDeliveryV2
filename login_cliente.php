<?php include 'includes/header.php'; ?>

<div class="auth-page">
    <div class="card auth-card">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Bienvenido de Vuelta</h2>
            
            <?php if(isset($_GET['status']) && $_GET['status'] == 'registrado'): ?>
                <div class="alert alert-success">¡Cuenta creada! Ya puedes iniciar sesión.</div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger">Usuario o contraseña incorrectos.</div>
            <?php endif; ?>

            <form action="procesos/procesar_login_cliente.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>
        </div>
        <p class="text-center mt-3 mb-0">¿Aún no tienes cuenta? <a href="registro_cliente.php">Crea una aquí</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>