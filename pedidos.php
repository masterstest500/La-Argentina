<?php
// 1. 🛡️ Control de Sesión y Escudo de Seguridad
session_start();
$cargoUsuario = isset($_SESSION['cargo']) ? strtolower($_SESSION['cargo']) : '';

// Permitir el acceso a administradores, ventas y preventistas
if (!isset($_SESSION['user']) || !in_array($cargoUsuario, ['administrador', 'ventas', 'preventista'])) {
    header("Location: index.php?error=acceso_restringido");
    exit();
}

include('conexion.php'); 
$nombre_usuario = $_SESSION['user'];
$rol_usuario = $_SESSION['cargo'];

$mensaje_alerta = "";

// ==========================================
// 2. 🔥 MOTOR BACKEND: PROCESAR EL PEDIDO COMPLETO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pedido'])) {
    $cliente_id   = intval($_POST['cliente_id']);
    $vendedor     = $nombre_usuario;
    $json_carrito = $_POST['items_carrito']; // Cadena JSON desde el frontend
    $items        = json_decode($json_carrito, true);

    if ($cliente_id > 0 && !empty($items)) {
        
        // 🚨 PASO CRÍTICO DE CALIDAD: Iniciamos una transacción SQL
        // Si algo falla a mitad de camino, MySQL deshace todo para no dejar datos corruptos.
        mysqli_begin_transaction($conexion);

        try {
            $total_pedido = 0;
            $detalles_a_insertar = [];

            // Primera pasada: Validar stock en el servidor y calcular subtotales
            foreach ($items as $item) {
                $producto_id = intval($item['id']);
                $cantidad    = intval($item['cantidad']);

                // Consultamos el stock real actual en base de datos
                $sql_p = "SELECT sabor, stock_potes, precio FROM productos WHERE id = $producto_id";
                $res_p = mysqli_query($conexion, $sql_p);
                $prod  = mysqli_fetch_assoc($res_p);

                if (!$prod || $prod['stock_potes'] < $cantidad) {
                    throw new Exception("Stock insuficiente para el sabor: " . ($prod ? $prod['sabor'] : "Desconocido"));
                }

                $precio_u = floatval($prod['precio']);
                $subtotal = $cantidad * $precio_u;
                $total_pedido += $subtotal;

                // Guardamos en memoria para la inserción masiva posterior
                $detalles_a_insertar[] = [
                    'producto_id' => $producto_id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio_u,
                    'subtotal' => $subtotal
                ];
            }

            // 1️⃣ Insertar en Tabla Maestro (pedidos)
            $sql_maestro = "INSERT INTO pedidos (cliente_id, vendedor, total) VALUES ($cliente_id, '$vendedor', $total_pedido)";
            if (!mysqli_query($conexion, $sql_maestro)) {
                throw new Exception("Error al registrar la cabecera del pedido.");
            }
            $pedido_id = mysqli_insert_id($conexion); // Capturamos el ID autoincremental generado

            // 2️⃣ Insertar Renglones y Restar Inventario
            foreach ($detalles_a_insertar as $det) {
                // Insertar en detalle_pedidos
                $sql_det = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                            VALUES ($pedido_id, " . $det['producto_id'] . ", " . $det['cantidad'] . ", " . $det['precio_unitario'] . ", " . $det['subtotal'] . ")";
                
                if (!mysqli_query($conexion, $sql_det)) {
                    throw new Exception("Error al registrar los renglones del pedido.");
                }

                // Descontar existencias en productos
                $sql_update = "UPDATE productos SET stock_potes = stock_potes - " . $det['cantidad'] . " WHERE id = " . $det['producto_id'];
                if (!mysqli_query($conexion, $sql_update)) {
                    throw new Exception("Error al actualizar el inventario físico.");
                }
            }

            // Si todo salió perfecto, consolidamos los datos en la BD
            mysqli_commit($conexion);
            header("Location: pedidos.php?guardado=exito");
            exit();

        } catch (Exception $e) {
            // Si ocurrió algún error o falta stock, cancelamos toda la operación
            mysqli_rollback($conexion);
            $mensaje_alerta = "<div class='alerta error'><i class='fa-solid fa-triangle-exclamation'></i> Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensaje_alerta = "<div class='alerta error'><i class='fa-solid fa-circle-xmark'></i> Por favor, seleccione un cliente y agregue al menos un sabor.</div>";
    }
}

if (isset($_GET['guardado']) && $_GET['guardado'] == 'exito') {
    $mensaje_alerta = "<div class='alerta exito'><i class='fa-solid fa-circle-check'></i> ¡Pedido registrado y stock descontado con éxito!</div>";
}

// Consultas para alimentar los Select dinámicos del Formulario
$query_clientes  = "SELECT id, nombre_negocio, rif FROM clientes ORDER BY nombre_negocio ASC";
$result_clientes = mysqli_query($conexion, $query_clientes);

