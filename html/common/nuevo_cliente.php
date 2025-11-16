<?php
session_start();

// --- CAMBIO 1: Verificación de permisos (solo logueado) ---
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}
$user_rol = $_SESSION['user_rol'];

// --- Si el script llega aquí, está validado ---
require '../config/conexion.php';

// --- Consultas para poblar el formulario ---
try {
    // 1. Obtener la lista de empresas (para el dropdown)
    $stmt_empresas = $pdo->query("SELECT id_empresa, nombre_empresa FROM empresa ORDER BY nombre_empresa ASC");
    $empresas = $stmt_empresas->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    echo "Error al consultar la base de datos: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Cliente - Sistema de Administración</title>
    
    <style>
        :root {
            --color-primario: #0d6efd;
            --color-fondo: #f8f9fa;
            --color-blanco: #ffffff;
            --color-texto: #212529;
            --color-texto-secundario: #6c757d;
            --color-borde: #e9ecef;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            background-color: var(--color-fondo);
            color: var(--color-texto);
            display: flex;
        }
        /* (Todo tu CSS de .sidebar, .main-content, .card, .form-grid, etc.) */
        .sidebar { width: 240px; background-color: var(--color-blanco); border-right: 1px solid var(--color-borde); height: 100vh; display: flex; flex-direction: column; padding: 24px; box-sizing: border-box; position: fixed; left: 0; top: 0; z-index: 10; }
        .sidebar-header { font-size: 24px; font-weight: 600; margin-bottom: 30px; }
        .sidebar-nav { flex-grow: 1; }
        .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav li { margin-bottom: 10px; }
        .sidebar-nav a { text-decoration: none; color: var(--color-texto-secundario); font-weight: 500; display: flex; align-items: center; padding: 10px 12px; border-radius: 6px; transition: background-color 0.2s, color 0.2s; }
        .sidebar-nav a:hover { background-color: var(--color-fondo); color: var(--color-texto); }
        .sidebar-nav a.active { background-color: #e7f1ff; color: var(--color-primario); font-weight: 600; }
        .sidebar-nav a span { margin-right: 12px; font-size: 20px; }
        .sidebar-footer a { text-decoration: none; color: var(--color-texto-secundario); font-weight: 500; display: flex; align-items: center; padding: 10px 12px; }
        .main-content { margin-left: 240px; flex-grow: 1; padding: 24px 32px; }
        .main-header h1 { font-size: 28px; margin: 0; margin-bottom: 24px; }
        .card { background-color: var(--color-blanco); border: 1px solid var(--color-borde); border-radius: 8px; padding: 24px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 24px; }
        .form-col-span-2 { grid-column: span 2; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { display: block; font-weight: 500; color: var(--color-texto-secundario); margin-bottom: 6px; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--color-borde); border-radius: 6px; font-size: 15px; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; font-family: inherit; background-color: var(--color-blanco); }
        .form-control:focus { outline: none; border-color: var(--color-primario); box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25); }
        .form-actions { grid-column: span 2; display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; }
        .btn { border: none; padding: 10px 16px; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 14px; text-decoration: none; transition: background-color 0.2s, color 0.2s; }
        .btn-primary { background-color: var(--color-primario); color: var(--color-blanco); }
        .btn-primary:hover { background-color: #0b5ed7; }
        .btn-secondary { background-color: var(--color-fondo); color: var(--color-texto-secundario); border: 1px solid var(--color-borde); }
        .btn-secondary:hover { background-color: var(--color-borde); }
    </style>
</head>
<body>
        
    <aside class="sidebar">
        <?php if ($user_rol == 'admin'): ?>
            <div class="sidebar-header">Admin</div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../admin/dashboard.php"><span>📊</span> Tablero</a></li>
                    <li><a href="ordenes.php"><span>📦</span> Órdenes</a></li>
                    <li><a href="ventas.php"><span>💰</span> Ventas</a></li>
                    <li><a href="../admin/clientes.php" class="active"><span>👥</span> Clientes</a></li> <li><a href="../admin/inventario.php"><span>🧾</span> Inventario</a></li>
                    <li><a href="garantias.php"><span>🛡️</span> Garantías</a></li>
                    <li><a href="../admin/proveedores.php"><span>🚚</span> Proveedores</a></li>
                    <li><a href="../admin/trabajadores.php"><span>👨</span> Trabajadores</a></li>
                </ul>
            </nav>
        
        <?php else: // Es Trabajador ?>
            <div class="sidebar-header">Trabajador</div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../trabajador/dashboard.php"><span>📊</span> Tablero</a></li>
                    <li><a href="ventas.php"><span>💰</span> Ventas</a></li>
                    <li><a href="ordenes.php"><span>📦</span> Órdenes</a></li>
                    <li><a href="garantias.php"><span>🛡️</span> Garantías</a></li>
                    <li><a href="nuevo_cliente.php" class="active"><span>👥</span> Nuevo Cliente</a></li> 
                </ul>
            </nav>
        <?php endif; ?>
        
        <div class="sidebar-footer">
            <a href="/controllers/logout.php"><span>🚪</span> Cerrar sesión</a>
        </div>
    </aside>

    <main class="main-content">

        <header class="main-header">
            <h1>Registrar cliente</h1>
        </header>

        <?php 
        if (isset($_SESSION['error_message'])) {
            echo '<div style="background-color: #fdeded; color: #b91c1c; border: 1px solid #f8b4b4; padding: 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 500;">';
            echo htmlspecialchars($_SESSION['error_message']);
            echo '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="card">
            <form action="../controllers/crear_cliente.php" method="POST">
                
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido</label>
                        <input type="text" name="apellido" id="apellido" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" name="telefono" id="telefono" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="email" name="correo" id="correo" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección (Calle y Colonia)</label>
                        <input type="text" name="direccion" id="direccion" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="num_direccion">Número (Ext/Int)</label>
                        <input type="text" name="num_direccion" id="num_direccion" class="form-control" required>
                    </div>
                    
                    <div class="form-group form-col-span-2">
                        <label for="id_empresa">Empresa (Compañía a la que pertenece)</label>
                        <select name="id_empresa" id="id_empresa" class="form-control" required>
                            <option value="">— Selecciona una empresa —</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa['id_empresa']; ?>">
                                    <?php echo htmlspecialchars($empresa['nombre_empresa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <a href="<?php echo ($user_rol == 'admin') ? '../admin/clientes.php' : '../trabajador/dashboard.php'; ?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Crear cliente</button>
                    </div>

                </div>
            </form>
        </div>
    </main>
</body>
</html>