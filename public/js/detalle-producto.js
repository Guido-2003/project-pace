/* PROJECT PACE — Detalle de la prenda */
const params = new URLSearchParams(window.location.search);
const productId = Number(params.get("id"));
const product = products.find(p => p.id === productId);
const detailContainer = document.getElementById("product-detail");
let selectedSize = null;

function showProduct() {
    if (!product) {
        detailContainer.innerHTML = `<div class="detail-information"><h1>PRODUCT NOT FOUND</h1><a href="/">VOLVER</a></div>`;
        return;
    }
    const status = availabilityStatus(product);
    const sizesHTML = Object.entries(product.sizes).map(([size, available]) => {
        const confirmedOut = available === false;
        const pending = available === null;
        return `<button type="button" class="size-button ${confirmedOut ? 'unavailable' : ''}"
            data-size="${escapeHtml(size)}" ${canPurchaseSize(product, size) ? '' : 'disabled'}
            aria-pressed="false" aria-label="Talla ${escapeHtml(size)}: ${pending ? 'por confirmar' : confirmedOut ? 'agotada' : 'disponible'}">${escapeHtml(size)}</button>`;
    }).join("");
    const showPending = Object.values(product.sizes).some(v => v === null);
    detailContainer.innerHTML = `
        <div class="pace-gallery" role="group" aria-label="Fotografías de la prenda">
            <figure class="pace-gallery-stage">
                <img id="pace-gallery-main" alt="" decoding="async">
                <figcaption class="pace-gallery-caption" id="pace-gallery-caption" aria-live="polite">FRONT VIEW</figcaption>
            </figure>
            <div class="pace-gallery-thumbnails" role="group" aria-label="Seleccionar vista">
                <button type="button" class="pace-gallery-thumb is-active" data-view="front"
                    aria-pressed="true" aria-label="Mostrar vista frontal">
                    <img alt="" decoding="async">
                    <span>FRONT VIEW</span>
                </button>
                <button type="button" class="pace-gallery-thumb" data-view="back"
                    aria-pressed="false" aria-label="Mostrar vista posterior">
                    <img alt="" decoding="async">
                    <span>BACK VIEW</span>
                </button>
            </div>
        </div>
        <div class="detail-information">
            <p class="eyebrow">PROJECT PACE / ${escapeHtml(product.drop)}</p>
            <h1>${escapeHtml(product.name)}</h1>
            ${hasPublicPrice(product) ? `<p class="detail-price">${formatPrice(product.price)}</p>` : ''}
            <p class="detail-availability">${status}</p>
            <p class="detail-description">${escapeHtml(product.description)}</p>
            <div class="size-selection">
                <p>SELECT SIZE</p>
                <div class="size-options">${sizesHTML}</div>
                <small>${showPending ? 'Disponibilidad de tallas pendiente de confirmar.' : 'Las tallas tachadas están agotadas.'}</small>
            </div>
            <button type="button" id="add-to-cart" class="add-to-cart" disabled>
                ${status === 'SOLD OUT' ? 'SOLD OUT' : !hasPublicPrice(product) ? 'NOT AVAILABLE' : 'SELECT AN AVAILABLE SIZE'}
            </button>
            <p id="cart-message" class="cart-message" role="status" aria-live="polite"></p>
            <p class="detail-note">LIMITED RELEASE / PROJECT PACE</p>
        </div>`;
    initializeProductGallery();
    const sizeButtons = detailContainer.querySelectorAll('.size-button:not(:disabled)');
    const addButton = document.getElementById('add-to-cart');
    sizeButtons.forEach(button => button.addEventListener('click', () => {
        sizeButtons.forEach(b => {b.classList.remove('selected'); b.setAttribute('aria-pressed','false');});
        button.classList.add('selected');
        button.setAttribute('aria-pressed','true');
        selectedSize = button.dataset.size;
        addButton.disabled = false;
        addButton.textContent = 'ADD TO CART';
    }));
    addButton.addEventListener('click', addToCart);
}

// Galería: solo sustituye la fotografía, sin volver a generar el producto.
function initializeProductGallery() {
    const views = {
        front: { path: product.image, label: 'FRONT VIEW', alt: 'Vista frontal' },
        back: { path: product.backImage, label: 'BACK VIEW', alt: 'Vista posterior' }
    };
    const mainImage = detailContainer.querySelector('#pace-gallery-main');
    const caption = detailContainer.querySelector('#pace-gallery-caption');
    const thumbnails = detailContainer.querySelectorAll('.pace-gallery-thumb');

    function selectView(viewKey) {
        const view = views[viewKey];
        if (!view || !view.path) return;
        mainImage.src = `/${view.path}`;
        mainImage.alt = `${product.name} — ${view.alt}`;
        caption.textContent = view.label;
        thumbnails.forEach(button => {
            const active = button.dataset.view === viewKey;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
    }

    thumbnails.forEach(button => {
        const view = views[button.dataset.view];
        if (!view || !view.path) {
            button.hidden = true;
            return;
        }
        button.querySelector('img').src = `/${view.path}`;
        button.addEventListener('click', () => selectView(button.dataset.view));
    });
    selectView(views.front.path ? 'front' : 'back');
}

function addToCart() {
    if (!product || !selectedSize) return;
    document.getElementById('cart-message').textContent = PaceCart.add(product.id, selectedSize);
}

showProduct();
