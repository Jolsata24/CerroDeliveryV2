<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        
        <?php 
        // Mostrar un mensaje de éxito si vienen del registro
        if(isset($_GET['status']) && $_GET['status'] == 'success'): 
        ?>
            <div class="alert alert-success" role="alert">
                ¡Registro exitoso! Ahora puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <?php 
        // Mostrar un mensaje de error si el login falla
        if(isset($_GET['error']) && $_GET['error'] == '1'): 
        ?>
            <div class="alert alert-danger" role="alert">
                Correo electrónico o contraseña incorrectos.
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Iniciar Sesión</h2>
                <form action="procesos/procesar_login_restaurante.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Acceder</button>
                </form>
            </div>
        </div>
        <p class="text-center mt-3">¿Aún no tienes una cuenta? <a href="registro_restaurante.php">Regístrate aquí</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>