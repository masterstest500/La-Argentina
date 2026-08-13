// ========================================================
// 1. PANTALLA DE BIENVENIDA (SPLASH SCREEN)
// ========================================================
document.addEventListener("DOMContentLoaded", () => {
    const splashScreen = document.getElementById('splash-screen');

    if (splashScreen) {
        const yaVisitado = sessionStorage.getItem('splashMostrado');

        if (yaVisitado) {
            splashScreen.style.display = 'none';
        } else {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    splashScreen.classList.add('oculto');
                    sessionStorage.setItem('splashMostrado', 'true');
                    
                    setTimeout(() => {
                        splashScreen.style.display = 'none';
                    }, 800);
                }, 1000); 
            });
        }
    }

    // ¡IMPORTANTE! Ejecutamos el control del Navbar aquí adentro al cargar el DOM
    controlarEstadoUsuarioNavbar();

    

    // 🚨 ¡NUEVO! Ejecutamos el detector de intrusos de la Fase 2
    detectarErrorAcceso();
});

// ========================================================
// 2. SCROLL REVEAL PRODUCTOS
// ========================================================
const tarjetas = document.querySelectorAll('.producto-card');

if (tarjetas.length > 0) {
    const observador = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observador.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15
    });

    tarjetas.forEach(tarjeta => {
        observador.observe(tarjeta);
    });
}

// ========================================================
// 3. NAVBAR HAMBURGUESA / MENÚ MÓVIL
// ========================================================
const navToggle = document.getElementById('nav-toggle');
const navMenu = document.querySelector('.nav-menu');

if (navToggle && navMenu) {
    navToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        navMenu.classList.toggle('activo');
        navToggle.classList.toggle('activo');
    });

    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            if (toggle.id === "user-nav-trigger" || toggle.id === "user-name-display") {
                const estaAutenticado = localStorage.getItem("trabajadorAutenticado") === "true";
                if (!estaAutenticado) return;
            }

            e.preventDefault();
            e.stopPropagation();
            const dropdown = toggle.closest('.dropdown');
            
            document.querySelectorAll('.dropdown').forEach(d => {
                if (d !== dropdown) d.classList.remove('activo');
            });
            dropdown.classList.toggle('activo');
        });
    });

    document.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('activo');
            navToggle.classList.remove('activo');
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('activo'));
        });
    });

    document.querySelectorAll('.dropdown-link').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('activo');
            navToggle.classList.remove('activo');
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('activo'));
        });
    });

    document.addEventListener('click', (e) => {
        if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('activo');
            navToggle.classList.remove('activo');
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('activo'));
        }
    });
}

// ========================================================
// 4. GESTOR DE SESIÓN UNIFICADO Y ANIMACIÓN DEL NAVBAR
// ========================================================
function controlarEstadoUsuarioNavbar() {
    const trigger = document.getElementById("user-nav-trigger") || document.getElementById("user-name-display");
    const navText = document.getElementById("user-nav-text") || document.getElementById("user-text-name");
    const dropdownMenu = document.getElementById("user-dropdown-options");
    const menuDesplegable = document.getElementById('user-menu-logged');

    if (!trigger || !dropdownMenu) return;

    // Diccionario con las NUEVAS funciones oficiales solicitadas por el tutor
    const rutasPorCargo = {
        "preventista": [
            { texto: "Catálogo", url: "catalogo.php" },
            { texto: "Pedidos", url: "pedidos.php" },
            { texto: "Captación", url: "captacion.php" },
            { texto: "Disponibilidad", url: "neveras.php" }
        ],
        "ventas": [
            { texto: "Creación de clientes", url: "clientes.php" },
            { texto: "Editar Catálogo", url: "editar-catalogo.php" },
            { texto: "Cambio de precio y tasa", url: "precio_tasa.php" },
            { texto: "Documentación de captados", url: "ventas_descargas.php" },
            { texto: "Disponibilidad", url: "disponibilidad.php" }
        ],
    };

    const estaAutenticado = localStorage.getItem("trabajadorAutenticado") === "true";
    const dataGuardada = localStorage.getItem("usuarioSesion") || localStorage.getItem("usuarioData");
    const usuario = dataGuardada ? JSON.parse(dataGuardada) : null;

    if (estaAutenticado && usuario && usuario.nombre) {
        if (menuDesplegable) menuDesplegable.classList.remove('guest-mode');
        
        const primerNombre = usuario.nombre.trim().split(" ")[0];
        if (navText) {
            navText.textContent = primerNombre;
            navText.style.display = "inline";
        }

        trigger.setAttribute("href", "#");

        const cargoUsuario = usuario.cargo ? usuario.cargo.toLowerCase().trim() : "";
        let enlacesHtml = "";

        // 1. Primera Opción Fija: Perfil de Usuario (Para todos los perfiles)
        enlacesHtml += `<li><a href="perfil.php" class="dropdown-link" style="color: #fff; padding: 10px; display: block; text-decoration: none;">Perfil de Usuario</a></li>`;
        
        // 2. Primera raya blanca (Separador estético del perfil)
        enlacesHtml += `<li><hr style="margin: 5px 0; border-color: rgba(255, 255, 255, 0.4);"></li>`;

        // 3. Inyección de funciones según el cargo del trabajador
        if (rutasPorCargo[cargoUsuario] && rutasPorCargo[cargoUsuario].length > 0) {
            rutasPorCargo[cargoUsuario].forEach(ruta => {
                enlacesHtml += `<li><a href="${ruta.url}" class="dropdown-link" style="color: #fff; padding: 10px; display: block; text-decoration: none;">${ruta.texto}</a></li>`;
            });
        }

        // 4. Segunda raya (Separador oscuro antes del botón de salida)
        enlacesHtml += `<li><hr style="margin: 5px 0; border-color: #333;"></li>`;
        
        // 5. Última Opción Fija: Cerrar Sesión
        enlacesHtml += `<li><a href="javascript:void(0)" class="dropdown-link" id="btn-logout" style="color: #fc0800; font-weight: bold; padding: 10px; display: block; text-decoration: none;">Cerrar Sesión</a></li>`;

        // Acoplamos todo el menú construido al contenedor de la interfaz
        dropdownMenu.innerHTML = enlacesHtml;

        // Comportamiento del click para abrir/cerrar
        trigger.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropdownMenu.classList.toggle("mostrar-menu");
        };

    } else {
        if (menuDesplegable) menuDesplegable.classList.add('guest-mode');
        
        if (navText) {
            navText.textContent = "Iniciar Sesión";
            navText.style.display = "none"; 
        }

        trigger.setAttribute("href", "login.php");
        trigger.onclick = null;
        
        dropdownMenu.classList.remove("mostrar-menu");
        dropdownMenu.innerHTML = "";
    }
}

