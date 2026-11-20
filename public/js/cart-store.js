/* PROJECT PACE — Carrito local de demostración compartido.
   No reserva inventario ni sustituye la validación del servidor. */
const PaceCart = (() => {
    'use strict';
    const key = 'projectPaceCartV2';
    const signature = item => `${item.productId}:${item.size}`;
    const findProduct = id => products.find(product => product.id === id);
    const toCents = price => Math.round(price * 100);

    function read() {
        let data;
        try {
            data = JSON.parse(localStorage.getItem(key) || '[]');
        } catch {
            return { items: [], issues: true };
        }
        if (!Array.isArray(data)) return { items: [], issues: true };
        const items = [];
        const seen = new Set();
        let issues = false;
        let subtotal = 0;
        let totalQuantity = 0;
        for (const item of data) {
            const product = item && findProduct(item.productId);
            if (!product || typeof item.size !== 'string' || !Object.hasOwn(product.sizes, item.size) ||
                !Number.isSafeInteger(item.quantity) || item.quantity < 1 ||
                seen.has(signature(item))) {
                issues = true;
                continue;
            }
            const amount = canPurchaseSize(product, item.size) ? toCents(product.price) * item.quantity : 0;
            if (!Number.isSafeInteger(amount) || !Number.isSafeInteger(subtotal + amount) ||
                !Number.isSafeInteger(totalQuantity + item.quantity)) {
                issues = true;
                continue;
            }
            subtotal += amount;
            totalQuantity += item.quantity;
            seen.add(signature(item));
            items.push({ productId: product.id, size: item.size, quantity: item.quantity });
        }
        return { items, issues };
    }

    function write(items) {
        try {
            localStorage.setItem(key, JSON.stringify(items));
        } catch {
            return false;
        }
        window.dispatchEvent(new Event('cartUpdated'));
        return true;
    }

    function inspect(state = read()) {
        const items = state.items.map(item => {
            const product = findProduct(item.productId);
            return { ...item, product, amount: canPurchaseSize(product, item.size) ? toCents(product.price) * item.quantity : null };
        });
        const subtotal = items.reduce((sum, item) => sum + (item.amount ?? 0), 0);
        const quantity = items.reduce((sum, item) => sum + item.quantity, 0);
        const hasUnavailable = items.some(item => !canPurchaseSize(item.product, item.size));
        const result = { items, subtotal, quantity, hasUnavailable };
        if (state.issues) return { ...result, error: 'Hay datos inválidos en el carrito. Volvé al carrito para revisarlos y repararlos.' };
        if (!items.length) return { ...result, empty: true, error: 'Tu carrito está vacío. Agregá una prenda antes de continuar.' };
        if (hasUnavailable) {
            return { ...result, error: 'Hay prendas agotadas o no disponibles en tu carrito. Retiralas antes de continuar.' };
        }
        return result;
    }

    function add(productId, size) {
        const state = read();
        if (state.issues) return 'Revisá y repará tu carrito antes de agregar prendas.';
        const product = findProduct(productId);
        if (!product || !Object.hasOwn(product.sizes, size) || !canPurchaseSize(product, size)) return 'Talla no disponible.';
        if (state.items.some(item => item.productId === productId && item.size === size)) return 'Esta prenda y talla ya están en tu carrito.';
        const totals = inspect(state);
        if (!Number.isSafeInteger(totals.subtotal + toCents(product.price)) ||
            !Number.isSafeInteger(totals.quantity + 1)) return 'No se pudo calcular el subtotal.';
        state.items.push({ productId, size, quantity: 1 });
        return write(state.items) ? 'Prenda agregada al carrito de prueba.' : 'No se pudo guardar el carrito en este navegador.';
    }

    function change(productId, size, action) {
        const state = read();
        if (state.issues) return 'Repará el carrito antes de modificar las prendas.';
        const index = state.items.findIndex(item => item.productId === productId && item.size === size);
        if (index === -1) return 'La prenda ya no está en el carrito.';
        const item = state.items[index];
        if (action === 'remove' || action === 'decrease' && item.quantity === 1) state.items.splice(index, 1);
        else if (action === 'decrease') item.quantity--;
        else if (action === 'increase') {
            const product = findProduct(productId);
            if (!canPurchaseSize(product, size)) return 'La talla no está disponible.';
            const result = inspect(state);
            if (!Number.isSafeInteger(item.quantity + 1) || !Number.isSafeInteger(result.quantity + 1) ||
                !Number.isSafeInteger(result.subtotal + toCents(product.price))) return 'Cantidad demasiado grande.';
            item.quantity++;
        } else return 'Acción no válida.';
        return write(state.items) ? '' : 'No se pudo guardar el carrito en este navegador.';
    }

    function subscribe(callback) {
        window.addEventListener('cartUpdated', callback);
        window.addEventListener('storage', event => {
            if (event.key === key || event.key === null) callback();
        });
        window.addEventListener('pageshow', callback);
    }

    return { read, inspect, add, change, subscribe, repair: () => write(read().items) };
})();
