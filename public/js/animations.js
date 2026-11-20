/* PROJECT PACE — Animaciones progresivas, sin dependencias.
   Cargar al final de body, después de los scripts de cada página. */
(() => {
    'use strict';
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (typeof Element.prototype.animate !== 'function') return;

    const selector = '.product-grid .product-card, .archive-editorial .archive-story-main';
    const seen = new WeakSet();
    const running = new Map();
    let observer = null;
    let mutations = null;

    function cancel(element) {
        const previous = running.get(element);
        if (previous) {
            previous.cancel();
            running.delete(element);
        }
    }

    function animate(element, frames, duration) {
        if (!element || !element.isConnected || motion.matches) return;
        cancel(element);
        const effect = element.animate(frames, {
            duration,
            easing: 'cubic-bezier(.2,.7,.3,1)',
            fill: 'none'
        });
        running.set(element, effect);
        const cleanup = () => {
            if (running.get(element) === effect) running.delete(element);
        };
        effect.onfinish = cleanup;
        effect.oncancel = cleanup;
    }

    // Entrada de una sola vez. El contenido nunca queda oculto por CSS.
    function reveal(element, distance, duration) {
        animate(element, [
            { opacity: .55, translate: `0 ${distance}px` },
            { opacity: 1, translate: '0 0' }
        ], duration);
    }

    function register(element) {
        if (!observer || seen.has(element)) return;
        const rect = element.getBoundingClientRect();
        // No volver a animar elementos visibles al buscar o cambiar filtros.
        if (rect.top < window.innerHeight && rect.bottom > 0 || rect.bottom <= 0) {
            seen.add(element);
            return;
        }
        observer.observe(element);
    }

    function scan(root) {
        if (root.nodeType !== Node.ELEMENT_NODE) return;
        if (root.matches(selector)) register(root);
        root.querySelectorAll(selector).forEach(register);
    }

    function startObserving() {
        if (motion.matches || !('IntersectionObserver' in window) || observer) return;
        observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                const element = entry.target;
                if (!entry.isIntersecting || seen.has(element)) return;
                seen.add(element);
                observer?.unobserve(element);
                if (!element.isConnected || element.contains(document.activeElement)) return;
                const story = element.classList.contains('archive-story-main');
                reveal(element, story ? 20 : 12, story ? 500 : 400);
            });
        }, { threshold: 0 });
        scan(document.body);

        // SHOP reemplaza sus tarjetas al filtrar; registrar las nuevas.
        if ('MutationObserver' in window) {
            mutations = new MutationObserver(records => {
                records.forEach(record => {
                    record.removedNodes.forEach(node => {
                        if (node.nodeType !== Node.ELEMENT_NODE) return;
                        if (node.matches(selector) && !node.isConnected) {
                            observer?.unobserve(node);
                            cancel(node);
                        }
                        node.querySelectorAll(selector).forEach(element => {
                            if (!element.isConnected) {
                                observer?.unobserve(element);
                                cancel(element);
                            }
                        });
                    });
                    record.addedNodes.forEach(scan);
                });
            });
            mutations.observe(document.body, { childList: true, subtree: true });
        }
    }

    // Portada: solo el contenido, no la imagen ni el encabezado.
    if (!motion.matches) reveal(document.querySelector('.hero .hero-content'), 14, 600);
    startObserving();

    // Galería: el efecto empieza cuando la nueva fotografía está cargada.
    const galleryImage = document.getElementById('pace-gallery-main');
    if (galleryImage) {
        let lastSource = galleryImage.getAttribute('src');
        galleryImage.addEventListener('load', () => {
            const source = galleryImage.getAttribute('src');
            if (!source || source === lastSource) return;
            lastSource = source;
            animate(galleryImage, [{ opacity: .55 }, { opacity: 1 }], 220);
        });
        galleryImage.addEventListener('error', () => cancel(galleryImage));
    }

    // Si el usuario navega con teclado, no animar el elemento enfocado.
    document.addEventListener('focusin', event => {
        for (const element of running.keys()) {
            if (element.contains(event.target)) cancel(element);
        }
        const target = event.target.closest(selector);
        if (target) {
            seen.add(target);
            observer?.unobserve(target);
        }
    });

    function onMotionChange() {
        if (motion.matches) {
            for (const element of running.keys()) cancel(element);
            observer?.disconnect();
            mutations?.disconnect();
            observer = null;
            mutations = null;
        } else {
            startObserving();
        }
    }
    if (motion.addEventListener) motion.addEventListener('change', onMotionChange);
    else if (motion.addListener) motion.addListener(onMotionChange);
})();
