<?php
// 1. Inicia la sesión (para poder acceder a ella)
session_start();

// 2. Limpia todas las variables de sesión
session_unset();

// 3. Destruye la sesión
session_destroy();

// 4. Redirige al usuario a la página de login

header('Location: /login.php');
exit;
?>