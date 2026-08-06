<?php
// ==========================================
// 1. 🛡️ CONTROL DE SESIÓN Y SEGURIDAD
// ==========================================
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

$cargoUsuario = strtolower(trim($_SESSION['cargo']));
$cargos_autorizados = ['preventista'];

if (!in_array($cargoUsuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

include('conexion.php'); 
$nombre_usuario = $_SESSION['user'];
$rol_usuario = $_SESSION['cargo'];
$mensaje_alerta = "";

// ==========================================
// 2. ⚡ ENDPOINTS AJAX (Para Selectores Dinámicos)
// ==========================================
if (isset($_GET['accion'])) {
    header('Content-Type: application/json');
    
    if ($_GET['accion'] == 'get_clientes' && isset($_GET['id_ruta'])) {
        $id_ruta = intval($_GET['id_ruta']);
        $sql = "SELECT id, codigo_cliente, nombre_negocio FROM clientes WHERE id_ruta = $id_ruta ORDER BY nombre_negocio ASC";
        $res = mysqli_query($conexion, $sql);
        $clientes = [];
        while($row = mysqli_fetch_assoc($res)) { $clientes[] = $row; }
        echo json_encode($clientes);
        exit();
    }
    
    if ($_GET['accion'] == 'get_sucursales' && isset($_GET['id_cliente'])) {
        $id_cliente = intval($_GET['id_cliente']);
        $sql = "SELECT id, codigo_sucursal, nombre_sucursal FROM sucursales WHERE id_cliente = $id_cliente ORDER BY nombre_sucursal ASC";
        $res = mysqli_query($conexion, $sql);
        $sucursales = [];
        while($row = mysqli_fetch_assoc($res)) { $sucursales[] = $row; }
        echo json_encode($sucursales);
        exit();
    }
}

// ==========================================
// 3. 🔥 MOTOR BACKEND: PROCESAR EL PEDIDO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pedido'])) {
    $cliente_id   = intval($_POST['hidden_cliente_id']);
    $sucursal_id  = !empty($_POST['hidden_sucursal_id']) ? intval($_POST['hidden_sucursal_id']) : "NULL"; 
    $vendedor     = $nombre_usuario;
    $json_carrito = $_POST['items_carrito'];
    $items        = json_decode($json_carrito, true);

    if ($cliente_id > 0 && !empty($items)) {
        mysqli_begin_transaction($conexion);

        try {
            $total_pedido = 0;
            $detalles_a_insertar = [];

            foreach ($items as $item) {
                $producto_id = intval($item['id']);
                $cantidad    = intval($item['cantidad']);

                $sql_p = "SELECT p.sabor, p.precio, p.codigo, di.cantidad AS stock_potes FROM productos p INNER JOIN disponibilidad_inventario di ON p.codigo COLLATE utf8mb4_general_ci = di.codigo COLLATE utf8mb4_general_ci WHERE p.id = $producto_id";
                $res_p = mysqli_query($conexion, $sql_p);
                $prod  = mysqli_fetch_assoc($res_p);

                if (!$prod || $prod['stock_potes'] < $cantidad) {
                    throw new Exception("Stock insuficiente para el sabor: " . ($prod ? $prod['sabor'] : "Desconocido"));
                }

                $precio_u = floatval($prod['precio']);
                $subtotal = $cantidad * $precio_u;
                $total_pedido += $subtotal;

                $detalles_a_insertar[] = [
                    'producto_id' => $producto_id,
                    'codigo_inventario' => $prod['codigo'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio_u,
                    'subtotal' => $subtotal
                ];
            }

            $sql_maestro = "INSERT INTO pedidos (cliente_id, sucursal_id, vendedor, total) VALUES ($cliente_id, $sucursal_id, '$vendedor', $total_pedido)";
            if (!mysqli_query($conexion, $sql_maestro)) {
                throw new Exception("Error al registrar la cabecera del pedido.");
            }
            $pedido_id = mysqli_insert_id($conexion);

            foreach ($detalles_a_insertar as $det) {
                $sql_det = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                            VALUES ($pedido_id, " . $det['producto_id'] . ", " . $det['cantidad'] . ", " . $det['precio_unitario'] . ", " . $det['subtotal'] . ")";
                if (!mysqli_query($conexion, $sql_det)) {
                    throw new Exception("Error al registrar los renglones del pedido.");
                }

                $codigo_inv = $det['codigo_inventario'];
                $cantidad_descontar = $det['cantidad'];
                $sql_update = "UPDATE disponibilidad_inventario SET cantidad = cantidad - $cantidad_descontar WHERE codigo = '$codigo_inv'";                
                if (!mysqli_query($conexion, $sql_update)) {
                    throw new Exception("Error al actualizar el inventario físico.");
                }
            }

            mysqli_commit($conexion);
            header("Location: pedidos.php?guardado=exito");
            exit();

        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $mensaje_alerta = "<div class='alerta error'><i class='fa-solid fa-triangle-exclamation'></i> Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensaje_alerta = "<div class='alerta error'><i class='fa-solid fa-circle-xmark'></i> Formulario incompleto. Asegúrese de completar el embudo y añadir sabores.</div>";
    }
}

if (isset($_GET['guardado']) && $_GET['guardado'] == 'exito') {
    $mensaje_alerta = "<div class='alerta exito'><i class='fa-solid fa-circle-check'></i> ¡Pedido registrado y stock descontado con éxito!</div>";
}

// Consultas iniciales para pintar la vista
$query_rutas = "SELECT id, nombre_ruta FROM rutas ORDER BY id ASC";
$result_rutas = mysqli_query($conexion, $query_rutas);

$query_productos = "
    SELECT 
        di.codigo,
        di.producto AS sabor, 
        di.cantidad AS stock_potes,
        IFNULL(p.id, di.codigo) AS id,
        IFNULL(p.precio, 0) AS precio,
        IFNULL(dc.presentacion, '') AS presentacion
    FROM disponibilidad_inventario di
    LEFT JOIN productos p 
        ON di.codigo COLLATE utf8mb4_general_ci = p.codigo COLLATE utf8mb4_general_ci 
    LEFT JOIN detalles_catalogo dc 
        ON di.codigo COLLATE utf8mb4_general_ci = dc.codigo_producto COLLATE utf8mb4_general_ci
    WHERE di.cantidad > 0 
    ORDER BY di.producto ASC
";
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

        .panel-fondo { background-color: #141414; border-radius: 8px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); margin-bottom: 20px; }
        .panel-embudo { border-top: 4px solid #ff0015; }
        
        .dashboard-container { display: flex; gap: 30px; flex-wrap: wrap; }
        .panel-izquierdo { flex: 1; min-width: 380px; }
        .panel-derecho { flex: 1.5; min-width: 500px; display: flex; flex-direction: column; justify-content: space-between; }

        h2 { font-size: 1.3rem; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 1px; }
        
        .fila-embudo { display: flex; gap: 20px; align-items: flex-end; }
        .fila-embudo .grupo-input { flex: 1; margin-bottom: 0; }

        .grupo-input { margin-bottom: 20px; }
        .grupo-input label { display: block; color: #ff0015; font-weight: 600; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; }
        .grupo-input select, .grupo-input input { width: 100%; padding: 12px; background-color: #222222; border: 1px solid #333333; border-radius: 6px; color: #ffffff; font-size: 1rem; }
        .grupo-input select:focus, .grupo-input input:focus { outline: none; border-color: #ff0015; }
        .grupo-input select:disabled { background-color: #1a1a1a; color: #555; cursor: not-allowed; }

        .btn-secundario { width: 100%; padding: 12px; background-color: #333333; color: #fff; border: 1px solid #444; border-radius: 6px; font-weight: 600; text-transform: uppercase; cursor: pointer; transition: background-color 0.3s; }
        .btn-secundario:hover { background-color: #444; }
        
        .btn-sucursal { background-color: transparent; color: #fff; border: 1px solid #ff0015; padding: 12px; border-radius: 6px; cursor: pointer; width: 100%; font-weight: 600; transition: 0.3s; }
        .btn-sucursal:hover { background-color: #ff0015; }

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

        /* Contenedor bloqueado hasta completar embudo */
        #zona_transaccion { opacity: 0.4; pointer-events: none; transition: 0.3s; }

        /* Modal CSS */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background: #141414; padding: 30px; border-radius: 8px; width: 100%; max-width: 400px; border: 1px solid #333; }
        .modal-active { display: flex; }

        .lista-helados-container {
            background-color: #222222;
            border: 1px solid #333333;
            border-radius: 6px;
            max-height: 250px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 10px;
        }

        .lista-helados-container::-webkit-scrollbar { width: 8px; }
        .lista-helados-container::-webkit-scrollbar-track { background: #1a1a1a; border-radius: 4px; }
        .lista-helados-container::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }
        .lista-helados-container::-webkit-scrollbar-thumb:hover { background: #ff0015; }

        .item-helado {
            background-color: #1a1a1a;
            border: 1px solid transparent;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }

        .item-helado:hover { background-color: #2a2a2a; border-color: #555; }
        .item-info { display: flex; flex-direction: column; gap: 4px; }
        .item-info strong { font-size: 0.95rem; color: #fff; }
        .item-info span { font-size: 0.75rem; color: #888; }
        .item-detalles { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
        .badge-stock { font-size: 0.8rem; color: #28a745; font-weight: 600; }
        .badge-precio { font-size: 0.9rem; color: #ff0015; font-weight: 700; }

        .tabla-scroll-container {
            max-height: 350px;
            overflow-y: auto;
            border: 1px solid #333333;
            border-radius: 6px;
            background-color: #1a1a1a;
        }

        .tabla-scroll-container table { width: 100%; border-collapse: collapse; }
        .tabla-scroll-container thead th {
            position: sticky;
            top: 0;
            background-color: #222222;
            color: #ffffff;
            z-index: 10;
            padding: 10px;
            text-align: left;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); 
        }

        .tabla-scroll-container tbody td { padding: 10px; border-bottom: 1px solid #333333; color: #ddd; }
        .tabla-scroll-container::-webkit-scrollbar { width: 8px; }
        .tabla-scroll-container::-webkit-scrollbar-track { background: #1a1a1a; border-radius: 4px; }
        .tabla-scroll-container::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }
        .tabla-scroll-container::-webkit-scrollbar-thumb:hover { background: #ff0015; }

        /* ==========================================
           ESTILOS DE BOTONES DE RENGLÓN (Capturas)
           ========================================== */
        .card-renglon {
            background-color: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 15px;
            position: relative;
        }

        .btn-box-add {
            width: 44px;
            height: 44px;
            background-color: #141414;
            border: 2px solid #ff0015;
            border-radius: 8px;
            color: #ff0015;
            font-size: 1.2rem;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-box-add:hover {
            background-color: #ff0015;
            color: #ffffff;
            box-shadow: 0 0 10px rgba(255, 0, 21, 0.4);
        }

        .btn-box-delete {
            background: none;
            border: none;
            color: #ff0015;
            font-size: 1.3rem;
            cursor: pointer;
            padding: 8px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .btn-box-delete:hover {
            color: #ff4d5e;
            transform: scale(1.15);
        }

        .contenedor-acciones-renglon {
            display: flex;
            align-items: center;
            gap: 12px;
        }
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Módulo de <span>Pedidos y Preventa</span></h1>
        <p>Ejecutivo de Calle: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
        <a href="index.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
    </div>

    <?php echo $mensaje_alerta; ?>

    <!-- BLOQUE 1: EMBUDO DE SELECCIÓN -->
    <div class="panel-fondo panel-embudo">
        <h2>1. Datos del Destinatario</h2>
        <div class="fila-embudo">
            <div class="grupo-input">
                <label for="select_ruta">Ruta Asignada</label>
                <select id="select_ruta">
                    <option value="" selected disabled>-- Seleccione Ruta --</option>
                    <?php while ($ruta = mysqli_fetch_assoc($result_rutas)): ?>
                        <option value="<?php echo $ruta['id']; ?>"><?php echo htmlspecialchars($ruta['nombre_ruta']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="grupo-input">
                <label for="select_cliente">Cliente Destino</label>
                <select id="select_cliente" disabled>
                    <option value="" selected disabled>-- Primero elija una ruta --</option>
                </select>
            </div>

            <div class="grupo-input" id="contenedor_btn_sucursal" style="display: none;">
                <button type="button" class="btn-sucursal" onclick="abrirModal()">
                    <i class="fa-solid fa-location-dot"></i> Elegir Sucursal
                </button>
            </div>
        </div>
        <p id="indicador_destino" style="color: #28a745; margin-top: 15px; font-size: 0.9rem; font-weight: bold; display: none;">
            <i class="fa-solid fa-check-circle"></i> Destino fijado. Puede armar el pedido.
        </p>
    </div>

    <!-- BLOQUE 2: ZONA DE TRANSACCIÓN -->
    <div id="zona_transaccion" class="dashboard-container">
        
        <!-- PANEL IZQUIERDO: CONFIGURAR RENGLÓN -->
        <div class="panel-fondo panel-izquierdo">
            <h2>Configurar Renglón</h2>
            
            <!-- CONTENEDOR DINÁMICO DE RENGLONES -->
            <div id="contenedor_renglones">
                <!-- Se genera dinámicamente mediante JS -->
            </div>

            <button type="button" class="btn-primario" onclick="agregarTodosLosRenglones()" style="margin-top: 10px;">
                <i class="fa-solid fa-cart-plus"></i> Agregar al Renglón
            </button>
        </div>

        <!-- PANEL DERECHO: RESUMEN DE LA ORDEN -->
        <div class="panel-fondo panel-derecho">
            <form action="pedidos.php" method="POST" id="form_pedido">
                <input type="hidden" name="hidden_cliente_id" id="hidden_cliente_id" required>
                <input type="hidden" name="hidden_sucursal_id" id="hidden_sucursal_id">
                <input type="hidden" name="items_carrito" id="items_carrito" value="[]">

                <div>
                    <h2>Resumen de la Orden</h2>
                    
                    <div class="tabla-scroll-container">
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
                </div>

                <div class="contenedor-total">
                    <div>
                        <div class="total-label">Total Neto a Pagar</div>
                        <div class="total-monto" id="txt_total">$0.00</div>
                    </div>
                    <button type="submit" name="registrar_pedido" class="btn-primario" style="width: auto; padding: 14px 40px;">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Registrar Pedido Total
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- MODAL DE SUCURSALES -->
    <div id="modalSucursal" class="modal-overlay">
        <div class="modal-content">
            <h2 style="color: #ff0015; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 20px;">Sedes Múltiples</h2>
            <p style="font-size: 0.9rem; color: #aaa; margin-bottom: 15px;">Este cliente posee varias sucursales. Seleccione la de destino:</p>
            
            <div class="grupo-input">
                <select id="select_sucursal_modal"></select>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-secundario" onclick="cerrarModal()">Cancelar</button>
                <button type="button" class="btn-primario" style="margin-top: 0;" onclick="confirmarSucursal()">Confirmar Sede</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE CATÁLOGO DE HELADOS -->
    <div id="modalHelados" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
                <h2 style="color: #ff0015; margin: 0; font-size: 1.3rem;">Catálogo de Helados</h2>
                <button type="button" onclick="cerrarModalHelados()" style="background: none; border: none; color: #aaa; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            
            <input type="text" id="buscar_helado" placeholder="🔍 Buscar por sabor..." onkeyup="filtrarHelados()" style="width: 100%; padding: 12px; margin-bottom: 15px; background-color: #222; border: 1px solid #333; color: #fff; border-radius: 6px;">
            
            <div class="lista-helados-container" id="contenedor_lista_helados" style="max-height: 350px;">
                <?php while ($prod = mysqli_fetch_assoc($result_productos)): ?>
                    <div class="item-helado" 
                        data-id="<?php echo $prod['id']; ?>" 
                        data-sabor="<?php echo htmlspecialchars($prod['sabor']); ?>" 
                        data-precio="<?php echo $prod['precio']; ?>" 
                        data-stock="<?php echo $prod['stock_potes']; ?>"
                        onclick="seleccionarHeladoModal(this)">
                        
                        <div class="item-info">
                            <strong><?php echo htmlspecialchars($prod['sabor']); ?></strong>
                            <span>Cód: <?php echo htmlspecialchars($prod['codigo']); ?> | Pres: <b><?php echo htmlspecialchars($prod['presentacion']); ?></b></span>
                        </div>
                        <div class="item-detalles">
                            <span class="badge-stock">Disp: <?php echo $prod['stock_potes']; ?></span>
                            <span class="badge-precio">$<?php echo number_format($prod['precio'], 2); ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
        // ==========================================
        // LÓGICA DEL EMBUDO (Ruta -> Cliente -> Sucursal)
        // ==========================================
        const selectRuta = document.getElementById('select_ruta');
        const selectCliente = document.getElementById('select_cliente');
        const btnSucursalContainer = document.getElementById('contenedor_btn_sucursal');
        const indicadorDestino = document.getElementById('indicador_destino');
        const zonaTransaccion = document.getElementById('zona_transaccion');
        
        let sucursalesTemporales = [];

        selectRuta.addEventListener('change', async function() {
            const rutaId = this.value;
            bloquearTransaccion();
            selectCliente.innerHTML = '<option value="">Cargando clientes...</option>';
            selectCliente.disabled = true;

            try {
                const response = await fetch(`pedidos.php?accion=get_clientes&id_ruta=${rutaId}`);
                const clientes = await response.json();
                
                selectCliente.innerHTML = '<option value="" selected disabled>-- Seleccione Cliente --</option>';
                clientes.forEach(c => {
                    selectCliente.innerHTML += `<option value="${c.id}">${c.codigo_cliente} - ${c.nombre_negocio}</option>`;
                });
                selectCliente.disabled = false;
            } catch (error) {
                console.error("Error cargando clientes", error);
            }
        });

        selectCliente.addEventListener('change', async function() {
            const clienteId = this.value;
            bloquearTransaccion();
            
            try {
                const response = await fetch(`pedidos.php?accion=get_sucursales&id_cliente=${clienteId}`);
                sucursalesTemporales = await response.json();

                if (sucursalesTemporales.length > 0) {
                    btnSucursalContainer.style.display = 'block';
                    const selectModal = document.getElementById('select_sucursal_modal');
                    selectModal.innerHTML = '<option value="" selected disabled>-- Elija la Sucursal --</option>';
                    sucursalesTemporales.forEach(s => {
                        selectModal.innerHTML += `<option value="${s.id}">${s.codigo_sucursal} - ${s.nombre_sucursal}</option>`;
                    });
                } else {
                    btnSucursalContainer.style.display = 'none';
                    desbloquearTransaccion(clienteId, null);
                }
            } catch (error) {
                console.error("Error cargando sucursales", error);
            }
        });

        function abrirModal() { document.getElementById('modalSucursal').classList.add('modal-active'); }
        function cerrarModal() { document.getElementById('modalSucursal').classList.remove('modal-active'); }
        
        function confirmarSucursal() {
            const idSucursal = document.getElementById('select_sucursal_modal').value;
            if (!idSucursal) { alert("Debe seleccionar una sucursal."); return; }
            cerrarModal();
            desbloquearTransaccion(selectCliente.value, idSucursal);
        }

        function bloquearTransaccion() {
            zonaTransaccion.style.opacity = '0.4';
            zonaTransaccion.style.pointerEvents = 'none';
            btnSucursalContainer.style.display = 'none';
            indicadorDestino.style.display = 'none';
            document.getElementById('hidden_cliente_id').value = '';
            document.getElementById('hidden_sucursal_id').value = '';
        }

        function desbloquearTransaccion(clienteId, sucursalId) {
            zonaTransaccion.style.opacity = '1';
            zonaTransaccion.style.pointerEvents = 'auto';
            indicadorDestino.style.display = 'block';
            document.getElementById('hidden_cliente_id').value = clienteId;
            if (sucursalId) document.getElementById('hidden_sucursal_id').value = sucursalId;
        }

        // ==========================================
        // LÓGICA DE RENGLONES DINÁMICOS
        // ==========================================
        let renglonContador = 0;
        let renglonActivoModal = null;

        function crearRenglonHTML(idRenglon) {
            const card = document.createElement('div');
            card.className = 'card-renglon';
            card.id = `renglon_card_${idRenglon}`;
            card.innerHTML = `
                <div class="grupo-input">
                    <label>Sabor de Helado Disponible</label>
                    <button type="button" class="btn-secundario" onclick="abrirModalHeladosPara(${idRenglon})" style="margin-bottom: 12px; border-color: #555; display: flex; justify-content: center; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar Helados
                    </button>
                    <input type="text" id="sabor_visual_${idRenglon}" placeholder="Ningún helado seleccionado..." disabled style="background-color: #1a1a1a; border: 1px dashed #555; color: #ff0015; font-weight: bold; text-align: center;">
                    
                    <input type="hidden" id="input_id_sabor_${idRenglon}" value="">
                    <input type="hidden" id="input_nombre_sabor_${idRenglon}" value="">
                    <input type="hidden" id="input_precio_sabor_${idRenglon}" value="">
                    <input type="hidden" id="input_stock_sabor_${idRenglon}" value="">
                </div>

                <div style="display: flex; gap: 15px; align-items: flex-end;">
                    <div class="grupo-input" style="flex: 1; margin-bottom: 0;">
                        <label for="input_cantidad_${idRenglon}">Cantidad de Potes</label>
                        <input type="number" id="input_cantidad_${idRenglon}" min="1" value="1">
                    </div>
                    <div class="contenedor-acciones-renglon">
                        <button type="button" class="btn-box-add" onclick="agregarNuevoRenglón()" title="Añadir otro producto">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                        <button type="button" class="btn-box-delete btn-eliminar-renglon" onclick="eliminarRenglón(${idRenglon})" title="Eliminar este producto">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            `;
            return card;
        }

        function agregarNuevoRenglón() {
            renglonContador++;
            const contenedor = document.getElementById('contenedor_renglones');
            contenedor.appendChild(crearRenglonHTML(renglonContador));
            actualizarVisibilidadEliminar();
        }

        function eliminarRenglón(idRenglon) {
            const card = document.getElementById(`renglon_card_${idRenglon}`);
            if (card) {
                card.remove();
                actualizarVisibilidadEliminar();
            }
        }

        function actualizarVisibilidadEliminar() {
            const renglones = document.querySelectorAll('.card-renglon');
            renglones.forEach(card => {
                const btnEliminar = card.querySelector('.btn-eliminar-renglon');
                if (btnEliminar) {
                    // Muestra la papelera solo si hay 2 o más renglones
                    btnEliminar.style.display = (renglones.length > 1) ? 'inline-flex' : 'none';
                }
            });
        }

        // Inicializar con un renglón
        document.addEventListener('DOMContentLoaded', function() {
            agregarNuevoRenglón();
        });

        // ==========================================
        // LÓGICA DEL MODAL DE HELADOS Y CARRITO
        // ==========================================
        function abrirModalHeladosPara(idRenglon) { 
            renglonActivoModal = idRenglon;
            document.getElementById('modalHelados').classList.add('modal-active'); 
            document.getElementById('buscar_helado').value = '';
            filtrarHelados();
            document.getElementById('buscar_helado').focus();
        }

        function cerrarModalHelados() { 
            document.getElementById('modalHelados').classList.remove('modal-active'); 
            renglonActivoModal = null;
        }

        function filtrarHelados() {
            const input = document.getElementById('buscar_helado').value.toLowerCase();
            const items = document.querySelectorAll('.item-helado');
            items.forEach(item => {
                const sabor = item.getAttribute('data-sabor').toLowerCase();
                item.style.display = sabor.includes(input) ? 'flex' : 'none';
            });
        }

        function seleccionarHeladoModal(elemento) {
            if (renglonActivoModal === null) return;

            const id = elemento.getAttribute('data-id');
            const sabor = elemento.getAttribute('data-sabor');
            const precio = elemento.getAttribute('data-precio');
            const stock = elemento.getAttribute('data-stock');

            document.getElementById(`input_id_sabor_${renglonActivoModal}`).value = id;
            document.getElementById(`input_nombre_sabor_${renglonActivoModal}`).value = sabor;
            document.getElementById(`input_precio_sabor_${renglonActivoModal}`).value = precio;
            document.getElementById(`input_stock_sabor_${renglonActivoModal}`).value = stock;
            document.getElementById(`sabor_visual_${renglonActivoModal}`).value = `${sabor} (Disp: ${stock})`;

            cerrarModalHelados();
        }

        // CARRITO / RESUMEN DE LA ORDEN
        let carrito = [];

        function agregarTodosLosRenglones() {
            const renglones = document.querySelectorAll('.card-renglon');
            let itemsProcesados = 0;

            renglones.forEach(card => {
                const idRenglon = card.id.replace('renglon_card_', '');
                const idSeleccionado = document.getElementById(`input_id_sabor_${idRenglon}`).value;
                const inputCant = document.getElementById(`input_cantidad_${idRenglon}`);
                const cantidad = parseInt(inputCant.value);

                if (idSeleccionado && !isNaN(cantidad) && cantidad > 0) {
                    const sabor = document.getElementById(`input_nombre_sabor_${idRenglon}`).value;
                    const precio = parseFloat(document.getElementById(`input_precio_sabor_${idRenglon}`).value);
                    const stockMax = parseInt(document.getElementById(`input_stock_sabor_${idRenglon}`).value);

                    if (cantidad > stockMax) {
                        alert(`Atención: Solo quedan ${stockMax} potes de ${sabor}.`);
                        return;
                    }

                    const indexExistente = carrito.findIndex(item => item.id === idSeleccionado);
                    if (indexExistente !== -1) {
                        const nuevaCantidad = carrito[indexExistente].cantidad + cantidad;
                        if (nuevaCantidad > stockMax) {
                            alert(`El acumulado de ${sabor} supera el stock disponible (${stockMax}).`);
                            return;
                        }
                        carrito[indexExistente].cantidad = nuevaCantidad;
                        carrito[indexExistente].subtotal = nuevaCantidad * precio;
                    } else {
                        carrito.push({ id: idSeleccionado, sabor: sabor, precio: precio, cantidad: cantidad, subtotal: cantidad * precio });
                    }
                    itemsProcesados++;
                }
            });

            if (itemsProcesados === 0) {
                alert('Selecciona al menos un helado e ingresa una cantidad válida.');
                return;
            }

            // Reiniciar panel a un solo renglón limpio
            document.getElementById('contenedor_renglones').innerHTML = '';
            renglonContador = 0;
            agregarNuevoRenglón();

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
                            <button type="button" class="btn-eliminar" onclick="eliminarItem(${index})"><i class="fa-solid fa-trash-can"></i></button>
                        </td>
                    </tr>
                `;
            });

            txtTotal.innerText = `$${totalAcumulado.toFixed(2)}`;
            hiddenInput.value = JSON.stringify(carrito);
        }

        document.getElementById('form_pedido').addEventListener('submit', function(e) {
            if (carrito.length === 0) {
                e.preventDefault();
                alert('No puedes registrar un pedido vacío.');
            }
        });
    </script>
</body>
</html>