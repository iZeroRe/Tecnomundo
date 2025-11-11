<?php
require_once 'config/conexion.php';




?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UFT-8">
    <title>Inicio de Sesion</title>
    <style>
        /* --- Variables de estilo del Dashboard --- */
        :root {
            --color-primario: #0d6efd;
            --color-fondo: #f8f9fa;
            --color-blanco: #ffffff;
            --color-texto: #212529;
            --color-texto-secundario: #6c757d;
            --color-borde: #e9ecef;
            /* Colores para el mensaje de error */
            --color-rojo-fondo: #fdeded; 
            --color-rojo-texto: #b91c1c;
            --color-rojo-borde: #f8b4b4;
        }

        /* --- Estilos Generales --- */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--color-fondo);
            margin: 0;
            display: grid; /* Usamos grid para centrar fácil */
            place-items: center; /* Centra horizontal y verticalmente */
            min-height: 100vh; /* Ocupa toda la altura de la pantalla */
            color: var(--color-texto);
        }

        /* --- Tarjeta de Login (como las del Dashboard) --- */
        .login-card {
            background-color: var(--color-blanco);
            border: 1px solid var(--color-borde);
            border-radius: 8px; /* Bordes redondeados */
            padding: 32px; /* Espaciado interno */
            width: 100%;
            max-width: 400px; /* Ancho máximo de la tarjeta */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); /* Sombra suave */
            box-sizing: border-box; /* Para que el padding no afecte el ancho */
        }

        .login-card h2 {
            text-align: center;
            color: var(--color-texto);
            margin-top: 0;
            margin-bottom: 24px;
            font-weight: 600;
        }

        /* --- Estilos del Formulario --- */
        form {
            display: flex;
            flex-direction: column;
            gap: 20px; /* Espacio entre los elementos del form (reemplaza <br>) */
        }

        /* Contenedor para cada "label + input" */
        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            display: block;
            font-weight: 500;
            color: var(--color-texto-secundario);
            margin-bottom: 6px;
            font-size: 14px;
        }

        /* Estilo para los campos de texto */
        input[type="number"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--color-borde);
            border-radius: 6px; /* Bordes redondeados */
            font-size: 16px;
            box-sizing: border-box; /* Importante */
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input[type="number"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--color-primario);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25); /* Sombra de foco */
        }
        
        /* Oculta las flechas en el campo de "ID de Empleado" (number) */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }


        /* --- Botón (Clase del Dashboard) --- */
        .btn-primary {
            background-color: var(--color-primario);
            color: var(--color-blanco);
            border: none;
            padding: 12px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7; /* Un poco más oscuro al pasar el mouse */
        }

        /* --- Mensaje de Error (Estilo nuevo) --- */
        .error-message {
            background-color: var(--color-rojo-fondo);
            color: var(--color-rojo-texto);
            border: 1px solid var(--color-rojo-borde);
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px; /* Espacio si aparece el error */
        }

    </style>
</head>
<body>

<div>
    <h2>Inicio de Sesion</h2>

    <form action="/controllers/login_auth.php" method="post">
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