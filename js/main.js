// ========== PANTALLA DE BIENVENIDA (SPLASH SCREEN) ==========
document.addEventListener("DOMContentLoaded", () => {
    const splashScreen = document.getElementById('splash-screen');

    if (splashScreen) {
        // Verificamos si el usuario ya vio el splash en esta sesión de navegación
        const yaVisitado = sessionStorage.getItem('splashMostrado');

        if (yaVisitado) {
            // Si ya lo vio (ej. recargó la página o cambió de pestaña), lo quitamos de golpe sin estorbar
            splashScreen.style.display = 'none';
        } else {
            // Si es su primera vez hoy, esperamos a que carguen las imágenes y el mapa
            window.addEventListener('load', () => {
                // Agregamos un pequeño retraso de 1 segundo para que el logo alcance a hacer su "latido"
                setTimeout(() => {
                    // Agregamos la clase que hace la transición de desvanecimiento
                    splashScreen.classList.add('oculto');
                    
                    // Guardamos el registro en la memoria temporal del navegador
                    sessionStorage.setItem('splashMostrado', 'true');
                    
                    // Retiramos el contenedor del código después de la animación para que no bloquee clics
                    setTimeout(() => {
                        splashScreen.style.display = 'none';
                    }, 800); // 800ms coincide con el tiempo que le pusimos al CSS
                }, 1000); 
            });
        }
    }
});

// ========== SCROLL REVEAL PRODUCTOS ==========

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

// ========== NAVBAR HAMBURGUESA ==========
const navToggle = document.getElementById('nav-toggle');
const navMenu = document.querySelector('.nav-menu');

if (navToggle && navMenu) {
    navToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        navMenu.classList.toggle('activo');
        navToggle.classList.toggle('activo');
    });

    // Manejar dropdown en móvil
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = toggle.closest('.dropdown');
            // Cerrar otros dropdowns abiertos
            document.querySelectorAll('.dropdown').forEach(d => {
                if (d !== dropdown) d.classList.remove('activo');
            });
            dropdown.classList.toggle('activo');
        });
    });

    // Cerrar menú solo al hacer clic en links que NO son dropdown-toggle
    document.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('activo');
            navToggle.classList.remove('activo');
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('activo'));
        });
    });

    // Cerrar dropdown links sin cerrar menú
    document.querySelectorAll('.dropdown-link').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('activo');
            navToggle.classList.remove('activo');
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('activo'));
        });
    });

    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('activo');
            navToggle.classList.remove('activo');
            document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('activo'));
        }
    });
}

// ========== GESTOR DE SESIÓN PASIVO ==========
document.addEventListener("DOMContentLoaded", () => {
    const botonLogin = document.querySelector('.navbar-login');
    const estaAutenticado = localStorage.getItem("trabajadorAutenticado") === "true";

    if (botonLogin) {
        if (estaAutenticado) {
            // 1. Extraemos los datos guardados del usuario
            const usuarioSesion = JSON.parse(localStorage.getItem("usuarioSesion"));
            
            if (usuarioSesion && usuarioSesion.nombre) {
                // Cortamos el string para obtener solo el Primer Nombre (Ej: "Eliezer Chirinos" -> "Eliezer")
                const primerNombre = usuarioSesion.nombre.trim().split(" ")[0];
                
                // 2. Creamos dinámicamente la etiqueta span para el nombre
                const nombreSpan = document.createElement("span");
                nombreSpan.className = "user-name-navbar";
                nombreSpan.textContent = primerNombre;
                
                // Lo metemos dentro del contenedor del botón (se colocará automáticamente al lado de la imagen)
                botonLogin.appendChild(nombreSpan);
                
                // 3. Mejoramos el tooltip mostrando su Cargo corporativo y la acción
                // Convertimos la primera letra del cargo a Mayúscula para que se vea pro (Ej: "preventista" -> "Preventista")
                const cargoFormateado = usuarioSesion.cargo.charAt(0).toUpperCase() + usuarioSesion.cargo.slice(1);
                botonLogin.setAttribute('data-red', `Cerrar Sesión`);
            } else {
                // Fallback de seguridad por si no encuentra el objeto con el nombre
                botonLogin.setAttribute('data-red', 'Cerrar Sesión');
            }

            // 4. Modificamos el clic: En vez de ir a login.html, gestiona el cierre de sesión
            botonLogin.addEventListener('click', (e) => {
                e.preventDefault(); // Evita que la página navegue al formulario
                
                const confirmar = confirm("¿Está seguro de que desea cerrar sesión?");
                if (confirmar) {
                    // Limpiamos TODO lo relacionado a la sesión de este trabajador
                    localStorage.removeItem("trabajadorAutenticado");
                    localStorage.removeItem("usuarioSesion"); 

                    alert("Sesión cerrada. Ahora navegas como invitado.");
                    window.location.reload(); // Recarga el inicio limpio
                }
            });
        } else {
            // Si no está autenticado, nos aseguramos de que mantenga su comportamiento normal
            botonLogin.setAttribute('data-red', 'Inicia Sesión');
        }
    }
});