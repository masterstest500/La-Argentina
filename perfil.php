<?php
// 1. 🛡️ Control de Sesión y Escudo de Seguridad
session_start();

// Si no hay sesión iniciada, directo al login
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

include('conexion.php');

$usuario_id = $_SESSION['usuario_id'];
$cargo_usuario = strtolower(trim($_SESSION['cargo']));
$nombre_usuario = $_SESSION['user'];

// Crear directorio de subida si no existe por seguridad
$directorio_subida = 'uploads/perfiles/';
if (!file_exists($directorio_subida)) {
    mkdir($directorio_subida, 0777, true);
}

// 2. 📁 PROCESAR SUBIDA, ELIMINACIÓN DE FOTO DE PERFIL & AJUSTES
$mensaje_exito = "";
$mensaje_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CASO A: Actualizar Foto de Perfil
    if (isset($_POST['accion_perfil']) && $_POST['accion_perfil'] === 'guardar_foto') {
        if (isset($_FILES['foto_perfil_file']) && $_FILES['foto_perfil_file']['error'] === UPLOAD_ERR_OK) {
            
            $file_tmp  = $_FILES['foto_perfil_file']['tmp_name'];
            $file_name = $_FILES['foto_perfil_file']['name'];
            $file_size = $_FILES['foto_perfil_file']['size'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Extensiones permitidas
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($file_ext, $extensiones_permitidas)) {
                // Límite de tamaño: 3MB
                if ($file_size <= 3 * 1024 * 1024) {
                    
                    // Nombre de archivo único usando ID y marca de tiempo para evitar caché del navegador
                    $nuevo_nombre_archivo = "user_" . $usuario_id . "_" . time() . "." . $file_ext;
                    $ruta_destino = $directorio_subida . $nuevo_nombre_archivo;
                    
                    if (move_uploaded_file($file_tmp, $ruta_destino)) {
                        
                        // Eliminar foto anterior física si existe y no es la default
                        $sql_old = "SELECT foto_perfil FROM usuarios WHERE id = $usuario_id";
                        $res_old = mysqli_query($conexion, $sql_old);
                        $user_old = mysqli_fetch_assoc($res_old);
                        if (!empty($user_old['foto_perfil']) && file_exists($user_old['foto_perfil'])) {
                            unlink($user_old['foto_perfil']);
                        }
                        
                        // Actualizar en BD
                        $sql_update_foto = "UPDATE usuarios SET foto_perfil = '$ruta_destino' WHERE id = $usuario_id";
                        if (mysqli_query($conexion, $sql_update_foto)) {
                            $mensaje_exito = "¡Foto de perfil actualizada con éxito!";
                        } else {
                            $mensaje_error = "Error al actualizar la ruta en la base de datos.";
                        }
                    } else {
                        $mensaje_error = "Error al mover el archivo al servidor.";
                    }
                } else {
                    $mensaje_error = "El archivo excede el límite de tamaño de 3MB.";
                }
            } else {
                $mensaje_error = "Formato no permitido. Use JPG, PNG o WEBP.";
            }
        } else {
            $mensaje_error = "Por favor, seleccione un archivo válido.";
        }
    }

    // CASO B: Cambiar Contraseña
    if (isset($_POST['cambiar_password'])) {
        $pass_actual  = $_POST['pass_actual'];
        $pass_nueva   = $_POST['pass_nueva'];
        $pass_confirm = $_POST['pass_confirm'];

        if (!empty($pass_actual) && !empty($pass_nueva) && !empty($pass_confirm)) {
            $sql_pass = "SELECT password FROM usuarios WHERE id = $usuario_id";
            $res_pass = mysqli_query($conexion, $sql_pass);
            $user_pass = mysqli_fetch_assoc($res_pass)['password'];

            if ($pass_actual === $user_pass) {
                if ($pass_nueva === $pass_confirm) {
                    $pass_nueva_esc = mysqli_real_escape_string($conexion, $pass_nueva);
                    $sql_update = "UPDATE usuarios SET password = '$pass_nueva_esc' WHERE id = $usuario_id";
                    if (mysqli_query($conexion, $sql_update)) {
                        $mensaje_exito = "Contraseña actualizada de forma segura.";
                    } else {
                        $mensaje_error = "Error al actualizar la contraseña en la base de datos.";
                    }
                } else {
                    $mensaje_error = "Las nuevas contraseñas no coinciden.";
                }
            } else {
                $mensaje_error = "La contraseña actual es incorrecta.";
            }
        } else {
            $mensaje_error = "Complete todos los campos de seguridad.";
        }
    }

    // CASO C: 🗑️ Eliminar Foto de Perfil (Nuevo)
    if (isset($_POST['accion_perfil']) && $_POST['accion_perfil'] === 'eliminar_foto') {
        $sql_old = "SELECT foto_perfil FROM usuarios WHERE id = $usuario_id";
        $res_old = mysqli_query($conexion, $sql_old);
        $user_old = mysqli_fetch_assoc($res_old);
        
        if (!empty($user_old['foto_perfil']) && file_exists($user_old['foto_perfil'])) {
            unlink($user_old['foto_perfil']); // Borrado físico del servidor
        }
        
        $sql_delete_foto = "UPDATE usuarios SET foto_perfil = NULL WHERE id = $usuario_id";
        if (mysqli_query($conexion, $sql_delete_foto)) {
            $mensaje_exito = "¡Foto de perfil eliminada correctamente!";
        } else {
            $mensaje_error = "Error al eliminar la foto de la base de datos.";
        }
    }
}

