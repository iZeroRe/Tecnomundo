<?php 
session_start();
// Verificar sesion
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}

// Verificar si el rol
if ($_SESSION['user_rol'] !== 'admin') {
    header('Location: ../trabajador/dashboard.php');
    exit;
}
require '../config/conexion.php';

// Consultas

try{
    //Lista de trabajadores
    $sql_lista = "SELECT
                    t.id_trabajador,
                    t.nombre,
                    t.apellido,
                    t.telefono,
                    t.correo,
                    t.especialidad,
                    t.rol,
                    e.nombre_empresa
                FROM trabajador AS t
                LEFT JOIN empresa AS e ON t.id_empresa = e.id_empresa
                ORDER BY t.apellido ASC, t.nombre ASC";    
$stmt_lista = $pdo->query($sql_lista);
$trabajadores = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

    //Segunda consulta para ver el join (vista)
    $sql_top = "SELECT nombre, apellido, total_ordenes
                FROM vista_ranking_tecnicos LIMIT 3"; //Podiramos quitar el limite si queremos una tabla general
    $stmt_top = $pdo->query($sql_top);
    $top_tecnicos = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
}
catch (\PDOException $e){
    echo "Error al consultar la base de datos: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabajadores - Sistema de Administración</title>
    <style>
        /* CCS de dashboard.php  */
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
        
        .btn-edit {
            display: inline-block;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 500;
            color: #fff;
            background-color: #0d6efd;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
        }
        .btn-edit:hover {
            background-color: #0b5ed7;
        }
        
        /* CCS para trabajadores.php */
        
        /* Layout de 2 columnas: Tabla (más ancha) y Tarjeta (más angosta) */
        .page-layout {
            display: flex;
            gap: 24px;
        }
        .main-column {
            flex: 3; /* Ocupa 3 partes del espacio */
        }
        .sidebar-column {
            flex: 1; /* Ocupa 1 parte del espacio */
            min-width: 250px;
        }
        
        /* Estilos para la lista de "Top Técnicos" */
        .top-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .top-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background-color: var(--color-fondo);
            border: 1px solid var(--color-borde);
            border-radius: 6px;
        }
        .top-list-item .name {
            font-weight: 500;
            color: var(--color-texto);
        }
        .top-list-item .count {
            font-size: 12px;
            font-weight: 600;
            background-color: #e9ecef; /* Un gris más oscuro */
            padding: 4px 8px;
            border-radius: 4px;
            color: var(--color-texto-secundario);
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
                <li><a href="dashboard.php"><span>📊</span> Tablero</a></li>
                <li><a href="../common/ordenes.php"><span>📦</span> Órdenes</a></li>
                <li><a href="ventas.php"><span>💰</span> Ventas</a></li>
                <li><a href="clientes.php"><span>👥</span> Clientes</a></li>
                <li><a href="inventario.php"><span>🧾</span> Inventario</a></li>
                <li><a href="../common/garantias.php"><span>🛡️</span> Garantías</a></li>
                <li><a href="proveedores.php"><span>🚚</span> Proveedores</a></li>
                <li><a href="trabajadores.php" class="active"><span>👨</span> Trabajadores</a></li>
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
            <h1>Gestión de Trabajadores</h1>
            <div class="header-actions">
                <div class="search-bar">
                    <input type="text" placeholder="Buscar por nombre...">
                </div>
                <a href="nuevo_trabajador.php" class="btn-primary">+ Nuevo Trabajador</a>
            </div>
        </header>
        <?php 
        if (isset($_SESSION['success_message'])) {
            echo '<div style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 500;">';
            echo htmlspecialchars($_SESSION['success_message']);
            echo '</div>';
            unset($_SESSION['success_message']); // Limpiamos el mensaje
        }
        ?>
        <section class="page-layout">

            <div class="main-column">
                <div class="card">
                    <h2 class="card-header">Listado de Trabajadores</h2>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Especialidad</th>
                                <th>Rol</th>
                                <th>Empresa</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($trabajadores)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No se encontraron trabajadores.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($trabajadores as $trabajador): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($trabajador['id_trabajador']); ?></td>
                                        <td><?php echo htmlspecialchars($trabajador['nombre'] . ' ' . $trabajador['apellido']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($trabajador['telefono']); ?><br>
                                            <?php echo htmlspecialchars($trabajador['correo']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($trabajador['especialidad']); ?></td>
                                        <td><?php echo htmlspecialchars($trabajador['rol']); ?></td>
                                        <td><?php echo htmlspecialchars($trabajador['nombre_empresa']); ?></td>
                                        <td>
                                            <a href="editar_trabajador.php?id=<?php echo $trabajador['id_trabajador']; ?>" class="btn-edit">Editar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                </div>
            </div>

            <div class="sidebar-column">
                <div class="card">
                    <h2 class="card-header">Top Técnicos</h2>
                    
                    <ul class="top-list">
                        <?php if (empty($top_tecnicos)): ?>
                            <li style="text-align: center; color: var(--color-texto-secundario); font-size: 14px;">
                                No hay datos de reparaciones.
                            </li>
                        <?php else: ?>
                            <?php foreach ($top_tecnicos as $tecnico): ?>
                                <li class="top-list-item">
                                    <span class="name"><?php echo htmlspecialchars($tecnico['nombre'] . ' ' . $tecnico['apellido']); ?></span>
                                    <span class="count"><?php echo $tecnico['total_ordenes']; ?> órdenes</span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>

                </div>
            </div>

        </section>

    </main>

</body>
</html>