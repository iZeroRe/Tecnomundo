<?php
session_start();
require '../config/conexion.php'; // Asegúrate que la ruta a tu conexión es correcta

// --- Bloque de Seguridad ---
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}
if ($_SESSION['user_rol'] !== 'admin') {
    header('Location: ../trabajador/dashboard.php');
    exit;
}
// --- Fin Bloque de Seguridad ---

// 1. OBTENER Y VALIDAR EL ID
$id = $_GET['id'] ?? 0;
if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
    die("ID de proveedor no válido.");
}

// 2. CONSULTAR DATOS DEL PROVEEDOR
$stmt_prov = $pdo->prepare("SELECT * FROM proveedor WHERE id_proveedor = ?");
$stmt_prov->execute([$id]);
$proveedor = $stmt_prov->fetch(PDO::FETCH_ASSOC);

if (!$proveedor) {
    die("Proveedor no encontrado.");
}

// 3. Consultar las empresas para el dropdown
$stmt_empresas = $pdo->query("SELECT id_empresa, nombre_empresa FROM empresa ORDER BY nombre_empresa");
$empresas = $stmt_empresas->fetchAll(PDO::FETCH_ASSOC);

// 4. Variables para el formulario (con datos existentes)
$nombre = $proveedor['nombre'];
$telefono = $proveedor['telefono'];
$correo = $proveedor['correo'];
$direccion = $proveedor['direccion'];
$pieza_accesorio = $proveedor['pieza_accesorio'];
$id_empresa_sel = $proveedor['id_empresa'];
$modo = 'editar';
$titulo_pagina = 'Editar Proveedor';
$action_url = 'proveedor_guardar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?></title>
    <style>
        :root {
            --color-primario: #0d6efd;
            --color-fondo: #f8f9fa;
            --color-blanco: #ffffff;
            --color-texto: #212529;
            --color-texto-secundario: #6c757d;
            --color-borde: #e9ecef;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; margin: 0; background-color: var(--color-fondo); color: var(--color-texto); display: flex; }
        .sidebar { width: 240px; background-color: var(--color-blanco); border-right: 1px solid var(--color-borde); height: 100vh; display: flex; flex-direction: column; padding: 24px; box-sizing: border-box; position: fixed; left: 0; top: 0; }
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
        .card-header { font-size: 18px; font-weight: 600; margin: 0 0 20px 0; }
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
        <div class="sidebar-header">Admin</div>
          <nav class="sidebar-nav">
            <ul>
                <li><a href="javascript:history.back()">🔙 Volver atrás</a></li>

            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="/controllers/logout.php"><span>🚪</span> Cerrar sesión</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h1><?php echo $titulo_pagina; ?></h1>
        </header>

        <div class="card">
            <h2 class="card-header">Datos del Proveedor</h2>
            
            <form action="<?php echo $action_url; ?>" method="POST">
                <input type="hidden" name="id_proveedor" value="<?php echo $id; ?>">

                <div class="form-grid">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre del Proveedor</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($nombre); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="pieza_accesorio">Suministra</label>
                        <select id="pieza_accesorio" name="pieza_accesorio" class="form-control" required>
                            <option value="" disabled>Seleccione un tipo</option>
                            <option value="Pieza" <?php echo ($pieza_accesorio == 'Pieza') ? 'selected' : ''; ?>>Pieza</option>
                            <option value="Accesorio" <?php echo ($pieza_accesorio == 'Accesorio') ? 'selected' : ''; ?>>Accesorio</option>
                            <option value="Pieza y Accesorio" <?php echo ($pieza_accesorio == 'Pieza y Accesorio') ? 'selected' : ''; ?>>Pieza y Accesorio</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" value="<?php echo htmlspecialchars($telefono); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" class="form-control" value="<?php echo htmlspecialchars($correo); ?>" required>
                    </div>

                    <div class="form-group form-col-span-2">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" class="form-control" value="<?php echo htmlspecialchars($direccion); ?>" required>
                    </div>

                    <div class="form-group form-col-span-2">
                        <label for="id_empresa">Empresa (Sucursal)</label>
                        <select id="id_empresa" name="id_empresa" class="form-control" required>
                            <option value="" disabled>Seleccione una empresa</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa['id_empresa']; ?>" <?php echo ($id_empresa_sel == $empresa['id_empresa']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($empresa['nombre_empresa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <a href="proveedores.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Proveedor</button>
                    </div>

                </div>
            </form>
        </div>
    </main>

</body>
</html>