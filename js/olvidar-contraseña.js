document.addEventListener("DOMContentLoaded", () => {
    if (localStorage.getItem("trabajadorAutenticado") === "true") {
        window.location.href = "index.php";
        return;
    }

    const recuperarForm = document.getElementById("recuperar-form");
    const errorBox = document.getElementById("recuperar-error");
    const errorText = document.getElementById("recuperar-error-text");

    if (recuperarForm) {
        recuperarForm.addEventListener("submit", (e) => {
            e.preventDefault(); // 🛑 Detiene la recarga automática de la página inmediatamente

            const cedula = document.getElementById("rec-cedula").value.trim();
            const correo = document.getElementById("rec-correo").value.trim();
            const nuevaPassword = document.getElementById("rec-password").value;
            const confirmarPassword = document.getElementById("rec-password-confirm").value;

            // Ocultamos cualquier error previo
            errorBox.classList.add("hidden");

            // ==========================================
            // 🛡️ CAPA DE VALIDACIONES CORPORATIVAS
            // ==========================================

            // 1. Validar que la cédula sean solo números y máximo 8 dígitos
            if (!/^\d+$/.test(cedula) || cedula.length > 8) {
                mostrarError("La Cédula debe contener solo números y un máximo de 8 dígitos.");
                return;
            }

            // 2. Validar que la contraseña no exceda los 10 dígitos
            if (nuevaPassword.length > 10) {
                mostrarError("La contraseña no puede superar los 10 caracteres.");
                return;
            }

            // 3. Validar que ambas contraseñas coincidan perfectamente
            if (nuevaPassword !== confirmarPassword) {
                mostrarError("Las contraseñas ingresadas no coinciden. Intente de nuevo.");
                return;
            }

            // ==========================================
            // 💾 PROCESAMIENTO EN "BASE DE DATOS"
            // ==========================================
            let usuariosBBDD = JSON.parse(localStorage.getItem('usuariosBBDD')) || [];

            // Buscamos al usuario por Cédula Y Correo
            const usuarioIndice = usuariosBBDD.findIndex(usuario => 
                usuario.cedula === cedula && usuario.correo.toLowerCase() === correo.toLowerCase()
            );

            if (usuarioIndice !== -1) {
                // Modificamos la contraseña en la base de datos simulada
                usuariosBBDD[usuarioIndice].password = nuevaPassword;

                // Impactamos los cambios de vuelta en el LocalStorage
                localStorage.setItem('usuariosBBDD', JSON.stringify(usuariosBBDD));

                // 🔔 AVISO DE CONFIRMACIÓN REQUERIDO
                alert(`¡Contraseña modificada con éxito, ${usuariosBBDD[usuarioIndice].nombre}! El sistema ha registrado el cambio. Será redirigido al inicio de sesión.`);
                
                // Redirección automática inmediata tras aceptar el alert
                window.location.href = "login.php";
            } else {
                mostrarError("Los datos no coinciden con ningún trabajador registrado en el sistema.");
            }
        });
    }

    function mostrarError(mensaje) {
        errorText.textContent = mensaje;
        errorBox.classList.remove("hidden");
    }
});