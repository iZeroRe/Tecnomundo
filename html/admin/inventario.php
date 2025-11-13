<?php
session_start();

// 1. Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php'); // Redirigir a login si no está logueado
    exit;
}

// 2. Verificar si el rol es 'admin'
if ($_SESSION['user_rol'] !== 'admin') {
    header('Location: ../trabajador/dashboard.php'); // Si es trabajador, a su dashboard
    exit;
}

// --- Si el script llega aquí, es un admin validado ---
require '../config/conexion.php';


// 3. Ejecutamos la consulta para OBTENER EL INVENTARIO
try {
    // Unimos producto con proveedor para obtener el nombre del proveedor
    $sql = "SELECT
                p.id_producto,
                p.nombre,
                p.marca,
                p.tipo_producto,
                p.precio,
                p.stock,
                p.min_stock,
                p.nivel_alerta,
                prov.nombre AS nombre_proveedor
            FROM producto AS p
            LEFT JOIN proveedor AS prov ON p.id_proveedor = prov.id_proveedor
            ORDER BY p.stock ASC, p.nombre ASC"; // Ordenar por stock (más bajos primero)
            
    $stmt_inventario = $pdo->query($sql);
    $inventario = $stmt_inventario->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    // Manejo simple de errores
    echo "Error al consultar la base de datos: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Sistema de Administración</title>
    <style>
        /* (Todo tu CSS idéntico al de dashboard.php va aquí) */
        :root {
            --color-primario: #0d6efd;
            --color-fondo: #f8f9fa;
            --color-blanco: #ffffff;
            --color-texto: #212529;
            --color-texto-secundario: #6c757d;
            --color-borde: #e9ecef;
            --alerta-baja: #fffbeb;      /* Amarillo pálido */
            --alerta-critica: #fef2f2;  /* Rojo muy pálido */
            --borde-baja: #f59e0b;       /* Amarillo */
            --borde-critica: #ef4444;   /* Rojo */
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            background-color: var(--color-fondo);
            color: var(--color-texto);
            display: flex;
        }
        .sidebar {
            width: 240px;
            background-color: var(--color-blanco);
            border-right: 1px solid var(--color-borde);
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 24px;
            box-sizing: border-box;
            position: fixed;
            left: 0;
            top: 0;
        }
        .sidebar-header { font-size: 24px; font-weight: 600; margin-bottom: 30px; }
        .sidebar-nav { flex-grow: 1; }
        .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav li { margin-bottom: 10px; }
        .sidebar-nav a {
            text-decoration: none;
            color: var(--color-texto-secundario);
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 6px;
            transition: background-color 0.2s, color 0.2s;
        }
        .sidebar-nav a:hover { background-color: var(--color-fondo); color: var(--color-texto); }
        .sidebar-nav a.active {
            background-color: #e7f1ff;
            color: var(--color-primario);
            font-weight: 600;
        }
        .sidebar-nav a span { margin-right: 12px; font-size: 20px; }
        .sidebar-footer a {
            text-decoration: none;
            color: var(--color-texto-secundario);
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 10px 12px;
        }
        .main-content {
            margin-left: 240px;
            flex-grow: 1;
            padding: 24px 32px;
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .main-header h1 { font-size: 28px; margin: 0; }
        .header-actions { display: flex; align-items: center; gap: 16px; }
        .search-bar { position: relative; }
        .search-bar input {
            padding: 10px 10px 10px 36px;
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 14px;
        }
        .search-bar::before {
            content: '🔍';
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-texto-secundario);
        }
        
        /* Contenedor principal para la tabla */
        .card {
            background-color: var(--color-blanco);
            border: 1px solid var(--color-borde);
            border-radius: 8px;
            padding: 24px;
        }
        .card-header {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 20px 0;
        }

        /* --- Estilos de Tabla --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--color-borde); /* Solo borde inferior */
            text-align: left;
            font-size: 14px;
            word-wrap: break-word;
        }
        .data-table th {
            background-color: var(--color-fondo);
            font-weight: 600;
            border-top: 1px solid var(--color-borde);
        }
        .data-table tbody tr:hover {
            background-color: #f1f5f9; /* Azul pálido */
        }
        
        /* --- NUEVO: Estilos para filas de inventario --- */
        /* Estas clases se aplican a la fila <tr> */
        .data-table tbody tr.low {
            background-color: var(--alerta-baja);
            border-left: 4px solid var(--borde-baja);
        }
        .data-table tbody tr.critical {
            background-color: var(--alerta-critica);
            border-left: 4px solid var(--borde-critica);
        }
        .data-table tbody tr.critical td {
             font-weight: 600; /* Texto en negrita para crítico */
        }
    </style>
</head>
<body>
        
    <aside class="sidebar">
        <div class="sidebar-header">
            Admin
        </div>

       <nav class="sidebar-nav">
            <ul>
                <li><a href="#" class="active"><span>📊</span> Tablero</a></li>
                <li><a href="../common/ordenes.php"><span>📦</span> Órdenes</a></li>
                <li><a href="ventas.php"><span>💰</span> Ventas</a></li>
                <li><a href="../admin/clientes.php"><span>👥</span> Clientes</a></li>
                <li><a href="../admin/inventario.php"><span>🧾</span> Inventario</a></li>
                <li><a href="../common/garantias.php"><span>🛡️</span> Garantías</a></li>
                <li><a href="../admin/proveedores.php"><span>🚚</span> Proveedores</a></li>
                <li><a href="../admin/trabajadores.php"><span>👨</span> Trabajadores</a></li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="/controllers/logout.php">
                <span>🚪</span> Cerrar sesión
            </a>
        </div>
    </aside>

    <main class="main-content">

        <header class="main-header">
            <h1>Gestión de Inventario</h1>
            <div class="header-actions">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar por nombre, marca, ID...">
                </div>
                <button class="btn-primary">+ Nuevo Producto</button>
            </div>
        </header>

        <section class="inventario-content">
            <div class="card">
                <h2 class="card-header">Listado de Productos</h2>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Marca</th>
                            <th>Tipo</th>
                            <th>Proveedor</th>
                            <th>Precio</th>
                            <th>Stock / Mín.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventario)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No se encontraron productos registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventario as $item): ?>
                                <tr class="<?php echo htmlspecialchars($item['nivel_alerta']); ?>">
                                    <td><?php echo htmlspecialchars($item['id_producto']); ?></td>
                                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($item['marca']); ?></td>
                                    <td><?php echo htmlspecialchars($item['tipo_producto']); ?></td>
                                    <td><?php echo htmlspecialchars($item['nombre_proveedor'] ?? 'N/A'); ?></td>
                                    <td>$<?php echo number_format($item['precio'], 2); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['stock']); ?></strong> / <?php echo htmlspecialchars($item['min_stock']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                s
            </div>
        </section>

    </main>

</body>
</html>