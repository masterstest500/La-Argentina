<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>body { background-color: #ffffff; }</style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/logos/logo3.png">
    <meta name="description" content="Detalle del producto - Helados La Argentina">
    <title>Detalle — Helados La Argentina</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/detalle.css">
    <link rel="sitemap" type="application/xml" href="/sitemap.xml">
</head>
<body>

<?php include 'navbar.php'; ?>


    <!-- ========== DETALLE DEL HELADO ========== -->
    <main id="detalle">

        <!-- Sección principal -->
        <div class="detalle-hero" id="detalle-hero">
            <div class="detalle-galeria">
                <div class="detalle-imagen-principal">
                    <img id="imagen-principal" src="" alt="">
                </div>
                <div class="detalle-miniaturas" id="miniaturas">
                    <!-- Miniaturas generadas por JS -->
                </div>
            </div>
            <div class="detalle-info">
                <a href="productos.php" class="detalle-volver">← Volver a Productos</a>
                <span class="detalle-categoria">Helados La Argentina</span>
                <h1 class="detalle-nombre" id="detalle-nombre"></h1>
                <div class="detalle-separador"></div>
                <p class="detalle-descripcion" id="detalle-descripcion"></p>
            </div>
        </div>

        <!-- Sección Podría interesarte -->
        <div class="detalle-relacionados">
            <h2 class="relacionados-titulo">Podría interesarte</h2>
            <div class="relacionados-grid" id="relacionados-grid">
                <!-- Generado por JS -->
            </div>
        </div>

    </main>

    <!-- ========== FOOTER ========== -->
    <footer id="footer">
        <div class="footer-contenido">
            <div class="footer-col">
                <img src="img/logos/logo2.png" alt="Helados La Argentina" class="footer-logo">
                <p class="footer-slogan">"Demasiado Buenos"</p>
                <div class="footer-redes">
                    <a href="#" class="footer-red proximamente" data-red="Instagram">
                        <img src="img/logos/instagram.png" alt="Instagram">
                    </a>
                    <a href="#" class="footer-red proximamente" data-red="Facebook">
                        <img src="img/logos/facebook.png" alt="Facebook">
                    </a>
                    <a href="#" class="footer-red proximamente" data-red="YouTube">
                        <img src="img/logos/youtube.png" alt="YouTube">
                    </a>
                </div>
            </div>
            <div class="footer-col">
                <h4 class="footer-titulo">Navegación</h4>
                <ul class="footer-nav">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="productos.php">Productos</a></li>
                    <li><a href="quienes-somos.php">¿Quiénes Somos?</a></li>
                    <li><a href="index.php#contacto">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="footer-titulo">Contacto</h4>
                <ul class="footer-contacto">
                    <li>📍 Calle 61 entre calles 114A y 114B, Galpon Numero 28, Zona Industrial Los Robles, Maracaibo - Estado Zulia</li>
                    <li>📞 +58 414-6147918</li>
                    <li>📧 soporteit.helar@gmail.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-barra">
            <footer style = "text-align: center; width: 100%">   
            <p>© 2026 Helados La Argentina · Todos los derechos reservados</p>
            <a href="terminos-condiciones.php" class="footer-terminos">Términos y Condiciones</a>
        </div>
    </footer>

    <div id="whatsapp-btn"></div>
    <script src="js/whatsapp.js"></script>
    <script src="js/detalle.js"></script>

</body>
</html>