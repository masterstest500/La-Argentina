<?php
session_start();
// 🛡️ Escudo Multi-Rol: Solo entran usuarios autenticados con cargos válidos
$cargoUsuario = isset ($_SESSION['cargo']) ? strtolower($_SESSION['cargo']) : '';

if (!isset($_SESSION['user']) || !in_array($cargoUsuario, ['administrador', 'ventas', 'preventista'])) {
    header('Location: index.php?error=acceso_denegado');
    exit();
}
$esPreventista = ($cargoUsuario === 'preventista');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:image" content="img/logos/logo3.png">
    <title>La Argentina - Gestión de Clientes</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        .container { max-width: 1200px; margin: 40px auto; padding: 20px; font-family: 'Montserrat', sans-serif; color: #fff; }
        .grid-clientes { display: grid; grid-template-columns: 1fr; gap: 30px; }
        @media(min-width: 768px) { .grid-clientes { grid-template-columns: 1fr 1fr; } }
        
        .formulario-card { background: #1c1c1c; padding: 25px; border-radius: 8px; border-left: 4px solid #cc0000; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: #ff2222; }
        .form-group input { width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; border-radius: 4px; color: #fff; font-family: 'Montserrat', sans-serif; }
        .form-group input[readonly] { background: #151515; color: #888; cursor: not-allowed; }
        
        .btn-guardar { background: #cc0000; color: white; border: none; padding: 12px 20px; font-weight: bold; border-radius: 4px; cursor: pointer; width: 100%; text-transform: uppercase; transition: background 0.3s; }
        .btn-guardar:hover { background: #ff2222; }
        
        /* Contenedor del mapa */
        #mapa { height: 450px; width: 100%; border-radius: 8px; border: 2px solid #333; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .instruccion-mapa { font-size: 0.85rem; color: #aaa; margin-bottom: 10px; font-style: italic; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Gestión de Clientes</h2>
        <p>Bienvenido, <?php echo $_SESSION['user']; ?> (<?php echo ucfirst($_SESSION['cargo']); ?>)</p>
        <a href="index.php" style="color: #aaa; text-decoration: none; font-size: 0.9rem;">← Volver al Inicio</a>
        
        <br><br>

        <div class="grid-clientes">
            <!-- El formulario ahora es visible para TODOS los roles -->
            <div class="formulario-card">
                <h3>
                    <?php echo $esPreventista ? 'Registrar Nuevo Cliente en Ruta' : '🏢 Registrar Nuevo Cliente'; ?>
                </h3>
                
                <?php if ($esPreventista): ?>
                    <p style="font-size: 0.85rem; color: #aaa; margin-bottom: 15px;">
                        Como Ejecutivo de Calle, utiliza el mapa para capturar las coordenadas exactas del nuevo negocio.
                    </p>
                <?php endif; ?>

                <form action="guardar-cliente.php" method="POST">
                    <div class="form-group">
                        <label>Nombre del Negocio / Cliente</label>
                        <input type="text" name="nombre" required placeholder="Ej. Heladería La Paragua">
                    </div>
                    <div class="form-group">
                        <label>RIF o Cédula</label>
                        <input type="text" name="rif" required placeholder="Ej. J-12345678-0">
                    </div>
                    <div class="form-group">
                        <label>Dirección Corta</label>
                        <input type="text" name="direccion" required placeholder="Ej. Av. Universidad, local 4">
                    </div>
                    <div class="form-group">
                        <label>Latitud (Capturada desde el mapa)</label>
                        <input type="text" id="latitud" name="latitud" readonly required placeholder="Haz clic en el mapa">
                    </div>
                    <div class="form-group">
                        <label>Longitud (Capturada desde el mapa)</label>
                        <input type="text" id="longitud" name="longitud" readonly required placeholder="Haz clic en el mapa">
                    </div>
                    <button type="submit" class="btn-guardar">Guardar Cliente Geolocalizado</button>
                </form>
            </div>

            <div>
                <p class="instruccion-mapa">Haga clic en cualquier punto de Maracaibo para fijar la ubicación exacta del negocio.</p>
                <div id="mapa"></div>
            </div>
        </div>
    </div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // 1. Inicializar el mapa centrado en Maracaibo
    const mapa = L.map('mapa').setView([10.6442, -71.6197], 13);

    // 2. Cargar la capa de diseño de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapa);

    // 3. Variables globales para los inputs y el marcador único
    const inputLat = document.getElementById('latitud');
    const inputLng = document.getElementById('longitud');
    let marcador = null;

    // 🧹 FUNCIÓN MAESTRA: Borra el marcador del mapa y vacía los campos de texto
    function limpiarUbicacion() {
        if (marcador) {
            mapa.removeLayer(marcador);
            marcador = null;
        }
        if (inputLat && inputLng) {
            inputLat.value = '';
            inputLng.value = '';
        }
    }

    // 4. Capturar el clic en el mapa para posicionar o mover el marcador
    mapa.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        // Rellenar los inputs con precisión de 8 decimales (perfecto para tu base de datos)
        if (inputLat && inputLng) {
            inputLat.value = lat.toFixed(8);
            inputLng.value = lng.toFixed(8);
        }

        // SI YA EXISTE EL MARCADOR: Simplemente lo movemos de lugar (evita que se acumulen)
        if (marcador) {
            marcador.setLatLng(e.latlng);
            marcador.openPopup();
        } 
        // SI NO EXISTE: Lo creamos desde cero con todos sus superpoderes interactivos
        else {
            marcador = L.marker(e.latlng).addTo(mapa);
            
            // 🟥 CONTROL 1: Botón interactivo dentro del Popup
            marcador.bindPopup(`
                <div style="text-align:center; font-family:'Montserrat', sans-serif;">
                    <b style="color:#1c1c1c;">📍 Ubicación Fijada</b><br>
                    <span style="font-size:0.75rem; color:#666;">¿Te equivocaste?</span><br><br>
                    <button onclick="limpiarUbicacion()" style="background:#cc0000; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-weight:bold; font-size:0.75rem; text-transform:uppercase;">
                        ❌ Quitar Punto
                    </button>
                </div>
            `);

            // 🖱️ CONTROL 2: Doble clic sobre el marcador para eliminarlo directamente
            marcador.on('dblclick', function() {
                limpiarUbicacion();
            });

            // 🔲 CONTROL 3: Clic derecho sobre el marcador para eliminarlo
            marcador.on('contextmenu', function() {
                limpiarUbicacion();
            });

            marcador.openPopup();
        }
    });
</script>