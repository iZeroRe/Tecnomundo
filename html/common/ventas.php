<?php
session_start();

//Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}

//Guardamos el rol y el ID del trabajador
$user_rol = $_SESSION['user_rol'];
$id_trabajador_actual = $_SESSION['user_id'];

require '../config/conexion.php';


// 3. Ejecutamos la consulta para OBTENER LAS VENTAS
try {
    $sql_base = "SELECT
                v.id_venta,
                v.fecha_venta,
                v.total,
                c.nombre AS cliente_nombre,
                c.apellido AS cliente_apellido,
                GROUP_CONCAT(p.nombre SEPARATOR ', ') AS productos_vendidos
            FROM venta AS v
            JOIN cliente AS c ON v.id_cliente = c.id_cliente
            LEFT JOIN detalle_venta AS dv ON v.id_venta = dv.id_venta
            LEFT JOIN producto AS p ON dv.id_producto = p.id_producto";
    
    $params = []; // Un array para guardar los parámetros

    // Si el usuario es un trabajador, añadimos un WHERE para filtrar
    if ($user_rol === 'trabajador') {
        $sql_base .= " WHERE v.id_trabajador = ?";
        $params[] = $id_trabajador_actual; // Añadimos el ID al array
    }

    // Agrupamos y ordenamos al final
    $sql_base .= " GROUP BY v.id_venta, v.fecha_venta, v.total, c.nombre, c.apellido
                   ORDER BY v.fecha_venta DESC";
            
    $stmt_ventas = $pdo->prepare($sql_base);
    $stmt_ventas->execute($params); // Ejecutamos con los parámetros
    $ventas = $stmt_ventas->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    echo "Error al consultar la base de datos: " . $e->getMessage();// Manejo simple de errores
    die();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Sistema de Administración</title>
    <style>
        /* (Todo tu CSS idéntico al de dashboard.php va aquí) */
        :root {
            --color-primario: #0d6efd;
            --color-fondo: #f8f9fa;
            --color-blanco: #ffffff;
            --color-texto: #212529;
            --color-texto-secundario: #6c757d;
            --color-borde: #e9ecef;
            --alerta-baja: #fffbeb;
            --alerta-critica: #fff8e1;
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
            border: 1px solid var(--color-borde);
            text-align: left;
            font-size: 14px;
            /* Para la columna de productos */
            max-width: 400px;
            word-wrap: break-word;
        }
        .data-table th {
            background-color: var(--color-fondo);
            font-weight: 600;
        }
        .data-table tbody tr:nth-of-type(even) {
            background-color: var(--color-fondo);
        }
        .data-table tbody tr:hover {
            background-color: #e9ecef;
        }
        /* CSS para boton de pago */
        .acciones-cell {
            display: flex;
            gap: 8px;
        }

        .btn-cobrar {
            display: inline-block;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 500;
            color: #fff;
            background-color: #198754; /* Verde */
            border: none;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
        }
        .btn-cobrar:hover {
            background-color: #157347;
        }

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
                    <li><a href="ventas.php" class="active"><span>💰</span> Ventas</a></li>
                    <li><a href="../admin/clientes.php"><span>👥</span> Clientes</a></li>
                    <li><a href="../admin/inventario.php"><span>🧾</span> Inventario</a></li>
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
                    <li><a href="ventas.php" class="active"><span>💰</span> Ventas</a></li> 
                    <li><a href="ordenes.php"><span>📦</span> Órdenes</a></li>
                    <li><a href="garantias.php"><span>🛡️</span> Garantías</a></li>
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
            <h1><?php echo ($user_rol == 'admin') ? 'Historial de Ventas' : 'Mis Ventas'; ?></h1>
            <div class="header-actions">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar por folio, cliente...">
                </div>
                <a href="venta_nueva.php" class="btn-primary">+ Nueva Venta</a>
            </div>
        </header>

        <?php 
        if (isset($_SESSION['success_message'])) {
            echo '<div style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 500;">';
            echo htmlspecialchars($_SESSION['success_message']);
            echo '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div style="background-color: #fdeded; color: #b91c1c; border: 1px solid #f8b4b4; padding: 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 500;">';
            echo htmlspecialchars($_SESSION['error_message']);
            echo '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <section class="ventas-content">
            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Productos Vendidos</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ventas)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No se encontraron ventas registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($venta['id_venta']); ?></td>
                                    <td><?php echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($venta['productos_vendidos'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($venta['fecha_venta']); ?></td>
                                    <td>$<?php echo number_format($venta['total'], 2); ?></td>
                                    <td class="acciones-cell">
                                        <a href="../controllers/registrar_pago_venta.php?venta_id=<?php echo $venta['id_venta']; ?>" 
                                           class="btn-cobrar">
                                           Cobrar
                                        </a>
                                    </td>
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