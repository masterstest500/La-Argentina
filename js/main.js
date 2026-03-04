// ========== SCROLL REVEAL PRODUCTOS ==========

const tarjetas = document.querySelectorAll('.producto-card');

if (tarjetas.length > 0) {
    const observador = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observador.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15
    });

    tarjetas.forEach(tarjeta => {
        observador.observe(tarjeta);
    });
}