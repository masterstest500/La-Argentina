<?php
session_start();

// 1. Autenticación básica
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['user']) && !isset($_SESSION['nombre']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

// 2. Control de accesos
$cargos_autorizados = ['preventista']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? $_SESSION['rol'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

$nombre_usuario = $_SESSION['user'] ?? $_SESSION['nombre'] ?? 'Preventista';
$rol_usuario = $_SESSION['cargo'] ?? $_SESSION['rol'] ?? 'Preventista';

require_once 'conexion.php'; 

// Consulta uniendo ambas tablas
$sql = "SELECT 
            i.codigo AS codigo_producto, 
            COALESCE(d.nombre_producto, i.producto) AS nombre, 
            d.ingredientes, 
            d.presentacion, 
            d.ruta_imagen AS imagen 
        FROM disponibilidad_inventario i
        LEFT JOIN detalles_catalogo d ON i.codigo = d.codigo_producto
        WHERE i.cantidad > 0";

$resultado_query = $conexion->query($sql);

if (!$resultado_query) {
    die("Error en la consulta SQL: " . $conexion->error);
}

$resultados = $resultado_query->fetch_all(MYSQLI_ASSOC);

$catalogo_helados = [];
foreach ($resultados as $row) {
    $catalogo_helados[] = [
        "codigo" => $row['codigo_producto'],
        "nombre" => $row['nombre'],
        "ingredientes" => !empty($row['ingredientes']) ? $row['ingredientes'] : "Sin descripción disponible",
        "presentacion" => !empty($row['presentacion']) ? $row['presentacion'] : "",
        "imagen" => !empty($row['imagen']) ? $row['imagen'] : "" 
    ];
}

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
        
        .header-seccion { margin-bottom: 30px; }
        .header-seccion h1 { font-size: 2rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .header-seccion h1 span { color: #ff0015; }
        .header-seccion p { color: #aaaaaa; margin-top: 5px; font-size: 0.95rem; }
        .btn-volver { color: #ffffff; text-decoration: none; display: inline-block; margin-top: 10px; font-size: 0.9rem; transition: color 0.3s; }
        .btn-volver:hover { color: #ff0015; }

        .panel-fondo { background-color: #141414; border-radius: 8px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); margin-bottom: 20px; }
        .panel-embudo { border-top: 4px solid #ff0015; }
        .panel-header { margin-bottom: 25px; }
        .panel-header h2 { font-size: 1.2rem; margin: 0; text-transform: uppercase; letter-spacing: 1px; color: #ffffff; }

        .lista-tarjetas-grandes { display: flex; flex-direction: column; gap: 25px; }

        .tarjeta-bloque-doble {
            background-color: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-left: 5px solid #ff0015;
            border-radius: 10px;
            padding: 35px 30px;
            min-height: 220px;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            opacity: 0;
            animation: entradaTarjeta 0.6s ease-out forwards;
        }

        .helado-subseccion { display: flex; align-items: center; gap: 22px; padding: 10px; }
        .helado-img-box { width: 200px; height: 200px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .helado-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 6px 8px rgba(0,0,0,0.7)); }
        
        .helado-info { flex: 1; display: flex; flex-direction: column; gap: 8px; }
        .helado-titulo-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .helado-titulo-row h3 { font-size: 1.3rem; font-weight: 700; color: #ffffff; margin: 0; }
        
        .badge-medida {
            background-color: rgba(255, 0, 21, 0.15);
            color: #ff4d4d;
            border: 1px solid #ff0015;
            padding: 4px 12px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .helado-ingredientes { color: #aaaaaa; font-size: 0.90rem; line-height: 1.5; margin: 0; }
        .divisor-vertical { width: 1px; height: 80%; background-color: #333333; }

        @keyframes entradaTarjeta {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 992px) {
            .tarjeta-bloque-doble { grid-template-columns: 1fr; gap: 15px; }
            .divisor-vertical { width: 100%; height: 1px; margin: 10px 0; }
        }
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Catálogo de <span>Productos</span></h1>
        <p>Bienvenido: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
        <a href="index.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
    </div>

    <div class="panel-fondo panel-embudo">
        <div class="panel-header">
            <h2>Listado de Sabores y Presentaciones Disponibles</h2>
        </div>

        <div class="lista-tarjetas-grandes">
            <?php if (empty($bloques_helados)): ?>
                <p style="text-align: center; color: #aaaaaa; padding: 20px;">No hay productos disponibles en este momento.</p>
            <?php else: ?>
                <?php foreach ($bloques_helados as $index => $par_helados): ?>
                    <div class="tarjeta-bloque-doble" style="animation-delay: <?php echo $index * 0.10; ?>s;">
                        
                        <!-- Primer helado -->
                        <div class="helado-subseccion">
                            <div class="helado-img-box">
                                <?php if (!empty($par_helados[0]['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($par_helados[0]['imagen']); ?>" alt="<?php echo htmlspecialchars($par_helados[0]['nombre']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-ice-cream" style="font-size: 5rem; color: #2a2a2a;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="helado-info">
                                <div class="helado-titulo-row">
                                    <h3><?php echo htmlspecialchars($par_helados[0]['nombre']); ?></h3>
                                    <?php if (!empty($par_helados[0]['presentacion'])): ?>
                                        <span class="badge-medida"><?php echo htmlspecialchars($par_helados[0]['presentacion']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="helado-ingredientes"><?php echo htmlspecialchars($par_helados[0]['ingredientes']); ?></p>
                            </div>
                        </div>

                        <?php if (isset($par_helados[1])): ?>
                            <div class="divisor-vertical"></div>

                            <!-- Segundo helado -->
                            <div class="helado-subseccion">
                                <div class="helado-img-box">
                                    <?php if (!empty($par_helados[1]['imagen'])): ?>
                                        <img src="<?php echo htmlspecialchars($par_helados[1]['imagen']); ?>" alt="<?php echo htmlspecialchars($par_helados[1]['nombre']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-ice-cream" style="font-size: 5rem; color: #2a2a2a;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="helado-info">
                                    <div class="helado-titulo-row">
                                        <h3><?php echo htmlspecialchars($par_helados[1]['nombre']); ?></h3>
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

</body>
</html>