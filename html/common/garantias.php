<?php
session_start();

// 1. Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}

// 2. Guardamos el rol y el ID del trabajador
$user_rol = $_SESSION['user_rol'];
$id_trabajador_actual = $_SESSION['user_id'];

// --- Si el script llega aquí, está validado ---
require '../config/conexion.php';


// 3. Ejecutamos la consulta "inteligente" para OBTENER LAS GARANTÍAS
try {
    
    // Consulta 1: Base de garantías de REPARACIONES
    $sql_reparaciones_base = "SELECT
                                g.id_garantia, g.fecha_inicio, g.fecha_fin,
                                c.nombre, c.apellido,
                                CONCAT(eq.marca, ' ', eq.modelo) AS item_descripcion,
                                r.id_reparacion AS folio_origen,
                                'Reparación' AS tipo_origen,
                                r.id_trabajador
                            FROM garantia g
                            JOIN reparacion r ON g.id_reparacion = r.id_reparacion
                            JOIN equipo eq ON r.id_equipo = eq.id_equipo
                            JOIN cliente c ON eq.id_cliente = c.id_cliente
                            WHERE g.id_reparacion IS NOT NULL"; // Solo garantías de reparaciones

    // Consulta 2: Base de garantías de VENTAS
    $sql_ventas_base = "SELECT
                            g.id_garantia, g.fecha_inicio, g.fecha_fin,
                            c.nombre, c.apellido,
                            (SELECT GROUP_CONCAT(p.nombre SEPARATOR ', ') FROM detalle_venta dv JOIN producto p ON dv.id_producto = p.id_producto WHERE dv.id_venta = v.id_venta) AS item_descripcion,
                            v.id_venta AS folio_origen,
                            'Venta' AS tipo_origen,
                            v.id_trabajador
                        FROM garantia g
                        JOIN venta v ON g.id_venta = v.id_venta
                        JOIN cliente c ON v.id_cliente = c.id_cliente
                        WHERE g.id_venta IS NOT NULL"; // Solo garantías de ventas
    
    $params = []; // Array para los parámetros

    // === LÓGICA INTELIGENTE ===
    if ($user_rol === 'trabajador') {
        // Añadimos el AND DENTRO de las consultas base
        $sql_reparaciones_base .= " AND r.id_trabajador = ?";
        $sql_ventas_base .= " AND v.id_trabajador = ?";
        $params[] = $id_trabajador_actual; // ID para la consulta de reparaciones
        $params[] = $id_trabajador_actual; // ID para la consulta de ventas
    }

    // Unimos las dos consultas, AHORA envolviéndolas en paréntesis
    $sql_final = "(" . $sql_reparaciones_base . ") UNION (" . $sql_ventas_base . ") ORDER BY fecha_fin DESC";
    
    $stmt_garantias = $pdo->prepare($sql_final);
    $stmt_garantias->execute($params);
    $garantias = $stmt_garantias->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Garantías - Sistema de Administración</title>
    <style>
        /* (Todo tu CSS idéntico al de dashboard.php va aquí) */
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
        /* (Estilos de .sidebar, .main-content, .card, etc.) */
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
        .main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .main-header h1 { font-size: 28px; margin: 0; }
        .card { background-color: var(--color-blanco); border: 1px solid var(--color-borde); border-radius: 8px; padding: 24px; }
        .card-header { font-size: 18px; font-weight: 600; margin: 0 0 20px 0; }
        
        /* (Estilos de .data-table) */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 15px; border: 1px solid var(--color-borde); text-align: left; font-size: 14px; max-width: 300px; word-wrap: break-word; }
        .data-table th { background-color: var(--color-fondo); font-weight: 600; }
        .data-table tbody tr:nth-of-type(even) { background-color: var(--color-fondo); }
        .data-table tbody tr:hover { background-color: #e9ecef; }
        
        /* Estilo para el tipo de origen */
        .tipo-reparacion { background-color: #e7f1ff; color: #0d6efd; padding: 3px 6px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .tipo-venta { background-color: #ecfdf5; color: #065f46; padding: 3px 6px; border-radius: 4px; font-size: 12px; font-weight: 600; }
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
                    <li><a href="../admin/clientes.php"><span>👥</span> Clientes</a></li>
                    <li><a href="../admin/inventario.php"><span>🧾</span> Inventario</a></li>
                    <li><a href="garantias.php" class="active"><span>🛡️</span> Garantías</a></li>
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
                    <li><a href="garantias.php" class="active"><span>🛡️</span> Garantías</a></li>
                    <li><a href="../common/nuevo_cliente.php"><span>👥</span> Nuevo Cliente</a></li>
                </ul>
            </nav>
        <?php endif; ?>
        
        <div class="sidebar-footer">
            <a href="/controllers/logout.php"><span>🚪</span> Cerrar sesión</a>
        </div>
    </aside>

    <main class="main-content">

        <header class="main-header">
            <h1><?php echo ($user_rol == 'admin') ? 'Historial de Garantías' : 'Mis Garantías'; ?></h1>
        </header>

        <section class="garantias-content">
            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Folio Garantía</th>
                            <th>Cliente</th>
                            <th>Item (Producto/Equipo)</th>
                            <th>Tipo</th>
                            <th>Folio Origen</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($garantias)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No se encontraron garantías registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($garantias as $garantia): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($garantia['id_garantia']); ?></td>
                                    <td><?php echo htmlspecialchars($garantia['nombre'] . ' ' . $garantia['apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($garantia['item_descripcion'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="tipo-<?php echo strtolower($garantia['tipo_origen']); ?>">
                                            <?php echo htmlspecialchars($garantia['tipo_origen']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($garantia['folio_origen']); ?></td>
                                    <td><?php echo htmlspecialchars($garantia['fecha_inicio']); ?></td>
                                    <td><?php echo htmlspecialchars($garantia['fecha_fin']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
            </div>
        </section>

    </main>

</body>
</html>