$query_productos = "SELECT id, sabor, stock_potes, precio FROM productos WHERE stock_potes > 0 ORDER BY sabor ASC";
$result_productos = mysqli_query($conexion, $query_productos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Argentina - Toma de Pedidos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #0b0b0b; color: #ffffff; padding: 40px; }
        .header-seccion { margin-bottom: 30px; }
        .header-seccion h1 { font-size: 2rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .header-seccion h1 span { color: #ff0015; }
        .header-seccion p { color: #aaaaaa; margin-top: 5px; font-size: 0.95rem; }
        .btn-volver { color: #ffffff; text-decoration: none; display: inline-block; margin-top: 10px; font-size: 0.9rem; transition: color 0.3s; }
        .btn-volver:hover { color: #ff0015; }

        .dashboard-container { display: flex; gap: 30px; flex-wrap: wrap; margin-top: 20px; }
        .panel-izquierdo, .panel-derecho { background-color: #141414; border-radius: 8px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .panel-izquierdo { flex: 1; min-width: 350px; border-left: 4px solid #ff0015; height: fit-content; }
        .panel-derecho { flex: 1.5; min-width: 500px; display: flex; flex-direction: column; justify-content: space-between; }

        h2 { font-size: 1.3rem; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1px; }
        .grupo-input { margin-bottom: 20px; }
        .grupo-input label { display: block; color: #ff0015; font-weight: 600; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; }
        .grupo-input select, .grupo-input input { width: 100%; padding: 12px; background-color: #222222; border: 1px solid #333333; border-radius: 6px; color: #ffffff; font-size: 1rem; }
        .grupo-input select:focus, .grupo-input input:focus { outline: none; border-color: #ff0015; }

        .btn-secundario { width: 100%; padding: 12px; background-color: #333333; color: #fff; border: 1px solid #444; border-radius: 6px; font-weight: 600; text-transform: uppercase; cursor: pointer; transition: background-color 0.3s; }
        .btn-secundario:hover { background-color: #444; }
        .btn-primario { width: 100%; padding: 14px; background-color: #ff0015; color: #ffffff; border: none; border-radius: 6px; font-size: 1.05rem; font-weight: 700; text-transform: uppercase; cursor: pointer; transition: background-color 0.3s; margin-top: 20px; }
        .btn-primario:hover { background-color: #ff0019; }

        table { width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 20px; }
        th { color: #ff0015; padding: 12px; border-bottom: 2px solid #333333; font-size: 0.9rem; text-transform: uppercase; }
        td { padding: 14px 12px; border-bottom: 1px solid #222222; font-size: 0.95rem; }
        .btn-eliminar { background: none; border: none; color: #ff0015; cursor: pointer; font-size: 1.1rem; }

        .contenedor-total { border-top: 2px dashed #333; padding-top: 20px; display: flex; justify-content: space-between; align-items: center; }
        .total-label { font-size: 1.1rem; text-transform: uppercase; color: #aaa; }
        .total-monto { font-size: 1.8rem; font-weight: 700; color: #ff0015; }

        /* Estilos de Alertas */
        .alerta { padding: 15px; border-radius: 6px; margin-bottom: 25px; font-size: 0.95rem; display: flex; align-items: center; gap: 10px; }
        .error { background-color: rgba(230, 57, 70, 0.15); color: #ff0015; border: 1px solid #ff0015; }
        .exito { background-color: rgba(40, 167, 69, 0.15); color: #28a745; border: 1px solid #28a745; }
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Módulo de <span>Pedidos y Preventa</span></h1>
        <p>Ejecutivo de Calle: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
        <a href="index.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
    </div>

    <?php echo $mensaje_alerta; ?>

    <div class="dashboard-container">
        
        <div class="panel-formulario panel-izquierdo">
            <h2>Configurar Renglón</h2>
            
            <div class="grupo-input">
                <label for="select_sabor">Sabor de Helado Disponible</label>
                <select id="select_sabor">
                    <option value="">-- Seleccione Helado --</option>
                    <?php while ($prod = mysqli_fetch_assoc($result_productos)): ?>
                        <option value="<?php echo $prod['id']; ?>" 
                                data-sabor="<?php echo htmlspecialchars($prod['sabor']); ?>" 
                                data-precio="<?php echo $prod['precio']; ?>" 
                                data-stock="<?php echo $prod['stock_potes']; ?>">
                            <?php echo htmlspecialchars($prod['sabor']); ?> (Disp: <?php echo $prod['stock_potes']; ?> Potes - $<?php echo number_format($prod['precio'], 2); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="grupo-input">
                <label for="input_cantidad">Cantidad de Potes</label>
                <input type="number" id="input_cantidad" min="1" value="1">
            </div>

            <button type="button" class="btn-secundario" onclick="agregarItem()">
                <i class="fa-solid fa-cart-plus"></i> Agregar al Renglón
            </button>
        </div>

        <div class="panel-tabla panel-derecho">
            <div>
                <h2>Resumen de la Orden</h2>
                
                <form action="pedidos.php" method="POST" id="form_pedido">
                    
                    <div class="grupo-input">
                        <label for="cliente_id">Cliente Receptor</label>
                        <select name="cliente_id" id="cliente_id" required>
                            <option value="">-- Seleccione el Cliente Destino --</option>
                            <?php while ($cli = mysqli_fetch_assoc($result_clientes)): ?>
                                <option value="<?php echo $cli['id']; ?>">
                                    <?php echo htmlspecialchars($cli['nombre_negocio']); ?> [RIF: <?php echo htmlspecialchars($cli['rif']); ?>]
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <input type="hidden" name="items_carrito" id="items_carrito" value="[]">

                    <table>
                        <thead>
                            <tr>
                                <th>Sabor</th>
                                <th>Precio U.</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tabla_carrito">
                            <tr>
                                <td colspan="5" style="text-align: center; color: #666;">No se han añadido sabores a esta orden.</td>
                            </tr>
                        </tbody>
                    </table>
            </div>

            <div class="contenedor-total">
                <div>
                    <div class="total-label">Total Neto a Pagar</div>
                    <div class="total-monto" id="txt_total">$0.00</div>
                </div>
                <button type="submit" name="registrar_pedido" class="btn-primario" style="width: auto; padding: 14px 40px;">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Registrar Pedido Total
                </button>
                </form>
            </div>
        </div>

    </div>

    <script>
        let carrito = [];

        function agregarItem() {
            const select = document.getElementById('select_sabor');
            const inputCant = document.getElementById('input_cantidad');
            
            const productoId = select.value;
            const cantidad = parseInt(inputCant.value);

            if (!productoId) {
                alert('Por favor, selecciona un sabor de helado.');
                return;
            }
            if (isNaN(cantidad) || cantidad <= 0) {
                alert('Ingrese una cantidad válida de potes.');
                return;
            }

            // Capturamos la metadata desde los atributos data- de la opción HTML
            const optionSelected = select.options[select.selectedIndex];
            const sabor = optionSelected.getAttribute('data-sabor');
            const precio = parseFloat(optionSelected.getAttribute('data-precio'));
            const stockMax = parseInt(optionSelected.getAttribute('data-stock'));

            // Control preventivo en el frontend para ahorrar recursos
            if (cantidad > stockMax) {
                alert(`¡Alerta de Stock! Solo quedan ${stockMax} potes de ${sabor} en las cavas.`);
                return;
            }

            // Validamos si el producto ya fue listado abajo para acumularlo
            const indexExistente = carrito.findIndex(item => item.id === productoId);
            
            if (indexExistente !== -1) {
                const nuevaCantidad = carrito[indexExistente].cantidad + cantidad;
                if (nuevaCantidad > stockMax) {
                    alert(`No puedes agregar más potes. El acumulado en el carrito (${nuevaCantidad}) supera las existencias reales (${stockMax}).`);
                    return;
                }
                carrito[indexExistente].cantidad = nuevaCantidad;
                carrito[indexExistente].subtotal = nuevaCantidad * precio;
            } else {
                // Nuevo ítem al carrito local
                carrito.push({
                    id: productoId,
                    sabor: sabor,
                    precio: precio,
                    cantidad: cantidad,
                    subtotal: cantidad * precio
                });
            }

            // Resetear input de cantidad a 1
            inputCant.value = 1;
            renderizarCarrito();
        }

        function eliminarItem(index) {
            carrito.splice(index, 1);
            renderizarCarrito();
        }

        function renderizarCarrito() {
            const tbody = document.getElementById('tabla_carrito');
            const txtTotal = document.getElementById('txt_total');
            const hiddenInput = document.getElementById('items_carrito');
            
            tbody.innerHTML = '';
            let totalAcumulado = 0;

            if (carrito.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #666;">No se han añadido sabores a esta orden.</td></tr>`;
                txtTotal.innerText = "$0.00";
                hiddenInput.value = "[]";
                return;
            }

            carrito.forEach((item, index) => {
                totalAcumulado += item.subtotal;
                
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${item.sabor}</strong></td>
                        <td>$${item.precio.toFixed(2)}</td>
                        <td>${item.cantidad} Potes</td>
                        <td><strong>$${item.subtotal.toFixed(2)}</strong></td>
                        <td>
                            <button type="button" class="btn-eliminar" onclick="eliminarItem(${index})">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            // Actualizamos la vista del precio y cargamos el JSON en el formulario
            txtTotal.innerText = `$${totalAcumulado.toFixed(2)}`;
            hiddenInput.value = JSON.stringify(carrito);
        }

        // Validación final antes de disparar el POST a PHP
        document.getElementById('form_pedido').addEventListener('submit', function(e) {
            if (carrito.length === 0) {
                e.preventDefault();
                alert('No puedes registrar un pedido vacío. Añade renglones de sabores primero.');
            }
        });
    </script>
</body>
</html>