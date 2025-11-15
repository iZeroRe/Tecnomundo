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

// 1. Consultar clientes para el dropdown
$stmt_clientes = $pdo->query("SELECT id_cliente, nombre, apellido FROM cliente ORDER BY apellido, nombre");
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// 2. Consultar productos para el dropdown Y para JavaScript
$stmt_productos = $pdo->query("SELECT id_producto, nombre, precio, stock FROM producto WHERE stock > 0 ORDER BY nombre");
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

// 3. Crear un mapa de productos (ID => datos) para que JavaScript pueda usarlos
$productos_map = [];
foreach ($productos as $producto) {
    // Usamos 'precio' y 'stock' como números
    $productos_map[$producto['id_producto']] = [
        'nombre' => $producto['nombre'],
        'precio' => (float) $producto['precio'],
        'stock' => (int) $producto['stock']
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale-1.0">
    <title>Nueva Venta</title>
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
            .form-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
            .form-group label { display: block; font-weight: 500; color: var(--color-texto-secundario); margin-bottom: 6px; font-size: 14px; }
         .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--color-borde); border-radius: 6px; font-size: 15px; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; font-family: inherit; background-color: var(--color-blanco); }
            .form-control:focus { outline: none; border-color: var(--color-primario); box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25); }
        .form-control[readonly] { background-color: var(--color-fondo); }
        .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; }
            .btn { border: none; padding: 10px 16px; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 14px; text-decoration: none; transition: background-color 0.2s, color 0.2s; }
            .btn-primary { background-color: var(--color-primario); color: var(--color-blanco); }
            .btn-primary:hover { background-color: #0b5ed7; }
            .btn-secondary { background-color: var(--color-fondo); color: var(--color-texto-secundario); border: 1px solid var(--color-borde); }
            .btn-secondary:hover { background-color: var(--color-borde); }
        .btn-danger { background-color: var(--color-rojo); color: var(--color-blanco); }
        .btn-danger:hover { background-color: #c82333; }
        
        /* Estilos de la tabla de productos */
        .products-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .products-table th, .products-table td { padding: 10px; border: 1px solid var(--color-borde); text-align: left; }
        .products-table th { background-color: var(--color-fondo); }
        .products-table td { background-color: var(--color-blanco); }
        .products-table .form-control { width: 90%; }
        .products-table .input-cantidad { width: 80px; }
        .products-table .stock-info { font-size: 12px; color: var(--color-texto-secundario); }
        .products-table .stock-error { color: var(--color-rojo); font-weight: bold; }
        
        .total-section { text-align: right; margin-top: 20px; }
        .total-section h3 { font-size: 24px; color: var(--color-texto); margin: 0; }
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
            <h1>Nueva Venta</h1>
        </header>

        <form action="venta_guardar.php" method="POST" id="form-venta">
            <div class="card">
                <h2 class="card-header">Datos de la Venta</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="id_cliente">Cliente</label>
                        <select id="id_cliente" name="id_cliente" class="form-control" required>
                            <option value="" disabled selected>Seleccione un cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id_cliente']; ?>">
                                    <?php echo htmlspecialchars($cliente['apellido'] . ', ' . $cliente['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 24px;">
                <h2 class="card-header">Productos</h2>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio Unit.</th>
                            <th>Stock Disp.</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Quitar</th>
                        </tr>
                    </thead>
                    <tbody id="productos-tbody">
                        </tbody>
                </table>
                <button type="button" id="btn-add-producto" class="btn btn-secondary" style="margin-top: 20px;">+ Añadir Producto</button>
                
                <div class="total-section">
                    <h3>Total: $<span id="total-display">0.00</span></h3>
                    <input type="hidden" name="total" id="total-hidden-input">
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <a href="ventas.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Venta</button>
                </div>
            </div>
        </form>
    </main>

    <script>
        // 1. Pasar los datos de productos de PHP a JavaScript
        const productosData = <?php echo json_encode($productos_map); ?>;
        
        // 2. Elementos del DOM
        const btnAddProducto = document.getElementById('btn-add-producto');
        const productosTbody = document.getElementById('productos-tbody');
        const totalDisplay = document.getElementById('total-display');
        const totalHiddenInput = document.getElementById('total-hidden-input');
        const formVenta = document.getElementById('form-venta');

        // 3. Función para agregar una nueva fila de producto
        function agregarFila() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select name="id_producto[]" class="form-control" required>
                        <option value="" disabled selected>Seleccione...</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['id_producto']; ?>">
                                <?php echo htmlspecialchars($producto['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="text" name="precio_unitario[]" class="form-control" readonly value="0.00">
                </td>
                <td>
                    <span class="stock-info">N/A</span>
                </td>
                <td>
                    <input type="number" name="cantidad[]" class="form-control input-cantidad" value="1" min="1" required>
                </td>
                <td>
                    <input type="text" name="subtotal[]" class="form-control" readonly value="0.00">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-remove">X</button>
                </td>
            `;
            productosTbody.appendChild(tr);
        }

        // 4. Función para recalcular todo
        function recalcular() {
            let granTotal = 0;
            let stockValido = true;
            
            productosTbody.querySelectorAll('tr').forEach(tr => {
                const selectProducto = tr.querySelector('select[name="id_producto[]"]');
                const inputCantidad = tr.querySelector('input[name="cantidad[]"]');
                const inputPrecio = tr.querySelector('input[name="precio_unitario[]"]');
                const inputSubtotal = tr.querySelector('input[name="subtotal[]"]');
                const spanStock = tr.querySelector('.stock-info');
                
                const idProducto = selectProducto.value;
                let cantidad = parseInt(inputCantidad.value) || 0;
                
                if (idProducto && productosData[idProducto]) {
                    const producto = productosData[idProducto];
                    
                    // Actualizar campos de la fila
                    inputPrecio.value = producto.precio.toFixed(2);
                    spanStock.textContent = `${producto.stock} en stock`;
                    spanStock.classList.remove('stock-error');

                    // Validar stock
                    if (cantidad > producto.stock) {
                        spanStock.textContent = `¡Solo hay ${producto.stock}!`;
                        spanStock.classList.add('stock-error');
                        inputCantidad.value = producto.stock; // Corregir cantidad
                        cantidad = producto.stock;
                        stockValido = false;
                    }

                    const subtotal = producto.precio * cantidad;
                    inputSubtotal.value = subtotal.toFixed(2);
                    granTotal += subtotal;

                } else {
                    // Si no hay producto seleccionado
                    inputPrecio.value = "0.00";
                    spanStock.textContent = "N/A";
                    inputSubtotal.value = "0.00";
                }
            });

            // Actualizar el total general
            totalDisplay.textContent = granTotal.toFixed(2);
            totalHiddenInput.value = granTotal.toFixed(2);
        }

        // 5. Event Listeners
        
        // Al hacer clic en "+ Añadir Producto"
        btnAddProducto.addEventListener('click', agregarFila);
        
        // Al cambiar algo en la tabla (producto seleccionado, cantidad, o borrar)
        productosTbody.addEventListener('change', (e) => {
            // Si se cambió un <select> de producto o una <input> de cantidad
            if (e.target.tagName === 'SELECT' || e.target.tagName === 'INPUT') {
                recalcular();
            }
        });
        
        // Usar 'input' para que recalcule mientras se teclea la cantidad
        productosTbody.addEventListener('input', (e) => {
            if (e.target.tagName === 'INPUT') {
                recalcular();
            }
        });

        // Al hacer clic en un botón "Quitar"
        productosTbody.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-remove')) {
                e.target.closest('tr').remove();
                recalcular();
            }
        });
        
        // Al enviar el formulario
        formVenta.addEventListener('submit', (e) => {
            if (productosTbody.rows.length === 0) {
                alert('Debe agregar al menos un producto a la venta.');
                e.preventDefault(); // Detener el envío
                return;
            }
            
            // Re-validar stock antes de enviar
            recalcular(); 
            const hayErroresStock = productosTbody.querySelector('.stock-error');
            if(hayErroresStock) {
                 alert('Revise las cantidades. Hay productos que superan el stock disponible.');
                 e.preventDefault(); // Detener el envío
                 return;
            }
        });

        // 6. Agregar una fila al cargar la página
        agregarFila();

    </script>

</body>
</html>