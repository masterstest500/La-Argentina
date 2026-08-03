<?php
session_start();
include('conexion.php');

// 1. Verificación de autenticación y rol
if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$cargos_autorizados = ['ventas']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// 2. Capturar y limpiar datos principales del formulario
$nombre_negocio     = trim($_POST['nombre_negocio'] ?? '');
$rif                = trim($_POST['rif'] ?? '');
$codigo_cliente     = trim($_POST['codigo_cliente'] ?? '');
$id_ruta            = intval($_POST['id_ruta'] ?? 0);
$direccion_fiscal   = trim($_POST['direccion_fiscal'] ?? '');
$latitud            = trim($_POST['latitud'] ?? '');
$longitud           = trim($_POST['longitud'] ?? '');
$persona_contacto   = trim($_POST['persona_contacto'] ?? '');
$telefono           = trim($_POST['telefono'] ?? '');
$correo             = trim($_POST['correo'] ?? '');

// Datos opcionales: Sucursal
$es_sucursal        = isset($_POST['es_sucursal']) ? 1 : 0;
$codigo_sucursal    = $es_sucursal ? trim($_POST['codigo_sucursal'] ?? '') : null;
$nombre_sucursal    = $es_sucursal ? trim($_POST['nombre_sucursal'] ?? '') : null;

// Dato de nevera
$tiene_nevera       = isset($_POST['tiene_nevera']) ? 1 : 0;

// Validar campos obligatorios básicos y GPS
if (empty($nombre_negocio) || empty($rif) || empty($codigo_cliente) || empty($id_ruta) || empty($direccion_fiscal) || empty($latitud) || empty($longitud)) {
    header("Location: clientes.php?error=campos_vacios");
    exit();
}

// 3. Iniciar transacción para asegurar integridad de datos
$conexion->begin_transaction();

try {
    // Insertar el cliente principal
    $sql_cliente = "INSERT INTO clientes (nombre_negocio, rif, codigo_cliente, codigo_sucursal, id_ruta, direccion_fiscal, latitud, longitud, persona_contacto, telefono, correo, tiene_nevera) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql_cliente);
    $stmt->bind_param("ssssissssssi", 
        $nombre_negocio, 
        $rif, 
        $codigo_cliente,
        $codigo_sucursal,
        $id_ruta, 
        $direccion_fiscal, 
        $latitud, 
        $longitud, 
        $persona_contacto, 
        $telefono, 
        $correo,
        $tiene_nevera
    );
    
    $stmt->execute();
    $id_cliente_insercion = $conexion->insert_id; 
    $stmt->close();

    // 4. Si es sucursal, registrar en su tabla correspondiente (usando id_cliente)
    if ($es_sucursal && !empty($codigo_sucursal)) {
        $sql_sucursal = "INSERT INTO sucursales (codigo_sucursal, id_cliente, nombre_sucursal, direccion) VALUES (?, ?, ?, ?)";
        $stmt_sucursal = $conexion->prepare($sql_sucursal);
        $stmt_sucursal->bind_param("siss", $codigo_sucursal, $id_cliente_insercion, $nombre_sucursal, $direccion_fiscal);
        $stmt_sucursal->execute();
        $stmt_sucursal->close();
    }

    // 5. Procesar Equipos en cliente_equipos (usando cliente_id)
    if ($tiene_nevera && isset($_POST['modelo_nevera'])) {
        $modelos = $_POST['modelo_nevera'];
        $cantidades = $_POST['cantidad_nevera'];

        // CORREGIDO: cliente_id coincide exactamente con la columna de la BBDD
        $sql_equipo = "INSERT INTO cliente_equipos (cliente_id, modelo_nevera, cantidad) VALUES (?, ?, ?)";
        $stmt_equipo = $conexion->prepare($sql_equipo);

        for ($i = 0; $i < count($modelos); $i++) {
            $modelo = trim($modelos[$i]);
            $cantidad = intval($cantidades[$i] ?? 1);

            if (!empty($modelo) && $cantidad > 0) {
                $stmt_equipo->bind_param("isi", $id_cliente_insercion, $modelo, $cantidad);
                $stmt_equipo->execute();
            }
        }
        $stmt_equipo->close();
    }

    // Confirmar transacción
    $conexion->commit();

    // Redireccionar con éxito
    header("Location: clientes.php?status=exito");
    exit();

} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conexion->rollback();
    echo "Error al registrar el cliente: " . $e->getMessage();
}
?>