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

    const rutasPorCargo = {
        "preventista": [
            { texto: " Mis Rutas", url: "preventista.php" },
            { texto: " Ver Clientes", url: "clientes.php" }
        ],
        "administrador": [
            { texto: " Panel Admin", url: "admin.php" },
            { texto: " Empleados", url: "empleados.php" },
            { texto: " Panel de Control", url: "dashboard.php" },
            { texto: " Gestión de Usuarios", url: "usuarios.php" },
            { texto: " Reportes Globales", url: "reportes.php" }
        ],
        "ventas": [
            { texto: " Módulo de Ventas", url: "ventas.php" },
            { texto: " Caja y Cierre", url: "caja.php" },
            { texto: " Inventario", url: "inventario.php" }
        ]
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

        if (rutasPorCargo[cargoUsuario]) {
            rutasPorCargo[cargoUsuario].forEach(ruta => {
                enlacesHtml += `<li><a href="${ruta.url}" class="dropdown-link" style="color: #fff; padding: 10px; display: block; text-decoration: none;">${ruta.texto}</a></li>`;
            });
        } else {
            enlacesHtml += `<li><a href="#" class="dropdown-link" style="color: #fff; padding: 10px; display: block; text-decoration: none;">Panel General</a></li>`;
        }

        dropdownMenu.innerHTML = `
            ${enlacesHtml}
            <li><hr style="margin: 5px 0; border-color: #333;"></li>
            <li><a href="javascript:void(0)" class="dropdown-link" id="btn-logout" style="color: #fc0800; font-weight: bold; padding: 10px; display: block; text-decoration: none;">Cerrar Sesión</a></li>
        `;

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

// Pon esto al final de tu archivo main.js
document.addEventListener('click', function(e) {
    // Verificamos si el elemento clickeado es el botón de logout
    if (e.target && e.target.id === 'btn-logout') {
        e.preventDefault(); // Detiene el salto del '#'
        
        if (confirm("¿Está seguro de que desea cerrar sesión?")) {
            // Limpiamos datos
            localStorage.removeItem("trabajadorAutenticado");
            localStorage.removeItem("usuarioSesion");
            localStorage.removeItem("usuarioData");
            
            // Redirección forzada
            window.location.replace("index.php"); 
        }
    }
});