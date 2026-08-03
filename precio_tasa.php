<?php
session_start();

// 1. Verificación de autenticación básica
if (!isset($_SESSION['user']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

// 2. Control de accesos por roles (Filtro estricto)
$cargos_autorizados = ['ventas']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// Incluimos conexión (La usaremos en la Fase 4)

// Incluimos conexión
include('conexion.php'); 
$nombre_usuario = $_SESSION['user'] ?? 'Usuario';
$rol_usuario = $_SESSION['cargo'] ?? 'Ventas';

// --- NUEVA LÓGICA PARA LA TASA ---
$pestaña_activa = 'precios'; // Pestaña por defecto

// 1. Si el usuario envió una nueva tasa, la guardamos en la BBDD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_tasa'])) {
    $nueva_tasa = floatval($_POST['nueva_tasa']);
    
    // Asumimos que en tu tabla 'configuracion' tienes un registro donde la clave es 'tasa_bcv'
    $stmt = $conexion->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'tasa_bcv'");
    $stmt->bind_param("d", $nueva_tasa);
    $stmt->execute();
    $stmt->close();
    
    // Cambiamos la pestaña activa para que al recargar se quede en Tasa
    $pestaña_activa = 'tasa';
}

// 2. Consultamos la tasa actual de la BBDD para mostrarla en pantalla
$tasa_actual = 0.00;
$resultado = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'tasa_bcv'");
if ($resultado && $fila = $resultado->fetch_assoc()) {
    $tasa_actual = $fila['valor'];
}
// ---------------------------------

// Consultar todos los productos del inventario trayendo su precio actual si existe
$query_catalogo = "
    SELECT 
        di.codigo, 
        di.producto AS sabor, 
        IFNULL(p.precio, 0.00) AS precio 
    FROM disponibilidad_inventario di
    LEFT JOIN productos p 
        ON di.codigo COLLATE utf8mb4_general_ci = p.codigo COLLATE utf8mb4_general_ci
    WHERE di.cantidad > 0
    ORDER BY di.producto ASC
";
$resultado_catalogo = $conexion->query($query_catalogo);
$catalogo_php = [];
while ($row = $resultado_catalogo->fetch_assoc()) {
    $catalogo_php[] = $row;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Argentina - Actualización de Valores</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #0b0b0b; color: #ffffff; padding: 40px; }
        
        /* Cabecera */
        .header-seccion { margin-bottom: 30px; }
        .header-seccion h1 { font-size: 2rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .header-seccion h1 span { color: #ff0015; }
        .header-seccion p { color: #aaaaaa; margin-top: 5px; font-size: 0.95rem; }
        .btn-volver { color: #ffffff; text-decoration: none; display: inline-block; margin-top: 10px; font-size: 0.9rem; transition: color 0.3s; }
        .btn-volver:hover { color: #ff0015; }

        /* Contenedor Principal */
        .panel-fondo { background-color: #141414; border-radius: 8px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        
        /* 2 Opciones Superiores (Tabs) */
        .tabs-container { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #222; padding-bottom: 15px; }
        .tab-btn { 
            flex: 1; 
            padding: 15px; 
            background-color: #222; 
            color: #aaa; 
            border: 1px solid #333; 
            border-radius: 6px; 
            font-size: 1.1rem; 
            font-weight: 600; 
            text-transform: uppercase; 
            cursor: pointer; 
            transition: all 0.3s ease; 
        }
        .tab-btn:hover { background-color: #333; color: #fff; }
        .tab-btn.active { 
            background-color: #ff0015; 
            color: #ffffff; 
            border-color: #ff0015; 
            box-shadow: 0 4px 10px rgba(255, 0, 21, 0.3);
        }

        /* Secciones Ocultables */
        .vista-seccion { display: none; animation: fadeIn 0.4s ease; }
        .vista-seccion.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        /* Elementos de la Grilla (Precios) */
        table { width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 20px; }
        th { color: #ff0015; padding: 15px 12px; border-bottom: 2px solid #333333; font-size: 0.95rem; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #222222; font-size: 0.95rem; vertical-align: middle; }
        
        .input-precio { 
            width: 120px; 
            padding: 10px; 
            background-color: #0b0b0b; 
            border: 1px solid #333; 
            border-radius: 6px; 
            color: #28a745; 
            font-size: 1rem; 
            font-weight: bold;
        }
        .input-precio:focus { outline: none; border-color: #ff0015; }

        /* Elementos de la Card (Tasa) */
        .card-tasa {
            background-color: #0b0b0b;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }
        .tasa-actual-label { color: #aaa; font-size: 1.1rem; text-transform: uppercase; margin-bottom: 10px; }
        .tasa-actual-valor { font-size: 3rem; font-weight: 700; color: #ff0015; margin-bottom: 30px; display: flex; justify-content: center; align-items: center; gap: 10px; }
        
        .grupo-input { margin-bottom: 25px; text-align: left; }
        .grupo-input label { display: block; color: #ff0015; font-weight: 600; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; }
        .grupo-input input { width: 100%; padding: 14px; background-color: #141414; border: 1px solid #333; border-radius: 6px; color: #fff; font-size: 1.1rem; text-align: center; }
        .grupo-input input:focus { outline: none; border-color: #ff0015; }

        /* Botones dinámicos de la tabla */
        .btn-agregar-fila {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #222;
            color: #fff;
            border: 1px dashed #555;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .btn-agregar-fila:hover {
            background-color: #333;
            border-color: #ff0015;
            color: #ff0015;
        }
        .btn-eliminar-fila {
            background: transparent;
            border: none;
            color: #ff0015;
            cursor: pointer;
            font-size: 1.2rem;
            transition: transform 0.2s;
        }
        .btn-eliminar-fila:hover {
            transform: scale(1.2);
        }

        /* Inputs dentro de la tabla */
        .select-tabla, .input-tabla {
            width: 100%;
            padding: 10px;
            background-color: #0b0b0b;
            border: 1px solid #333;
            border-radius: 6px;
            color: #fff;
            font-size: 0.95rem;
        }
        .select-tabla:focus, .input-tabla:focus {
            outline: none;
            border-color: #ff0015;
        }
        .input-desc-readonly {
            background-color: transparent;
            border: none;
            color: #aaa;
            font-style: italic;
        }

        /* Botón Guardar */
        .btn-primario { width: 100%; padding: 15px; background-color: #ff0015; color: #ffffff; border: none; border-radius: 6px; font-size: 1.05rem; font-weight: 700; text-transform: uppercase; cursor: pointer; transition: background-color 0.3s; }
        .btn-primario:hover { background-color: #ff0019; }
        .btn-flotante { display: flex; justify-content: flex-end; margin-top: 20px; }
        .btn-flotante .btn-primario { width: auto; padding: 15px 40px; }
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Módulo de <span>Actualización de Valores</span></h1>
        <p>Usuario: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
        <a href="index.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
    </div>

    <div class="panel-fondo">
        
        <!-- LAS 2 OPCIONES EN LA PARTE SUPERIOR -->
        <div class="tabs-container">
            <button class="tab-btn <?php echo $pestaña_activa == 'precios' ? 'active' : ''; ?>" onclick="cambiarPestaña('precios', this)">
                <i class="fa-solid fa-tags"></i> Actualizar Precios
            </button>
            <button class="tab-btn <?php echo $pestaña_activa == 'tasa' ? 'active' : ''; ?>" onclick="cambiarPestaña('tasa', this)">
                <i class="fa-solid fa-chart-line"></i> Actualizar Tasa
            </button>
        </div>

        <!-- VISTA 1: GRILLA DE PRECIOS -->
        <div id="vista_precios" class="vista-seccion <?php echo $pestaña_activa == 'precios' ? 'active' : ''; ?>">
            <form action="guardar_precios.php" method="POST">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%;">Código</th>
                            <th style="width: 45%;">Descripción del Producto</th>
                            <th style="width: 20%;">Precio Nuevo ($)</th>
                            <th style="width: 10%; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-precios">
                        <!-- Las filas se inyectarán aquí mediante JavaScript -->
                    </tbody>
                </table>
                
                <!-- Botón de Agregar "+" -->
                <button type="button" class="btn-agregar-fila" onclick="agregarFilaPrecio()">
                    <i class="fa-solid fa-plus"></i> Añadir Producto
                </button>
                
                <div class="btn-flotante">
                    <button type="submit" class="btn-primario">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios en Grilla
                    </button>
                </div>
            </form>
        </div>

        <!-- VISTA 2: CUADRO SOLICITANDO TASA -->
        <div id="vista_tasa" class="vista-seccion <?php echo $pestaña_activa == 'tasa' ? 'active' : ''; ?>">            <!-- Agregamos la etiqueta form apuntando a este mismo archivo -->
            <form method="POST" action="">
                <div class="card-tasa">
                    <div class="tasa-actual-label">Tasa de Cambio Actual (BCV)</div>
                    <div class="tasa-actual-valor">
                        <!-- Imprimimos dinámicamente el valor de la BBDD -->
                        Bs. <?php echo number_format($tasa_actual, 2); ?> <i class="fa-solid fa-circle-check" style="font-size: 1.5rem; color: #28a745;"></i>
                    </div>

                    <div class="grupo-input">
                        <label for="nueva_tasa">Ingresar Nuevo Valor Oficial</label>
                        <!-- Le agregamos el atributo name="nueva_tasa" -->
                        <input type="number" name="nueva_tasa" id="nueva_tasa" step="0.01" placeholder="Ej: 36.65" required>
                    </div>

                    <!-- Cambiamos el type a "submit" -->
                    <button type="submit" class="btn-primario">
                        <i class="fa-solid fa-arrows-rotate"></i> Actualizar Tasa del Sistema
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        // Lógica simple para alternar entre Precio y Tasa sin recargar la página
        function cambiarPestaña(vista, elementoBoton) {
            // 1. Quitar la clase 'active' de todos los botones y secciones
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.vista-seccion').forEach(sec => sec.classList.remove('active'));
            
            // 2. Agregar la clase 'active' al botón clicado y a su vista correspondiente
            elementoBoton.classList.add('active');
            document.getElementById('vista_' + vista).classList.add('active');
        }
    </script>
    
    <script>
    // 1. Lógica de Pestañas
    function cambiarPestaña(vista, elementoBoton) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.vista-seccion').forEach(sec => sec.classList.remove('active'));
        elementoBoton.classList.add('active');
        document.getElementById('vista_' + vista).classList.add('active');
    }

    // 2. Catálogo conectado desde la Base de Datos (PHP a JS)
    const catalogoHelados = [
        <?php foreach ($catalogo_php as $prod): ?>
        { codigo: "<?php echo $prod['codigo']; ?>", descripcion: "<?php echo htmlspecialchars($prod['sabor']); ?>", precio: "<?php echo $prod['precio']; ?>" },
        <?php endforeach; ?>
    ];

    // 3. Generar las opciones de AMBOS Selects dinámicamente
    let opcionesCodigoHTML = '<option value="">Seleccione un código...</option>';
    let opcionesDescHTML = '<option value="">Seleccione un producto...</option>';
    
    catalogoHelados.forEach(item => {
        opcionesCodigoHTML += `<option value="${item.codigo}">${item.codigo}</option>`;
        opcionesDescHTML += `<option value="${item.codigo}">${item.descripcion}</option>`;
    });

    // 4. Función para agregar una nueva fila vacía con campo de texto controlado
    function agregarFilaPrecio() {
        const tbody = document.getElementById('tbody-precios');
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>
                <select class="select-tabla select-codigo" name="codigo_producto[]" onchange="sincronizarFila(this, 'codigo')" required>
                    ${opcionesCodigoHTML}
                </select>
            </td>
            <td>
                <select class="select-tabla select-desc" name="desc_producto[]" onchange="sincronizarFila(this, 'descripcion')" required>
                    ${opcionesDescHTML}
                </select>
            </td>
            <td>
                <input type="text" 
                    class="input-precio" 
                    name="nuevo_precio[]" 
                    placeholder="0.00"
                    autocomplete="off" 
                    maxlength="7"
                    oninput="filtrarEntradaPrecio(this)"
                    onblur="validarYFormatearPrecio(this)" 
                    required>
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-eliminar-fila" onclick="eliminarFila(this)" title="Quitar fila">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    // Filtra la escritura en tiempo real (impide pegar letras, múltiples puntos o > 3 dígitos enteros)
    function filtrarEntradaPrecio(input) {
        // Permite solo números y punto
        input.value = input.value.replace(/[^0-9.]/g, '');
        
        // Evita múltiples puntos consecutivos o dispersos
        const partes = input.value.split('.');
        if (partes.length > 2) {
            input.value = partes[0] + '.' + partes.slice(1).join('');
        }
        
        // Restringe la parte entera a un máximo de 3 dígitos (máximo 999)
        if (partes[0].length > 3) {
            partes[0] = partes[0].substring(0, 3);
            input.value = partes.join('.');
        }
    }

    // Valida y corrige la estructura final al salir del casilla
    function validarYFormatearPrecio(input) {
        if (!input.value) return;

        let valorNum = parseFloat(input.value);

        // Rechaza ceros (0, 0.00, 000.00) o valores que excedan 999
        if (isNaN(valorNum) || valorNum <= 0 || valorNum > 999) {
            alert("Ingrese un precio válido (mayor a 0.00 y máximo 999.99).");
            input.value = "";
            return;
        }

        const partes = input.value.split('.');
        
        // Si no tiene punto decimal, le asigna .00 por defecto
        if (partes.length === 1) {
            input.value = valorNum.toFixed(2);
            return;
        }

        // Manejo exacto de decimales (admite 2 o 3 dígitos, ej: 10.00 o 10.000)
        const decimales = partes[1];
        if (decimales.length < 2) {
            input.value = valorNum.toFixed(2); // Corrige ej: 10.0 a 10.00
        } else if (decimales.length > 3) {
            input.value = valorNum.toFixed(2); // Recorta exceso de decimales ej: 10.00000 a 10.00
        }
    }

    // 5. Función de autocompletado bidireccional
    function sincronizarFila(selectElement, origen) {
        const fila = selectElement.closest('tr');
        const selectCodigo = fila.querySelector('.select-codigo');
        const selectDesc = fila.querySelector('.select-desc');
        const inputPrecio = fila.querySelector('.input-precio');
        
        const valorSeleccionado = selectElement.value;
        
        if (origen === 'codigo') {
            selectDesc.value = valorSeleccionado;
        } else if (origen === 'descripcion') {
            selectCodigo.value = valorSeleccionado;
        }

        // Opcional: Autocompletar el precio actual si se desea
        const productoEncontrado = catalogoHelados.find(item => item.codigo === valorSeleccionado);
        if (productoEncontrado && inputPrecio.value === "") {
            let precioBase = parseFloat(productoEncontrado.precio);
            inputPrecio.value = !isNaN(precioBase) && precioBase > 0 ? precioBase.toFixed(2) : "";
        }
    }

    // 6. Función para eliminar la fila
    function eliminarFila(boton) {
        const fila = boton.closest('tr');
        fila.remove();
    }

    // 7. Arrancar el módulo con una fila vacía por defecto
    document.addEventListener("DOMContentLoaded", () => {
        agregarFilaPrecio();
    });
    </script>

</body>
</html>