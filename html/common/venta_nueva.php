<?php
session_start();

// 1. Verificación de permisos (solo logueado)
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}
$user_rol = $_SESSION['user_rol'];

require '../config/conexion.php';

// 2. Consultas para los dropdowns
try {
    // Cargar clientes
    $stmt_clientes = $pdo->query("SELECT id_cliente, nombre, apellido FROM cliente ORDER BY nombre ASC");
    $clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

    // Cargar productos (accesorios y dispositivos)
    $stmt_productos = $pdo->query("SELECT id_producto, nombre, precio, stock FROM producto 
                                   WHERE tipo_producto IN ('accesorio', 'dispositivo') 
                                   ORDER BY nombre ASC");
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertimos los productos a un formato JSON para JavaScript
    $productos_json = json_encode($productos);

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
    <title>Nueva Venta - Sistema de Administración</title>
    
    <style>
        :root {
            --color-primario: #0d6efd;
            --color-fondo: #f8f9fa;
            --color-blanco: #ffffff;
            --color-texto: #212529;
            --color-texto-secundario: #6c757d;
            --color-borde: #e9ecef;
            --color-rojo: #dc3545;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            background-color: var(--color-fondo);
            color: var(--color-texto);
            display: flex;
        }
        /* (Todo tu CSS de .sidebar, .main-content, .card, etc.) */
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
        .form-group { display: flex; flex-direction: column; margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 500; color: var(--color-texto-secundario); margin-bottom: 6px; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--color-borde); border-radius: 6px; font-size: 15px; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; font-family: inherit; background-color: var(--color-blanco); }
        .form-control:focus { outline: none; border-color: var(--color-primario); box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25); }
        .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; }
        .btn { border: none; padding: 10px 16px; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 14px; text-decoration: none; transition: background-color 0.2s, color 0.2s; }
        .btn-primary { background-color: var(--color-primario); color: var(--color-blanco); }
        .btn-primary:hover { background-color: #0b5ed7; }
        .btn-secondary { background-color: var(--color-fondo); color: var(--color-texto-secundario); border: 1px solid var(--color-borde); }
        .btn-secondary:hover { background-color: var(--color-borde); }
        
        /* Estilos para la tabla de productos */
        .product-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .product-table th, .product-table td { padding: 10px; border: 1px solid var(--color-borde); text-align: left; }
        .product-table th { background-color: var(--color-fondo); }
        .product-table td .form-control { width: auto; /* Ajuste para campos en tabla */ }
        .product-table .col-producto { width: 40%; }
        .product-table .col-cantidad { width: 15%; }
        .product-table .col-precio, .product-table .col-subtotal { width: 20%; }
        .product-table .col-accion { width: 5%; text-align: center; }
        
        .btn-danger { background-color: var(--color-rojo); color: white; font-weight: bold; border-radius: 4px; padding: 5px 8px; font-size: 12px; }
        .btn-add-row { background-color: #198754; color: white; border: none; border-radius: 6px; padding: 8px 12px; font-weight: 500; cursor: pointer; }
        
        .total-summary { text-align: right; margin-top: 20px; }
        .total-summary h3 { font-size: 20px; font-weight: 600; }
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
                    <li><a href="ordenes.php"><span>📦</span> Órdenes</a></li>
                    <li><a href="ventas.php"><span>💰</span> Ventas</a></li>
                    <li><a href="garantias.php"><span>🛡️</span> Garantías</a></li>
                </ul>
            </nav>
        <?php endif; ?>
        
        <div class="sidebar-footer">
            <a href="/controllers/logout.php"><span>🚪</span> Cerrar sesión</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h1>Registrar Nueva Venta</h1>
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
            <form action="../controllers/crear_venta.php" method="POST">
                
                <div class="form-group">
                    <label for="cliente">Cliente</label>
                    <select name="id_cliente" id="cliente" class="form-control" required>
                        <option value="">-- Selecciona un cliente --</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id_cliente']; ?>">
                                <?php echo htmlspecialchars($cliente['nombre']) . ' ' . htmlspecialchars($cliente['apellido']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <h3 style="font-weight: 600; font-size: 16px; margin-top: 30px;">Productos</h3>
                <table class="product-table">
                    <thead>
                        <tr>
                            <th class="col-producto">Producto</th>
                            <th class="col-cantidad">Cantidad</th>
                            <th class="col-precio">P. Unitario</th>
                            <th class="col-subtotal">Subtotal</th>
                            <th class="col-accion"></th>
                        </tr>
                    </thead>
                    <tbody id="product-list">
                        </tbody>
                </table>
                <button type="button" id="add-row-btn" class="btn-add-row" style="margin-top: 10px;">+ Añadir Producto</button>
                
                <div class="total-summary">
                    <h3>Total: $<span id="total-display">0.00</span></h3>
                </div>

                <div class="form-actions">
                    <a href="ventas.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar Venta</button>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        // Pasamos los productos de PHP a JavaScript
        const productos = <?php echo $productos_json; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const addRowBtn = document.getElementById('add-row-btn');
            const productListTbody = document.getElementById('product-list');

            // Función para crear una nueva fila
            function createProductRow() {
                const tr = document.createElement('tr');
                
                // Celda 1: Select de Producto
                const tdProducto = document.createElement('td');
                const selectProducto = document.createElement('select');
                selectProducto.name = 'id_producto[]'; // IMPORTANTE: nombre como array
                selectProducto.className = 'form-control';
                selectProducto.required = true;
                let optionsHtml = '<option value="">-- Selecciona --</option>';
                productos.forEach(p => {
                    optionsHtml += `<option value="${p.id_producto}" data-precio="${p.precio}" data-stock="${p.stock}">
                                        ${p.nombre} (Stock: ${p.stock})
                                    </option>`;
                });
                selectProducto.innerHTML = optionsHtml;
                tdProducto.appendChild(selectProducto);
                
                // Celda 2: Cantidad
                const tdCantidad = document.createElement('td');
                const inputCantidad = document.createElement('input');
                inputCantidad.type = 'number';
                inputCantidad.name = 'cantidad[]'; // IMPORTANTE: nombre como array
                inputCantidad.className = 'form-control';
                inputCantidad.value = 1;
                inputCantidad.min = 1;
                inputCantidad.required = true;
                tdCantidad.appendChild(inputCantidad);
                
                // Celda 3: Precio Unitario (oculto/deshabilitado)
                const tdPrecio = document.createElement('td');
                const inputPrecio = document.createElement('input');
                inputPrecio.type = 'text';
                inputPrecio.name = 'precio_unitario[]'; // IMPORTANTE: nombre como array
                inputPrecio.className = 'form-control price-field';
                inputPrecio.readOnly = true;
                inputPrecio.placeholder = '0.00';
                tdPrecio.appendChild(inputPrecio);
                
                // Celda 4: Subtotal
                const tdSubtotal = document.createElement('td');
                tdSubtotal.className = 'subtotal-field';
                tdSubtotal.textContent = '0.00';
                
                // Celda 5: Botón Eliminar
                const tdAccion = document.createElement('td');
                const btnEliminar = document.createElement('button');
                btnEliminar.type = 'button';
                btnEliminar.className = 'btn-danger';
                btnEliminar.textContent = 'X';
                btnEliminar.onclick = function() {
                    tr.remove();
                    calculateTotal();
                };
                tdAccion.appendChild(btnEliminar);
                
                // Unir celdas a la fila
                tr.appendChild(tdProducto);
                tr.appendChild(tdCantidad);
                tr.appendChild(tdPrecio);
                tr.appendChild(tdSubtotal);
                tr.appendChild(tdAccion);
                
                // Añadir fila al tbody
                productListTbody.appendChild(tr);

                // Añadir listeners a los nuevos campos
                selectProducto.addEventListener('change', calculateTotal);
                inputCantidad.addEventListener('input', calculateTotal);
            }

            // Función para calcular el total
            function calculateTotal() {
                let granTotal = 0;
                const rows = productListTbody.querySelectorAll('tr');
                
                rows.forEach(row => {
                    const selectProducto = row.querySelector('select[name="id_producto[]"]');
                    const inputCantidad = row.querySelector('input[name="cantidad[]"]');
                    const inputPrecio = row.querySelector('input[name="precio_unitario[]"]');
                    const tdSubtotal = row.querySelector('.subtotal-field');
                    
                    const selectedOption = selectProducto.options[selectProducto.selectedIndex];
                    const precio = selectedOption ? selectedOption.getAttribute('data-precio') : 0;
                    const cantidad = inputCantidad.valueAsNumber || 0;
                    
                    if (precio) {
                        inputPrecio.value = parseFloat(precio).toFixed(2);
                    } else {
                        inputPrecio.value = '0.00';
                    }

                    const subtotal = (precio * cantidad);
                    tdSubtotal.textContent = subtotal.toFixed(2);
                    granTotal += subtotal;
                });
                
                document.getElementById('total-display').textContent = granTotal.toFixed(2);
            }

            // Event listener para el botón "Añadir Producto"
            addRowBtn.addEventListener('click', createProductRow);

            // Crear la primera fila al cargar la página
            createProductRow();
        });
    </script>
    
</body>
</html>