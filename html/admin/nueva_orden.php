<?php
session_start();

// 1 Verificacion de inicio de sesion
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}


require '../config/conexion.php';

//Consultas para el formulario
try{
    //1 Listas Clientes
    $stmt_clientes = $pdo->query("SELECT id_cliente, nombre, apellido FROM cliente ORDER BY nombre ASC");
    $clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
    
    //2 Lista de productos
    $stmt_piezas = $pdo->query("SELECT id_producto, nombre, precio FROM producto WHERE tipo_producto = 'repuesto' ORDER BY nombre ASC");
    $piezas = $stmt_piezas->fetchAll(PDO::FETCH_ASSOC);
}
catch (\PDOException $e) {
    // Manejo simple de errores
    echo "Error al consultar la base de datos: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Orden</title>
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

        .main-header h1 {
            font-size: 28px;
            margin: 0;
            margin-bottom: 24px;
        }

        /* Tarjeta Genérica (la usaremos para el form) */
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

        /* --- ESTILOS NUEVOS PARA EL FORMULARIO --- */
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Dos columnas */
            gap: 20px 24px; /* Espacio entre filas y columnas */
        }

        /* Para elementos que ocupan las 2 columnas */
        .form-col-span-2 {
            grid-column: span 2;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--color-texto-secundario);
            margin-bottom: 6px;
            font-size: 14px;
        }

        /* Estilo para inputs y selects (como en el login) */
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--color-borde);
            border-radius: 6px; /* Bordes redondeados */
            font-size: 15px;
            box-sizing: border-box; 
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit; /* Hereda la fuente del body */
            background-color: var(--color-blanco);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-primario);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25); /* Sombra de foco */
        }
        
        textarea.form-control {
            min-height: 80px;
            resize: vertical;
        }

        .form-actions {
            grid-column: span 2; /* Ocupa las 2 columnas */
            display: flex;
            justify-content: flex-end; /* Alinea botones a la derecha */
            gap: 12px;
            margin-top: 20px;
        }

        .btn {
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none; /* Para los links <a> */
            transition: background-color 0.2s, color 0.2s;
        }
        
        .btn-primary {
            background-color: var(--color-primario);
            color: var(--color-blanco);
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        
        .btn-secondary {
            background-color: var(--color-fondo);
            color: var(--color-texto-secundario);
            border: 1px solid var(--color-borde);
        }
        .btn-secondary:hover {
            background-color: var(--color-borde);
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
            <h1>Nueva orden de reparación</h1>
        </header>

        <div class="card">
            <form action="../controllers/crear_orden.php" method="POST">
                
                <div class="form-grid">
                    
                    <div class="form-group form-col-span-2">
                        <label for="cliente">Cliente (selecciona de base de datos)</label>
                        <select name="id_cliente" id="cliente" class="form-control" required>
                            <option value="">-- Este select vendrá de la BD --</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id_cliente']; ?>">
                                    <?php echo htmlspecialchars($cliente['nombre']) . ' ' . htmlspecialchars($cliente['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="marca">Marca</label>
                        <input type="text" name="marca" id="marca" class="form-control" placeholder="Ej. Apple, Samsung" required>
                    </div>

                    <div class="form-group">
                        <label for="modelo">Modelo</label>
                        <input type="text" name="modelo" id="modelo" class="form-control" placeholder="Ej. iPhone 13 Pro, Galaxy S22" required>
                    </div>

                    <div class="form-group form-col-span-2">
                        <label for="falla">Falla reportada</label>
                        <textarea name="falla" id="falla" class="form-control" placeholder="Ej. no carga, pantalla rota, etc." required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="pieza">Pieza necesaria</label>
                        <select name="id_producto" id="pieza" class="form-control">
                            <option value="">-- Selecciona una pieza (opcional) --</option>
                            <?php foreach ($piezas as $pieza): ?>
                                <option value="<?php echo $pieza['id_producto']; ?>" data-precio="<?php echo $pieza['precio']; ?>">
                                    <?php echo htmlspecialchars($pieza['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="costo">Costo estimado (MXN)</label>
                        <input type="number" name="costo" id="costo" class="form-control" placeholder="Ej. 1200" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_promesa">Fecha promesa</label>
                        <input type="date" name="fecha_promesa" id="fecha_promesa" class="form-control" required>
                    </div>
                    
                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Orden</button>
                    </div>

                </div>
            </form>
        </div>
    </main>
    
</body>
</html>