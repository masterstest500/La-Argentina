document.addEventListener("DOMContentLoaded", () => {
    // Verificamos si el usuario ya está logueado para no dejarlo registrarse de nuevo
    if (localStorage.getItem("trabajadorAutenticado") === "true") {
        window.location.href = "index.html";
        return;
    }

    const registroForm = document.getElementById("registro-form");
    const errorBox = document.getElementById("registro-error");
    const errorText = document.getElementById("registro-error-text");

    if (registroForm) {
        registroForm.addEventListener("submit", (e) => {
            e.preventDefault(); // Evitamos que la página recargue

            // 1. Capturamos todos los valores de los inputs
            const nombre = document.getElementById("reg-nombre").value.trim();
            const cedula = document.getElementById("reg-cedula").value.trim();
            const telefono = document.getElementById("reg-telefono").value.trim();
            const correo = document.getElementById("reg-correo").value.trim();
            const password = document.getElementById("reg-password").value;
            const cargo = document.getElementById("reg-cargo").value;

            // Ocultamos la caja de errores antes de evaluar
            errorBox.classList.add("hidden");

            // 2. Validación de seguridad básica
            if (!/^\d+$/.test(cedula)) {
                mostrarError("La Cédula de Identidad debe contener solo números.");
                return;
            }

            // ====================================================================
            // 💾 CONEXIÓN CON LA "BASE DE DATOS" (LocalStorage)
            // ====================================================================
            
            // Traemos la lista de usuarios guardados. Si está vacía, creamos un arreglo nuevo [].
            let usuariosBBDD = JSON.parse(localStorage.getItem('usuariosBBDD')) || [];

            // Buscamos si ya existe alguien con esa cédula
            const usuarioExiste = usuariosBBDD.find(usuario => usuario.cedula === cedula);
            
            if (usuarioExiste) {
                mostrarError("Error: Esta Cédula ya se encuentra registrada en el sistema.");
                return;
            }

            // 3. Empaquetamos la información del nuevo trabajador
            const nuevoTrabajador = {
                nombre: nombre,
                cedula: cedula,
                telefono: telefono,
                correo: correo,
                password: password, // Nota: En un sistema real con servidor, esto iría encriptado
                cargo: cargo
            };

            // 4. Lo metemos en la lista y guardamos la tabla actualizada en la memoria
            usuariosBBDD.push(nuevoTrabajador);
            localStorage.setItem('usuariosBBDD', JSON.stringify(usuariosBBDD));

            // ¡Éxito!
            alert(`¡Registro exitoso! Bienvenido al equipo, ${nombre}. Ahora inicie sesión.`);
            
            // Redirigimos al usuario al login para que entre con su cuenta recién creada
            window.location.href = "login.html";
        });
    }

    // Función auxiliar para mostrar errores en pantalla
    function mostrarError(mensaje) {
        errorText.textContent = mensaje;
        errorBox.classList.remove("hidden");
    }
});