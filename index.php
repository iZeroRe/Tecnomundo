<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Inicio de Sesión</title>

    <link rel="stylesheet" type="text/css" href="code/assets/css/main.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body>
    <section class="material-half-bg">
      <div class="cover"></div>
    </section>

    <section class="login-content">
      <div class="logo">
        <h1>TECNOMUNDO</h1>
      </div>

      <div class="login-box">
        <form class="login-form" action="code/controllers/login_controller.php" method="post">
          <h3 class="login-head"><i class="bi bi-person me-2"></i>Iniciar Sesión</h3>

          <div class="mb-3">
            <label class="form-label">ID de Empleado</label>
            <input class="form-control" type="number" name="id_trabajador" id="id_trabajador" required autofocus placeholder="Ingresa tu ID">
          </div>

          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input class="form-control" type="password" name="contrasena" id="contrasena" required placeholder="Ingresa tu contraseña">
          </div>

          <?php if (isset($_GET['error'])): ?>
            <p style="color:red; font-weight:bold;"><?php echo htmlspecialchars($_GET['error']); ?></p>
          <?php endif; ?>

          <div class="mb-3 btn-container d-grid">
            <button class="btn btn-primary btn-block" type="submit">
              <i class="bi bi-box-arrow-in-right me-2 fs-5"></i>Ingresar
            </button>
          </div>
        </form>
      </div>
    </section>

    <script src="js/jquery-3.7.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
