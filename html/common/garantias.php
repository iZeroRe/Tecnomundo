<?php
session_start();

// 1. Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php'); // Redirigir a login si no está logueado
    exit;
}


// --- Si el script llega aquí, es un admin validado ---
require '../config/conexion.php';


// 3. Ejecutamos la consulta para OBTENER LAS GARANTÍAS
try {
    
    $sql = "SELECT
                g.id_garantia,
                g.fecha_inicio,
                g.fecha_fin,
                g.condiciones,
                
                -- Determinar el tipo de servicio
                CASE
                    WHEN g.id_reparacion IS NOT NULL THEN CONCAT('Reparación #', g.id_reparacion)
                    WHEN g.id_venta IS NOT NULL THEN CONCAT('Venta #', g.id_venta)
                    ELSE 'N/A'
                END AS servicio_referencia,
                
                -- Obtener el nombre del cliente desde la ruta que no sea NULL
                COALESCE(cr.nombre, cv.nombre) AS cliente_nombre,
                COALESCE(cr.apellido, cv.apellido) AS cliente_apellido
                
            FROM garantia AS g
            
            -- Camino para garantías de Reparación
            LEFT JOIN reparacion AS r ON g.id_reparacion = r.id_reparacion
            LEFT JOIN equipo AS e ON r.id_equipo = e.id_equipo
            LEFT JOIN cliente AS cr ON e.id_cliente = cr.id_cliente
            
            -- Camino para garantías de Venta
            LEFT JOIN venta AS v ON g.id_venta = v.id_venta
            LEFT JOIN cliente AS cv ON v.id_cliente = cv.id_cliente
            
            ORDER BY g.fecha_fin DESC"; // Mostrar las que expiran más pronto
            
    $stmt_garantias = $pdo->query($sql);
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
            --alerta-baja: #fffbeb;
            --alerta-critica: #fff8e1;
            --status-active: #28a745; /* Verde */
            --status-expired: #6c757d; /* Gris */
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
            /* Para la columna de condiciones */
            max-width: 300px;
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
        
        /* --- NUEVO: Estilos para el Estado --- */
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
            color: #fff;
            white-space: nowrap; /* Evita que "Expirada" se parta */
        }
        .status-active {
            background-color: var(--status-active);
        }
        .status-expired {
            background-color: var(--status-expired);
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
                <li><a href="javascript:history.back()">🔙 Volver atrás</a></li>

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
            <h1>Gestión de Garantías</h1>
            <div class="header-actions">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar por folio, cliente...">
                </div>
            </div>
        </header>

        <section class="garantias-content">
            <div class="card">
                <h2 class="card-header">Historial de Garantías</h2>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Estado</th>
                            <th>Referencia</th>
                            <th>Cliente</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Condiciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $hoy = new DateTime(); // Obtenemos la fecha de hoy una sola vez ?>
                        
                        <?php if (empty($garantias)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No se encontraron garantías registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($garantias as $garantia): ?>
                                <?php
                                    // Comparamos la fecha de hoy con la fecha de fin
                                    $fecha_fin = new DateTime($garantia['fecha_fin']);
                                    $estado_info = ($hoy > $fecha_fin) 
                                        ? ['texto' => 'Expirada', 'clase' => 'status-expired']
                                        : ['texto' => 'Activa', 'clase' => 'status-active'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($garantia['id_garantia']); ?></td>
                                    <td>
                                        <span class="status <?php echo $estado_info['clase']; ?>">
                                            <?php echo $estado_info['texto']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($garantia['servicio_referencia']); ?></td>
                                    <td><?php echo htmlspecialchars($garantia['cliente_nombre'] . ' ' . $garantia['cliente_apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($garantia['fecha_inicio']); ?></td>
                                    <td><?php echo htmlspecialchars($garantia['fecha_fin']); ?></td>
                                    <td><?php echo htmlspecialchars($garantia['condiciones']); ?></td>
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