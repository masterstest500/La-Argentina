// ========== DATA DE PRODUCTOS ==========

const productos = {
    'chocolate': {
        nombre: 'Chocolate',
        color: '#2c1506',
        descripcion: 'Un helado cremoso de intenso sabor a chocolate, elaborado con los mejores ingredientes para los amantes del cacao. Porque hay días en que solo el chocolate puede salvarte.',
        imagenes: ['img/helados/chocolate3.png', 'img/helados/chocolate4.png']
    },
    'cookies-and-cream': {
        nombre: 'Cookies & Cream',
        color: '#1a1a1a',
        descripcion: 'El encuentro perfecto entre el helado cremoso de vainilla y los crujientes trozos de galleta de chocolate. Simplemente irresistible.',
        imagenes: ['img/helados/ckies-and-cream.png', 'img/helados/ckies-and-cream2.png']
    },
    'fantasia': {
        nombre: 'Fantasía',
        color: '#4a0a6b',
        descripcion: 'La combinación perfecta de merengue, cola y mantecado. Un helado único y especial que despierta la fantasía en cada bocado.',
        imagenes: ['img/helados/fantasy.png', 'img/helados/fantasy2.png']
    },
    'fantoche': {
        nombre: 'Fantoche',
        color: '#8a6e00',
        descripcion: 'Vainilla cremosa bañada en sirope de chocolate, lluvia de maní y chocolate. Para los que creen que un solo sabor nunca es suficiente.',
        imagenes: ['img/helados/fantoche2.png', 'img/helados/fantoche3.png']
    },
    'fantoche-fresa': {
        nombre: 'Fantoche Fresa',
        color: '#6b1a3a',
        descripcion: 'Todo el sabor del Fantoche que ya conoces, ahora con un toque irresistible de fresa. Dos favoritos unidos en uno.',
        imagenes: ['img/helados/fantofresa.png', 'img/helados/fantofresa2.png']
    },
    'fantoche-chocolate': {
        nombre: 'Fantoche Chocolate',
        color: '#1a0a00',
        descripcion: 'La versión más chocolatosa del Fantoche. Vainilla, maní y una explosión de chocolate en cada cucharada.',
        imagenes: ['img/helados/fantoche-chocolate.png', 'img/helados/fantoche-chocolate3.png']
    },
    'fresa': {
        nombre: 'Fresa',
        color: '#7a1040',
        descripcion: 'Cremoso, rosado y con ese sabor a fresa que enamora desde el primer bocado. Fresco, natural y delicioso en cada cucharada.',
        imagenes: ['img/helados/fresa.png', 'img/helados/fresa2.png']
    },
    'mantecado': {
        nombre: 'Mantecado',
        color: '#1a5a8a',
        descripcion: 'El clásico venezolano de siempre, suave y cremoso con ese inconfundible sabor a vainilla que nunca pasa de moda.',
        imagenes: ['img/helados/manteca3.png', 'img/helados/manteca4.png']
    },
    'pistacho': {
        nombre: 'Pistacho',
        color: '#2d4a1e',
        descripcion: 'Cremoso y sofisticado, con el inconfundible sabor del pistacho en cada cucharada. Para los paladares más exigentes.',
        imagenes: ['img/helados/pistacho.png', 'img/helados/pistacho2.png']
    },
    'ron-con-pasas': {
        nombre: 'Ron Pasas',
        color: '#4a3000',
        descripcion: 'Una combinación exótica y sofisticada de helado cremoso con ron y pasas. Para los que buscan una experiencia diferente y atrevida.',
        imagenes: ['img/helados/ron-pasas.png', 'img/helados/ron-pasas-2.png']
    },
    'tramontana': {
        nombre: 'Tramontana',
        color: '#3d2800',
        descripcion: 'Vainilla con sirope de dulce de leche y trozos de galletas de chocolate. Una combinación irresistible que te transporta al cielo.',
        imagenes: ['img/helados/tramontana2.png', 'img/helados/tramontana3.png']
    },
    'trisabor': {
        nombre: 'Trisabor',
        color: '#5a1a3a',
        descripcion: 'Fresa, mantecado y chocolate unidos en un solo pote. Tres sabores, un momento perfecto para compartir con quienes más quieres.',
        imagenes: ['img/helados/trisabor2.png', 'img/helados/trisabor3.png']
    }
};

// ========== CARGAR DETALLE ==========

const params = new URLSearchParams(window.location.search);
const id = params.get('id');
const producto = productos[id];

if (producto) {

    // Fondo con color del sabor
    document.getElementById('detalle-hero').style.backgroundColor = producto.color;

    // Nombre
    document.getElementById('detalle-nombre').textContent = producto.nombre;

    // Descripción
    document.getElementById('detalle-descripcion').textContent = producto.descripcion;

    // Imagen principal
    const imgPrincipal = document.getElementById('imagen-principal');
    imgPrincipal.src = producto.imagenes[0];
    imgPrincipal.alt = producto.nombre;

    // Miniaturas
    const contenedorMiniaturas = document.getElementById('miniaturas');
    producto.imagenes.forEach((src, index) => {
        const min = document.createElement('div');
        min.classList.add('miniatura');
        if (index === 0) min.classList.add('activa');
        min.innerHTML = `<img src="${src}" alt="${producto.nombre}">`;
        min.addEventListener('click', () => {
            imgPrincipal.style.opacity = '0';
            setTimeout(() => {
                imgPrincipal.src = src;
                imgPrincipal.style.opacity = '1';
            }, 200);
            document.querySelectorAll('.miniatura').forEach(m => m.classList.remove('activa'));
            min.classList.add('activa');
        });
        contenedorMiniaturas.appendChild(min);
    });

    // ========== PODRÍA INTERESARTE ==========
    const relacionadosGrid = document.getElementById('relacionados-grid');
    const otrosIds = Object.keys(productos).filter(k => k !== id);
    const aleatorios = otrosIds.sort(() => Math.random() - 0.5).slice(0, 4);

    aleatorios.forEach(relId => {
        const rel = productos[relId];
        const card = document.createElement('a');
        card.href = `detalle-helado.html?id=${relId}`;
        card.classList.add('relacionado-card');
        card.style.backgroundColor = rel.color;
        card.innerHTML = `
            <div class="relacionado-img">
                <img src="${rel.imagenes[0]}" alt="${rel.nombre}">
            </div>
            <div class="relacionado-info">
                <p class="relacionado-nombre">${rel.nombre}</p>
                <span class="relacionado-btn">Ver más →</span>
            </div>
        `;
        relacionadosGrid.appendChild(card);
    });

} else {
    window.location.href = 'productos.html';
}

// Transición suave imagen principal
document.getElementById('imagen-principal').style.transition = 'opacity 0.2s ease';