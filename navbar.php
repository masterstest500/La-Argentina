<header id="navbar">
    <nav class="nav-container">
        <!-- Logo -->
        <div class="nav-logo">
            <a href="index.php">
                <img src="img/logos/logo.png" alt="Helados La Argentina">
            </a>
        </div>

        <!-- Menú -->
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

        <!-- Redes Sociales -->
        <div class="nav-redes">
            <a href="#" class="red-social proximamente" data-red="Instagram"><img src="img/logos/instagram.png" alt="Instagram"></a>
            <a href="#" class="red-social proximamente" data-red="Facebook"><img src="img/logos/facebook.png" alt="Facebook"></a>
            <a href="#" class="red-social proximamente" data-red="YouTube"><img src="img/logos/youtube.png" alt="YouTube"></a>
        </div>

        <!-- Acciones de Usuario / Login unificadas para evitar doble fuente de verdad -->
        <div class="navbar-acciones">
            <div class="nav-item dropdown" id="user-menu-logged">
                <!-- CORRECCIÓN: href cambiado a "#" -->
                <a href="#" class="navbar-user dropdown-toggle" id="user-name-display" style="text-decoration: none; display: flex; align-items: center;" onclick="event.preventDefault();">
                    <img src="img/logos/login.png" alt="Usuario" class="user-avatar-img" style="filter: brightness(0) invert(1); width: 20px; height: 20px; vertical-align: middle;">
                    <span id="user-text-name" style="color: var(--color-texto-principal); font-weight: bold; margin-left: 8px;">
                        Cargando...
                    </span>
                </a>
                <ul class="dropdown-menu" id="user-dropdown-options">
                    <!-- Las opciones y el nombre se inyectan instantáneamente desde localStorage mediante main.js -->
                </ul>
            </div>
        </div>

        <!-- Botón menú móvil -->
        <div class="nav-toggle" id="nav-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>