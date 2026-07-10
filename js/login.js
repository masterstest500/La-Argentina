document.addEventListener("DOMContentLoaded", () => {
    // 🛡️ GUARDIÁN INVERSO: Si el trabajador YA está autenticado, lo mandamos directo al index
    if (localStorage.getItem("trabajadorAutenticado") === "true") {
        window.location.href = "index.php";
        return; 
    }
    
    const loginForm = document.getElementById("login-form");
    const errorBox = document.getElementById("error-message");
    const errorText = document.getElementById("error-text");

    if (loginForm) {
        loginForm.addEventListener("submit", (e) => {
            e.preventDefault(); // Evitamos recarga de página

            const cedulaIngresada = document.getElementById("cedula").value.trim();
            const passwordIngresada = document.getElementById("password").value;

            // Ocultar el error box antes de cada intento
            if (errorBox) errorBox.classList.add("hidden");

            // 1. Validación de formato de cédula (Solo números)
            if (!/^\d+$/.test(cedulaIngresada)) {
                mostrarError("La Cédula de Identidad debe contener solo números.");
                return;
            }

            // ====================================================================
            // 🚀 PETICIÓN REAL AL SERVIDOR DE BASE DE DATOS (FETCH -> LOGIN.PHP)
            // ====================================================================
            
            const datosLogin = new FormData();
            datosLogin.append("cedula", cedulaIngresada);
            datosLogin.append("password", passwordIngresada);

            fetch("login.php", {
                method: "POST",
                body: datosLogin
            })
            .then(respuesta => respuesta.json())
            .then(data => {
                // 👁️ LÍNEA DE ESPIONAJE: Ver exactamente qué responde el servidor
                console.log("Respuesta real del servidor PHP:", data);
                if (data.status === "success") {
                    // Guardar estado de autenticación general en el navegador para los guardianes visuales
                    localStorage.setItem("trabajadorAutenticado", "true"); 
                    
                    // Guardamos los datos que devolvió MySQL en la sesión actual
                    localStorage.setItem("usuarioSesion", JSON.stringify({
                        nombre: data.nombre,
                        cargo: data.cargo
                    }));
                    
                    // ¡ÉXITO! El sistema saluda al trabajador por su nombre real
                    alert(`¡Autenticación exitosa! Bienvenido al sistema, ${data.nombre}.`);
                    
                    window.location.href = "index.php"; 
                } else {
                    // Si el PHP dice que no coincide la clave o no existe la cédula
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                console.error("Error en el login:", error);
                mostrarError("Ocurrió un error de comunicación con el servidor local XAMPP.");
            });
        });
    }

    function mostrarError(mensaje) {
        if (errorText && errorBox) {
            errorText.textContent = mensaje;
            errorBox.classList.remove("hidden");
        } else {
            alert(mensaje);
        }
    }
});