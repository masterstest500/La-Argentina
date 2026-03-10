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
        color: '#25adb3',
        descripcion: 'La combinación perfecta de merengue, cola y mantecado. Un helado único y especial que despierta la fantasía en cada bocado.',
        imagenes: ['img/helados/fantasy.png', 'img/helados/fantasy2.png']
    },
    'fantoche': {
        nombre: 'Fantoche',
        color: '#9A7548',
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
        color: '#b1306a',
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
        color: '#55bb21',
        descripcion: 'Cremoso y sofisticado, con el inconfundible sabor del pistacho en cada cucharada. Para los paladares más exigentes.',
        imagenes: ['img/helados/pistacho.png', 'img/helados/pistacho2.png']
    },
    'ron-con-pasas': {
        nombre: 'Ron Pasas',
        color: '#D29514',
        descripcion: 'Una combinación exótica y sofisticada de helado cremoso con ron y pasas. Para los que buscan una experiencia diferente y atrevida.',
        imagenes: ['img/helados/ron-pasas.png', 'img/helados/ron-pasas-2.png']
    },
    'tramontana': {
        nombre: 'Tramontana',
        color: '#FF8C00',
        descripcion: 'Vainilla con sirope de dulce de leche y trozos de galletas de chocolate. Una combinación irresistible que te transporta al cielo.',
        imagenes: ['img/helados/tramontana2.png', 'img/helados/tramontana3.png']
    },
    'trisabor': {
        nombre: 'Trisabor',
        color: '#936B12',
        descripcion: 'Fresa, mantecado y chocolate unidos en un solo pote. Tres sabores, un momento perfecto para compartir con quienes más quieres.',
        imagenes: ['img/helados/trisabor2.png', 'img/helados/trisabor3.png']
    },

    'sunny-cream': {
    nombre: 'Sunny Cream',
    color: '#e98f08',
    descripcion: 'Cremoso, suave y radiante como un día de sol. Un helado de vainilla con un toque dulce que alegra cada momento.',
    imagenes: ['img/helados/sunny-cream.png']
},
'fruty-top': {
    nombre: 'Fruty Top',
    color: '#99C267',
    descripcion: 'Una explosión de frutas tropicales en cada mordida. Fresco, colorido y lleno de sabor natural que conquista desde el primer bocado.',
    imagenes: ['img/helados/fruty-top.png']
},
'sandwich': {
    nombre: 'Sandwich',
    color: '#3B2314',
    descripcion: 'Dos galletas crujientes abrazando un corazón cremoso de helado. El clásico irresistible que nunca pasa de moda.',
    imagenes: ['img/helados/sandwich.png']
},
'nevadito': {
    nombre: 'Nevadito',
    color: '#5B8DB8',
    descripcion: 'Suave como la nieve, dulce como un sueño. Un helado recubierto que derrite el corazón con su textura única.',
    imagenes: ['img/helados/nevadito.png']
},
'pinta-lengua': {
    nombre: 'Pinta Lengua',
    color: '#0073df',
    descripcion: 'El favorito de los más pequeños. Colorido, divertido y con ese sabor intenso que deja huella... y color en la lengua.',
    imagenes: ['img/helados/pinta-lengua.png']
},
'maxi-cream': {
    nombre: 'Maxi Cream',
    color: '#B8860B',
    descripcion: 'Para los que quieren más. Una porción generosa de cremosidad pura con el sabor que tanto te gusta.',
    imagenes: ['img/helados/maxi-cream.png']
},
'maximus': {
    nombre: 'Maximus',
    color: '#1A3A5C',
    descripcion: 'Grande en tamaño, inmenso en sabor. El helado para quienes no se conforman con poco.',
    imagenes: ['img/helados/maximus.png']
},
'trompo-loco': {
    nombre: 'Trompo Loco',
    color: '#d45dd8',
    descripcion: 'Gira de sabor en sabor en cada mordida. Una combinación loca y deliciosa que te sorprenderá siempre.',
    imagenes: ['img/helados/trompo-loco.png']
},
'choco-mini': {
    nombre: 'Choco Mini',
    color: '#bb7a4f',
    descripcion: 'Pequeño pero poderoso. Intenso chocolate en formato mini para disfrutar sin culpa en cualquier momento.',
    imagenes: ['img/helados/choco-mini2.png']
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