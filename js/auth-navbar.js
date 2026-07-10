document.addEventListener("DOMContentLoaded", () => {
    // Usamos los IDs REALES que existen en tu index.html
    const userNavTrigger = document.getElementById("user-nav-trigger");
    const userNavText = document.getElementById("user-nav-text");
    const userDropdownOptions = document.getElementById("user-dropdown-options");

    // 1. Verificamos si hay alguien en sesión
    const estaAutenticado = localStorage.getItem("trabajadorAutenticado");
    const usuarioSesion = JSON.parse(localStorage.getItem("usuarioSesion"));

    if (estaAutenticado === "true" && usuarioSesion) {
        
        // Evitamos que al hacer clic en el avatar redirija a login.html si ya está logueado
        if (userNavTrigger) {
            userNavTrigger.setAttribute("href", "#");
        }

        // Extraer primer nombre y mostrarlo en el Navbar
        const primerNombre = usuarioSesion.nombre.split(" ")[0];
        if (userNavText) {
            userNavText.textContent = primerNombre;
            userNavText.style.display = "inline"; // Le quitamos el 'display: none' que trae de fábrica
        }

        // 2. Mapeo ÚNICO y unificado de rutas por cargo
        const rutasPorCargo = {
            "preventista": [
                { texto: " Mis Rutas", url: "preventista.php" },
                { texto: " Ver Clientes", url: "clientes.php" },
                { texto: " <i class='fa-solid fa-file-invoice-dollar'></i> Gestión de Pedidos", url: "pedidos.php" },
            ],
            "administrador": [
                { texto: " Panel Admin", url: "admin.php" },
                { texto: " Empleados", url: "empleados.php" },
                { texto: " Panel de Control", url: "dashboard.php" },
                { texto: " <i class='fa-solid fa-clipboard-list'></i> Auditoría de Pedidos", url: "historial_pedidos.php" },
                { texto: " Reportes Globales", url: "reportes_globales.php" }
            ],
            "ventas": [
                { texto: " Módulo de Ventas", url: "ventas.php" },
                { texto: " <i class='fa-solid fa-file-invoice-dollar'></i> Gestión de Pedidos", url: "pedidos.php" },
                { texto: " Caja y Cierre", url: "caja.php" },
                { texto: " Inventario", url: "inventario.php" }
            ]
        };

        const cargo = usuarioSesion.cargo.trim().toLowerCase();
        const rutas = rutasPorCargo[cargo] || [];

        // 3. Limpieza quirúrgica e Inyección de enlaces en el Dropdown
        if (userDropdownOptions) {
            userDropdownOptions.innerHTML = ""; // Borramos cualquier residuo o duplicado viejo

            // Renderizar las rutas del rol correspondiente
            rutas.forEach(ruta => {
                userDropdownOptions.innerHTML += `
                    <li><a href="${ruta.url}" class="dropdown-link">${ruta.texto}</a></li>
                `;
            });

            // Agregamos una línea divisoria y el botón de Cerrar Sesión dinámicamente al final
            userDropdownOptions.innerHTML += `
                <li><hr style="border: 0; border-top: 1px solid #eee; margin: 8px 0;"></li>
                <li><a href="javascript:void(0)" id="btn-logout" class="dropdown-link" style="color: #ff4d4d; font-weight: bold;"> Cerrar Sesión</a></li>
            `;
        }

        // ====================================================================
        // ⚡ GESTOR DE EVENTOS INTERNOS (INTERRUPTORES CLIC)
        // ====================================================================

        // Abrir y cerrar menú desplegable
        if (userNavTrigger && userDropdownOptions) {
            userNavTrigger.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                userDropdownOptions.classList.toggle("show");
            });

            // Cerrar si hace clic fuera del menú
            document.addEventListener("click", (e) => {
                if (!userNavTrigger.contains(e.target) && !userDropdownOptions.contains(e.target)) {
                    userDropdownOptions.classList.remove("show");
                }
            });
        }
    }
});