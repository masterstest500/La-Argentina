// ========== BOTÓN FLOTANTE WHATSAPP ==========

const numeroWS = '584246577356';
const mensajeWS = '¡Hola! Me interesa conocer más sobre los productos de Heladerías La Argentina 🍦';

const btnWhatsApp = document.getElementById('whatsapp-btn');

// Crear el botón
btnWhatsApp.innerHTML = `
    <a href="https://wa.me/${numeroWS}?text=${encodeURIComponent(mensajeWS)}" 
       target="_blank" 
       class="ws-flotante"
       title="Contáctanos por WhatsApp">
        <img src="img/logos/whatsapp.png" alt="WhatsApp">
    </a>
`;