<?php
require '../config/conexion.php'; 
session_start();

// Verificación de Sesion
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}

//  Verificar si el rol es 'trabajador'
if ($_SESSION['user_rol'] !== 'trabajador') {
    header('Location: ../admin/dashboard.php');
    exit;
}
$id_trabajador_actual = $_SESSION['user_id'];

// Ejecutamos todas las consultas personalizadas dependiendo del id
try {
    // 1 Ordenes activas 
    $sql_ordenes = "SELECT COUNT(*) FROM reparacion 
                    WHERE id_trabajador = ? 
                    AND (fecha_terminado >= CURDATE() OR fecha_terminado IS NULL)";
    $stmt_ordenes = $pdo->prepare($sql_ordenes);
    $stmt_ordenes->execute([$id_trabajador_actual]);
    $total_ordenes = $stmt_ordenes->fetchColumn();

    // 2 Por entregar hoy 
    $sql_hoy = "SELECT COUNT(*) FROM reparacion 
                WHERE id_trabajador = ? AND fecha_terminado = CURDATE()";
    $stmt_hoy = $pdo->prepare($sql_hoy);
    $stmt_hoy->execute([$id_trabajador_actual]);
    $total_hoy = $stmt_hoy->fetchColumn();
    
    // 3 Garantias
    $sql_reclamos = "SELECT COUNT(g.id_garantia) 
                     FROM garantia AS g
                     JOIN reparacion AS r ON g.id_reparacion = r.id_reparacion
                     WHERE r.id_trabajador = ?";
    $stmt_reclamos = $pdo->prepare($sql_reclamos);
    $stmt_reclamos->execute([$id_trabajador_actual]);
    $total_reclamos = $stmt_reclamos->fetchColumn();

    // Gráfica 1: Actividad Semanal (SOLO de este trabajador)
    $sql_semanal = "SELECT 
                        DATE(fecha_ingreso) as dia, 
                        COUNT(*) as total 
                    FROM reparacion 
                    WHERE fecha_ingreso >= CURDATE() - INTERVAL 6 DAY
                    AND id_trabajador = ?
                    GROUP BY dia
                    ORDER BY dia ASC";
    $stmt_semanal = $pdo->prepare($sql_semanal);
    $stmt_semanal->execute([$id_trabajador_actual]);
    $actividad_semanal = $stmt_semanal->fetchAll(PDO::FETCH_ASSOC);

    // (Procesamiento de datos de la gráfica - esto está bien)
    $labels_semana = [];
    $data_semana = [];
    $datos_por_fecha = array_column($actividad_semanal, 'total', 'dia');
    for ($i = 7; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        $labels_semana[] = date('D d', strtotime($fecha)); 
        $data_semana[] = $datos_por_fecha[$fecha] ?? 0;
    }

    // NUEVA CONSULTA: Mis ordenes pendientes usando join
    $sql_pendientes = "SELECT
                            r.id_reparacion,
                            c.nombre AS cliente_nombre,
                            c.apellido AS cliente_apellido,
                            e.marca,
                            e.modelo
                       FROM reparacion AS r
                       JOIN equipo AS e ON r.id_equipo = e.id_equipo 
                       JOIN cliente AS c ON e.id_cliente = c.id_cliente
                       WHERE r.id_trabajador = ?
                       AND (r.fecha_terminado >= CURDATE() OR r.fecha_terminado IS NULL)
                       ORDER BY r.fecha_terminado ASC
                       LIMIT 10"; // limitamos a 10
    $stmt_pendientes = $pdo->prepare($sql_pendientes);
    $stmt_pendientes->execute([$id_trabajador_actual]);
    $ordenes_pendientes = $stmt_pendientes->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    echo "Error al consultar la base de datos: " . $e->getMessage(); //Erro
    die();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Administración</title>
    <style>
        /* (Todo tu CSS de la respuesta anterior va aquí) */
        /* --- Reseteo Básico y Fuentes --- */
        :root {
            --color-primario: #0d6efd; /* Azul para el botón */
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

        /* --- 1. Menú Lateral (Sidebar) --- */
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

        .sidebar-header {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .sidebar-nav {
            flex-grow: 1;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 10px;
        }

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

        .sidebar-nav a:hover {
            background-color: var(--color-fondo);
            color: var(--color-texto);
        }

        .sidebar-nav a.active {
            background-color: #e7f1ff;
            color: var(--color-primario);
            font-weight: 600;
        }

        .sidebar-nav a span {
            margin-right: 12px;
            font-size: 20px;
        }

        .sidebar-footer a {
            text-decoration: none;
            color: var(--color-texto-secundario);
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 10px 12px;
        }

        /* --- 2. Contenido Principal (Main Content) --- */
        .main-content {
            margin-left: 240px; /* Mismo ancho que el sidebar */
            flex-grow: 1;
            padding: 24px 32px;
        }

        /* --- 2a. Encabezado --- */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .main-header h1 {
            font-size: 28px;
            margin: 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .search-bar {
            position: relative;
        }

        .search-bar input {
            padding: 10px 10px 10px 36px; /* Espacio para el ícono */
            border: 1px solid var(--color-borde);
            border-radius: 6px;
            font-size: 14px;
        }
        
        /* (Aquí se usaría un ícono real, pero '🔍' sirve de placeholder) */
        .search-bar::before {
            content: '🔍';
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-texto-secundario);
        }

        .btn-primary {
            background-color: var(--color-primario);
            color: var(--color-blanco);
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
        }

        /* --- 2b. Tarjetas de Estadísticas (Stats) --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 24px;
        }

        .stat-card {
            background-color: var(--color-blanco);
            border: 1px solid var(--color-borde);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-card .icon {
            font-size: 24px;
            padding: 12px;
            background-color: var(--color-fondo);
            border-radius: 50%;
            color: var(--color-texto-secundario);
            line-height: 1;
        }
        
        .stat-card .info h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: var(--color-texto);
        }
        
        .stat-card .info p {
            margin: 0;
            font-size: 14px;
            color: var(--color-texto-secundario);
        }

        /* --- 2c. Cuerpo del Dashboard (Gráficas y Alertas) --- */
        .dashboard-body {
            display: flex;
            gap: 24px;
        }

        /* Columna Izquierda (Gráfica Principal) */
        .main-chart-area {
            flex: 3; /* Ocupa más espacio */
        }
        
        /* Columna Derecha (Pastel y Alertas) */
        .right-sidebar {
            flex: 1.5; /* Ocupa menos espacio */
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Tarjeta Genérica para Gráficas/Alertas */
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

        /* Marcadores de posición para las gráficas */
        .chart-placeholder {
            width: 100%;
            height: 350px;
            display: grid;
            place-items: center;
            background-color: var(--color-fondo);
            border: 1px dashed var(--color-borde);
            border-radius: 6px;
            color: var(--color-texto-secundario);
        }
        
        .pie-chart-placeholder {
            width: 100%;
            height: 200px;
            display: grid;
            place-items: center;
            background-color: var(--color-fondo);
            border: 1px dashed var(--color-borde);
            border-radius: 6px;
            color: var(--color-texto-secundario);
        }


        /* --- 2d. Alertas de Inventario --- */
        .alert-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .alert-item {
            padding: 12px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Colores de alerta */
        .alert-item.low {
            background-color: var(--alerta-baja);
            border-left: 4px solid #f59e0b; /* Amarillo */
        }
        
        .alert-item.critical {
            background-color: var(--alerta-critica);
            border-left: 4px solid #ef4444; /* Rojo */
        }

        .alert-item .details p {
            margin: 0;
            font-weight: 500;
            font-size: 14px;
        }
        
        .alert-item .details .sku {
            font-size: 12px;
            color: var(--color-texto-secundario);
            font-weight: 400;
        }

        .alert-item .stock {
            text-align: right;
            font-size: 14px;
        }
        
        .alert-item .stock .current {
            font-weight: 600;
        }
        
        .alert-item .stock .min {
            font-size: 12px;
            color: var(--color-texto-secundario);
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
        
    <aside class="sidebar">
        <div class="sidebar-header">
            Trabajador
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li><a href="#" class="active"><span>📊</span> Tablero</a></li>
                <li><a href="../common/ventas.php"><span>💰</span> Ventas</a></li>
                <li><a href="../common/ordenes.php"><span>📦</span> Órdenes</a></li>
                <li><a href="../common/garantias.php"><span>🛡️</span> Garantías</a></li>
                <li><a href="../common/nuevo_cliente.php"><span>👥</span> Nuevo Cliente</a></li>
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
            <h1>Sistema de administración</h1>
            <div class="header-actions">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar orden, cliente, IMEI...">
                </div>
                <a href="../common/nueva_orden.php" class="btn-primary">+ Nueva orden</a>
            </div>
        </header>

        <section class="stats-grid">
            
            <div class="stat-card">
                <div class="icon">⏱️</div>
                <div class="info">
                    <h3><?php echo $total_ordenes; ?></h3>
                    <p>Órdenes activas</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="icon">🚚</div>
                <div class="info">
                    <h3><?php echo $total_hoy; ?></h3>
                    <p>Por entregar hoy</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="icon">🛡️</div>
                <div class="info">
                    <h3><?php echo $total_reclamos; ?></h3>
                    <p>Garantias</p>
                </div>
            </div>

        </section>

</section>

        <section class="dashboard-body">

            <div class="main-chart-area">
                <div class="card">
                    <h2 class="card-header">Mi Actividad semanal</h2>
                    <div class="chart-container" style="position: relative; height:350px;">
                        <canvas id="actividadSemanalChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="right-sidebar">
                <div class="card">
                    <h2 class="card-header">Mis Órdenes Pendientes</h2>
                    
                    <ul class="pending-list">
                        <?php if (empty($ordenes_pendientes)): ?>
                            <li style="text-align: center; color: var(--color-texto-secundario); font-size: 14px;">
                                ¡No tienes órdenes pendientes!
                            </li>
                        <?php else: ?>
                            <?php foreach ($ordenes_pendientes as $orden): ?>
                                <li class="pending-item">
                                    <div class="details">
                                        <p><?php echo htmlspecialchars($orden['cliente_nombre'] . ' ' . $orden['cliente_apellido']); ?></p>
                                        <span class="subtext"><?php echo htmlspecialchars($orden['marca'] . ' ' . $orden['modelo']); ?></span>
                                    </div>
                                    <span class="folio">#<?php echo $orden['id_reparacion']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>

                </div>
            </div>

        </section>

    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        
        //Grafica 1 Activdad semanal
        const ctxSemanal = document.getElementById('actividadSemanalChart');
        if (ctxSemanal) {
            new Chart(ctxSemanal, {
                type: 'line',
                data: {
                    // Usamos los datos PHP que ahora estan personalizados
                    labels: <?php echo json_encode($labels_semana); ?>,
                    datasets: [{
                        label: 'Órdenes Creadas',
                        data: <?php echo json_encode($data_semana); ?>,
                        fill: false,
                        borderColor: '#0d6efd',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max :10 } }
                }
            });
        }
    });
    </script>
</body>
</html>