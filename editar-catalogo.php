<?php
session_start();

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['user']) && !isset($_SESSION['nombre']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

$cargos_autorizados = ['ventas', 'administrador']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? $_SESSION['rol'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// Variables para el mensaje de bienvenida
$nombre_usuario = $_SESSION['user'] ?? $_SESSION['nombre'] ?? 'Usuario';
$rol_usuario = $_SESSION['cargo'] ?? $_SESSION['rol'] ?? 'Administrador';

require_once 'conexion.php'; 

// Control de ordenamiento
$orden = $_GET['orden'] ?? 'desc';
$order_clause = "ORDER BY id DESC"; // Por defecto: Más recientes primero

if ($orden === 'asc') {
    $order_clause = "ORDER BY id ASC"; // Del más viejo al más nuevo
} else if ($orden === 'az') {
    $order_clause = "ORDER BY nombre_producto ASC";
} else if ($orden === 'za') {
    $order_clause = "ORDER BY nombre_producto DESC";
}

// Obtenemos todos los registros de detalles_catalogo
$sql = "SELECT id, codigo_producto, nombre_producto, ingredientes, presentacion, ruta_imagen FROM detalles_catalogo $order_clause";
$resultado_query = $conexion->query($sql);
$productos = $resultado_query->fetch_all(MYSQLI_ASSOC);

$bloques_helados = array_chunk($productos, 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Argentina - Editar Catálogo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #0b0b0b; color: #ffffff; padding: 40px; }
        
        /* Header */
        .header-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .header-seccion h1 { font-size: 2rem; font-weight: 700; text-transform: none; line-height: 1.1; } /* Se quitó uppercase */
        .header-seccion h1 span { color: #ff0015; }
        .header-seccion p { color: #aaaaaa; margin-top: 5px; font-size: 0.9rem; }
        
        .btn-volver { color: #aaaaaa; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 0.9rem; transition: color 0.3s; margin-top: 6px; }
        .btn-volver:hover { color: #ff0015; }

        .header-acciones { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

        .btn-crear { background-color: #ff0015; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: background 0.3s; }
        .btn-crear:hover { background-color: #cc0011; }

        /* Selector de ordenamiento */
        .ordenar-box { display: flex; align-items: center; gap: 6px; background-color: #141414; padding: 6px 12px; border-radius: 6px; border: 1px solid #2a2a2a; }
        .ordenar-box label { font-size: 0.8rem; color: #aaa; display: flex; align-items: center; gap: 4px; }
        .select-orden-small { background-color: #0b0b0b; color: #fff; border: 1px solid #333; border-radius: 4px; padding: 4px 8px; font-size: 0.8rem; cursor: pointer; outline: none; transition: border-color 0.3s; }
        .select-orden-small:hover, .select-orden-small:focus { border-color: #ff0015; }

        /* Panel principal */
        .panel-fondo { background-color: #141414; border-radius: 8px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); border-top: 4px solid #ff0015; }

        .lista-tarjetas-grandes { display: flex; flex-direction: column; gap: 25px; margin-top: 10px; }

        .tarjeta-bloque-doble {
            background-color: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-left: 5px solid #ff0015;
            border-radius: 10px;
            padding: 25px 30px;
            min-height: 200px;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            opacity: 0;
            animation: entradaTarjeta 0.6s ease-out forwards;
        }

        .helado-subseccion { position: relative; display: flex; align-items: center; gap: 20px; padding: 10px; }
        .helado-img-box { width: 150px; height: 150px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .helado-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 6px 8px rgba(0,0,0,0.7)); }

        .helado-info { flex: 1; display: flex; flex-direction: column; gap: 6px; }
        .helado-info h3 { font-size: 1.2rem; font-weight: 700; color: #ffffff; margin: 0; padding-right: 70px; }
        
        .badge-medida {
            background-color: rgba(255, 0, 21, 0.15);
            color: #ff4d4d;
            border: 1px solid #ff0015;
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 0.78rem;
            font-weight: 700;
            width: fit-content;
        }

        .helado-ingredientes { color: #aaaaaa; font-size: 0.88rem; line-height: 1.4; margin: 0; }

        .acciones-tarjeta { position: absolute; top: 0px; right: 0px; display: flex; gap: 6px; z-index: 5; }
        .btn-accion { background: #222; border: 1px solid #333; color: #fff; width: 32px; height: 32px; border-radius: 5px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
        .btn-accion.editar:hover { background: #ff0015; border-color: #ff0015; }
        .btn-accion.eliminar:hover { background: #dc3545; border-color: #dc3545; }

        .divisor-vertical { width: 1px; height: 80%; background-color: #333333; }

        @keyframes entradaTarjeta {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Modal Compacto */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        
        .modal-content { 
            background-color: #141414; 
            padding: 22px 28px; 
            border-radius: 10px; 
            width: 90%; 
            max-width: 460px;
            border: 1px solid #2a2a2a; 
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
        }

        .modal-header h3 { 
            color: #ffffff; 
            font-size: 1.25rem; 
            font-weight: 700;
            border-bottom: 2px solid #ff0015; 
            padding-bottom: 8px; 
            margin-bottom: 16px;
        }

        .close-btn { position: absolute; top: 16px; right: 20px; color: #aaa; font-size: 1.3rem; cursor: pointer; transition: color 0.2s; }
        .close-btn:hover { color: #ff0015; }

        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; color: #aaa; margin-bottom: 4px; font-size: 0.82rem; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px 12px; border-radius: 5px; background-color: #0b0b0b; color: #fff; border: 1px solid #2a2a2a; font-size: 0.88rem; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #ff0015; }

        .img-container-preview { display: flex; align-items: center; gap: 12px; margin-top: 6px; }
        .img-preview { width: 45px; height: 45px; object-fit: contain; display: none; border: 1px solid #333; border-radius: 5px; background: #000; padding: 2px; }

        .btn-guardar { background-color: #ff0015; color: #fff; border: none; padding: 10px; width: 100%; border-radius: 5px; font-weight: 600; font-size: 0.95rem; cursor: pointer; margin-top: 12px; transition: background 0.3s; }
        .btn-guardar:hover { background-color: #cc0011; }

        @media (max-width: 992px) {
            .tarjeta-bloque-doble { grid-template-columns: 1fr; gap: 15px; }
            .divisor-vertical { width: 100%; height: 1px; margin: 10px 0; }
        }
    </style>
</head>
<body>

    <div class="header-seccion">
        <div>
            <h1>Editar <span>Catálogo</span></h1>
            <p>Bienvenido: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
            <a href="index.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
        </div>

        <div class="header-acciones">
            <!-- Selector de Orden con opciones del más nuevo, más antiguo y alfabético -->
            <div class="ordenar-box">
                <label for="selectOrden"><i class="fas fa-sort"></i> Ordenar:</label>
                <select id="selectOrden" class="select-orden-small" onchange="location = this.value;">
                    <option value="editar-catalogo.php?orden=desc" <?php echo ($orden == 'desc') ? 'selected' : ''; ?>>Más recientes</option>
                    <option value="editar-catalogo.php?orden=asc" <?php echo ($orden == 'asc') ? 'selected' : ''; ?>>Más antiguos</option>
                    <option value="editar-catalogo.php?orden=az" <?php echo ($orden == 'az') ? 'selected' : ''; ?>>A - Z</option>
                    <option value="editar-catalogo.php?orden=za" <?php echo ($orden == 'za') ? 'selected' : ''; ?>>Z - A</option>
                </select>
            </div>

            <button class="btn-crear" onclick="abrirModalCrear()"><i class="fas fa-plus"></i> Nuevo Producto</button>
        </div>
    </div>

    <div class="panel-fondo">
        <div class="lista-tarjetas-grandes">
            <?php if (empty($bloques_helados)): ?>
                <p style="text-align: center; color: #aaa; padding: 20px;">No hay productos registrados en el catálogo.</p>
            <?php else: ?>
                <?php foreach ($bloques_helados as $index => $par): ?>
                    <div class="tarjeta-bloque-doble" style="animation-delay: <?php echo $index * 0.10; ?>s;">
                        
                        <!-- Producto 1 -->
                        <div class="helado-subseccion">
                            <div class="acciones-tarjeta">
                                <button class="btn-accion editar" title="Editar" onclick="abrirModalEditar('<?php echo $par[0]['id']; ?>', '<?php echo htmlspecialchars(addslashes($par[0]['codigo_producto'])); ?>', '<?php echo htmlspecialchars(addslashes($par[0]['nombre_producto'])); ?>', '<?php echo htmlspecialchars(addslashes($par[0]['presentacion'])); ?>', '<?php echo htmlspecialchars(addslashes($par[0]['ingredientes'])); ?>', '<?php echo htmlspecialchars($par[0]['ruta_imagen']); ?>')"><i class="fas fa-pencil"></i></button>
                                <button class="btn-accion eliminar" title="Eliminar" onclick="confirmarEliminar('<?php echo $par[0]['id']; ?>', '<?php echo htmlspecialchars(addslashes($par[0]['nombre_producto'])); ?>')"><i class="fas fa-trash"></i></button>
                            </div>
                            <div class="helado-img-box">
                                <?php if (!empty($par[0]['ruta_imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($par[0]['ruta_imagen']); ?>" alt="Foto">
                                <?php else: ?>
                                    <i class="fas fa-ice-cream" style="font-size: 4rem; color: #2a2a2a;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="helado-info">
                                <h3><?php echo htmlspecialchars($par[0]['nombre_producto'] ?: $par[0]['codigo_producto']); ?></h3>
                                <?php if (!empty($par[0]['presentacion'])): ?>
                                    <span class="badge-medida"><?php echo htmlspecialchars($par[0]['presentacion']); ?></span>
                                <?php endif; ?>
                                <p class="helado-ingredientes"><?php echo htmlspecialchars($par[0]['ingredientes']); ?></p>
                            </div>
                        </div>

                        <?php if (isset($par[1])): ?>
                            <div class="divisor-vertical"></div>

                            <!-- Producto 2 -->
                            <div class="helado-subseccion">
                                <div class="acciones-tarjeta">
                                    <button class="btn-accion editar" title="Editar" onclick="abrirModalEditar('<?php echo $par[1]['id']; ?>', '<?php echo htmlspecialchars(addslashes($par[1]['codigo_producto'])); ?>', '<?php echo htmlspecialchars(addslashes($par[1]['nombre_producto'])); ?>', '<?php echo htmlspecialchars(addslashes($par[1]['presentacion'])); ?>', '<?php echo htmlspecialchars(addslashes($par[1]['ingredientes'])); ?>', '<?php echo htmlspecialchars($par[1]['ruta_imagen']); ?>')"><i class="fas fa-pencil"></i></button>
                                    <button class="btn-accion eliminar" title="Eliminar" onclick="confirmarEliminar('<?php echo $par[1]['id']; ?>', '<?php echo htmlspecialchars(addslashes($par[1]['nombre_producto'])); ?>')"><i class="fas fa-trash"></i></button>
                                </div>
                                <div class="helado-img-box">
                                    <?php if (!empty($par[1]['ruta_imagen'])): ?>
                                        <img src="<?php echo htmlspecialchars($par[1]['ruta_imagen']); ?>" alt="Foto">
                                    <?php else: ?>
                                        <i class="fas fa-ice-cream" style="font-size: 4rem; color: #2a2a2a;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="helado-info">
                                    <h3><?php echo htmlspecialchars($par[1]['nombre_producto'] ?: $par[1]['codigo_producto']); ?></h3>
                                    <?php if (!empty($par[1]['presentacion'])): ?>
                                        <span class="badge-medida"><?php echo htmlspecialchars($par[1]['presentacion']); ?></span>
                                    <?php endif; ?>
                                    <p class="helado-ingredientes"><?php echo htmlspecialchars($par[1]['ingredientes']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ventana Modal -->
    <div class="modal-overlay" id="modalFormulario">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModal()">&times;</span>
            <div class="modal-header">
                <h3 id="modalTitulo">Producto</h3>
            </div>
            
            <form id="formProducto" action="procesar_catalogo.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" id="accionInput" value="crear">
                <input type="hidden" name="id" id="idInput">

                <div class="form-group">
                    <label for="codigoInput">Código de Producto: </label>
                    <input type="text" name="codigo_producto" id="codigoInput" required placeholder="Ej: 280451" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="nombreInput">Nombre del Producto:</label>
                    <input type="text" name="nombre_producto" id="nombreInput" required placeholder="Ej: CHOCOLATE TRADICIONAL 700cm3" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="presentacionInput">Presentación (Ej: 700cm3, 4,4L):</label>
                    <input type="text" name="presentacion" id="presentacionInput" placeholder="Ej: 700cm3, 4,4L" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="ingredientesInput">Descripción / Ingredientes:</label>
                    <textarea name="ingredientes" id="ingredientesInput" rows="2" placeholder="Descripción breve..." autocomplete="off"></textarea>
                </div>

                <div class="form-group">
                    <label for="imagenInput">Fotografía del Producto:</label>
                    <div class="img-container-preview">
                        <input type="file" name="imagen" id="imagenInput" accept="image/*" onchange="previewImagen(this)">
                        <img id="imgPreview" class="img-preview" alt="Vista previa">
                    </div>
                </div>

                <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModalCrear() {
            document.getElementById('modalFormulario').style.display = 'flex';
            document.getElementById('modalTitulo').innerHTML = 'Crear <span style="color: #ff0015;">Nuevo Producto</span>';
            document.getElementById('accionInput').value = 'crear';
            document.getElementById('formProducto').reset();
            document.getElementById('codigoInput').readOnly = false;
            document.getElementById('imgPreview').style.display = 'none';
        }

        function abrirModalEditar(id, codigo, nombre, presentacion, ingredientes, rutaImagen) {
            document.getElementById('modalFormulario').style.display = 'flex';
            document.getElementById('modalTitulo').innerHTML = 'Editar: <span style="color: #ff0015;">' + nombre + '</span>';
            document.getElementById('accionInput').value = 'editar';
            document.getElementById('idInput').value = id;
            document.getElementById('codigoInput').value = codigo;
            document.getElementById('codigoInput').readOnly = true;
            document.getElementById('nombreInput').value = nombre;
            document.getElementById('presentacionInput').value = presentacion;
            document.getElementById('ingredientesInput').value = ingredientes;
            
            let preview = document.getElementById('imgPreview');
            if(rutaImagen) {
                preview.src = rutaImagen;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        function cerrarModal() {
            document.getElementById('modalFormulario').style.display = 'none';
        }

        function previewImagen(input) {
            let preview = document.getElementById('imgPreview');
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function confirmarEliminar(id, nombre) {
            Swal.fire({
                title: '¿Eliminar producto?',
                text: `¿Deseas eliminar "${nombre}" del catálogo?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff0015',
                cancelButtonColor: '#333',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                background: '#1a1a1a',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `procesar_catalogo.php?accion=eliminar&id=${id}`;
                }
            });
        }
    </script>
</body>
</html>