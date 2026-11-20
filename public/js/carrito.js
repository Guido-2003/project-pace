/* PROJECT PACE — Carrito de demostración */
(() => {
    'use strict';
    const $ = id => document.getElementById(id);
    const container = $('cart-items');
    const checkoutButton = $('checkout-button');
    const notice = $('cart-notice');
    const repairButton = $('cart-repair');

    function showNotice(text = '') {
        notice.textContent = text;
        notice.hidden = !text;
    }

    function renderCart() {
        const state = PaceCart.read();
        const result = PaceCart.inspect(state);
        const { items, quantity, subtotal } = result;
        checkoutButton.disabled = Boolean(result.error);
        $('cart-count').textContent = `${quantity} ${quantity === 1 ? 'ITEM' : 'ITEMS'}`;
        $('summary-quantity').textContent = quantity;
        $('cart-subtotal').textContent = result.hasUnavailable || state.issues || !items.length ? '—' : formatPrice(subtotal / 100);
        $('cart-total').textContent = 'Por confirmar';
        repairButton.hidden = !state.issues;
        showNotice(state.issues ? 'El carrito contiene datos inválidos. REPAIR CART eliminará solo los registros inválidos o duplicados; conservará los válidos.' : items.length && result.error ? result.error : '');

        if (!items.length) {
            container.innerHTML = `<div class="empty-cart"><h3>YOUR CART IS EMPTY.</h3>
                <p>Explore the latest Project Pace releases.</p>
                <a href="/#latest-drop" class="empty-cart-button">EXPLORE THE DROP</a></div>`;
            return;
        }
        container.innerHTML = items.map(({ product, size, quantity }) => {
            const safeSize = escapeHtml(size);
            const status = product.sizes[size];
            return `<article class="cart-item">
                <a class="cart-item-image" href="/product?id=${encodeURIComponent(product.id)}">
                    <img src="/${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}" decoding="async"></a>
                <div class="cart-item-info">
                    <p class="cart-item-drop">${escapeHtml(product.drop)}</p>
                    <h3>${escapeHtml(product.name)}</h3>
                    <p class="cart-item-size">SIZE: ${safeSize}</p>
                    <p class="cart-item-status">${status === true ? 'AVAILABLE' : status === false ? 'SOLD OUT' : 'POR CONFIRMAR'}</p>
                    ${canPurchaseSize(product, size) ? `<p class="cart-item-price">${formatPrice(product.price)}</p>` : ''}
                    <div class="cart-item-actions">
                        <div class="quantity-control">
                            <button type="button" data-action="decrease" data-id="${product.id}" data-size="${safeSize}" aria-label="Disminuir cantidad">−</button>
                            <span>${quantity}</span>
                            <button type="button" data-action="increase" data-id="${product.id}" data-size="${safeSize}" aria-label="Aumentar cantidad" ${canPurchaseSize(product, size) ? '' : 'disabled'}>+</button>
                        </div>
                        <button type="button" class="remove-item" data-action="remove" data-id="${product.id}" data-size="${safeSize}">REMOVE</button>
                    </div>
                </div>
            </article>`;
        }).join('');
    }

    container.addEventListener('click', event => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const { id, size, action } = button.dataset;
        const error = PaceCart.change(Number(id), size, action);
        if (error) { showNotice(error); return; }
        // Mantener el foco en el control equivalente tras redibujar el carrito.
        const next = [...container.querySelectorAll('button[data-action]')]
            .find(b => b.dataset.id === id && b.dataset.size === size && b.dataset.action === action);
        (next || container.querySelector('button, a'))?.focus({ preventScroll: true });
    });
    repairButton.addEventListener('click', () => {
        if (!PaceCart.repair()) showNotice('No se pudo guardar el carrito en este navegador.');
    });
    checkoutButton.addEventListener('click', () => {
        if (!PaceCart.inspect().error) window.location.href = '/checkout';
        else renderCart();
    });
    PaceCart.subscribe(renderCart);
    renderCart();
})();
