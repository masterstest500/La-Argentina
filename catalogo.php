<?php
session_start();

// 1. Verificación de autenticación básica
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['user']) && !isset($_SESSION['nombre']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

// 2. Control de accesos por roles (Filtro estricto)
$cargos_autorizados = ['preventista', 'administrador']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? $_SESSION['rol'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// Obtención robusta del nombre y rol del usuario
$nombre_usuario = $_SESSION['user'] ?? $_SESSION['nombre'] ?? $_SESSION['nombre_usuario'] ?? $_SESSION['usuario'] ?? 'Preventista';
$rol_usuario = $_SESSION['cargo'] ?? $_SESSION['rol'] ?? 'Preventista';

// 3. Conexión a la base de datos
// IMPORTANTE: Asegúrate de que el nombre del archivo sea el correcto (ej: conexion.php, db.php)
require_once 'conexion.php'; 

/* 
 * 4. Consulta dinámica usando LEFT JOIN
 * Traemos los productos del inventario donde la cantidad > 0.
 * Cruzamos con detalles_catalogo para obtener la foto y los ingredientes si existen.
 */
$sql = "SELECT 
            i.codigo AS codigo_producto, 
            i.producto AS nombre, 
            d.ingredientes, 
            d.presentacion, 
            d.ruta_imagen AS imagen 
        FROM disponibilidad_inventario i
        LEFT JOIN detalles_catalogo d ON i.codigo = d.codigo_producto
        WHERE i.cantidad > 0";

// Ejecución de la consulta usando MySQLi
$resultado_query = $conexion->query($sql);

if (!$resultado_query) {
    die("Error en la consulta SQL: " . $conexion->error);
}

$resultados = $resultado_query->fetch_all(MYSQLI_ASSOC);

// 5. Mapeo de resultados y asignación de valores por defecto
$catalogo_helados = [];

foreach ($resultados as $row) {
    $catalogo_helados[] = [
        "codigo" => $row['codigo_producto'],
        "nombre" => $row['nombre'],
        // Si no hay ingredientes registrados, mostramos un texto por defecto
        "ingredientes" => !empty($row['ingredientes']) ? $row['ingredientes'] : "Sin descripción disponible",
        // Si no hay presentación, mostramos N/A
        "presentacion" => !empty($row['presentacion']) ? $row['presentacion'] : "",
        // Si no hay imagen, usamos tu logo/imagen por defecto
        "imagen" => !empty($row['imagen']) ? $row['imagen'] : "" 
    ];
}

// Agrupamos la lista de helados de 2 en 2 para las tarjetas dobles
$bloques_helados = array_chunk($catalogo_helados, 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Argentina - Catálogo de Productos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #0b0b0b; color: #ffffff; padding: 40px; }
        
        /* Cabecera corporativa */
        .header-seccion { margin-bottom: 30px; }
        .header-seccion h1 { font-size: 2rem; font-weight: 700; display: flex; align-items: center; gap: 10px; text-transform: uppercase; }
        .header-seccion h1 span { color: #ff0015; }
        .header-seccion p { color: #aaaaaa; margin-top: 5px; font-size: 0.95rem; }
        .btn-volver { color: #ffffff; text-decoration: none; display: inline-block; margin-top: 10px; font-size: 0.9rem; transition: color 0.3s; }
        .btn-volver:hover { color: #ff0015; }

        /* Panel contenedor principal */
        .panel-fondo { background-color: #141414; border-radius: 8px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); margin-bottom: 20px; }
        .panel-embudo { border-top: 4px solid #ff0015; }

        .panel-header {
            margin-bottom: 25px;
        }

        .panel-header h2 {
            font-size: 1.2rem;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ffffff;
        }

        /* CONTENEDOR VERTICAL DE TARJETAS (Arriba hacia Abajo) */
        .lista-tarjetas-grandes {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        /* TARJETA CADA UNA EN DUO (Gran bloque horizontal) */
        .tarjeta-bloque-doble {
            background-color: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-left: 5px solid #ff0015;
            border-radius: 10px;
            padding: 35px 30px;
            min-height: 220px;
            display: grid;
            grid-template-columns: 1fr auto 1fr; /* Mitad 1 | Divisor | Mitad 2 */
            align-items: center;
            gap: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            
            /* Animación de entrada */
            opacity: 0;
            animation: entradaTarjeta 0.6s ease-out forwards;
            transition: border-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }

        .tarjeta-bloque-doble:hover {
            border-color: #3d3d3d;
            border-left-color: #ff0015;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 0, 21, 0.12);
        }

        /* Sub-sección interna para cada uno de los 2 helados */
        .helado-subseccion {
            display: flex;
            align-items: center;
            gap: 22px;
            padding: 10px;
        }

        /* Imagen grande del Pote/Tarrina */
        .helado-img-box {
            width: 200px;
            height: 200px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            padding: 0;
            cursor: pointer; /* Añadido para dar la sensación de que se puede clickear en el paso 4 */
        }

        .helado-img-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 6px 8px rgba(0,0,0,0.7));
            transition: transform 0.3s ease;
        }

        .tarjeta-bloque-doble:hover .helado-img-box img {
            transform: scale(1.06);
        }

        /* Info de cada helado */
        .helado-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .helado-titulo-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .helado-titulo-row h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            letter-spacing: 0.5px;
        }

        /* Badge con la capacidad/presentación (700cm3 / 2L) */
        .badge-medida {
            background-color: rgba(255, 0, 21, 0.15);
            color: #ff4d4d;
            border: 1px solid #ff0015;
            padding: 4px 12px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .helado-ingredientes {
            color: #aaaaaa;
            font-size: 0.90rem;
            line-height: 1.5;
            margin: 0;
        }

        /* Divisor central elegante entre los 2 helados */
        .divisor-vertical {
            width: 1px;
            height: 80%;
            background-color: #333333;
        }

        /* Estilos para la Ventana Modal de Edición */
        .modal-overlay {
            display: none; 
            position: fixed; 
            top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0, 0, 0, 0.8); 
            z-index: 1000; 
            align-items: center; 
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: #1a1a1a; 
            padding: 30px; 
            border-radius: 10px; 
            width: 90%; 
            max-width: 500px; 
            border: 1px solid #333;
            box-shadow: 0 10px 30px rgba(255, 0, 21, 0.2);
            position: relative;
        }

        .modal-header h3 {
            color: #ffffff;
            margin-bottom: 20px;
            font-size: 1.4rem;
            border-bottom: 2px solid #ff0015;
            padding-bottom: 10px;
        }

        .close-btn {
            position: absolute; 
            top: 20px; right: 20px; 
            color: #aaaaaa; 
            font-size: 1.5rem; 
            cursor: pointer; 
            transition: color 0.3s;
        }

        .close-btn:hover { color: #ff0015; }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block; color: #aaaaaa; margin-bottom: 5px; font-size: 0.9rem;
        }

        .form-group input, .form-group textarea {
            width: 100%; padding: 10px; border-radius: 5px;
            background-color: #0b0b0b; color: #ffffff;
            border: 1px solid #333; font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus, .form-group textarea:focus {
            outline: none; border-color: #ff0015;
        }

        .btn-guardar {
            background-color: #ff0015; color: #ffffff; border: none;
            padding: 12px 20px; width: 100%; border-radius: 5px;
            font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.3s;
            margin-top: 10px;
        }

        .btn-guardar:hover { background-color: #cc0011; }

        /* Keyframes Animación */
        @keyframes entradaTarjeta {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsividad (Móviles y Tablets) */
        @media (max-width: 992px) {
            .tarjeta-bloque-doble { grid-template-columns: 1fr; gap: 15px; }
            .divisor-vertical { width: 100%; height: 1px; margin: 10px 0; }
        }

        @media (max-width: 576px) {
            body { padding: 20px; }
            .helado-subseccion { flex-direction: column; align-items: flex-start; }
            .helado-img-box { width: 100%; height: 140px; }
        }
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Catálogo de <span>Productos</span></h1>
        <p>Ejecutivo de Calle: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
        <a href="index.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
    </div>

    <div class="panel-fondo panel-embudo">
        <div class="panel-header">
            <h2>Listado de Sabores y Presentaciones Disponibles</h2>
        </div>

        <div class="lista-tarjetas-grandes">
            <?php if (empty($bloques_helados)): ?>
        <p style="text-align: center; color: #aaaaaa; padding: 20px;">No hay productos con inventario disponible en este momento.</p>
            <?php else: ?>
                <?php foreach ($bloques_helados as $index => $par_helados): ?>
                    <div class="tarjeta-bloque-doble" style="animation-delay: <?php echo $index * 0.12; ?>s;">
                        
                        <!-- Primer helado de la tarjeta -->
                        <div class="helado-subseccion" style="cursor: pointer;" onclick="abrirModalEdicion('<?php echo htmlspecialchars($par_helados[0]['codigo']); ?>', '<?php echo addslashes($par_helados[0]['nombre']); ?>', '<?php echo addslashes($par_helados[0]['presentacion']); ?>', '<?php echo addslashes($par_helados[0]['ingredientes']); ?>')">
                            <div class="helado-img-box" data-codigo="<?php echo htmlspecialchars($par_helados[0]['codigo']); ?>">
                                <!-- Validación de Imagen o Ícono Placeholder -->
                                <?php if (!empty($par_helados[0]['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($par_helados[0]['imagen']); ?>" alt="<?php echo htmlspecialchars($par_helados[0]['nombre']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-ice-cream" style="font-size: 5rem; color: #2a2a2a; text-shadow: 0 4px 6px rgba(0,0,0,0.5);"></i>
                                <?php endif; ?>
                            </div>
                            <div class="helado-info">
                                <div class="helado-titulo-row">
                                    <h3><?php echo htmlspecialchars($par_helados[0]['nombre']); ?></h3>
                                    <!-- Validación para ocultar el badge si no hay presentación -->
                                    <?php if (!empty($par_helados[0]['presentacion'])): ?>
                                        <span class="badge-medida"><?php echo htmlspecialchars($par_helados[0]['presentacion']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="helado-ingredientes"><?php echo htmlspecialchars($par_helados[0]['ingredientes']); ?></p>
                            </div>
                        </div>

                        <?php if (isset($par_helados[1])): ?>
                            <!-- Línea Divisora -->
                            <div class="divisor-vertical"></div>

                            <!-- Segundo helado de la tarjeta -->
                            <div class="helado-subseccion" style="cursor: pointer;" onclick="abrirModalEdicion('<?php echo htmlspecialchars($par_helados[1]['codigo']); ?>', '<?php echo addslashes($par_helados[1]['nombre']); ?>', '<?php echo addslashes($par_helados[1]['presentacion']); ?>', '<?php echo addslashes($par_helados[1]['ingredientes']); ?>')">
                                <div class="helado-img-box" data-codigo="<?php echo htmlspecialchars($par_helados[1]['codigo']); ?>">
                                    <!-- Validación de Imagen o Ícono Placeholder -->
                                    <?php if (!empty($par_helados[1]['imagen'])): ?>
                                        <img src="<?php echo htmlspecialchars($par_helados[1]['imagen']); ?>" alt="<?php echo htmlspecialchars($par_helados[1]['nombre']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-ice-cream" style="font-size: 5rem; color: #2a2a2a; text-shadow: 0 4px 6px rgba(0,0,0,0.5);"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="helado-info">
                                    <div class="helado-titulo-row">
                                        <h3><?php echo htmlspecialchars($par_helados[1]['nombre']); ?></h3>
                                        <!-- Validación para ocultar el badge si no hay presentación -->
                                        <?php if (!empty($par_helados[1]['presentacion'])): ?>
                                            <span class="badge-medida"><?php echo htmlspecialchars($par_helados[1]['presentacion']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="helado-ingredientes"><?php echo htmlspecialchars($par_helados[1]['ingredientes']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para Editar Detalles -->
    <div class="modal-overlay" id="modalEdicion">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModal()">&times;</span>
            <div class="modal-header">
                <h3>Editar: <span id="nombreProductoModal" style="color:#ff0015;"></span></h3>
            </div>
            
            <!-- Formulario que enviará los datos al servidor -->
            <form id="formEdicion" action="guardar_detalles.php" method="POST" enctype="multipart/form-data">
                <!-- Campo oculto para saber qué producto estamos editando -->
                <input type="hidden" name="codigo_producto" id="codigoProductoModal">
                
                <div class="form-group">
                    <label for="presentacionInput">Presentación (Ej: 700cm3, 2 Litros):</label>
                    <input type="text" name="presentacion" id="presentacionInput" placeholder="Aparecerá en el recuadro rojo">
                </div>

                <div class="form-group">
                    <label for="ingredientesInput">Descripción / Ingredientes:</label>
                    <textarea name="ingredientes" id="ingredientesInput" rows="3" placeholder="Describe el sabor..."></textarea>
                </div>

                <div class="form-group">
                    <label for="imagenInput">Fotografía del Producto:</label>
                    <input type="file" name="imagen" id="imagenInput" accept="image/*">
                </div>

                <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        // Función para abrir el modal y cargar los datos actuales
        function abrirModalEdicion(codigo, nombre, presentacionActual, ingredientesActuales) {
            document.getElementById('modalEdicion').style.display = 'flex';
            
            // Llenamos el modal con la info del helado clickeado
            document.getElementById('codigoProductoModal').value = codigo;
            document.getElementById('nombreProductoModal').innerText = nombre;
            
            // Si no es el texto por defecto, lo colocamos en el input
            document.getElementById('presentacionInput').value = presentacionActual;
            
            if(ingredientesActuales !== "Sin descripción disponible" && ingredientesActuales !== "Características no especificadas.") {
                document.getElementById('ingredientesInput').value = ingredientesActuales;
            } else {
                document.getElementById('ingredientesInput').value = ""; // Lo dejamos limpio para escribir
            }
        }

        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('modalEdicion').style.display = 'none';
            document.getElementById('formEdicion').reset(); // Limpiamos el formulario al cerrar
        }

        // Cierra el modal si se hace clic fuera de la caja negra
        window.onclick = function(event) {
            let modal = document.getElementById('modalEdicion');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>

</body>
</html>