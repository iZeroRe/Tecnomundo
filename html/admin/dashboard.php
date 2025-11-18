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


// 2. Ejecutamos todas las consultas para las tarjetas
try {
    // Tarjeta 1: Órdenes activas (Contamos el total de reparaciones) //(Prueba de consulta, dependiendo de la fecha activo o no) anterior  $stmt_ordenes = $pdo->query("SELECT COUNT(*) FROM reparacion");
    $stmt_ordenes = $pdo->query("SELECT COUNT(*) FROM reparacion WHERE fecha_terminado >= CURDATE() OR fecha_terminado IS NULL");
    $total_ordenes = $stmt_ordenes->fetchColumn();

    // Tarjeta 2: Por entregar hoy (Reparaciones con fecha_terminado = hoy)
    $stmt_hoy = $pdo->query("SELECT COUNT(*) FROM reparacion WHERE fecha_terminado = CURDATE()");
    $total_hoy = $stmt_hoy->fetchColumn();

    // Tarjeta 3: Reclamos (Contamos el total de garantías emitidas) //(Pueba) anterior  $stmt_reclamos = $pdo->query("SELECT COUNT(*) FROM garantia");
    $stmt_reclamos = $pdo->query("SELECT COUNT(*) FROM garantia WHERE fecha_fin >= CURDATE()");
    $total_reclamos = $stmt_reclamos->fetchColumn();

    // Tarjeta 4: Ingresos del mes (Sumamos pagos de este mes y año)
    $stmt_ingresos = $pdo->query("SELECT SUM(monto_pago) as total_mes FROM pago WHERE MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())");
    $ingresos_mes = $stmt_ingresos->fetch();
    $total_ingresos = $ingresos_mes['total_mes'] ?? 0; // Usamos ?? 0 por si no hay pagos

    //(Prueba de graficas)
// Gráfica 1: Actividad Semanal (Órdenes creadas en los últimos 7 días)
    $stmt_semanal = $pdo->query("
        SELECT 
            DATE(fecha_ingreso) as dia, 
            COUNT(*) as total 
        FROM reparacion 
        WHERE fecha_ingreso >= CURDATE() - INTERVAL 6 DAY
        GROUP BY dia
        ORDER BY dia ASC
    ");
    $actividad_semanal = $stmt_semanal->fetchAll(PDO::FETCH_ASSOC);

    // Procesar datos para que siempre haya 7 días (Lunes, Martes, ...)
    $labels_semana = [];
    $data_semana = [];
    // Un array asociativo para guardar los totales por fecha
    $datos_por_fecha = array_column($actividad_semanal, 'total', 'dia');

    for ($i = 6; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        // Usamos 'D d' para formato "Lun 04", "Mar 05", etc.
        $labels_semana[] = date('D d', strtotime($fecha)); 
        // Si no hubo registros en esa fecha, usamos 0
        $data_semana[] = $datos_por_fecha[$fecha] ?? 0;
    }


    // Gráfica 2: Distribución de Productos (Repuestos vs Accesorios vs Dispositivos)
    $stmt_distribucion = $pdo->query("
        SELECT tipo_producto, COUNT(*) as total
        FROM producto
        GROUP BY tipo_producto
    ");
    $distribucion_productos = $stmt_distribucion->fetchAll(PDO::FETCH_ASSOC);
    
    // Separamos las etiquetas (labels) y los datos (data) para Chart.js
    $labels_productos = array_column($distribucion_productos, 'tipo_producto');
    $data_productos = array_column($distribucion_productos, 'total');
    // --- Final de las graficas (Prueba) ---


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
    <title>Sistema de Administración</title>
    <style>
        /* CCS general */
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
            text-decoration: none;
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> (Prueba)

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
                <li><a href="../common/ventas.php"><span>💰</span> Ventas</a></li>
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
                    <p>Garantias Activas</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="icon">💵</div>
                <div class="info">
                    <h3>$<?php echo number_format($total_ingresos, 2); ?></h3>
                    <p>Ingresos mes</p>
                </div>
            </div>

        </section>

        <section class="dashboard-body">

            <div class="main-chart-area">
                <div class="card">
                    <h2 class="card-header">Actividad semanal</h2>
                    <div class="chart-container" style="position: relative; height:350px;"> <!-- Prueba de graficas -->
                        <canvas id="actividadSemanalChart"></canvas>
                            </div>
                </div>
            </div>

            <div class="right-sidebar">
                
                <h2 class="card-header">Distribución de productos</h2>
                        <div class="chart-container" style="position: relative; height:200px;"> <!-- Prueba de grafica -->
                             <canvas id="distribucionProductosChart"></canvas>
                        </div>
                
                <div class="card">
                    <h2 class="card-header">Alertas de inventario</h2>
                    <ul class="alert-list">
  
    <ul class="alert-list">
     <?php
    /*
    * Consulta súper rápida.
    * Solo pedimos los productos que NO TENGAN NULL
    * en la columna 'nivel_alerta'.
    */
    $stmt_stock = $pdo->query("SELECT nombre, stock, min_stock, nivel_alerta
                              FROM producto
                              WHERE nivel_alerta IS NOT NULL
                              ORDER BY stock ASC");
    
    while ($item = $stmt_stock->fetch()) {
        
        // La clase ('low' o 'critical') ya viene calculada
        // desde la base de datos.
        $alert_class = $item['nivel_alerta'];
        
        echo '<li class="alert-item ' . $alert_class . '">';
        echo '  <div class="details">';
        echo '      <p>' . htmlspecialchars($item['nombre']) . '</p>';
        echo '  </div>';
        echo '  <div class="stock">';
        echo '      <div class="current">Stock: ' . $item['stock'] . '</div>';
        echo '      <div class="min">Min: ' . $item['min_stock'] . '</div>';
        echo '  </div>';
        echo '</li>';
    }
    ?>
</ul>

                </div>
            </div>

        </section>

    </main>
   <script>
    // Usamos 'DOMContentLoaded' para asegurarnos de que el HTML este cargado
    document.addEventListener('DOMContentLoaded', function () {
        
        // Grafica 1
        
        // Obtenemos el 'contexto' del canvas (el lienzo donde vamos a dibujar)
        const ctxSemanal = document.getElementById('actividadSemanalChart');

        // Verificamos que el elemento exista antes de crear la grafica
        if (ctxSemanal) {
            new Chart(ctxSemanal, {
                type: 'line', // Tipo de grafica
                data: {
                    // Aquí 'inyectamos' los datos desde PHP a JavaScript
                    // json_encode convierte el array de PHP en un array de JS
                    labels: <?php echo json_encode($labels_semana); ?>,
                    datasets: [{
                        label: 'Órdenes Creadas',
                        data: <?php echo json_encode($data_semana); ?>,
                        fill: false,
                        borderColor: '#0d6efd', // Usamos tu color primario
                        tension: 0.1 // Suaviza la línea
                    }]
                },
                options: {
                    responsive: true, // Hace que la gráfica se ajuste al contenedor
                    maintainAspectRatio: false, // Permite que la altura sea personalizada
                    scales: {
                        y: {
                            beginAtZero: true, 
                            max: 10 // El eje Y empieza en 0 y termian en 10
                        }
                    }
                }
            });
        }

        // Grafica 2
        
        const ctxDistribucion = document.getElementById('distribucionProductosChart');
        
        if (ctxDistribucion) {
            new Chart(ctxDistribucion, {
                type: 'doughnut', // Tipo de grafica de dona
                data: {
                    // Inyectamos los datos de productos
                    labels: <?php echo json_encode($labels_productos); ?>,
                    datasets: [{
                        label: 'Cantidad de Productos',
                        data: <?php echo json_encode($data_productos); ?>,
                        backgroundColor: [ // Colores para cada segmento
                            '#0d6efd', // Azul
                            '#6c757d', // Gris
                            '#f59e0b'  // Amarillo
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        }
    });
    </script>
</body>
</html>

