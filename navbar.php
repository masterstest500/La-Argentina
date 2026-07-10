<header id="navbar">
    <nav class="nav-container">
        <div class="nav-logo">
            <a href="index.php"> <img src="img/logos/logo.png" alt="Helados La Argentina">
            </a>
        </div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php" class="nav-link">Inicio</a></li>
            <li class="nav-item"><a href="productos.php" class="nav-link">Productos</a></li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle">¿Quiénes Somos? ▾</a>
                <ul class="dropdown-menu">
                    <li><a href="quienes-somos.php?seccion=historia" class="dropdown-link">Historia</a></li>
                    <li><a href="quienes-somos.php" class="dropdown-link">Misión y Visión</a></li>
                    <li><a href="quienes-somos.php?seccion=valores" class="dropdown-link">Principios y Valores</a></li>
                </ul>
            </li>
            <li class="nav-item"><a href="index.php#contacto" class="nav-link">Contacto</a></li>
            <li class="nav-item"><a href="index.php#ubicanos" class="nav-link">Ubícanos</a></li>
            <li class="nav-item"><a href="descargables.php" class="nav-link">Descargables</a></li>
        </ul>

        <div class="nav-redes">
            <a href="#" class="red-social proximamente" data-red="Instagram"><img src="img/logos/instagram.png" alt="Instagram"></a>
            <a href="#" class="red-social proximamente" data-red="Facebook"><img src="img/logos/facebook.png" alt="Facebook"></a>
            <a href="#" class="red-social proximamente" data-red="YouTube"><img src="img/logos/youtube.png" alt="YouTube"></a>
        </div>

        <div class="navbar-acciones">
            <?php if(isset($_SESSION['rol'])): ?>
                <a href="panel_<?= strtolower($_SESSION['rol']) ?>.php" class="navbar-user">
                    <img src="img/logos/avatar.png" alt="Usuario" class="user-avatar-img">
                    <span style="color: #333; font-weight: bold; margin-left: 8px;">
                        <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Mi Panel') ?>
                    </span>
                </a>
            <?php else: ?>
                <a href="login.php" class="navbar-login" data-red="Iniciar sesión">
                    <img src="img/logos/login.png" alt="Login Helar" class="login-logo-img">
                </a>
            <?php endif; ?>
        </div>

        <div class="nav-toggle" id="nav-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>