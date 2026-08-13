<?php
session_start();

// 1. Verificación de autenticación básica
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

// 2. Control de accesos por roles (Filtro estricto)
$cargos_autorizados = ['preventista']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// Incluir la conexión a la base de datos
include('conexion.php');

$mensaje_alerta = "";

// ==========================================
// 3. MOTOR BACKEND: PROCESAR LA CAPTACIÓN
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recoger y sanitizar datos del formulario
    $fecha_visita        = mysqli_real_escape_string($conexion, $_POST['fecha_visita'] ?? '');
    $ruta                = mysqli_real_escape_string($conexion, $_POST['ruta'] ?? '');
    $frecuencia          = mysqli_real_escape_string($conexion, $_POST['frecuencia'] ?? '');
    $nombre_cliente      = mysqli_real_escape_string($conexion, $_POST['nombre_cliente'] ?? '');
    $rif_cliente         = mysqli_real_escape_string($conexion, $_POST['rif_cliente'] ?? '');
    $posicion_itinerario = mysqli_real_escape_string($conexion, $_POST['posicion_itinerario'] ?? '');
    $comentarios         = mysqli_real_escape_string($conexion, $_POST['comentarios'] ?? '');
    $instalacion_nevera  = mysqli_real_escape_string($conexion, $_POST['instalacion_nevera'] ?? 'NO');
    $vendedor            = mysqli_real_escape_string($conexion, $_SESSION['user'] ?? 'Desconocido');

    // Validación básica de campos obligatorios
    if (empty($fecha_visita) || empty($ruta) || empty($frecuencia) || empty($nombre_cliente) || empty($rif_cliente)) {
        $mensaje_alerta = "<div class='alert alert-danger'>Error: Hay campos obligatorios de planificación o cliente sin completar.</div>";
    } else {
        // Carpeta destino para archivos adjuntos
        $directorio_subidas = "uploads/captaciones/";
        if (!file_exists($directorio_subidas)) {
            mkdir($directorio_subidas, 0777, true);
        }

        // Array para almacenar las rutas de los archivos subidos
        $archivos_adjuntos = [
            'archivo_formato' => '',
            'archivo_acta'    => '',
            'archivo_rif'     => '',
            'archivo_cedula'  => '',
            'archivo_recibo'  => '',
            'archivo_fachada' => '',
            'archivo_firma'   => ''
        ];

        $upload_error = false;

        // Procesar subida de cada archivo si existe
        foreach ($archivos_adjuntos as $campo => $ruta_guardada) {
            if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                $nombre_archivo_original = basename($_FILES[$campo]['name']);
                $extension = strtolower(pathinfo($nombre_archivo_original, PATHINFO_EXTENSION));
                
                // Generar un nombre único para evitar colisiones
                $nombre_unico = $campo . "_" . uniqid() . "." . $extension;
                $ruta_destino_archivo = $directorio_subidas . $nombre_unico;

                if (move_uploaded_file($_FILES[$campo]['tmp_name'], $ruta_destino_archivo)) {
                    $archivos_adjuntos[$campo] = $ruta_destino_archivo;
                } else {
                    $upload_error = true;
                }
            }
        }

        if ($upload_error) {
            $mensaje_alerta = "<div class='alert alert-danger'>Error al subir uno de los archivos adjuntos. Intente nuevamente.</div>";
        } else {
            // Iniciar transacción SQL para garantizar integridad
            mysqli_begin_transaction($conexion);

            try {
                // 1. Insertar la cabecera de la captación
                $sql_captacion = "INSERT INTO captaciones (fecha_visita, ruta, frecuencia, nombre_cliente, rif_cliente, posicion_itinerario, comentarios, instalacion_nevera, vendedor, formato_path, acta_path, rif_path, cedula_path, recibo_path, fachada_path, firma_path) 
                                  VALUES ('$fecha_visita', '$ruta', '$frecuencia', '$nombre_cliente', '$rif_cliente', '$posicion_itinerario', '$comentarios', '$instalacion_nevera', '$vendedor', 
                                  '{$archivos_adjuntos['archivo_formato']}', '{$archivos_adjuntos['archivo_acta']}', '{$archivos_adjuntos['archivo_rif']}', '{$archivos_adjuntos['archivo_cedula']}', '{$archivos_adjuntos['archivo_recibo']}', '{$archivos_adjuntos['archivo_fachada']}', '{$archivos_adjuntos['archivo_firma']}')";
                
                if (!mysqli_query($conexion, $sql_captacion)) {
                    throw new Exception("Error al registrar la cabecera de la captación.");
                }

                $captacion_id = mysqli_insert_id($conexion);

                // 2. Si requiere nevera, insertar los modelos seleccionados (soporta múltiples)
                if ($instalacion_nevera === 'SI' && isset($_POST['modelo_nevera']) && is_array($_POST['modelo_nevera'])) {
                    foreach ($_POST['modelo_nevera'] as $modelo) {
                        $modelo_limpio = mysqli_real_escape_string($conexion, trim($modelo));
                        if (!empty($modelo_limpio)) {
                            $sql_modelo = "INSERT INTO captacion_neveras (captacion_id, modelo_nevera) VALUES ($captacion_id, '$modelo_limpio')";
                            if (!mysqli_query($conexion, $sql_modelo)) {
                                throw new Exception("Error al registrar los modelos de nevera requeridos.");
                            }
                        }
                    }
                }

                // Confirmar transacción
                mysqli_commit($conexion);
                header("Location: procesar_captacion.php?guardado=exito");
                exit();

            } catch (Exception $e) {
                mysqli_rollback($conexion);
                $mensaje_alerta = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Excepción capturada: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// Mensaje de éxito por GET
if (isset($_GET['guardado']) && $_GET['guardado'] == 'exito') {
    $mensaje_alerta = "<div class='alert alert-success'>¡Captación de cliente registrada con éxito en el sistema!</div>";
}

// Variables para el saludo visual en el HTML
$nombre_usuario = $_SESSION['user'] ?? 'Usuario';
$cargo_display = ucfirst($cargo_usuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Captación de Cliente - La Argentina</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados para el Tema Oscuro / Corporativo -->
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
        .form-control-custom, .form-select-custom {
            background-color: #262626;
            border: 1px solid #333;
            color: #ffffff;
            border-radius: 6px;
        }
        .form-control-custom:focus, .form-select-custom:focus {
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
            padding: 0.6rem 2rem;
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
        .link-back:hover { color: #ccc; }
        .btn-icon-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            background-color: transparent;
            border: 1px solid #e50914;
            color: #e50914;
            font-weight: bold;
            font-size: 1.1rem;
            line-height: 1;
            padding: 0;
            transition: all 0.2s;
        }
        .btn-icon-custom:hover {
            background-color: #e50914;
            color: #fff;
        }
        .btn-icon-trash {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            background-color: transparent;
            color: #e50914;
            transition: all 0.2s;
            padding: 0;
        }
        .btn-icon-trash:hover {
            color: #ff3333;
            transform: scale(1.1);
        }
        input[type="file"]::file-selector-button {
            background-color: #333;
            color: white;
            border: 1px solid #444;
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
            margin-right: 1rem;
            transition: background-color 0.2s;
        }
        input[type="file"]::file-selector-button:hover { background-color: #444; }
        .form-check-input {
            background-color: #262626;
            border-color: #444;
        }
        .form-check-input:checked {
            background-color: #e50914;
            border-color: #e50914;
        }
    </style>
</head>
<body>

    <!-- Contenedor Principal -->
    <div class="container mt-5 mb-5">
        
        <!-- Encabezado de la página -->
        <header class="mb-4">
            <h1 class="fw-bold mb-1">Registro de <span class="text-brand-red">Captación</span></h1>
            <p class="text-secondary mb-3">
                Bienvenido, <strong class="text-white"><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($cargo_display); ?>)
            </p>
            
            <!-- Botón Volver al Inicio -->
            <a href="index.php" class="link-back d-inline-block">
                &#8592; Volver al Inicio
            </a>
        </header>

        <!-- Bloque de Alertas del Servidor -->
        <?php echo $mensaje_alerta; ?>

        <!-- Formulario apuntando a sí mismo -->
        <form action="procesar_captacion.php" method="POST" enctype="multipart/form-data">
            
            <!-- BLOQUE 1: Planificación -->
            <div class="card card-custom shadow-sm">
                <div class="card-header card-header-custom">
                    <h5 class="card-title-custom">Datos de Planificación</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label for="fecha_visita" class="form-label form-label-custom">Fecha de la visita </label>
                            <input type="date" class="form-control form-control-custom" id="fecha_visita" name="fecha_visita" required>
                        </div>
                        <div class="col-md-4">
                            <label for="ruta" class="form-label form-label-custom">Ruta </label>
                            <select class="form-select form-select-custom" id="ruta" name="ruta" required>
                                <option value="" selected disabled>Seleccione una ruta</option>
                                <option value="Norte">Norte</option>
                                <option value="Sur">Sur</option>
                                <option value="COL">COL</option>
                                <option value="Cañada">Cañada</option>
                                <option value="Villa">Villa</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="frecuencia" class="form-label form-label-custom">Frecuencia de la visita </label>
                            <select class="form-select form-select-custom" id="frecuencia" name="frecuencia" required>
                                <option value="" selected disabled>Seleccione la frecuencia</option>
                                <option value="Semanal">Semanal</option>
                                <option value="Quincenal">Quincenal</option>
                                <option value="Mensual">Mensual</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 2: Datos del Cliente -->
            <div class="card card-custom shadow-sm">
                <div class="card-header card-header-custom">
                    <h5 class="card-title-custom">Información del Cliente</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label for="nombre_cliente" class="form-label form-label-custom">Nombre del Cliente </label>
                            <input type="text" class="form-control form-control-custom" id="nombre_cliente" name="nombre_cliente" placeholder="Ej. Inversiones San José C.A." required>
                        </div>
                        <div class="col-md-12">
                            <label for="rif_cliente" class="form-label form-label-custom">RIF del Cliente </label>
                            <input type="text" class="form-control form-control-custom" id="rif_cliente" name="rif_cliente" placeholder="Ej. J-12345678-9" required>
                        </div>
                        <div class="col-md-12">
                            <label for="posicion_itinerario" class="form-label form-label-custom">Posición en el itinerario </label>
                            <input type="text" class="form-control form-control-custom" id="posicion_itinerario" name="posicion_itinerario" placeholder="Ej: Debajo del cliente XXXXX">
                        </div>
                        <div class="col-md-12">
                            <label for="comentarios" class="form-label form-label-custom">Comentarios Adicionales</label>
                            <textarea class="form-control form-control-custom" id="comentarios" name="comentarios" rows="3" placeholder="Observaciones sobre la captación..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 3: Requerimientos de Equipo (Lógica dinámica) -->
            <div class="card card-custom shadow-sm">
                <div class="card-header card-header-custom">
                    <h5 class="card-title-custom">Requerimientos Físicos</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label form-label-custom d-block">¿Requiere Instalación de Nevera? </label>
                            <div class="mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="instalacion_nevera" id="nevera_si" value="SI" required>
                                    <label class="form-check-label text-white" for="nevera_si">SÍ</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="instalacion_nevera" id="nevera_no" value="NO" required>
                                    <label class="form-check-label text-white" for="nevera_no">NO</label>
                                </div>
                            </div>
                        </div>

                        <!-- Contenedor Principal de Modelos (Oculto por defecto) -->
                        <div class="col-md-6 d-none" id="contenedor_modelos_main">
                            <div class="modelo-row">
                                <div class="d-flex align-items-center mb-2">
                                    <label class="form-label form-label-custom mb-0 me-2">Modelo de Nevera </label>
                                    <button type="button" class="btn-icon-custom btn-add-modelo" title="Agregar otro modelo">+</button>
                                </div>
                                <select class="form-select form-select-custom" name="modelo_nevera[]">
                                    <option value="" selected disabled>Seleccione el modelo requerido</option>
                                    <option value="VV-16BTF">VV-16BTF</option>
                                    <option value="CV-350">CV-350</option>
                                    <option value="CV-330">CV-330</option>
                                    <option value="CV-200">CV-200</option>
                                    <option value="TECOVEN">TECOVEN</option>
                                    <option value="FRAMEC">FRAMEC</option>
                                    <option value="AMERIO">AMERIO</option>
                                    <option value="BFC-200">BFC-200</option>
                                    <option value="BFC-150">BFC-150</option>
                                    <option value="BFC-250">BFC-250</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 4: Archivos Adjuntos -->
            <div class="card card-custom shadow-sm">
                <div class="card-header card-header-custom">
                    <h5 class="card-title-custom">Documentación Adjunta</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6"><label class="form-label form-label-custom">Formato de Captación </label><input type="file" class="form-control form-control-custom" name="archivo_formato"></div>
                        <div class="col-md-6"><label class="form-label form-label-custom">Acta Constitutiva </label><input type="file" class="form-control form-control-custom" name="archivo_acta"></div>
                        <div class="col-md-6"><label class="form-label form-label-custom">RIF </label><input type="file" class="form-control form-control-custom" name="archivo_rif"></div>
                        <div class="col-md-6"><label class="form-label form-label-custom">Cédula </label><input type="file" class="form-control form-control-custom" name="archivo_cedula"></div>
                        <div class="col-md-6"><label class="form-label form-label-custom">Recibo o Factura </label><input type="file" class="form-control form-control-custom" name="archivo_recibo"></div>
                        <div class="col-md-6"><label class="form-label form-label-custom">Foto de la Fachada </label><input type="file" class="form-control form-control-custom" name="archivo_fachada" accept="image/*"></div>
                        <div class="col-md-6"><label class="form-label form-label-custom">Firma del Cliente </label><input type="file" class="form-control form-control-custom" name="archivo_firma"></div>
                    </div>
                </div>
            </div>

            <!-- Botón de Envío -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-brand-red btn-lg px-5">Guardar Captación</button>
            </div>
        </form>
    </div>

    <!-- Script de interactividad -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radioSi = document.getElementById('nevera_si');
            const radioNo = document.getElementById('nevera_no');
            const contenedorModelosMain = document.getElementById('contenedor_modelos_main');

            function toggleModeloNevera() {
                const selects = contenedorModelosMain.querySelectorAll('select');
                if (radioSi.checked) {
                    contenedorModelosMain.classList.remove('d-none');
                    selects.forEach(s => s.setAttribute('required', 'required'));
                } else {
                    contenedorModelosMain.classList.add('d-none');
                    selects.forEach(s => {
                        s.removeAttribute('required');
                        s.value = '';
                    });
                    
                    const filas = contenedorModelosMain.querySelectorAll('.modelo-row');
                    filas.forEach((fila, index) => {
                        if(index > 0) fila.remove(); 
                    });
                }
            }

            radioSi.addEventListener('change', toggleModeloNevera);
            radioNo.addEventListener('change', toggleModeloNevera);

            const templateFila = `
                <div class="modelo-row mt-3">
                    <div class="d-flex align-items-center mb-2">
                        <label class="form-label form-label-custom mb-0 me-2">Modelo de Nevera </label>
                        <button type="button" class="btn-icon-custom btn-add-modelo" title="Agregar otro modelo">+</button>
                        <button type="button" class="btn-icon-trash btn-remove-modelo ms-2" title="Eliminar modelo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                              <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>
                        </button>
                    </div>
                    <select class="form-select form-select-custom" name="modelo_nevera[]" required>
                        <option value="" selected disabled>Seleccione el modelo requerido</option>
                        <option value="VV-16BTF">VV-16BTF</option>
                        <option value="CV-350">CV-350</option>
                        <option value="CV-330">CV-330</option>
                        <option value="CV-200">CV-200</option>
                        <option value="TECOVEN">TECOVEN</option>
                        <option value="FRAMEC">FRAMEC</option>
                        <option value="AMERIO">AMERIO</option>
                        <option value="BFC-200">BFC-200</option>
                        <option value="BFC-150">BFC-150</option>
                        <option value="BFC-250">BFC-250</option>
                    </select>
                </div>
            `;

            contenedorModelosMain.addEventListener('click', function(e) {
                if (e.target.closest('.btn-add-modelo')) {
                    contenedorModelosMain.insertAdjacentHTML('beforeend', templateFila);
                }
                if (e.target.closest('.btn-remove-modelo')) {
                    e.target.closest('.modelo-row').remove();
                }
            });
        });
    </script>
</body>
</html>