<?php
session_start();

if (isset($_SESSION['id_trabajador'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'conexion.php';

    $id_empleado = $_POST['id_trabajador'];
    $contrasena = $_POST['contrasena'];

    if (empty($id_empleado) || empty($contrasena)) {
        $error = "Por favor, ingresa tu ID y contraseña.";
    } else {
        // La consulta ahora busca por id_trabajador
        $sql = "SELECT id_trabajador, nombre, contrasena FROM trabajador WHERE id_trabajador = ?";

        if ($stmt = $conexion->prepare($sql)) {
            // Se enlaza el parámetro como un entero ("i")
            $stmt->bind_param("i", $id_empleado);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nombre, $contrasena_db);
                $stmt->fetch();

                // IMPORTANTE: La recomendación de usar password_verify() sigue vigente.
                if ($contrasena == $contrasena_db) {
                    session_regenerate_id();
                    $_SESSION['id_trabajador'] = $id;
                    $_SESSION['nombre_trabajador'] = $nombre;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "La contraseña es incorrecta.";
                }
            } else {
                $error = "No se encontró un empleado con ese ID.";
            }
            $stmt->close();
        }
        $conexion->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Empleado</title>
</head>
<body>

<div>
    <h2>Inicio de Sesión</h2>
    <form action="login.php" method="post">
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
    </form>
    <?php if(!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
</div>

</body>
</html>