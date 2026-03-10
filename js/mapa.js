// ========== MAPA LEAFLET ==========

// Distribuidores al Mayor (marcadores rojos)
const distribuidoresMayor = [
    {
        nombre: "Distribuidora Central La Argentina",
        direccion: "Av. 5 de Julio, Centro Comercial Las Américas, Local 12, Maracaibo",
        lat: 10.6354,
        lng: -71.6189
    },
    {
        nombre: "Distribuidora Norte Zulia",
        direccion: "Calle 72 con Av. Bella Vista, Edificio Don Rafael, PB, Maracaibo",
        lat: 10.6612,
        lng: -71.6058
    },
    {
        nombre: "Distribuidora Industrial Los Robles",
        direccion: "Zona Industrial Los Robles, Galpón 28, Calle 61, Maracaibo",
        lat: 10.6201,
        lng: -71.5987
    },
    {
        nombre: "Distribuidora Sur del Lago",
        direccion: "Av. Delicias Norte, C.C. Delicias Mall, Local 8, Maracaibo",
        lat: 10.6089,
        lng: -71.6312
    }
];

// Distribuidores al Detal (marcadores azules)
const distribuidoresDetal = [
    {
        nombre: "Heladería El Sabor",
        direccion: "Av. Universidad, frente al IUTM, Local 3, Maracaibo",
        lat: 10.6478,
        lng: -71.6234
    },
    {
        nombre: "Minimarket La Esquina",
        direccion: "Calle 79 con Av. 3Y, Sector Los Olivos, Maracaibo",
        lat: 10.6723,
        lng: -71.6145
    },
    {
        nombre: "Bodegón El Criollo",
        direccion: "Av. El Milagro, Sector Ziruma, Local 5, Maracaibo",
        lat: 10.6834,
        lng: -71.6089
    },
    {
        nombre: "Supermercado Familiar",
        direccion: "Calle 68 con Av. 15, Sector Santa Rosa, Maracaibo",
        lat: 10.6156,
        lng: -71.6423
    },
    {
        nombre: "Licorería y Bodegón El Paraíso",
        direccion: "Av. Goajira, Sector El Paraíso, Local 2, Maracaibo",
        lat: 10.6945,
        lng: -71.6234
    }
];

// Inicializar mapa centrado en Maracaibo
const mapa = L.map('mapa-leaflet').setView([10.6412, -71.6124], 12);

// Capa de mapa OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(mapa);

// Icono rojo para al mayor
const iconoMayor = L.divIcon({
    className: '',
    html: `<div style="
        width: 28px;
        height: 28px;
        background: #cc0000;
        border: 3px solid #ffffff;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        box-shadow: 0 2px 8px rgba(204,0,0,0.5);
    "></div>`,
    iconSize: [28, 28],
    iconAnchor: [14, 28],
    popupAnchor: [0, -30]
});

// Icono azul para al detal
const iconoDetal = L.divIcon({
    className: '',
    html: `<div style="
        width: 28px;
        height: 28px;
        background: #0066cc;
        border: 3px solid #ffffff;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        box-shadow: 0 2px 8px rgba(0,102,204,0.5);
    "></div>`,
    iconSize: [28, 28],
    iconAnchor: [14, 28],
    popupAnchor: [0, -30]
});

// Arrays para guardar marcadores
const marcadoresMayor = [];
const marcadoresDetal = [];

// Agregar marcadores al mayor
distribuidoresMayor.forEach(d => {
    const marcador = L.marker([d.lat, d.lng], { icon: iconoMayor })
        .bindPopup(`
            <div class="popup-nombre">${d.nombre}</div>
            <span class="popup-tipo mayor">Al Mayor</span>
            <div class="popup-direccion">📍 ${d.direccion}</div>
        `);
    marcadoresMayor.push(marcador);
    marcador.addTo(mapa);
});

// Agregar marcadores al detal
distribuidoresDetal.forEach(d => {
    const marcador = L.marker([d.lat, d.lng], { icon: iconoDetal })
        .bindPopup(`
            <div class="popup-nombre">${d.nombre}</div>
            <span class="popup-tipo detal">Al Detal</span>
            <div class="popup-direccion">📍 ${d.direccion}</div>
        `);
    marcadoresDetal.push(marcador);
    marcador.addTo(mapa);
});

// ── Filtros ──
const filtrosBtns = document.querySelectorAll('.filtro-btn');

filtrosBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        filtrosBtns.forEach(b => b.classList.remove('activo'));
        btn.classList.add('activo');

        const filtro = btn.getAttribute('data-filtro');

        if (filtro === 'todos') {
            marcadoresMayor.forEach(m => mapa.addLayer(m));
            marcadoresDetal.forEach(m => mapa.addLayer(m));
        } else if (filtro === 'mayor') {
            marcadoresMayor.forEach(m => mapa.addLayer(m));
            marcadoresDetal.forEach(m => mapa.removeLayer(m));
        } else if (filtro === 'detal') {
            marcadoresMayor.forEach(m => mapa.removeLayer(m));
            marcadoresDetal.forEach(m => mapa.addLayer(m));
        }
    });
});