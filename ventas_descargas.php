<?php
session_start();

// 1. Verificación de autenticación básica
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['cargo'])) {
    // header("Location: login.php"); // Descomentar cuando unas todo el sistema
    // exit();
}

// 2. Control de accesos (Asegúrate de agregar el rol que usa Ventas)
$cargos_autorizados = ['ventas', 'administrador', 'preventista']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? 'ventas'); // 'ventas' por defecto para pruebas

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    // header("Location: index.php?error=acceso_denegado");
    // exit();
}

$nombre_usuario = $_SESSION['user'] ?? 'Usuario';
$cargo_display = ucfirst($cargo_usuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descarga de Documentos - La Argentina</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados (Mismos que captacion.php) -->
    <style>
        body {
            background-color: #0f0f0f;
            color: #ffffff;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .text-brand-red { color: #e50914 !important; }
        
        .card-custom {
            background-color: #1a1a1a;
            border: none;
            border-left: 4px solid #e50914;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .card-header-custom {
            background-color: transparent;
            border-bottom: 1px solid #333;
            padding: 1rem 1.25rem;
        }
        .card-title-custom {
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .form-label-custom {
            color: #e50914;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }
        .form-control-custom {
            background-color: #262626;
            border: 1px solid #333;
            color: #ffffff;
            border-radius: 6px;
        }
        .form-control-custom:focus {
            background-color: #2c2c2c;
            border-color: #e50914;
            color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(229, 9, 20, 0.25);
        }
        .form-control-custom::placeholder { color: #666; }
        
        .btn-brand-red {
            background-color: #e50914;
            border-color: #e50914;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-brand-red:hover {
            background-color: #b20710;
            border-color: #b20710;
            color: #fff;
        }
        .link-back {
            color: #ffffff;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }
        .link-back:hover { color: #e50914; }
        
        /* Estilos específicos para la caja de descargas */
        .doc-item {
            background-color: #262626;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            height: 100%;
        }
        .doc-title {
            font-size: 0.9rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #ccc;
        }
    </style>
</head>
<body>

    <div class="container mt-5 mb-5">
        
        <!-- Encabezado -->
        <header class="mb-4">
            <h1 class="fw-bold mb-1">Gestión de <span class="text-brand-red">Documentos</span></h1>
            <p class="text-secondary mb-3">
                Módulo de Ventas | Bienvenido: <strong class="text-white"><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($cargo_display); ?>)
            </p>
            <a href="index.php" class="link-back d-inline-block">&#8592; Volver al Menú Principal</a>
        </header>

        <!-- Buscador Inteligente por RIF -->
        <div class="card card-custom shadow-sm mb-4">
            <div class="card-header card-header-custom">
                <h5 class="card-title-custom">Seleccionar Cliente Registrado</h5>
            </div>
            <div class="card-body p-4">
                <form id="form_buscar" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label for="rif_busqueda" class="form-label form-label-custom">Seleccione el Cliente (El sistema consultará por su RIF)</label>
                        <select class="form-select form-select-custom form-select-lg" id="rif_busqueda" required>
                            <option value="" selected disabled>-- Seleccione un cliente de la lista --</option>
                            <?php
                            // Incluimos la conexión para poblar el selector dinámicamente
                            include('conexion.php');
                            $sql_sel = "SELECT rif_cliente, nombre_cliente FROM captaciones ORDER BY id DESC";
                            $res_sel = mysqli_query($conexion, $sql_sel);
                            
                            if ($res_sel) {
                                while($row = mysqli_fetch_assoc($res_sel)) {
                                    // El 'value' envía estrictamente el RIF por detrás a tu API JavaScript, 
                                    // mientras que el usuario ve el nombre de la empresa y el RIF.
                                    echo '<option value="' . htmlspecialchars($row['rif_cliente']) . '">';
                                    echo htmlspecialchars($row['nombre_cliente']) . ' (RIF: ' . htmlspecialchars($row['rif_cliente']) . ')';
                                    echo '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" id="btn_buscar" class="btn btn-brand-red btn-lg w-100">Consultar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alerta de Errores (Oculta por defecto) -->
        <div id="alerta_error" class="alert alert-danger d-none fw-bold" role="alert"></div>

        <!-- Resultados de Documentos (Oculto por defecto) -->
        <div id="resultados_documentos" class="d-none">
            
            <h4 class="mb-3">Archivos asociados a: <span id="cliente_nombre" class="text-brand-red"></span></h4>
            
            <div class="row g-3">
                <!-- 1. Formato de Captación -->
                <div class="col-md-3 col-sm-6">
                    <div class="doc-item shadow-sm">
                        <span class="doc-title">Formato de Captación</span>
                        <a id="btn_formato" class="btn btn-sm w-100 mt-auto" download>Descargar</a>
                    </div>
                </div>

                <!-- 2. Acta Constitutiva -->
                <div class="col-md-3 col-sm-6">
                    <div class="doc-item shadow-sm">
                        <span class="doc-title">Acta Constitutiva</span>
                        <a id="btn_acta" class="btn btn-sm w-100 mt-auto" download>Descargar</a>
                    </div>
                </div>

                <!-- 3. RIF -->
                <div class="col-md-3 col-sm-6">
                    <div class="doc-item shadow-sm">
                        <span class="doc-title">Documento RIF</span>
                        <a id="btn_rif" class="btn btn-sm w-100 mt-auto" download>Descargar</a>
                    </div>
                </div>

                <!-- 4. Cédula -->
                <div class="col-md-3 col-sm-6">
                    <div class="doc-item shadow-sm">
                        <span class="doc-title">Cédula de Identidad</span>
                        <a id="btn_cedula" class="btn btn-sm w-100 mt-auto" download>Descargar</a>
                    </div>
                </div>

                <!-- 5. Recibo / Factura -->
                <div class="col-md-4 col-sm-6 mt-3">
                    <div class="doc-item shadow-sm">
                        <span class="doc-title">Recibo o Factura</span>
                        <a id="btn_recibo" class="btn btn-sm w-100 mt-auto" download>Descargar</a>
                    </div>
                </div>

                <!-- 6. Fachada -->
                <div class="col-md-4 col-sm-6 mt-3">
                    <div class="doc-item shadow-sm">
                        <span class="doc-title">Foto de la Fachada</span>
                        <a id="btn_fachada" class="btn btn-sm w-100 mt-auto" download>Descargar</a>
                    </div>
                </div>

                <!-- 7. Firma -->
                <div class="col-md-4 col-sm-6 mt-3">
                    <div class="doc-item shadow-sm">
                        <span class="doc-title">Firma del Cliente</span>
                        <a id="btn_firma" class="btn btn-sm w-100 mt-auto" download>Descargar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script de Petición AJAX (Fetch API) -->
    <script>
        document.getElementById('form_buscar').addEventListener('submit', function(e) {
            e.preventDefault(); // Evitamos que la página se recargue
            
            const rif = document.getElementById('rif_busqueda').value.trim();
            const btnBuscar = document.getElementById('btn_buscar');
            const resultadosDiv = document.getElementById('resultados_documentos');
            const alertaError = document.getElementById('alerta_error');
            const nombreClienteUI = document.getElementById('cliente_nombre');

            // Estado de carga en el botón
            btnBuscar.innerHTML = 'Buscando...';
            btnBuscar.disabled = true;

            // Llamamos a la API que creamos en la Fase 3
            fetch('buscar_cliente.php?rif=' + encodeURIComponent(rif))
                .then(response => response.json())
                .then(data => {
                    // Restaurar botón
                    btnBuscar.innerHTML = 'Consultar';
                    btnBuscar.disabled = false;

                    if (data.success) {
                        // Ocultar error si existía y mostrar la tabla de documentos
                        alertaError.classList.add('d-none');
                        resultadosDiv.classList.remove('d-none');
                        
                        // Imprimir el nombre del cliente encontrado
                        nombreClienteUI.textContent = data.data.nombre_cliente;

                        // Asignar dinámicamente las rutas a los 7 botones
                        configurarBotonDescarga('btn_formato', data.data.formato_path);
                        configurarBotonDescarga('btn_acta', data.data.acta_path);
                        configurarBotonDescarga('btn_rif', data.data.rif_path);
                        configurarBotonDescarga('btn_cedula', data.data.cedula_path);
                        configurarBotonDescarga('btn_recibo', data.data.recibo_path);
                        configurarBotonDescarga('btn_fachada', data.data.fachada_path);
                        configurarBotonDescarga('btn_firma', data.data.firma_path);

                    } else {
                        // Ocultar resultados y mostrar mensaje de error
                        resultadosDiv.classList.add('d-none');
                        alertaError.textContent = data.mensaje;
                        alertaError.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    btnBuscar.innerHTML = 'Consultar';
                    btnBuscar.disabled = false;
                    resultadosDiv.classList.add('d-none');
                    alertaError.textContent = "Error de conexión con el servidor.";
                    alertaError.classList.remove('d-none');
                    console.error('Error Fetch:', error);
                });
        });

        // Función reutilizable para encender o apagar los botones dependiendo de si el archivo existe
        function configurarBotonDescarga(idBoton, rutaArchivo) {
            const btn = document.getElementById(idBoton);
            
            if (rutaArchivo && rutaArchivo.trim() !== '') {
                // El archivo existe: encendemos el botón de rojo
                btn.href = rutaArchivo;
                btn.target = "_blank"; // Abre en otra pestaña por si el navegador decide leerlo en vez de descargarlo
                btn.classList.remove('btn-secondary', 'disabled');
                btn.classList.add('btn-brand-red');
                btn.innerHTML = '&#8595; Descargar';
            } else {
                // El archivo no fue subido: apagamos el botón en gris
                btn.removeAttribute('href');
                btn.removeAttribute('target');
                btn.classList.remove('btn-brand-red');
                btn.classList.add('btn-secondary', 'disabled');
                btn.innerHTML = 'No adjuntado';
            }
        }
    </script>
</body>
</html>