// 3. 🔍 Obtener datos frescos del usuario
$query_user = "SELECT * FROM usuarios WHERE id = $usuario_id";
$result_user = mysqli_query($conexion, $query_user);
$datos_user = mysqli_fetch_assoc($result_user);

if (!$datos_user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$cedula = $datos_user['cedula'];
$fecha_ingreso = date('d/m/Y', strtotime($datos_user['fecha_registro'] ?? 'now'));
$foto_perfil = $datos_user['foto_perfil'] ?? '';

// Extraemos los estados reales de las preferencias desde la base de datos
$pref_stock = $datos_user['pref_stock'] ?? 0;
$pref_pdf   = $datos_user['pref_pdf'] ?? 0;
$pref_datos = $datos_user['pref_datos'] ?? 0;


// ==========================================================
// 4. 📈 METRICAS CORPORATIVAS DE DESEMPEÑO (Según Cargo)
// ==========================================================
$metrica_primaria_titulo = "";
$metrica_primaria_valor = "";
$metrica_secundaria_titulo = "";
$metrica_secundaria_valor = "";

// Escapamos el nombre del usuario por seguridad para las consultas[cite: 1]
$vendedor_escape = mysqli_real_escape_string($conexion, $nombre_usuario);

if ($cargo_usuario === 'preventista') {
    // MÉTRICAS EXCLUSIVAS PARA EL PREVENTISTA[cite: 1]
    $sql_pedidos_hoy = "SELECT COUNT(*) as total FROM pedidos WHERE vendedor = '$vendedor_escape' AND DATE(fecha_pedido) = CURDATE()";
    $res_p_hoy = mysqli_query($conexion, $sql_pedidos_hoy);
    $cant_pedidos = mysqli_fetch_assoc($res_p_hoy)['total'] ?? 0;
    
    $sql_monto_hoy = "SELECT SUM(total) as monto FROM pedidos WHERE vendedor = '$vendedor_escape' AND DATE(fecha_pedido) = CURDATE()";
    $res_m_hoy = mysqli_query($conexion, $sql_monto_hoy);
    $monto_hoy = mysqli_fetch_assoc($res_m_hoy)['monto'] ?? 0;

    $metrica_primaria_titulo = "Ventas Consolidadas (Hoy)";
    $metrica_primaria_valor = $cant_pedidos . " Facturas";
    $metrica_secundaria_titulo = "Recaudación Total (Hoy)";
    $metrica_secundaria_valor = "$" . number_format($monto_hoy, 2);

} elseif ($cargo_usuario === 'ventas') {
    // NUEVAS MÉTRICAS PERSONALIZADAS PARA VENTAS
    // 1. Mostrar cuántos productos conforman el catálogo actual
    $sql_productos = "SELECT COUNT(*) as total FROM productos";
    $res_prod = mysqli_query($conexion, $sql_productos);
    $total_productos = mysqli_fetch_assoc($res_prod)['total'] ?? 0;

    // 2. Mostrar la cantidad de clientes captados/creados hoy 
    // (Asumiendo que usas la tabla 'captaciones' o 'clientes')
    $sql_clientes_hoy = "SELECT COUNT(*) as total FROM captaciones WHERE DATE(fecha_registro) = CURDATE()";
    $res_cli_hoy = mysqli_query($conexion, $sql_clientes_hoy);
    $clientes_hoy = mysqli_fetch_assoc($res_cli_hoy)['total'] ?? 0;

    $metrica_primaria_titulo = "Catálogo Gestionado";
    $metrica_primaria_valor = $total_productos . " Productos";
    $metrica_secundaria_titulo = "Nuevos Clientes (Hoy)";
    $metrica_secundaria_valor = $clientes_hoy . " Registros";

} elseif ($cargo_usuario === 'administrador') {
    // MÉTRICAS PARA ADMINISTRADOR[cite: 1]
    $sql_total_users = "SELECT COUNT(*) as total FROM usuarios";
    $total_users = mysqli_fetch_assoc(mysqli_query($conexion, $sql_total_users))['total'] ?? 0;

    $sql_stock_critico = "SELECT COUNT(*) as total FROM productos WHERE stock_potes <= 5";
    $stock_critico = mysqli_fetch_assoc(mysqli_query($conexion, $sql_stock_critico))['total'] ?? 0;

    $metrica_primaria_titulo = "Control de Nómina";
    $metrica_primaria_valor = $total_users . " Operarios";
    $metrica_secundaria_titulo = "Alertas de Cámara de Frío";
    $metrica_secundaria_valor = $stock_critico . " Críticos";
} else {
    // MÉTRICAS POR DEFECTO[cite: 1]
    $metrica_primaria_titulo = "Asignación de Ruta";
    $metrica_primaria_valor = "Maracaibo Activo";
    $metrica_secundaria_titulo = "Estado Contable";
    $metrica_secundaria_valor = "Auditado";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Argentina - Mi Perfil Corporativo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #0b0b0b; color: #ffffff; padding: 40px; min-height: 100vh; }
        
        /* Contenedor Superior Estilo Moderno */
        .header-seccion { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; border-bottom: 1px solid #1f1f1f; padding-bottom: 20px; }
        .header-seccion h1 { font-size: 1.8rem; font-weight: 700; }
        .header-seccion h1 span { color: #ff0015; }
        .btn-volver { background-color: #141414; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-size: 0.9rem; border: 1px solid #282828; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease; }
        .btn-volver:hover { background-color: #ff0015; border-color: #ff0015; transform: translateY(-2px); }

        /* GRID ASIMÉTRICO ESTILO SAAS */
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 30px; align-items: start; }
        
        @media (max-width: 992px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }

        /* Tarjetas Premium */
        .tarjeta-premium { background-color: #141414; border: 1px solid #1f1f1f; border-radius: 12px; padding: 30px; box-shadow: 0 8px 30px rgba(0,0,0,0.6); position: relative; overflow: hidden; }
        .tarjeta-premium::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background-color: #ff0015; }

        /* SECCIÓN FOTO INTERACTIVA */
        .contenedor-avatar-upload { display: flex; flex-direction: column; align-items: center; gap: 15px; margin-bottom: 15px; text-align: center; }
        
        /* El círculo del avatar */
        .avatar-wrapper { position: relative; width: 140px; height: 140px; border-radius: 50%; border: 3px solid #ff0015; overflow: hidden; cursor: pointer; box-shadow: 0 0 20px rgba(255, 0, 21, 0.2); transition: all 0.3s ease; }
        .avatar-wrapper:hover { transform: scale(1.05); box-shadow: 0 0 30px rgba(255, 0, 21, 0.4); }
        
        .avatar-img { width: 100%; height: 100%; object-fit: cover; }
        
        /* Iniciales si no hay foto */
        .avatar-iniciales { width: 100%; height: 100%; background: linear-gradient(135deg, #1f1f1f, #111); display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; color: #ff0015; }
        
        /* Capa Overlay "Cambiar" */
        .avatar-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 0.8rem; color: #fff; font-weight: 500; opacity: 0; transition: opacity 0.3s ease; gap: 5px; }
        .avatar-wrapper:hover .avatar-overlay { opacity: 1; }

        /* Botón de Confirmar Subida */
        .btn-subir-imagen { background-color: #28a745; border: none; color: white; padding: 8px 16px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; display: none; margin-top: 5px; transition: all 0.3s ease; }
        .btn-subir-imagen:hover { background-color: #218838; }

        /* Botón Quitar Foto */
        .btn-quitar-foto { background: transparent; border: none; color: #ff0015; cursor: pointer; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 5px; margin: 5px auto 15px auto; transition: opacity 0.3s; }
        .btn-quitar-foto:hover { opacity: 0.8; text-decoration: underline; }

        /* Información del empleado */
        .perfil-nombre { font-size: 1.5rem; font-weight: 700; margin-top: 10px; }
        .perfil-cargo { display: inline-block; padding: 4px 12px; background-color: rgba(255, 0, 21, 0.1); border: 1px solid #ff0015; color: #ff0015; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; border-radius: 20px; margin-top: 5px; }

        /* Listado de Datos Internos */
        .detalles-corporativos { border-top: 1px solid #222; margin-top: 25px; padding-top: 20px; display: flex; flex-direction: column; gap: 12px; }
        .detalle-fila { display: flex; justify-content: space-between; font-size: 0.9rem; }
        .detalle-fila span:first-child { color: #777; font-weight: 500; }
        .detalle-fila span:last-child { color: #fff; font-weight: 600; }

        /* SECCIÓN DERECHA: CONFIGURACIÓN CORPORATIVA & SEGURIDAD */
        .columna-derecha { display: flex; flex-direction: column; gap: 30px; }
        
        /* Pestañas de Opciones */
        .titulo-caja { font-size: 1.1rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #222; padding-bottom: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .titulo-caja i { color: #ff0015; }

        /* Métricas */
        .dashboard-metricas { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .caja-metrica { background-color: #1a1a1a; border: 1px solid #222; padding: 20px; border-radius: 8px; text-align: center; position: relative; }
        .caja-metrica::after { content: ''; position: absolute; bottom: 0; left: 10%; width: 80%; height: 2px; background-color: #ff0015; opacity: 0.5; }
        .metrica-label { font-size: 0.75rem; color: #777; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 8px; }
        .metrica-valor { font-size: 1.4rem; font-weight: 700; color: #fff; }

        /* Formularios */
        .formulario-grupo { margin-bottom: 20px; }
        .formulario-grupo label { display: block; font-size: 0.8rem; font-weight: 600; color: #ff0015; text-transform: uppercase; margin-bottom: 8px; }
        .formulario-grupo input { width: 100%; padding: 12px 15px; background-color: #1a1a1a; border: 1px solid #282828; border-radius: 6px; color: #fff; font-size: 0.95rem; transition: all 0.3s ease; }
        .formulario-grupo input:focus { outline: none; border-color: #ff0015; box-shadow: 0 0 10px rgba(255, 0, 21, 0.2); }
        
        /* Botones de Acción */
        .btn-accion { background-color: #ff0015; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; transition: all 0.3s ease; }
        .btn-accion:hover { background-color: #cc0011; transform: translateY(-2px); }

        /* Toggles Modernos de Preferencias Corporativas */
        .toggle-fila { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #1a1a1a; }
        .toggle-info h4 { font-size: 0.9rem; font-weight: 600; }
        .toggle-info p { font-size: 0.75rem; color: #777; }
        
        /* Estilo Switch Checkbox */
        .switch { position: relative; display: inline-block; width: 44px; height: 22px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #2d2d2d; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #ff0015; }
        input:checked + .slider:before { transform: translateX(22px); }

        /* Notificaciones flotantes estéticas */
        .alerta-global { padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; font-size: 0.9rem; border: 1px solid; animation: fadeIn 0.4s ease-out; }
        .alerta-exito { background-color: rgba(40, 167, 69, 0.1); color: #28a745; border-color: rgba(40, 167, 69, 0.2); }
        .alerta-error { background-color: rgba(255, 0, 21, 0.1); color: #ff0015; border-color: rgba(255, 0, 21, 0.2); }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Mi Perfil <span>Corporativo</span></h1>
        <a href="index.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Panel Principal</a>
    </div>

    <?php if (!empty($mensaje_exito)): ?>
        <div class="alerta-global alerta-exito">
            <i class="fa-solid fa-circle-check"></i> <?php echo $mensaje_exito; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($mensaje_error)): ?>
        <div class="alerta-global alerta-error">
            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $mensaje_error; ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        
        <div class="columna-izquierda">
            <div class="tarjeta-premium">
                
                <form action="perfil.php" method="POST" enctype="multipart/form-data" id="form-foto-perfil">
                    <input type="hidden" name="accion_perfil" value="guardar_foto">
                    <input type="file" id="foto_perfil_file" name="foto_perfil_file" accept="image/*" style="display: none;">
                    
                    <div class="contenedor-avatar-upload">
                        <div class="avatar-wrapper" onclick="triggerFileSelect()">
                            <?php if (!empty($foto_perfil) && file_exists($foto_perfil)): ?>
                                <img src="<?php echo $foto_perfil; ?>" id="avatar-preview" class="avatar-img" alt="Foto de perfil">
                            <?php else: ?>
                                <div class="avatar-iniciales" id="avatar-iniciales-placeholder">
                                    <?php echo strtoupper(substr($nombre_usuario, 0, 2)); ?>
                                </div>
                                <img id="avatar-preview" class="avatar-img" alt="Foto de perfil" style="display: none;">
                            <?php endif; ?>
                            
                            <div class="avatar-overlay">
                                <i class="fa-solid fa-camera" style="font-size: 1.5rem;"></i>
                                <span>CAMBIAR</span>
                            </div>
                        </div>

                        <button type="submit" id="btn-confirmar-subida" class="btn-subir-imagen">
                            <i class="fa-solid fa-circle-check"></i> Guardar Imagen
                        </button>
                    </div>
                </form>

                <?php if (!empty($foto_perfil) && file_exists($foto_perfil)): ?>
                    <form action="eliminar_foto.php" method="POST" style="margin-top: -5px;">
                        <input type="hidden" name="accion_perfil" value="eliminar_foto">
                        <button type="submit" class="btn-quitar-foto" onclick="return confirm('¿Estás seguro de que deseas quitar tu foto de perfil y volver al avatar de iniciales?');">
                            <i class="fa-solid fa-trash-can"></i> Quitar Foto
                        </button>
                    </form>
                <?php endif; ?>

                <div style="text-align: center;">
                    <h2 class="perfil-nombre"><?php echo htmlspecialchars($nombre_usuario); ?></h2>
                    <span class="perfil-cargo"><?php echo htmlspecialchars($_SESSION['cargo']); ?></span>
                </div>

                <div class="detalles-corporativos">
                    <div class="detalle-fila">
                        <span>Documento</span>
                        <span>V-<?php echo number_format($cedula, 0, ',', '.'); ?></span>
                    </div>
                    <div class="detalle-fila">
                        <span>Cuentas de Acceso</span>
                        <span style="color: #28a745;"><i class="fa-solid fa-shield" style="margin-right: 5px;"></i> Activo</span>
                    </div>
                    <div class="detalle-fila">
                        <span>Alta en Sistema</span>
                        <span><?php echo $fecha_ingreso; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="columna-derecha">
            
            <div class="tarjeta-premium" style="border-left: 4px solid #fff;">
                <h3 class="titulo-caja"><i class="fa-solid fa-chart-pie"></i> Rendimiento de Operaciones (Hoy)</h3>
                
                <div class="dashboard-metricas">
                    <div class="caja-metrica">
                        <div class="metrica-label"><?php echo $metrica_primaria_titulo; ?></div>
                        <div class="metrica-valor"><?php echo $metrica_primaria_valor; ?></div>
                    </div>
                    <div class="caja-metrica">
                        <div class="metrica-label"><?php echo $metrica_secundaria_titulo; ?></div>
                        <div class="metrica-valor"><?php echo $metrica_secundaria_valor; ?></div>
                    </div>
                </div>
            </div>

            <div class="tarjeta-premium">
                <h3 class="titulo-caja"><i class="fa-solid fa-sliders"></i> Ajustes de Preferencias Corporativas</h3>
                
                <?php if (in_array($cargo_usuario, ['administrador', 'preventista', 'ventas'])): ?>
                    <div class="toggle-fila">
                        <div class="toggle-info">
                            <h4>Alertas de Stock Crítico</h4>
                            <p>Notificar cuando los potes de helados en cava bajen de 5 unidades.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" class="toggle-preferencia" data-pref="pref_stock" 
                                   <?php echo ($pref_stock == 1) ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                <?php endif; ?>

                <?php if ($cargo_usuario === 'administrador'): ?>
                    <div class="toggle-fila">
                        <div class="toggle-info">
                            <h4>Reporte Diario en PDF</h4>
                            <p>Generar balance automático de operaciones al cerrar el día.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" class="toggle-preferencia" data-pref="pref_pdf" 
                                   <?php echo ($pref_pdf == 1) ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                <?php endif; ?>

                <?php if (in_array($cargo_usuario, ['preventista', 'ventas'])): ?>
                    <div class="toggle-fila" style="border-bottom: none;">
                        <div class="toggle-info">
                            <h4>Modo de Datos Reducido</h4>
                            <p>Ocultar gráficas secundarias en móviles para optimizar datos de ruta.</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" class="toggle-preferencia" data-pref="pref_datos" 
                                   <?php echo ($pref_datos == 1) ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tarjeta-premium">
                <h3 class="titulo-caja"><i class="fa-solid fa-key"></i> Actualizar Clave de Acceso</h3>
                
                <form action="perfil.php" method="POST">
                    <div class="formulario-grupo">
                        <label for="pass_actual">Contraseña Actual</label>
                        <input type="password" id="pass_actual" name="pass_actual" required placeholder="••••••••">
                    </div>

                    <div class="formulario-grupo">
                        <label for="pass_nueva">Nueva Contraseña</label>
                        <input type="password" id="pass_nueva" name="pass_nueva" required placeholder="Máximo 10 caracteres" maxlength="10">
                    </div>

                    <div class="formulario-grupo">
                        <label for="pass_confirm">Confirmar Nueva Contraseña</label>
                        <input type="password" id="pass_confirm" name="pass_confirm" required placeholder="Repita la nueva contraseña" maxlength="10">
                    </div>

                    <button type="submit" name="cambiar_password" class="btn-accion">
                        <i class="fa-solid fa-lock-open"></i> Reestablecer Credenciales
                    </button>
                </form>
            </div>

        </div>

    </div>

    <script>
        // Trigger para abrir el explorador de archivos al hacer click en el avatar
        function triggerFileSelect() {
            document.getElementById('foto_perfil_file').click();
        }

        // Detectar cuando el usuario selecciona un archivo
        document.getElementById('foto_perfil_file').addEventListener('change', function(event) {
            const file = event.target.files[0];
            
            if (file) {
                // Validar tamaño en el lado del cliente por comodidad (3MB)
                if (file.size > 3 * 1024 * 1024) {
                    alert("El archivo es demasiado grande. El límite es de 3MB.");
                    this.value = ""; // Reiniciar input
                    return;
                }

                // Generar URL temporal de previsualización
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatar-preview');
                    const placeholder = document.getElementById('avatar-iniciales-placeholder');
                    
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }

                    // Revelar el botón verde de confirmación
                    document.getElementById('btn-confirmar-subida').style.display = 'inline-block';
                }
                reader.readAsDataURL(file);
            }
        });

        // ==========================================
        // ⚡ FASE 3: GUARDADO INSTANTÁNEO VIA AJAX
        // ==========================================
        document.querySelectorAll('.toggle-preferencia').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Obtenemos el nombre de la preferencia (del atributo data-pref) y su estado (1 o 0)
                const nombrePreferencia = this.getAttribute('data-pref');
                const nuevoEstado = this.checked ? 1 : 0;

                // Deshabilitamos temporalmente el switch para evitar que el usuario haga click repetidamente mientras procesa
                this.disabled = true;

                // Enviamos los datos usando Fetch a nuestro archivo procesador de fondo
                fetch('actualizar_preferencias.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `preferencia=${nombrePreferencia}&estado=${nuevoEstado}`
                })
                .then(response => response.json())
                .then(data => {
                    // Volvemos a habilitar el switch
                    this.disabled = false;

                    if (data.status === 'success') {
                        // Éxito: Opcional, puedes imprimir en consola o hacer un destello visual discreto
                        console.log(`Preferencia ${nombrePreferencia} actualizada a ${nuevoEstado}`);
                    } else {
                        // Si el servidor reporta un error, revertimos el switch a su estado anterior y avisamos
                        this.checked = !this.checked;
                        alert('Error corporativo: ' + data.message);
                    }
                })
                .catch(error => {
                    // Si hay un error de conexión (caída de internet, servidor apagado), revertimos el switch
                    this.disabled = false;
                    this.checked = !this.checked;
                    alert('Error de conexión: No se pudieron guardar tus preferencias.');
                    console.error('Error en AJAX:', error);
                });
            });
        });
    </script>
</body>
</html>