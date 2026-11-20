/* PROJECT PACE — catálogo público recibido de Laravel / MySQL.
   Los precios históricos y las cantidades de stock nunca se incluyen. */
const catalog = window.projectPaceCatalog;
if (!catalog || !Array.isArray(catalog.products) || !Array.isArray(catalog.drops)) {
    throw new Error('No se recibió el catálogo público de Laravel.');
}
const products = catalog.products;
const publicDrops = catalog.drops;

function availabilityStatus(product) {
    const values = Object.values(product.sizes);
    if (values.some(value => value === true)) return "AVAILABLE";
    if (values.some(value => value === null)) return "POR CONFIRMAR";
    return "SOLD OUT";
}

// Una prenda sin stock nunca expone su precio ni permite comprar.
function hasPublicPrice(product) {
    return availabilityStatus(product) === 'AVAILABLE' &&
        Number.isFinite(product.price) && product.price >= 0;
}

function canPurchaseSize(product, size) {
    return hasPublicPrice(product) && product.sizes[size] === true;
}

// Utilidades compartidas por HOME, SHOP, ARCHIVE y las páginas de compra.
function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[char]);
}

function formatPrice(price) {
    return Number.isFinite(price) ? `Bs ${price.toFixed(2)}` : 'Precio por confirmar';
}

function createProductCard(product, inShop = false) {
    const status = availabilityStatus(product);
    const badge = status === 'SOLD OUT' ? status : product.isLatest ? 'LATEST DROP' : product.drop.toUpperCase();
    return `
        <article class="product-card">
            <a href="/product?id=${encodeURIComponent(product.id)}" class="product-card-link">
                <div class="product-image">
                    <img src="/${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}" loading="lazy" decoding="async">
                    <span class="product-badge">${escapeHtml(badge)}</span>
                </div>
                <div class="product-info">
                    <p class="product-drop">${escapeHtml(product.drop)}</p>
                    <h3>${escapeHtml(product.name)}</h3>
                    ${hasPublicPrice(product) ? `<p class="product-price">${formatPrice(product.price)}</p>` : ''}
                    <p class="product-status">${status}</p>
                </div>
            </a>
        </article>`;
}

function renderProducts(containerId, items) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = items.length
        ? items.map(product => createProductCard(product)).join('')
        : '<p>No hay prendas para mostrar.</p>';
}

renderProducts('latest-products', products.filter(product => product.isLatest));
renderProducts('available-products', products.filter(product => !product.isLatest).slice(0, 4));
