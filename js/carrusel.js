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

// ========== CONTROL DEL CARRUSEL SUPERIOR ==========
document.addEventListener("DOMContentLoaded", () => {
    const heroSlides = document.querySelectorAll('.hero-slide');
    const heroPuntos = document.querySelectorAll('.hero-punto');
    const heroBtnIzq = document.getElementById('hero-btn-izq');
    const heroBtnDer = document.getElementById('hero-btn-der');
    
    let heroActual = 0;
    let heroIntervalo;

    if (heroSlides.length === 0) return;

     function cambiarHeroSlide(siguienteIndex, direccion = 'derecha') {
        const slideAnterior = heroSlides[heroActual];
        heroPuntos[heroActual].classList.remove('active');

        // Calcular nuevo índice circular
        heroActual = (siguienteIndex + heroSlides.length) % heroSlides.length;

        // 1. APAGAMOS LA TRANSICIÓN (Teletransportación instantánea)
        heroSlides[heroActual].style.transition = 'none';

        // Preparar nuevo slide fuera de pantalla según la dirección
        if (direccion === 'derecha') {
            heroSlides[heroActual].style.transform = 'translateX(100%)';
            slideAnterior.classList.add('saliendo-izquierda');

        } else {
            heroSlides[heroActual].style.transform = 'translateX(-100%)';
            slideAnterior.classList.add('saliendo-derecha');
        }

        heroSlides[heroActual].style.opacity = '1';
        heroSlides[heroActual].classList.add('active');


        // Forzar reflow para que la transición CSS se ejecute correctamente
        void heroSlides[heroActual].offsetWidth;

        // Deslizar el nuevo slide al centro
        heroSlides[heroActual].style.transition = 'transform 0.7s cubic-bezier(0.77, 0, 0.175, 1), opacity 0.7s ease';
        heroSlides[heroActual].style.transform = 'translateX(0)';

        // Limpiar clases del slide anterior después de que termine la animación (700ms)
        setTimeout(() => {
            slideAnterior.classList.remove('active');
            slideAnterior.classList.remove('saliendo-izquierda');
            slideAnterior.classList.remove('saliendo-derecha');
            slideAnterior.style.opacity = '0';
            slideAnterior.style.transition = 'none';
            slideAnterior.style.transform = 'none';
        }, 700);


        heroPuntos[heroActual].classList.add('active');

    } 

    // Funciones de navegación con detección inteligente de bucle (Loop)
    function proximoHero() { 
        // Si estamos en el último slide y vamos al siguiente (al primero), cambiamos la dirección a 'izquierda'
        if (heroActual === heroSlides.length - 1) {
            cambiarHeroSlide(0, 'izquierda'); 
        } else {
            cambiarHeroSlide(heroActual + 1, 'derecha'); 
        }
    }

    function anteriorHero() { 
        // Si estamos en el primer slide y vamos hacia atrás (al último), cambiamos la dirección a 'derecha'
        if (heroActual === 0) {
            cambiarHeroSlide(heroSlides.length - 1, 'derecha'); 
        } else {
            cambiarHeroSlide(heroActual - 1, 'izquierda'); 
        }
    }

    // Reiniciar el temporizador automático al interactuar manualmente
    function resetearHeroTimer() {
        clearInterval(heroIntervalo);
        heroIntervalo = setInterval(proximoHero, 5000);
    }

    // Eventos de flechas
    heroBtnDer.addEventListener('click', () => { proximoHero(); resetearHeroTimer(); });
    heroBtnIzq.addEventListener('click', () => { anteriorHero(); resetearHeroTimer(); });

    // Eventos de puntos (calcula la dirección comparando los índices)
    heroPuntos.forEach((punto, index) => {
        punto.addEventListener('click', () => {
            const direccion = index > heroActual ? 'derecha' : 'izquierda';
            cambiarHeroSlide(index, direccion);
            resetearHeroTimer();
        });
    });

    // Iniciar temporizador automático inicial
    heroIntervalo = setInterval(proximoHero, 5000);
});