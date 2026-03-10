// ========== QUIÉNES SOMOS ==========
const panelesQS = document.querySelectorAll('.qs-panel');

// ========== HISTORIA ==========
const historiaSlidesEls = document.querySelectorAll('.historia-slide');
const historiaPuntosEls = document.querySelectorAll('.historia-punto');
const historiaNext = document.getElementById('historia-next');
const historiaPrev = document.getElementById('historia-prev');
let historiaActual = 0;
let historiaIntervalo;

function irAHistoria(index, direccion = 'derecha') {
    const anterior = historiaSlidesEls[historiaActual];
    historiaPuntosEls[historiaActual].classList.remove('activo');
    historiaActual = (index + historiaSlidesEls.length) % historiaSlidesEls.length;
    if (direccion === 'derecha') {
        historiaSlidesEls[historiaActual].style.transform = 'translateX(100%)';
        anterior.classList.add('saliendo-izquierda');
    } else {
        historiaSlidesEls[historiaActual].style.transform = 'translateX(-100%)';
        anterior.classList.add('saliendo-derecha');
    }
    historiaSlidesEls[historiaActual].style.opacity = '1';
    historiaSlidesEls[historiaActual].classList.add('activo');
    historiaSlidesEls[historiaActual].offsetHeight;
    historiaSlidesEls[historiaActual].style.transform = 'translateX(0)';
    setTimeout(() => {
        anterior.classList.remove('activo');
        anterior.classList.remove('saliendo-izquierda');
        anterior.classList.remove('saliendo-derecha');
        anterior.style.opacity = '0';
        anterior.style.transform = 'translateX(100%)';
    }, 800);
    historiaPuntosEls[historiaActual].classList.add('activo');
}

function iniciarHistoria() {
    historiaIntervalo = setInterval(() => {
        irAHistoria(historiaActual + 1, 'derecha');
    }, 6000);
}

function reiniciarHistoria() {
    clearInterval(historiaIntervalo);
    iniciarHistoria();
}

if (historiaNext) {
    historiaNext.addEventListener('click', () => {
        irAHistoria(historiaActual + 1, 'derecha');
        reiniciarHistoria();
    });
}

if (historiaPrev) {
    historiaPrev.addEventListener('click', () => {
        irAHistoria(historiaActual - 1, 'izquierda');
        reiniciarHistoria();
    });
}

historiaPuntosEls.forEach(punto => {
    punto.addEventListener('click', () => {
        const index = parseInt(punto.getAttribute('data-index'));
        irAHistoria(index, index > historiaActual ? 'derecha' : 'izquierda');
        reiniciarHistoria();
    });
});

// Leer seccion desde URL
const params = new URLSearchParams(window.location.search);
const seccion = params.get('seccion') || 'mision';
const panelTarget = document.getElementById(seccion);
if (panelTarget) {
    panelesQS.forEach(p => p.classList.remove('activo'));
    panelTarget.classList.add('activo');
    if (seccion === 'historia') iniciarHistoria();
}