document.addEventListener("DOMContentLoaded", () => {
    // 🛡️ GUARDIÁN INVERSO: Si el trabajador YA está autenticado, lo mandamos directo al index
    if (localStorage.getItem("trabajadorAutenticado") === "true") {
        window.location.href = "index.html";
        return; 
    }
    
    const loginForm = document.getElementById("login-form");
    const errorBox = document.getElementById("error-message");
    const errorText = document.getElementById("error-text");

    if (loginForm) {
        loginForm.addEventListener("submit", (e) => {
            e.preventDefault(); 

            const cedulaIngresada = document.getElementById("cedula").value.trim();
            const passwordIngresada = document.getElementById("password").value;

            // Ocultar el error box antes de cada intento
            errorBox.classList.add("hidden");

            // 1. Validación de formato de cédula (Solo números)
            if (!/^\d+$/.test(cedulaIngresada)) {
                mostrarError("La Cédula de Identidad debe contener solo números.");
                return;
            }

            // ====================================================================
            // 🔍 BÚSQUEDA EN LA "BASE DE DATOS" (LocalStorage)
            // ====================================================================
            
            // Traemos todos los usuarios registrados. Si no hay ninguno, traemos un arreglo vacío.
            const usuariosBBDD = JSON.parse(localStorage.getItem('usuariosBBDD')) || [];

            // Buscamos un usuario que tenga EXACTAMENTE la misma cédula y contraseña ingresada
            const usuarioValido = usuariosBBDD.find(usuario => 
                usuario.cedula === cedulaIngresada && usuario.password === passwordIngresada
            );

            // 2. Validación de credenciales
            if (usuarioValido) {
                // Guardar estado de autenticación general
                localStorage.setItem("trabajadorAutenticado", "true"); 
                
                // 📦 ¡NUEVO! Guardamos los datos de este usuario específico en la sesión actual
                // Solo guardamos lo necesario (nombre y cargo) por seguridad
                localStorage.setItem("usuarioSesion", JSON.stringify({
                    nombre: usuarioValido.nombre,
                    cargo: usuarioValido.cargo
                }));
                
                // ¡ÉXITO! El sistema saluda al trabajador por su nombre
                alert(`¡Autenticación exitosa! Bienvenido al sistema, ${usuarioValido.nombre}.`);
                
                window.location.href = "index.html"; 
            } else {
                // ERROR: Datos incorrectos o usuario no registrado
                mostrarError("Cédula o contraseña incorrectas. Verifique sus datos o regístrese.");
            }
        });
    }

    function mostrarError(mensaje) {
        errorText.textContent = mensaje;
        errorBox.classList.remove("hidden");
    }
});