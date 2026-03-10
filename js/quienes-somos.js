// ========== QUIÉNES SOMOS ==========

const botonesQS = document.querySelectorAll('.qs-btn');
const panelesQS = document.querySelectorAll('.qs-panel');

const selectorMV = document.getElementById('selector-mv');

botonesQS.forEach(btn => {
    btn.addEventListener('click', () => {
        botonesQS.forEach(b => b.classList.remove('activo'));
        panelesQS.forEach(p => p.classList.remove('activo'));
        btn.classList.add('activo');
        const target = btn.getAttribute('data-target');
        document.getElementById(target).classList.add('activo');
        if (target === 'mision' || target === 'vision') {
            selectorMV.style.visibility = 'visible';
            selectorMV.style.marginBottom = '60px';
            selectorMV.style.height = 'auto';
            selectorMV.style.overflow = 'visible';
        } else {
            selectorMV.style.visibility = 'hidden';
            selectorMV.style.marginBottom = '0';
            selectorMV.style.height = '0';
            selectorMV.style.overflow = 'hidden';
        }
    });
});

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

// Iniciar autoplay solo cuando Historia este activo
const btnHistoria = document.querySelector('[data-target="historia"]');
if (btnHistoria) {
    btnHistoria.addEventListener('click', () => {
        clearInterval(historiaIntervalo);
        iniciarHistoria();
    });
}

// Leer seccion desde URL
const params = new URLSearchParams(window.location.search);
const seccion = params.get('seccion') || 'mision';
const panelTarget = document.getElementById(seccion);
if (panelTarget) {
    panelesQS.forEach(p => p.classList.remove('activo'));
    botonesQS.forEach(b => b.classList.remove('activo'));
    panelTarget.classList.add('activo');
    const btnActivo = document.querySelector(`.qs-btn[data-target="${seccion}"]`);
    if (btnActivo) btnActivo.classList.add('activo');
    if (seccion === 'historia' || seccion === 'valores') {
        selectorMV.style.visibility = 'hidden';
        selectorMV.style.marginBottom = '0';
        selectorMV.style.height = '0';
        selectorMV.style.overflow = 'hidden';
    } else {
        selectorMV.style.visibility = 'visible';
        selectorMV.style.marginBottom = '60px';
        selectorMV.style.height = 'auto';
        selectorMV.style.overflow = 'visible';
    }
    if (seccion === 'historia') iniciarHistoria();
}