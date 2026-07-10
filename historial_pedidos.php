<?php
session_start();

// 1. CONTROL DE ACCESO INTELIGENTE
$usuario_autenticado = isset($_SESSION['user']);
$cargo_actual = isset($_SESSION['cargo']) ? strtolower($_SESSION['cargo']) : '';
$rol_actual = isset($_SESSION['rol']) ? strtolower($_SESSION['rol']) : '';

if (!$usuario_autenticado || ($cargo_actual !== 'administrador' && $rol_actual !== 'administrador')) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// 2. CONEXIÓN A LA BASE DE DATOS
include("conexion.php");

// 3. CONSULTA RELACIONAL (Trae todos los estados para organizarlos con JS)
$query_historial = "SELECT p.id AS pedido_id, 
                           c.nombre_negocio AS cliente, 
                           p.vendedor, 
                           p.total, 
                           p.fecha_pedido,
                           p.estado 
                    FROM pedidos p
                    INNER JOIN clientes c ON p.cliente_id = c.id
                    ORDER BY p.fecha_pedido DESC";

$result_historial = mysqli_query($conexion, $query_historial);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Argentina - Auditoría de Pedidos</title>  
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/navbar.css"> 
    <link rel="stylesheet" href="css/estilos_globales.css">
    
    <style>
        /* INTERFAZ CORPORATIVA OSCURA - HELADOS LA ARGENTINA */
        body {
            margin: 0;
            background-color: #0b0b0d;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .contenedor-admin {
            display: flex;
            min-height: 100vh;
        }
        .contenido-principal {
            flex: 1;
            padding: 30px;
            background-color: #0b0b0d;
        }
        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #1a1a22;
            padding-bottom: 15px;
        }
        .encabezado h1 {
            color: #ffffff;
            margin: 0;
            font-size: 1.8em;
        }
        .encabezado h1 span {
            color: #ff3333; /* Rojo de la marca */
        }
        .usuario-badge {
            background-color: #1a1a22;
            border: 1px solid #ff3333;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        
        /* Sistema de pestañas de filtrado (Métrica Gerencial) */
        .contenedor-filtros {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .btn-tab {
            background-color: #1a1a22;
            color: #94a3b8;
            border: 1px solid #2d2d37;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-tab:hover {
            color: #ffffff;
            border-color: #ff3333;
        }
        .btn-tab.activo {
            background-color: #ff3333;
            color: #ffffff;
            border-color: #ff3333;
        }

        /* Tabla Estilo Dark Dashboard */
        .tabla-tarjeta {
            background: #141417;
            border-radius: 8px;
            border: 1px solid #1a1a22;
            padding: 20px;
            overflow-x: auto;
        }
        .tabla-auditoria {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .tabla-auditoria th {
            background-color: #1c1c22;
            color: #ff3333;
            padding: 14px 15px;
            font-weight: 600;
            font-size: 0.95em;
            border-bottom: 2px solid #2d2d37;
        }
        .tabla-auditoria td {
            padding: 14px 15px;
            border-bottom: 1px solid #1a1a22;
            color: #e2e8f0;
            font-size: 0.9em;
        }
        .tabla-auditoria tr:hover {
            background-color: #1c1c22;
        }
        
        /* Botones de acción */
        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background 0.2s;
        }
        .btn-detalle {
            background-color: #2563eb;
            color: white;
            margin-right: 5px;
        }
        .btn-detalle:hover { background-color: #1d4ed8; }
        
        .btn-anular {
            background-color: #dc2626;
            color: white;
        }
        .btn-anular:hover { background-color: #b91c1c; }

        /* Alertas de mensajes */
        .alerta-exito {
            background-color: rgba(15, 81, 50, 0.2);
            color: #39c16c;
            border: 1px solid #14452b;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
        }

        /* ==========================================================================
           📌 ARQUITECTURA CSS: VENTANA MODAL EN MODO OSCURO (NUEVO)
           ========================================================================== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8); /* Fondo traslúcido oscuro */
            backdrop-filter: blur(4px); /* Efecto difuminado */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            transition: all 0.3s ease;
        }
        .modal-content {
            background: #141417;
            border: 1px solid #ff3333; /* Borde sutil con el rojo de la marca */
            border-radius: 10px;
            width: 90%;
            max-width: 650px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
            overflow: hidden;
            animation: fadeInModal 0.3s ease-out;
        }
        .modal-header {
            background: #1c1c22;
            padding: 18px 24px;
            border-bottom: 1px solid #2d2d37;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 {
            margin: 0;
            color: #ffffff;
            font-size: 1.2rem;
        }
        .modal-header h3 span {
            color: #ff3333;
            font-weight: bold;
        }
        .btn-close-modal {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 28px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .btn-close-modal:hover {
            color: #dc2626;
        }
        .modal-body {
            padding: 24px;
            max-height: 400px;
            overflow-y: auto;
        }
        .tabla-renglones {
            width: 100%;
            border-collapse: collapse;
            color: #ffffff;
            text-align: left;
        }
        .tabla-renglones th {
            background-color: #1c1c22;
            color: #ff3333;
            padding: 12px;
            font-size: 0.9rem;
            text-transform: uppercase;
            border-bottom: 2px solid #2d2d37;
        }
        .tabla-renglones td {
            padding: 14px 12px;
            border-bottom: 1px solid #1a1a22;
            font-size: 0.95rem;
            color: #e2e8f0;
        }
        .tabla-renglones tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }
        .modal-footer {
            background: #1c1c22;
            padding: 14px 24px;
            border-top: 1px solid #2d2d37;
            display: flex;
            justify-content: flex-end;
        }
        .btn-entendido {
            background: #2d2d37;
            color: #ffffff;
            border: 1px solid #ff3333;
            padding: 8px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-entendido:hover {
            background: #ff3333;
        }
        @keyframes fadeInModal {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="contenedor-admin">
        
        <div id="sidebar-placeholder"></div>

        <main class="contenido-principal">
            <div class="encabezado">
                <div>
                    <h1>Auditoría y Control de <span>Pedidos</span></h1>
                    <small style="color: #94a3b8;">Historial de Preventas — Helados La Argentina</small>
                </div>
    
                <div class="encabezado-acciones" style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                    <div class="usuario-badge">
                        🔑 <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong> (<?php echo ucfirst($_SESSION['cargo']); ?>)
                    </div>
                    <a href="index.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
                </div>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'anulado_exito'): ?>
                <div class="alerta-exito">
                    ✅ El pedido ha sido anulado con éxito. Los potes de helado han sido reincorporados al inventario de las cavas.
                </div>
            <?php endif; ?>

            <div class="contenedor-filtros">
                <button class="btn-tab activo" onclick="filtrarTabla('completado', this)">✅ Activos (Completados)</button>
                <button class="btn-tab" onclick="filtrarTabla('anulado', this)">❌ Archivo de Anulados</button>
                <button class="btn-tab" onclick="filtrarTabla('todos', this)">📋 Mostrar Todos</button>
            </div>

            <div class="tabla-tarjeta">
                <table class="tabla-auditoria">
                    <thead>
                        <tr>
                            <th>Nro. Pedido</th>
                            <th>Cliente / Negocio</th>
                            <th>Preventista (Vendedor)</th>
                            <th>Total Orden</th>
                            <th>Fecha y Hora</th>
                            <th style="text-align: center;">Acciones de Auditoría</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla">
                        <?php if (mysqli_num_rows($result_historial) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_historial)): 
                                $estado_clase = strtolower($row['estado']);
                            ?>
                                <tr class="fila-pedido" data-estado="<?php echo $estado_clase; ?>">
                                    <td><strong>#<?php echo str_pad($row['pedido_id'], 5, "0", STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($row['vendedor']); ?></td>
                                    <td style="font-weight: bold; color: <?php echo ($row['estado'] === 'Anulado') ? '#94a3b8' : '#39c16c'; ?>;">
                                        $<?php echo number_format($row['total'], 2); ?>
                                    </td>
                                    <td><?php echo date('d/m/Y h:i A', strtotime($row['fecha_pedido'])); ?></td>
                                    <td style="text-align: center;">
                                        <button class="btn btn-detalle" onclick="verDetalle(<?php echo $row['pedido_id']; ?>)">
                                            🔍 Ver Renglones
                                        </button>
    
                                        <?php if ($row['estado'] !== 'Anulado'): ?>
                                            <button class="btn btn-anular" onclick="confirmarAnulacion(<?php echo $row['pedido_id']; ?>)">
                                                🚫 Anular Orden
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-weight: bold; margin-left: 10px;">❌ Anulado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 30px;">
                                    No se han registrado pedidos en el sistema todavía.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="modalRenglones" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Desglose del Pedido <span id="modal-nro-pedido">#00000</span></h3>
                <button onclick="cerrarModal()" class="btn-close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="tabla-renglones">
                    <thead>
                        <tr>
                            <th style="width: 15%; text-align: center;">Cant.</th>
                            <th style="width: 50%;">Producto / Sabor</th>
                            <th style="width: 15%; text-align: right;">P. Unitario</th>
                            <th style="width: 20%; text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="contenedor-renglones">
                        </tbody>
                </table>
            </div>
            <div class="modal-footer" style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 15px;">
                <a id="btn-imprimir-nota" href="#" target="_blank" style="background-color: #c81010; color: #ffffff; text-decoration: none; padding: 10px 18px; font-size: 14px; font-weight: bold; border-radius: 4px; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;">
                    🖨️ Imprimir Nota de Entrega
                </a>
                <button class="btn-cerrar" onclick="cerrarModal()">Cerrar Ventana</button>
            </div>
        </div>
    </div>

    <script src="js/auth-navbar.js"></script>
    <script>
        // FILTRADO EN TIEMPO REAL SIN RECARGAR PÁGINA
        function filtrarTabla(estadoObjetivo, botonPresionado) {
            // 1. Cambiar la pestaña activa visualmente
            document.querySelectorAll('.btn-tab').forEach(btn => btn.classList.remove('activo'));
            botonPresionado.classList.add('activo');

            // 2. Filtrar las filas de la tabla de manera instantánea
            const filas = document.querySelectorAll('.fila-pedido');
            filas.forEach(fila => {
                const estadoFila = fila.getAttribute('data-estado');
                
                if (estadoObjetivo === 'todos' || estadoFila === estadoObjetivo) {
                    fila.style.display = ''; // Muestra la fila
                } else {
                    fila.style.display = 'none'; // Oculta la fila
                }
            });
        }

        // Ejecutar filtro inicial al cargar la página para que los anulados no aparezcan de golpe
        document.addEventListener("DOMContentLoaded", function() {
            const botonActivos = document.querySelector('.btn-tab.activo');
            if(botonActivos) {
                filtrarTabla('completado', botonActivos);
            }
        });

        // ==========================================================================
        // 📌 INTERACTIVIDAD JAVASCRIPT: CONSUMO ASÍNCRONO DE RENGLONES (REESCRITO)
        // ==========================================================================
        function verDetalle(pedidoId) {
            // 1. Seteamos visualmente el número de pedido formateado en el título
            document.getElementById('modal-nro-pedido').innerText = '#' + String(pedidoId).padStart(5, '0');
            
            // 🔥 NUEVO: Cambiar dinámicamente el enlace del botón de impresión en PDF
            const btnImprimir = document.getElementById('btn-imprimir-nota');
            if (btnImprimir) {
                btnImprimir.href = `reporte_pedido.php?id=${pedidoId}`;
            }

            const tbody = document.getElementById('contenedor-renglones');
            
            // 2. Insertamos un mensaje de carga limpio e instantáneo
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align: center; color: #94a3b8; padding: 30px;">
                        ⏳ Consultando base de datos de cavas...
                    </td>
                </tr>`;
            
            // 3. Mostramos la ventana modal usando flexbox para centrarla perfectamente
            const modal = document.getElementById('modalRenglones');
            modal.style.display = 'flex';

            // 4. Petición AJAX asíncrona mediante Fetch API al backend que creamos
            fetch(`obtener_renglones.php?id=${pedidoId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(res => {
                    tbody.innerHTML = ''; // Limpiamos el mensaje de carga temporal

                    if (res.status === 'success') {
                        if (res.data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#94a3b8; padding: 15px;">Esta orden no tiene productos asociados.</td></tr>';
                            return;
                        }

                        // 5. Iteramos y pintamos los renglones devueltos en formato JSON
                        res.data.forEach(item => {
                            const fila = document.createElement('tr');
                            fila.innerHTML = `
                                <td style="text-align: center; font-weight: bold; color: #ff3333;">${item.cantidad}</td>
                                <td>📦 ${item.producto}</td>
                                <td style="text-align: right;">$${item.precio_unitario.toFixed(2)}</td>
                                <td style="text-align: right; color: #39c16c; font-weight: bold;">$${item.subtotal.toFixed(2)}</td>
                            `;
                            tbody.appendChild(fila);
                        });
                    } else {
                        // Despliega errores controlados desde PHP (ej: Sesión expirada)
                        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#dc2626; padding: 15px;">⚠️ Error: ${res.message}</td></tr>`;
                    }
                })
                .catch(error => {
                    console.error('Error en fetch:', error);
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#dc2626; padding: 15px;">❌ Error de conexión al cargar datos.</td></tr>';
                });
        }

        function cerrarModal() {
            document.getElementById('modalRenglones').style.display = 'none';
        }

        // Accesibilidad UX: Si el administrador hace clic fuera del modal, este se cierra solo
        window.onclick = function(event) {
            const modal = document.getElementById('modalRenglones');
            if (event.target === modal) {
                cerrarModal();
            }
        }

        function confirmarAnulacion(pedidoId) {
            if (confirm("⚠️ ¿Está seguro de anular el pedido #" + pedidoId + "?\n\nEsta acción devolverá los potes automáticamente al inventario físico de las cavas y cancelará el saldo.")) {
                window.location.href = "procesar_anulacion.php?id=" + pedidoId;
            }
        }
    </script>
</body>
</html>