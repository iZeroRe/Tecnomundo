<?php
//Variable error colocada en ControladorAuth.php
$error = isset($error) ? $error : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UFT-8">
    <title>Inicio de Sesion</title>
</head>
<body>

<div>
    <h2>Inicio de Sesion</h2>

    <from action="controllers/ControladorAuth.php" method="post">
        <div>
            <label for="id_trabajador">ID de Empleado:</label>
            <input type="number" name="id_trabajador" id="id_trabajador" required>
        </div>
        <br>
        <div>
            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" id="contrasena" required>
        </div>
        <br>
        <button type="submit">Ingresar</button>       
    </from>
    <?php 
    // Mostramos el mensaje de error si el Controlador lo envio
    if(!empty($error)): 
    ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</div>

</body>
</html>