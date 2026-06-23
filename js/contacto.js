// =========================================================================
// ✉️ LÓGICA DEL FORMULARIO DE CONTACTO (EMAILJS)
// =========================================================================

// 1. Inicializamos EmailJS con tu Llave Pública (Reemplaza con la tuya)
emailjs.init({
  publicKey: "S2z6Gg3KT0RmzX3-I",
});

document.addEventListener("DOMContentLoaded", () => {
    const contactoForm = document.getElementById("contacto-form");
    const alertaBox = document.getElementById("contacto-alerta");
    const btnEnviar = document.getElementById("contacto-btn");

    // Verificamos que el formulario exista en la página actual antes de actuar
    if (contactoForm) {
        contactoForm.addEventListener("submit", function(e) {
            // Evitamos que la página se recargue por defecto
            e.preventDefault();

            // Capturar el correo ingresado por el usuario para validarlo
            const correoUsuario = document.getElementById("contacto-correo").value.trim().toLocaleLowerCase();

            // Definimos el correo que estara restringido
            const correoRestringido = "soporteit.helar@gmail.com";

            // Si el usuario intenta usar el receptor, bloqueamos el envío y mostramos una alerta
            if (correoUsuario === correoRestringido) {
                alertaBox.textContent = "Por favor, escriba su Correo Electrónico.";
                alertaBox.className = "form-alerta error"; // Se muestra la alerta de error

                return;
            }
            // Modificamos el botón para dar feedback visual de "Cargando"
            btnEnviar.disabled = true;
            btnEnviar.textContent = "Enviando mensaje... ⏳";

            // Limpiamos cualquier alerta previa
            alertaBox.className = "form-alerta hidden";
            alertaBox.textContent = "";

            // Definimos tus IDs de servicio y plantilla (Reemplaza con los tuyos)
            const serviceID = "service_nvhprqy";
            const templateID = "template_9wm4i9g";

            // 2. Enviamos el formulario directamente a EmailJS
            // 'this' hace referencia al formulario actual (#contacto-form)
            emailjs.sendForm(serviceID, templateID, this)
                .then(() => {
                    // --- CASO DE ÉXITO ---
                    // Mostramos mensaje verde al usuario
                    alertaBox.textContent = "¡Mensaje enviado con éxito! Nos comunicaremos pronto.";
                    alertaBox.className = "form-alerta exito"; // Quitamos el hidden y añadimos diseño de éxito
                    
                    // Limpiamos los campos del formulario para que quede en blanco
                    contactoForm.reset();
                })
                .catch((error) => {
                    // --- CASO DE ERROR ---
                    console.error("Error de EmailJS:", error);
                    alertaBox.textContent = "Hubo un problema al enviar el mensaje. Inténtalo de nuevo.";
                    alertaBox.className = "form-alerta error";
                })
                .finally(() => {
                    // Al terminar (sea éxito o error), rehabilitamos el botón a su estado original
                    btnEnviar.disabled = false;
                    btnEnviar.textContent = "Enviar mensaje →";
                });
        });
    }
});