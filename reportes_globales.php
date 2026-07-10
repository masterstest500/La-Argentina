<?php
session_start();
// 1. Control de acceso de seguridad
if (!isset($_SESSION['user']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    header("Location: login.php"); // O la página de redirección que uses
    exit();
}

// Obtener el nombre del administrador logueado para personalizar la vista
$nombre_admin = $_SESSION['user']; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Argentina - Reportes Globales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        /* Estilos base alineados al tema oscuro del sistema */
        body {
            background-color: #0b0b0b;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .contenedor-reportes {
            max-width: 600px;
            margin: 50px auto;
            background-color: #16161a;
            border: 1px solid #c81010; /* Borde rojo sutil */
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }

        .encabezado-vista {
            border-bottom: 2px solid #24242b;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .encabezado-vista h2 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .encabezado-vista h2 span {
            color: #c81010; /* Resalte rojo en el título */
        }

        .encabezado-vista p {
            margin: 5px 0 0 0;
            color: #888893;
            font-size: 13px;
        }

        /* Estructura del Formulario */
        .grupo-filtro {
            margin-bottom: 20px;
        }

        .grupo-filtro label {
            display: block;
            margin-bottom: 8px;
            color: #e1e1e6;
            font-size: 14px;
            font-weight: 600;
        }

        /* Inputs de tipo fecha adaptados al modo oscuro */
        .input-fecha {
            width: 100%;
            padding: 12px;
            background-color: #1f1f24;
            border: 1px solid #2e2e38;
            border-radius: 6px;
            color: #ffffff;
            font-size: 15px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .input-fecha:focus {
            outline: none;
            border-color: #c81010;
        }

        /* Ajuste para el selector de fecha nativo del navegador en modo oscuro */
        ::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        .fila-fechas {
            display: flex;
            gap: 15px;
        }

        .columna-fecha {
            flex: 1;
        }

        /* Botón de acción con estilo Prolacteca */
        .btn-generar {
            width: 100%;
            background-color: #c81010;
            color: #ffffff;
            border: none;
            padding: 14px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-generar:hover {
            background-color: #a00c0c;
        }

        .btn-generar:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>

    <div class="contenedor-reportes">
        <div class="encabezado-vista">
            <h2>📊 Módulo de <span>Reportes Globales</span></h2>
            <p>Sesión activa: <?php echo htmlspecialchars($nombre_admin); ?> (Administrador)</p>
            <a href="index.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
        </div>

        <form action="reporte_global_pdf.php" method="GET" target="_blank">
            
            <div class="fila-fechas">
                <div class="columna-fecha grupo-filtro">
                    <label for="fecha_inicio">Fecha de Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="input-fecha" required>
                </div>

                <div class="columna-fecha grupo-filtro">
                    <label for="fecha_fin">Fecha de Finalización</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="input-fecha" required>
                </div>
            </div>

            <button type="submit" class="btn-generar">
                📄 Generar Reporte en PDF
            </button>
        </form>
    </div>

    <script>
        // Pequeña validación lógica: No permitir que la fecha fin sea menor que la fecha inicio
        const finputInicio = document.getElementById('fecha_inicio');
        const finputFin = document.getElementById('fecha_fin');

        finputInicio.addEventListener('change', function() {
            finputFin.min = this.value;
        });
    </script>
</body>
</html>