// ========== QUIÉNES SOMOS ==========

const botonesQS = document.querySelectorAll('.qs-btn');
const panelesQS = document.querySelectorAll('.qs-panel');

botonesQS.forEach(btn => {
    btn.addEventListener('click', () => {
        // Quitar activo de todos
        botonesQS.forEach(b => b.classList.remove('activo'));
        panelesQS.forEach(p => p.classList.remove('activo'));

        // Activar el seleccionado
        btn.classList.add('activo');
        const target = btn.getAttribute('data-target');
        document.getElementById(target).classList.add('activo');
    });
});