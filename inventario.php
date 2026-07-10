<?php
// 1. 🛡️ Control de Sesión y Escudo de Seguridad
session_start();
$cargoUsuario = isset($_SESSION['cargo']) ? strtolower($_SESSION['cargo']) : '';

// Permitir el acceso únicamente a administradores y personal de ventas
if (!isset($_SESSION['user']) || !in_array($cargoUsuario, ['administrador', 'ventas'])) {
    header("Location: index.php?error=acceso_restringido");
    exit();
}

include('conexion.php'); 
$nombre_usuario = $_SESSION['user'];
$rol_usuario = $_SESSION['cargo'];

// ==========================================
// 2. 🔥 MOTOR DEL BOTÓN: PROCESAR EL FORMULARIO (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sabor       = trim($_POST['sabor']);
    $stock_potes = intval($_POST['stock_potes']);
    $precio      = floatval($_POST['precio']);

    if (!empty($sabor) && $stock_potes >= 0 && $precio > 0) {
        
        // Verificamos si el sabor ya existe en la cava (ignorando mayúsculas/minúsculas)
        $sql_check = "SELECT id, stock_potes FROM productos WHERE LOWER(sabor) = LOWER('$sabor')";
        $res_check = mysqli_query($conexion, $sql_check);

        if (mysqli_num_rows($res_check) > 0) {
            // ¡Ya existe! Sumamos la nueva mercancía al stock actual y actualizamos precio
            $row = mysqli_fetch_assoc($res_check);
            $nuevo_stock = $row['stock_potes'] + $stock_potes;
            
            $sql_query = "UPDATE productos SET stock_potes = $nuevo_stock, precio = $precio WHERE id = " . $row['id'];
        } else {
            // ¡Sabor nuevo! Se registra desde cero
            $sql_query = "INSERT INTO productos (sabor, stock_potes, precio) VALUES ('$sabor', $stock_potes, $precio)";
        }

        // Ejecutamos la consulta
        if (mysqli_query($conexion, $sql_query)) {
            // Redirección limpia para limpiar los campos del formulario y evitar duplicados al pulsar F5
            header("Location: inventario.php?guardado=exito");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:image" content="img/logos/logo3.png">
    <title>La Argentina - Gestión de Inventario</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #0b0b0b;
            color: #ffffff;
            padding: 40px;
        }

        .header-seccion {
            margin-bottom: 30px;
        }

        .header-seccion h1 {
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-seccion h1 span {
            color: #ff0015; /* Rojo característico */
        }

        .header-seccion p {
            color: #aaaaaa;
            margin-top: 5px;
            font-size: 0.95rem;
        }

        .btn-volver {
            color: #ffffff;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .btn-volver:hover {
            color: #ff0015;
        }

        /* Contenedor de dos columnas */
        .dashboard-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        /* Columna Izquierda: Formulario */
        .panel-formulario {
            flex: 1;
            min-width: 350px;
            background-color: #141414;
            border-left: 4px solid #ff0015;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            height: fit-content;
        }

        .panel-formulario h2 {
            font-size: 1.3rem;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .grupo-input {
            margin-bottom: 20px;
        }

        .grupo-input label {
            display: block;
            color: #ff0015;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .grupo-input input {
            width: 100%;
            padding: 12px;
            background-color: #222222;
            border: 1px solid #333333;
            border-radius: 6px;
            color: #ffffff;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .grupo-input input:focus {
            outline: none;
            border-color: #ff0015;
        }

        .btn-guardar {
            width: 100%;
            padding: 14px;
            background-color: #ff0015;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-guardar:hover {
            background-color: #ff0019;
            transform: translateY(-2px);
        }

        /* Columna Derecha: Tabla de Stock */
        .panel-tabla {
            flex: 1.5;
            min-width: 500px;
            background-color: #141414;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }

        .panel-tabla h2 {
            font-size: 1.3rem;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            color: #ff0015;
            padding: 12px;
            border-bottom: 2px solid #333333;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        td {
            padding: 14px 12px;
            border-bottom: 1px solid #222222;
            font-size: 0.95rem;
        }

        tr:hover td {
            background-color: #1c1c1c;
        }

        /* Alertas visuales de stock */
        .badge-stock {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-critico {
            background-color: rgba(230, 57, 70, 0.2);
            color: #ff0015;
            border: 1px solid #fd0015;
        }

        .stock-ok {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Control de <span>Inventario y Sabores</span></h1>
        <p>Bienvenido, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
        <a href="index.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
    </div>

    <div class="dashboard-container">
        
        <div class="panel-formulario">
            <h2>Cargar / Reponer Sabor</h2>
            <form action="inventario.php" method="POST">
                
                <div class="grupo-input">
                    <label for="sabor">Nombre del Sabor / Producto</label>
                    <input type="text" id="sabor" name="sabor" placeholder="Ej. Tramontana Especial" required>
                </div>

                <div class="grupo-input">
                    <label for="stock_potes">Cantidad de Potes a Ingresar</label>
                    <input type="number" id="stock_potes" name="stock_potes" placeholder="Ej. 15" min="1" required>
                </div>

                <div class="grupo-input">
                    <label for="precio">Precio por Pote ($)</label>
                    <input type="number" id="precio" name="precio" placeholder="Ej. 25.50" step="0.01" min="0.1" required>
                </div>

                <button type="submit" class="btn-guardar">Actualizar Inventario</button>
            </form>
        </div>

        <div class="panel-tabla">
            <h2>Stock en Cavas en Tiempo Real</h2>
            <table>
                <thead>
                    <tr>
                        <th>Sabor</th>
                        <th>Existencia (Potes)</th>
                        <th>Precio Unitario</th>
                        <th>Última Actualización</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consultamos los productos cargados
                    $query = "SELECT * FROM productos ORDER BY sabor ASC";
                    $result = mysqli_query($conexion, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Lógica para pintar alerta si el stock es bajo (menos de 5 potes)
                            $clase_stock = ($row['stock_potes'] <= 5) ? 'stock-critico' : 'stock-ok';
                            
                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($row['sabor']) . "</strong></td>";
                            echo "<td><span class='badge-stock $clase_stock'>" . intval($row['stock_potes']) . " Potes</span></td>";
                            echo "<td>$" . number_format($row['precio'], 2) . "</td>";
                            echo "<td>" . date('d/m/Y g:i A', strtotime($row['fecha_actualizacion'])) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; color:#777;'>No hay sabores registrados en el inventario.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>