<?php
session_start();

// 1. Verificación de autenticación básica
if (!isset($_SESSION['user']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

// 2. Control de accesos por roles (Preventista y Administrador)
$cargos_autorizados = ['preventista', 'administrador', 'ventas']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? $_SESSION['rol'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// 3. Incluir la conexión a MySQL
require_once 'conexion.php';

$nombre_usuario = $_SESSION['user'] ?? $_SESSION['nombre'] ?? $_SESSION['nombre_usuario'] ?? $_SESSION['usuario'] ?? 'Preventista';
$rol_usuario = $_SESSION['cargo'] ?? $_SESSION['rol'] ?? 'Preventista';

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
    <title>La Argentina - Consulta de Disponibilidad</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #0b0b0b; color: #ffffff; padding: 30px; }
        
        /* Cabecera corporativa */
        .header-seccion { margin-bottom: 20px; }
        .header-seccion h1 { font-size: 2rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
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
            max-width: 380px;
            font-size: 0.9rem;
        }
        .input-buscador:focus { border-color: #ff0015; }

        /* TABLA EXTENDIDA */
        .tabla-contenedor {
            width: 100%;
            overflow-x: auto;
            max-height: 680px;
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
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Consulta de <span>Disponibilidad</span></h1>
        <p> Bienvenido: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
        <a href="index.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
    </div>

    <div class="contenedor-full">
        
        <div class="panel">
            <div class="panel-header">
                <h2>Inventario Disponible</h2>
                <span style="font-size: 0.8rem; color: #888;"><i class="fas fa-sync-alt"></i> Última act: <?php echo $ultima_fecha; ?></span>
            </div>
            
            <div class="toolbar">
                <input type="text" id="buscadorInventario" placeholder="🔍 Buscar producto, código o categoría..." class="input-buscador">
            </div>

            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th style="text-align: left;">PRODUCTOS</th>
                            <th>CANT</th>
                            <th>DÍAS VENTA</th>
                            <th>PEN. LIBERAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM disponibilidad_inventario ORDER BY id ASC";
                        $resultado = $conexion->query($query);
                        $categoria_anterior = "";

                        if ($resultado && $resultado->num_rows > 0) {
                            while ($row = $resultado->fetch_assoc()) {
                                if ($row['categoria'] !== $categoria_anterior) {
                                    $categoria_anterior = $row['categoria'];
                                    echo '<tr class="fila-categoria"><td colspan="5">' . htmlspecialchars($categoria_anterior) . '</td></tr>';
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
                                <tr>
                                    <td class="col-codigo"><?php echo htmlspecialchars($row['codigo']); ?></td>
                                    <td class="text-left col-producto"><?php echo htmlspecialchars($row['producto']); ?></td>
                                    <td class="col-cant"><?php echo number_format($row['cantidad'], 0, ',', '.'); ?></td>
                                    <td class="col-dias"><span class="badge <?php echo $clase_badge; ?>"><?php echo $row['dias_venta']; ?></span></td>
                                    <td class="col-pen"><?php echo $row['pen_liberar']; ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="5" style="text-align:center; padding: 30px; color: #888;">No hay inventario cargado.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // Buscador Dinámico
        document.getElementById('buscadorInventario').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let filas = document.querySelectorAll('.tabla-contenedor tbody tr');

            filas.forEach(function(fila) {
                let textoFila = fila.textContent.toLowerCase();
                fila.style.display = textoFila.includes(filtro) ? '' : 'none';
            });
        });
    </script>
</body>
</html>