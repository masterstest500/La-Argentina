<?php
session_start();

// 1. Verificación de autenticación y rol
if (!isset($_SESSION['user']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

$cargos_autorizados = ['ventas']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

$nombre_usuario = $_SESSION['user'] ?? 'Usuario';
$rol_usuario = $_SESSION['cargo'] ?? 'Ventas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Argentina - Creación de Clientes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
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

        /* Contenedor Principal y Grid */
        .panel-fondo { background-color: #141414; border-radius: 8px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        
        /* Secciones del Formulario */
        .bloque-form { background-color: #0b0b0b; border: 1px solid #222; border-radius: 8px; padding: 25px; }
        .bloque-titulo { color: #ff0015; font-size: 1.1rem; font-weight: 600; margin-bottom: 20px; border-bottom: 1px solid #222; padding-bottom: 10px; text-transform: uppercase; }

        /* Inputs */
        .grupo-input { margin-bottom: 20px; }
        .grupo-input label { display: block; color: #aaa; font-weight: 600; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; }
        .grupo-input input, .grupo-input select, .grupo-input textarea { 
            width: 100%; padding: 12px; background-color: #141414; border: 1px solid #333; 
            border-radius: 6px; color: #fff; font-size: 0.95rem; 
        }
        .grupo-input input:focus, .grupo-input select:focus, .grupo-input textarea:focus { outline: none; border-color: #ff0015; }
        .grupo-input textarea { resize: vertical; min-height: 80px; }

        /* Toggles (Interruptores Modernos) */
        .toggle-container { display: flex; align-items: center; justify-content: space-between; background-color: #141414; padding: 12px; border: 1px solid #333; border-radius: 6px; margin-bottom: 20px; }
        .toggle-label { color: #fff; font-weight: 600; font-size: 0.9rem; }
        .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #ff0015; }
        input:checked + .slider:before { transform: translateX(26px); }

        /* Elementos Dinámicos (Ocultos por defecto) */
        .campo-dinamico { display: none; opacity: 0; transition: opacity 0.4s ease; }
        .campo-dinamico.visible { display: block; opacity: 1; animation: slideDown 0.3s ease forwards; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Botón Guardar */
        .btn-primario { width: 100%; padding: 15px; background-color: #ff0015; color: #ffffff; border: none; border-radius: 6px; font-size: 1.05rem; font-weight: 700; text-transform: uppercase; cursor: pointer; transition: background-color 0.3s; margin-top: 20px; }
        .btn-primario:hover { background-color: #ff0019; }

        /* Botón para agregar más equipos */
        .btn-agregar-equipo {
            background: transparent;
            color: #ff0015;
            border: 1px dashed #ff0015;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            width: 100%;
            margin-top: 10px;
            transition: background 0.3s, color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-agregar-equipo:hover {
            background-color: rgba(255, 0, 21, 0.1);
        }

        /* Fila dinámica de equipo */
        .item-equipo-row {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 12px;
            align-items: flex-end;
            margin-bottom: 12px;
        }

        /* Botón de eliminar fila */
        .btn-quitar-equipo {
            background-color: #222;
            color: #ff4d4d;
            border: 1px solid #444;
            border-radius: 6px;
            height: 45px;
            width: 45px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-quitar-equipo:hover {
            background-color: #ff0015;
            color: #ffffff;
            border-color: #ff0015;
        }

        /* Contenedor del Mapa (Fase 3) */
        .mapa-placeholder { width: 100%; height: 200px; background-color: #1a1a1a; border: 1px dashed #444; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #666; font-size: 0.9rem; text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="header-seccion">
        <h1>Módulo de <span>Creación de Clientes</span></h1>
        <p>Bienvenido: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)</p>
        <a href="index.php" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
    </div>

    <div class="panel-fondo">
        <form action="#" method="POST" id="form-cliente">
            <div class="form-grid">
                
                <!-- ==========================================
                    FILA 1
                =========================================== -->
                
                <!-- Bloque 1 (Izquierda): Identidad -->
                <div class="bloque-form">
                    <div class="bloque-titulo"><i class="fa-solid fa-id-card"></i> Identidad del Cliente</div>
                    
                    <div class="grupo-input">
                        <label>Razón Social</label>
                        <input type="text" name="nombre_negocio" placeholder="Ej: Inversiones Los Andes C.A." required>
                    </div>
                    <div class="grupo-input">
                        <label>RIF / Documento</label>
                        <input type="text" name="rif" placeholder="Ej: J-12345678-9" required>
                    </div>
                    <div class="grupo-input">
                        <label>Código Principal</label>
                        <input type="text" name="codigo_cliente" placeholder="Ej: CLI-001" required>
                    </div>

                    <!-- Toggle Sucursal -->
                    <div class="toggle-container">
                        <span class="toggle-label">¿Es una Sucursal?</span>
                        <label class="switch">
                            <input type="checkbox" id="check_sucursal" name="es_sucursal">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Campo Dinámico: Datos de Sucursal -->
                    <div class="campo-dinamico" id="caja_sucursal">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                            <div class="grupo-input" style="margin-bottom: 0;">
                                <label>Código de Sucursal</label>
                                <input type="text" name="codigo_sucursal" placeholder="Ej: SUC-NORTE-01">
                            </div>
                            <div class="grupo-input" style="margin-bottom: 0;">
                                <label>Nombre de Sucursal</label>
                                <input type="text" name="nombre_sucursal" placeholder="Ej: Sucursal Bella Vista">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bloque 2 (Derecha): Logística y Ubicación -->
                <div class="bloque-form">
                    <div class="bloque-titulo"><i class="fa-solid fa-map-location-dot"></i> Logística y Ubicación</div>
                    <div class="grupo-input">
                        <label>Ruta Asignada</label>
                        <select name="id_ruta" required>
                            <option value="">Seleccione una ruta...</option>
                            <option value="1">NORTE</option>
                            <option value="2">SUR</option>
                            <option value="3">COL</option>
                            <option value="4">VILLA</option>
                            <option value="5">CONCEPCIÓN</option>
                        </select>
                    </div>
                    <div class="grupo-input">
                        <label>Dirección Fiscal Exacta</label>
                        <textarea name="direccion_fiscal" placeholder="Avenida, Calle, Edificio, Local..." required></textarea>
                    </div>
                    
                    <!-- Ubicación Geográfica (GPS) con Mapa -->
                    <div class="grupo-input">
                        <label>Ubicación Geográfica (GPS)</label>
                        <div id="mapa" style="width: 100%; height: 250px; border-radius: 6px; border: 1px solid #333; z-index: 1;"></div>
                        
                        <div id="coordenadas_text" style="margin-top: 8px; font-size: 0.85rem; color: #aaa;">
                            <i class="fa-solid fa-circle-info"></i> Mueve el marcador en el mapa para capturar la ubicación.
                        </div>

                        <input type="hidden" name="latitud" id="latitud">
                        <input type="hidden" name="longitud" id="longitud">
                    </div>
                </div>

                <!-- ==========================================
                    FILA 2
                =========================================== -->

                <!-- Bloque 3 (Izquierda): Contacto -->
                <div class="bloque-form">
                    <div class="bloque-titulo"><i class="fa-solid fa-address-book"></i> Datos de Contacto</div>
                    <div class="grupo-input">
                        <label>Persona de Contacto</label>
                        <input type="text" name="persona_contacto" placeholder="Ej: Juan Pérez">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="grupo-input">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" placeholder="0414-0000000">
                        </div>
                        <div class="grupo-input">
                            <label>Correo Electrónico</label>
                            <input type="email" name="correo" placeholder="correo@empresa.com">
                        </div>
                    </div>
                </div>

                <!-- Bloque 4 (Derecha): Equipamiento Comercial -->
                <div class="bloque-form">
                    <div class="bloque-titulo"><i class="fa-solid fa-snowflake"></i> Neveras (Modelo y Cantidad)</div>
                    
                    <!-- Toggle Nevera -->
                    <div class="toggle-container">
                        <span class="toggle-label">¿Posee Nevera de la Empresa?</span>
                        <label class="switch">
                            <input type="checkbox" id="check_nevera" name="tiene_nevera">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- Campos Dinámicos: Datos de la Nevera -->
                    <div class="campo-dinamico" id="caja_nevera">
                        <div id="contenedor_equipos">
                            <div class="item-equipo-row">
                                <div class="grupo-input" style="margin-bottom:0;">
                                    <label>Modelo del Equipo</label>
                                    <select name="modelo_nevera[]">
                                        <option value="">Seleccione...</option>
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
                                <div class="grupo-input" style="margin-bottom:0;">
                                    <label>Cantidad</label>
                                    <input type="number" name="cantidad_nevera[]" min="1" value="1">
                                </div>
                                <button type="button" class="btn-quitar-equipo" style="visibility: hidden;" onclick="eliminarFilaEquipo(this)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" class="btn-agregar-equipo" id="btn_agregar_equipo">
                            <i class="fa-solid fa-plus"></i> Agregar Otro Modelo
                        </button>
                    </div>
                </div>

                <!-- ==========================================
                    FILA 3 (Botón Final)
                =========================================== -->
                
                <!-- BOTÓN PRINCIPAL DE REGISTRO -->
                <!-- grid-column: 1 / -1; obliga al contenedor a expandirse por ambas columnas -->
                <div style="grid-column: 1 / -1;">
                    <button type="submit" class="btn-primario" style="margin-top: 0;">
                        <i class="fa-solid fa-user-plus"></i> Registrar Nuevo Cliente
                    </button>
                </div>

            </div>
        </form>
    </div>

    <!-- Lógica de Dinamismo (JavaScript) -->
    <script>
        // Función genérica para manejar los toggles
        function manejarToggle(checkboxId, cajaId) {
            const checkbox = document.getElementById(checkboxId);
            const caja = document.getElementById(cajaId);

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    caja.classList.add('visible');
                    // Hacer los campos requeridos si están visibles
                    caja.querySelectorAll('input, select').forEach(input => input.required = true);
                } else {
                    caja.classList.remove('visible');
                    // Quitar el requerido y limpiar valores si se ocultan
                    caja.querySelectorAll('input, select').forEach(input => {
                        input.required = false;
                        if(input.type !== 'checkbox' && input.type !== 'radio') input.value = '';
                    });
                }
            });
        }

        // Inicializar los listeners
        manejarToggle('check_sucursal', 'caja_sucursal');
        manejarToggle('check_nevera', 'caja_nevera');

        // --- LÓGICA PARA DUPLICAR FILAS DE EQUIPOS ---
        const btnAgregarEquipo = document.getElementById('btn_agregar_equipo');
        const contenedorEquipamiento = document.getElementById('contenedor_equipos');

        if (btnAgregarEquipo) {
            btnAgregarEquipo.addEventListener('click', function() {
                // Clonar la primera fila de equipos
                const primeraFila = contenedorEquipamiento.querySelector('.item-equipo-row');
                const nuevaFila = primeraFila.cloneNode(true);

                // Resetear valores de la nueva fila
                nuevaFila.querySelector('select').value = '';
                nuevaFila.querySelector('input[type="number"]').value = '1';

                // Mostrar el botón de eliminar en la nueva fila clonada
                const btnEliminar = nuevaFila.querySelector('.btn-quitar-equipo');
                btnEliminar.style.visibility = 'visible';

                // Insertar en el contenedor
                contenedorEquipamiento.appendChild(nuevaFila);
            });
        }

        // Función para eliminar filas agregadas dinámicamente
        function eliminarFilaEquipo(boton) {
            const filas = contenedorEquipamiento.querySelectorAll('.item-equipo-row');
            // Asegurar que quede al menos una fila
            if (filas.length > 1) {
                boton.closest('.item-equipo-row').remove();
            }
        }
    </script>

    <!-- Script de Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // --- LÓGICA DEL MAPA INTERACTIVO (VERSIÓN CLIC Y POPUP) ---
        
        // 1. Inicializar el mapa centrado en Maracaibo
        const latInicial = 10.6316;
        const lngInicial = -71.6406;
        const mapa = L.map('mapa').setView([latInicial, lngInicial], 13);

        // 2. Cargar la capa clásica de calles (Limpia y detallada, como en tu captura)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapa);

        // 3. Crear el Pin rojo
        const iconoRojo = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        let marcador = null;
        const feedbackText = document.getElementById('coordenadas_text');

        // 4. Evento: Cuando el usuario hace CLIC en cualquier parte del mapa
        mapa.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            // Si ya hay un marcador, lo movemos; si no, lo creamos
            if (marcador) {
                marcador.setLatLng(e.latlng);
            } else {
                marcador = L.marker(e.latlng, { icon: iconoRojo }).addTo(mapa);
                
                // Evento: DOBLE CLIC en el pin para eliminarlo
                marcador.on('dblclick', function() {
                    mapa.removeLayer(marcador);
                    marcador = null;
                    document.getElementById('latitud').value = '';
                    document.getElementById('longitud').value = '';
                    feedbackText.innerHTML = '<i class="fa-solid fa-circle-info"></i> Haz clic en el mapa para capturar la ubicación.';
                });
            }

            // Inyectar valores en los inputs ocultos
            document.getElementById('latitud').value = lat.toFixed(8);
            document.getElementById('longitud').value = lng.toFixed(8);
            feedbackText.innerHTML = `<span style="color:#28a745;"><i class="fa-solid fa-check-circle"></i> Coordenadas guardadas: <strong>${lat.toFixed(6)}, ${lng.toFixed(6)}</strong> (Doble clic para borrar)</span>`;
            // 5. Geocodificación Inversa: Consultar el nombre de la calle/zona
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    // Extraer el nombre de la ubicación de la respuesta de la API
                    const direccionCortada = data.display_name ? data.display_name.split(',').slice(0, 3).join(',') : "Ubicación seleccionada";
                    
                    // Mostrar la ventanita (Popup)
                    marcador.bindPopup(`<div style="text-align:center; font-weight:600; color:#333;">${direccionCortada}</div>`).openPopup();

                    // Cerrar la ventanita automáticamente después de 5 segundos
                    setTimeout(() => {
                        if (marcador) marcador.closePopup();
                    }, 5000);
                })
                .catch(error => {
                    // En caso de error de conexión, muestra un mensaje genérico
                    marcador.bindPopup("Ubicación capturada").openPopup();
                    setTimeout(() => { if (marcador) marcador.closePopup(); }, 5000);
                });
        });
    </script>
</body>
</html>