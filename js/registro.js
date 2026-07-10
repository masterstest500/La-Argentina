document.addEventListener("DOMContentLoaded", () => {
    // Verificamos si el usuario ya está logueado para no dejarlo registrarse de nuevo
    if (localStorage.getItem("trabajadorAutenticado") === "true") {
        window.location.href = "index.php";
        return;
    }

    const registroForm = document.getElementById("registro-form");
    const errorBox = document.getElementById("registro-error");
    const errorText = document.getElementById("registro-error-text");

    if (registroForm) {
        registroForm.addEventListener("submit", (e) => {
            e.preventDefault(); // Evitamos que la página recargue de golpe

            // 1. Capturamos todos los valores de los inputs corporativos
            const nombre = document.getElementById("reg-nombre").value.trim();
            const cedula = document.getElementById("reg-cedula").value.trim();
            const telefono = document.getElementById("reg-telefono").value.trim();
            const correo = document.getElementById("reg-correo").value.trim();
            const password = document.getElementById("reg-password").value;
            const cargo = document.getElementById("reg-cargo").value;

            // Ocultamos la caja de errores antes de evaluar
            if (errorBox) errorBox.classList.add("hidden");

            // 2. Tu validación de seguridad básica
            if (!/^\d+$/.test(cedula)) {
                mostrarError("La Cédula de Identidad debe contener solo números.");
                return;
            }

            // ====================================================================
            // 🚀 CONEXIÓN REAL CON EL SERVIDOR (FETCH -> PHP -> MYSQL)
            // ====================================================================
            
            // Empaquetamos los datos para enviárselos al archivo PHP
            const datosFormulario = new FormData();
            datosFormulario.append("nombre", nombre);
            datosFormulario.append("cedula", cedula);
            datosFormulario.append("telefono", telefono);
            datosFormulario.append("correo", correo);
            datosFormulario.append("password", password);
            datosFormulario.append("cargo", cargo);

            // Hacemos el envío por detrás de escena hacia registro.php
            fetch("registro.php", {
                method: "POST",
                body: datosFormulario
            })
            .then(respuesta => respuesta.json()) // Esperamos la respuesta en formato JSON
            .then(data => {
                if (data.status === "success") {
                    // ¡Éxito real en la Base de Datos!
                    alert(`¡Registro exitoso! Bienvenido al equipo, ${nombre}. Ahora inicie sesión.`);
                    window.location.href = "login.php";
                } else {
                    // Si el servidor PHP rebota un error (como cédula duplicada), lo mostramos en tu caja estética
                    mostrarError(data.message);
                }
            })
            .catch(error => {
                console.error("Error en la petición:", error);
                mostrarError("Ocurrió un error de comunicación con el servidor local XAMPP.");
            });
        });
    }

    // Tu función original para mostrar errores en pantalla
    function mostrarError(mensaje) {
        if (errorText && errorBox) {
            errorText.textContent = mensaje;
            errorBox.classList.remove("hidden");
        } else {
            alert(mensaje);
        }
    }
});