/* PROJECT PACE — CHECKOUT DE DEMOSTRACIÓN */
(() => {
    const $ = id => document.getElementById(id);
    const currency = cents => formatPrice(cents / 100);
    const form = $('checkout-form');
    const layout = $('checkout-layout');
    const review = $('checkout-review');
    const blocked = $('checkout-blocked');
    const method = $('delivery-method');
    const city = $('customer-city');
    const address = $('customer-address');

    function blockOrder(result) {
        layout.hidden = true;
        review.hidden = true;
        $('checkout-items').replaceChildren();
        $('checkout-subtotal').textContent = '—';
        $('checkout-review-data').replaceChildren();
        $('checkout-review-total').textContent = '';
        blocked.hidden = false;
        $('checkout-blocked-title').textContent = result.empty ? 'YOUR CART IS EMPTY.' : 'REVIEW YOUR CART.';
        $('checkout-blocked-text').textContent = result.error;
    }

    function renderSummary(result) {
        const list = $('checkout-items');
        list.replaceChildren();
        for (const {product, size, quantity, amount} of result.items) {
            const article = document.createElement('article');
            article.className = 'checkout-item';
            const image = document.createElement('img');
            image.src = `/${product.image}`;
            image.alt = product.name;
            const details = document.createElement('div');
            const name = document.createElement('h3');
            name.textContent = product.name;
            const meta = document.createElement('p');
            meta.textContent = `${product.drop} / SIZE ${size} / Qty ${quantity}`;
            const price = document.createElement('strong');
            price.textContent = currency(amount);
            details.append(name, meta, price);
            article.append(image, details);
            list.append(article);
        }
        $('checkout-subtotal').textContent = currency(result.subtotal);
    }

    function updateDelivery() {
        const delivery = method.value === 'delivery';
        $('delivery-address').hidden = !delivery;
        for (const field of [city, address]) {
            field.disabled = !delivery;
            field.required = delivery;
        }
    }
    method.addEventListener('change', updateDelivery);
    updateDelivery();

    // No se almacena ni se transmite información personal.
    form.addEventListener('submit', event => {
        event.preventDefault();
        const latest = PaceCart.inspect();
        if (latest.error) return blockOrder(latest);
        const name = $('customer-name');
        const phone = $('customer-phone');
        for (const field of [name, phone, city, address]) field.setCustomValidity('');
        if (!name.value.trim()) name.setCustomValidity('Ingresá un nombre.');
        if (phone.value.replace(/\D/g, '').length < 7) phone.setCustomValidity('Ingresá un teléfono válido.');
        if (method.value === 'delivery') {
            if (!city.value.trim()) city.setCustomValidity('Ingresá una ciudad.');
            if (!address.value.trim()) address.setCustomValidity('Ingresá una dirección.');
        }
        if (!form.reportValidity()) return;
        renderSummary(latest);
        const values = [ ['Nombre', name.value.trim()], ['Teléfono', phone.value.trim()], ['Correo', $('customer-email').value.trim()], ['Entrega', method.value === 'delivery' ? 'Envío a domicilio' : 'Recojo a coordinar'] ];
        if (method.value === 'delivery') values.push(['Ciudad', city.value.trim()], ['Dirección', address.value.trim()]);
        values.push(['Observaciones', $('customer-notes').value.trim()]);
        values.push(['Prendas', latest.items.map(({product, size, quantity, amount}) => `${product.name} · ${size} · ${quantity} × · ${currency(amount)}`).join('\n')]);
        const data = $('checkout-review-data');
        data.replaceChildren();
        for (const [label, value] of values) {
            if (!value) continue;
            const dt = document.createElement('dt'); dt.textContent = label;
            const dd = document.createElement('dd'); dd.textContent = value;
            data.append(dt, dd);
        }
        $('checkout-review-total').textContent = `Subtotal: ${currency(latest.subtotal)} · Total final y envío por confirmar.`;
        layout.hidden = true;
        review.hidden = false;
        $('review-heading').focus();
    });
    for (const field of [ $('customer-name'), $('customer-phone'), city, address ]) field.addEventListener('input', () => field.setCustomValidity(''));
    $('edit-checkout').addEventListener('click', () => { review.hidden = true; layout.hidden = false; $('customer-name').focus(); });
    function refreshCheckout() {
        const current = PaceCart.inspect();
        if (current.error) blockOrder(current);
        else {
            blocked.hidden = true;
            review.hidden = true;
            layout.hidden = false;
            renderSummary(current);
        }
    }
    refreshCheckout();
    PaceCart.subscribe(refreshCheckout);
})();
