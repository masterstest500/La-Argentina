// ========== CARRUSEL DE PRODUCTOS ==========

const slides = document.querySelectorAll('.slide');
const puntos = document.querySelectorAll('.punto');
const flechaIzq = document.getElementById('flecha-izq');
const flechaDer = document.getElementById('flecha-der');
let slideActual = 0;
let intervalo;

// Inicializar colores de todos los slides
slides.forEach(slide => {
    slide.style.backgroundColor = slide.getAttribute('data-color');
});

// Cambia al slide indicado
function irASlide(index, direccion = 'derecha') {
    const slideAnterior = slides[slideActual];
    puntos[slideActual].classList.remove('activo');

    slideActual = (index + slides.length) % slides.length;

    // Preparar nuevo slide fuera de pantalla
    if (direccion === 'derecha') {
        slides[slideActual].style.transform = 'translateX(100%)';
        slideAnterior.classList.add('saliendo-izquierda');
    } else {
        slides[slideActual].style.transform = 'translateX(-100%)';
        slideAnterior.classList.add('saliendo-derecha');
    }

    slides[slideActual].style.opacity = '1';
    slides[slideActual].classList.add('activo');

    // Forzar reflow para que la transición funcione
    slides[slideActual].offsetHeight;

    // Deslizar nuevo slide al centro
    slides[slideActual].style.transform = 'translateX(0)';

    // Limpiar clases del slide anterior después de la transición
    setTimeout(() => {
        slideAnterior.classList.remove('activo');
        slideAnterior.classList.remove('saliendo-izquierda');
        slideAnterior.classList.remove('saliendo-derecha');
        slideAnterior.style.opacity = '0';
        slideAnterior.style.transform = 'translateX(100%)';
    }, 700);

    puntos[slideActual].classList.add('activo');
}

// Flechas
flechaDer.addEventListener('click', () => {
    irASlide(slideActual + 1, 'derecha');
    reiniciarIntervalo();
});

flechaIzq.addEventListener('click', () => {
    irASlide(slideActual - 1, 'izquierda');
    reiniciarIntervalo();
});

// Autoplay siempre va hacia la derecha
function iniciarIntervalo() {
    intervalo = setInterval(() => {
        irASlide(slideActual + 1, 'derecha');
    }, 5000);
}

function reiniciarIntervalo() {
    clearInterval(intervalo);
    iniciarIntervalo();
}

// Arrancar
iniciarIntervalo();