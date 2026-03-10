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