document.addEventListener("click", () => {
    const dropdownMenu = document.getElementById("user-dropdown-options");
    if (dropdownMenu) {
        dropdownMenu.classList.remove("mostrar-menu");
    }
});

// ========================================================
// 🚨 5. DETECTOR DE ERRORES DE ACCESO (ALERTA TOAST)
// ========================================================
function detectarErrorAcceso() {
    const urlParams = new URLSearchParams(window.location.search);
    const errorMsg = urlParams.get('error');
    
    // Captura si el error es "aceso_denegado" o "acceso_denegado"
    if (urlParams.has('error') && (errorMsg === 'aceso_denegado' || errorMsg === 'acceso_denegado')) {
        
        const alerta = document.createElement('div');
        alerta.className = 'toast-alerta';
        alerta.innerHTML = `
            <div class="toast-contenido">
                <span class="toast-icono">⚠️</span>
                <div class="toast-texto">
                    <h5>Acceso Restringido</h5>
                    <p>No tienes los permisos necesarios para ingresar a esta sección.</p>
                </div>
                <button class="toast-cerrar">&times;</button>
            </div>
        `;
        
        document.body.appendChild(alerta);
        
        setTimeout(() => {
            alerta.classList.add('mostrar-toast');
        }, 150);
        
        // Limpieza de URL inmediata para mantener la estética impecable
        window.history.replaceState({}, document.title, window.location.pathname);
        
        const temporizador = setTimeout(() => {
            removerAlerta(alerta);
        }, 6000);
        
        alerta.querySelector('.toast-cerrar').addEventListener('click', () => {
            clearTimeout(temporizador);
            removerAlerta(alerta);
        });
    }
}

function removerAlerta(elemento) {
    elemento.classList.remove('mostrar-toast');
    setTimeout(() => {
        elemento.remove();
    }, 400);
}

// 🔄 FORZAR RECARGA AL NAVEGAR ATRÁS
window.addEventListener('pageshow', function(event) {
    // Si el navegador cargó la página desde la caché (persisted)...
    if (event.persisted) {
        // Recargamos la página para que el JS se ejecute de nuevo
        // y detecte que la sesión fue cerrada.
        window.location.reload();
    }
});

// ========================================================
// CIERRE DE SESIÓN SINCRONIZADO (CLIENTE Y SERVIDOR)
// ========================================================
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'btn-logout') {
        e.preventDefault();
        
        if (confirm("¿Está seguro de que desea cerrar sesión?")) {
            // 1. Llamamos al backend en segundo plano para destruir la sesión de PHP
            fetch('logout.php', { method: 'POST' })
                .then(() => {
                    // 2. Limpiamos las variables locales del navegador
                    localStorage.removeItem("trabajadorAutenticado");
                    localStorage.removeItem("usuarioSesion");
                    localStorage.removeItem("usuarioData");
                    
                    // 3. Redirección forzada al inicio sin bucles
                    window.location.replace("index.php");
                })
                .catch(() => {
                    // Fallback de seguridad si falla la red
                    localStorage.clear();
                    window.location.replace("index.php");
                });
        }
    }
});