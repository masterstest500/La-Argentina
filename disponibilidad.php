<?php
session_start();

// 1. Verificación de autenticación básica
if (!isset($_SESSION['user']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

// 2. Control de accesos restringido a Ventas y Administrador
$cargos_autorizados = ['ventas']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? $_SESSION['rol'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// 3. Incluir la conexión a MySQL
require_once 'conexion.php';

$nombre_usuario = $_SESSION['user'] ?? $_SESSION['nombre'] ?? $_SESSION['nombre_usuario'] ?? $_SESSION['usuario'] ?? 'Administrador';
$rol_usuario = $_SESSION['cargo'] ?? $_SESSION['rol'] ?? 'Ventas';

// Obtener la fecha de la última actualización grabada en la BD
$res_fecha = $conexion->query("SELECT MAX(fecha_actualizacion) AS ultima FROM disponibilidad_inventario");
$row_fecha = $res_fecha ? $res_fecha->fetch_assoc() : null;
$ultima_fecha = ($row_fecha && $row_fecha['ultima']) 
    ? date("d/m/Y h:i A", strtotime($row_fecha['ultima'])) 
    : 'Sin actualizaciones';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Argentina - Gestión de Disponibilidad</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #0b0b0b; color: #ffffff; padding: 30px; }
        
        /* Cabecera corporativa */
        .header-seccion { margin-bottom: 20px; }
        .header-seccion h1 { font-size: 2rem; font-weight: 700; display: flex; align-items: center; gap: 10px; text-transform: none; }
        .header-seccion h1 span { color: #ff0015; }
        .header-seccion p { color: #aaaaaa; margin-top: 5px; font-size: 0.95rem; }
        .btn-volver { color: #ffffff; text-decoration: none; display: inline-block; margin-top: 10px; font-size: 0.9rem; transition: color 0.3s; }
        .btn-volver:hover { color: #ff0015; }

        /* Contenedor principal */
        .contenedor-full {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .panel {
            background-color: #141414;
            border-radius: 8px;
            padding: 20px 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            border-top: 4px solid #ff0015;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #282828;
            padding-bottom: 10px;
        }

        .panel-header h2 { font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; }
        
        /* Toolbar */
        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .input-buscador {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #444;
            background-color: #282828;
            color: #fff;
            outline: none;
            flex-grow: 1; 
            max-width: 320px;
            font-size: 0.9rem;
        }
        .input-buscador:focus { border-color: #ff0015; }

        .btn-accion {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
        }
        
        .btn-peligro { background-color: rgba(255, 0, 21, 0.1); color: #ff0015; border: 1px solid #ff0015; }
        .btn-peligro:hover { background-color: #ff0015; color: #fff; }
        
        .btn-secundario { background-color: #282828; color: #fff; border: 1px solid #444; }
        .btn-secundario:hover { background-color: #444; }

        /* TABLA EXTENDIDA */
        .tabla-contenedor {
            width: 100%;
            overflow-x: auto;
            max-height: 650px;
            overflow-y: auto;
            border: 1px solid #222;
            border-radius: 6px;
        }
        
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        thead th {
            position: sticky; top: 0; background-color: #1a1a1a; color: #ff0015;
            padding: 12px; text-align: center; font-weight: 700;
            border-bottom: 2px solid #282828; z-index: 10;
        }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #222; text-align: center; color: #ddd; }
        tbody td.text-left { text-align: left; font-weight: 600; color: #fff; }

        input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: #ff0015; }

        tr.fila-categoria td {
            background-color: #1e1e1e; color: #ffffff; font-weight: 700;
            text-align: center; text-transform: uppercase; letter-spacing: 2px;
            padding: 8px; border-top: 2px solid #333; border-bottom: 2px solid #333;
        }

        /* BADGES SEMAFÓRICOS */
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            min-width: 45px;
        }
        .badge-rojo { background-color: rgba(255, 0, 21, 0.15); color: #ff4d4d; border: 1px solid rgba(255, 0, 21, 0.4); }
        .badge-amarillo { background-color: rgba(255, 193, 7, 0.15); color: #ffca28; border: 1px solid rgba(255, 193, 7, 0.4); }
        .badge-verde { background-color: rgba(46, 204, 113, 0.15); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.4); }

        /* PANEL DE CARGA COMPACTO (HORIZONTAL) */
        .panel-compacto {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #181818;
            border: 1px dashed #333;
            border-radius: 6px;
            padding: 10px 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .panel-compacto:hover { border-color: #ff0015; background-color: rgba(255,0,21,0.03); }
        .panel-compacto i { font-size: 1.6rem; color: #ff0015; }
        .panel-compacto-info { flex-grow: 1; }
        .panel-compacto-info h4 { font-size: 0.9rem; color: #fff; font-weight: 600; }
        .panel-compacto-info p { font-size: 0.78rem; color: #888; }
        
        .btn-cargar-mini {
            background-color: #ff0015; color: #fff; border: none;
            padding: 8px 18px; border-radius: 5px; font-size: 0.85rem; font-weight: 600;
            cursor: pointer; transition: background 0.3s; white-space: nowrap;
        }
        .btn-cargar-mini:hover { background-color: #cc0011; }

        .input-file-hidden { display: none; }

        /* MODAL DE EDICIÓN */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.85); display: none;
            justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-contenido {
            background-color: #141414; border: 1px solid #333; border-top: 4px solid #ff0015;
            border-radius: 8px; width: 90%; max-width: 800px; max-height: 85vh;
            display: flex; flex-direction: column; box-shadow: 0 10px 30px rgba(0,0,0,0.8);
        }
        .modal-header { padding: 15px 20px; border-bottom: 1px solid #282828; display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 20px; overflow-y: auto; flex-grow: 1; }
        .modal-footer { padding: 15px 20px; border-top: 1px solid #282828; display: flex; justify-content: flex-end; gap: 10px; }
        .input-edicion { width: 100%; padding: 6px 10px; background-color: #222; border: 1px solid #444; color: #fff; border-radius: 4px; text-align: center; }
        .input-edicion:focus { border-color: #ff0015; outline: none; }
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Gestión de <span>Disponibilidad</span></h1>
        <p>Bienvenido: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
        <a href="index.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
    </div>

    <div class="contenedor-full">
        
        <!-- PANEL SUPERIOR: TABLA EXTENDIDA -->
        <div class="panel">
            <div class="panel-header">
                <h2>Administración de Inventario</h2>
                <span style="font-size: 0.8rem; color: #888;"><i class="fas fa-sync-alt"></i> Última act: <?php echo $ultima_fecha; ?></span>
            </div>
            
            <form id="form-tabla" action="procesar_acciones_inventario.php" method="POST">
                <input type="hidden" name="accion" id="accion_input" value="">
                
                <div class="toolbar">
                    <input type="text" id="buscadorInventario" placeholder="🔍 Buscar producto, código o categoría..." class="input-buscador">
                    
                    <button type="button" class="btn-accion btn-secundario" onclick="prepararEdicion()"><i class="fas fa-edit"></i> Editar Sel.</button>
                    <button type="button" class="btn-accion btn-peligro" onclick="confirmarAccion('eliminar_seleccionados')"><i class="fas fa-trash-alt"></i> Eliminar Sel.</button>
                </div>

                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="checkAll"></th>
                                <th>CÓDIGO</th>
                                <th style="text-align: left;">PRODUCTOS</th>
                                <th>CANT</th>
                                <th>DÍAS VENTA</th>
                                <th>PEN. LIBERAR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM disponibilidad_inventario ORDER BY categoria ASC, id ASC";                            
                            $resultado = $conexion->query($query);
                            $categoria_anterior = "";

                            if ($resultado && $resultado->num_rows > 0) {
                                while ($row = $resultado->fetch_assoc()) {
                                    if ($row['categoria'] !== $categoria_anterior) {
                                        $categoria_anterior = $row['categoria'];
                                        echo '<tr class="fila-categoria"><td colspan="6">' . htmlspecialchars($categoria_anterior) . '</td></tr>';
                                    }

                                    $dias = floatval($row['dias_venta']);
                                    if ($dias < 5) {
                                        $clase_badge = 'badge-rojo';
                                    } elseif ($dias <= 15) {
                                        $clase_badge = 'badge-amarillo';
                                    } else {
                                        $clase_badge = 'badge-verde';
                                    }
                                    ?>
                                    <tr data-id="<?php echo $row['id']; ?>">
                                        <td><input type="checkbox" class="checkItem" name="ids[]" value="<?php echo $row['id']; ?>"></td>
                                        <td class="col-codigo"><?php echo htmlspecialchars($row['codigo']); ?></td>
                                        <td class="text-left col-producto"><?php echo htmlspecialchars($row['producto']); ?></td>
                                        <td class="col-cant" data-valor="<?php echo $row['cantidad']; ?>"><?php echo number_format($row['cantidad'], 0, ',', '.'); ?></td>
                                        <td class="col-dias" data-valor="<?php echo $row['dias_venta']; ?>"><span class="badge <?php echo $clase_badge; ?>"><?php echo $row['dias_venta']; ?></span></td>
                                        <td class="col-pen" data-valor="<?php echo $row['pen_liberar']; ?>"><?php echo $row['pen_liberar']; ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" style="text-align:center; padding: 30px; color: #888;">No hay inventario cargado.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        <!-- PANEL INFERIOR COMPACTO: CARGA DE EXCEL -->
        <div class="panel" style="padding: 15px 25px;">
            <form action="procesar_excel.php" method="POST" enctype="multipart/form-data" style="display: flex; gap: 15px; align-items: center;">
                <div class="panel-compacto" onclick="document.getElementById('excel_file').click();" style="flex-grow: 1;">
                    <i class="fas fa-file-excel"></i>
                    <div class="panel-compacto-info">
                        <h4 id="texto-area">Actualizar Datos (Subir Excel)</h4>
                        <p id="subtexto-area">Haz clic o arrastra un archivo .xlsx / .xls aquí</p>
                    </div>
                    <input type="file" name="excel_file" id="excel_file" class="input-file-hidden" accept=".xls,.xlsx" required onchange="mostrarNombreArchivo(this)">
                </div>
                <button type="submit" class="btn-cargar-mini"><i class="fas fa-cloud-upload-alt"></i> Procesar</button>
            </form>
        </div>

    </div>

    <!-- MODAL DE EDICIÓN MÚLTIPLE -->
    <div id="modalEdicion" class="modal-overlay">
        <div class="modal-contenido">
            <div class="modal-header">
                <h3><i class="fas fa-edit" style="color:#ff0015;"></i> Editar Productos Seleccionados</h3>
                <button type="button" onclick="cerrarModalEdicion()" style="background:none; border:none; color:#fff; font-size:1.2rem; cursor:pointer;">&times;</button>
            </div>
            <form id="formEdicionLote" action="actualizar_seleccionados.php" method="POST">
                <div class="modal-body">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align:left;">Producto</th>
                                <th style="width: 120px;">Cantidad</th>
                                <th style="width: 120px;">Días Venta</th>
                                <th style="width: 120px;">Pen. Liberar</th>
                            </tr>
                        </thead>
                        <tbody id="contenedorFilasEditar">
                            <!-- Se puebla dinámicamente desde JS -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-accion btn-secundario" onclick="cerrarModalEdicion()">Cancelar</button>
                    <button type="submit" class="btn-accion" style="background-color:#ff0015; color:#fff;"><i class="fas fa-save"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Checkbox "Seleccionar Todo"
        document.getElementById('checkAll').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.checkItem');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // Mostrar nombre del archivo seleccionado
        function mostrarNombreArchivo(input) {
            if (input.files && input.files[0]) {
                document.getElementById('texto-area').innerText = "Archivo listo:";
                document.getElementById('subtexto-area').innerText = input.files[0].name;
                document.getElementById('subtexto-area').style.color = "#2ecc71";
            }
        }

        // Confirmar eliminación
        function confirmarAccion(accion) {
            let formulario = document.getElementById('form-tabla');
            document.getElementById('accion_input').value = accion;
            
            let seleccionados = document.querySelectorAll('.checkItem:checked').length;
            if(seleccionados === 0) {
                Swal.fire({
                    icon: 'warning', 
                    title: 'Atención',
                    text: 'Debes seleccionar al menos un producto de la tabla.',
                    background: '#1a1a1a', 
                    color: '#fff', 
                    confirmButtonColor: '#ff0015'
                });
                return;
            }

            Swal.fire({
                title: '¿Eliminar filas seleccionadas?', 
                text: 'Se borrarán de la base de datos de forma permanente.', 
                icon: 'warning',
                showCancelButton: true, 
                confirmButtonColor: '#ff0015',
                cancelButtonColor: '#282828', 
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar', 
                background: '#141414', 
                color: '#ffffff'
            }).then((result) => {
                if (result.isConfirmed) formulario.submit();
            });
        }

        // Preparar y abrir Modal de Edición
        function prepararEdicion() {
            let seleccionados = document.querySelectorAll('.checkItem:checked');
            
            if(seleccionados.length === 0) {
                Swal.fire({
                    icon: 'warning', title: 'Sin selección',
                    text: 'Selecciona al menos un producto para editar.',
                    background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ff0015'
                });
                return;
            }

            let tbodyModal = document.getElementById('contenedorFilasEditar');
            tbodyModal.innerHTML = '';

            seleccionados.forEach(cb => {
                let fila = cb.closest('tr');
                let id = fila.getAttribute('data-id');
                let producto = fila.querySelector('.col-producto').innerText;
                let cant = fila.querySelector('.col-cant').getAttribute('data-valor');
                let dias = fila.querySelector('.col-dias').getAttribute('data-valor');
                let pen = fila.querySelector('.col-pen').getAttribute('data-valor');

                let htmlFila = `
                    <tr>
                        <td style="text-align:left; font-size:0.85rem;">
                            <strong>${producto}</strong>
                            <input type="hidden" name="items[${id}][id]" value="${id}">
                        </td>
                        <td><input type="number" name="items[${id}][cantidad]" value="${cant}" class="input-edicion" required></td>
                        <td><input type="number" step="0.1" name="items[${id}][dias_venta]" value="${dias}" class="input-edicion" required></td>
                        <td><input type="number" name="items[${id}][pen_liberar]" value="${pen}" class="input-edicion" required></td>
                    </tr>
                `;
                tbodyModal.innerHTML += htmlFila;
            });

            document.getElementById('modalEdicion').style.display = 'flex';
        }

        function cerrarModalEdicion() {
            document.getElementById('modalEdicion').style.display = 'none';
        }

        // Buscador Dinámico
        document.getElementById('buscadorInventario').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let filas = document.querySelectorAll('.tabla-contenedor tbody tr');

            filas.forEach(function(fila) {
                let textoFila = fila.textContent.toLowerCase();
                fila.style.display = textoFila.includes(filtro) ? '' : 'none';
            });
        });

        // Alertas SweetAlert
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            
            // Agregando el manejo de errores a tu mismo código
            if (status === 'deleted_selected') {
                Swal.fire({ icon: 'success', title: '¡Eliminados!', text: 'Los productos seleccionados fueron borrados.', background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ff0015', timer: 3000 });
            } else if (status === 'success') {
                Swal.fire({ icon: 'success', title: '¡Actualizado!', text: 'El inventario ha sido actualizado con éxito.', background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ff0015', timer: 3000 });
            } else if (status === 'error_extension') {
                Swal.fire({ icon: 'error', title: 'Archivo no válido', text: 'Por favor sube un archivo Excel (.xls o .xlsx).', background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ff0015' });
            } else if (status === 'error_procesamiento' || status === 'error') {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un problema procesando la solicitud.', background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ff0015' });
            }
        }
    </script>
</body>
</html>