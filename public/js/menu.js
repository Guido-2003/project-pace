/* PROJECT PACE — Menú móvil del encabezado compartido */
(() => {
    const header = document.querySelector('.pp-header');
    const toggle = header?.querySelector('.pp-menu-toggle');
    const menu = header?.querySelector('.pp-nav-links');
    if (!header || !toggle || !menu) return; // CHECKOUT no necesita menú.

    const mobile = window.matchMedia('(max-width: 850px)');
    const isOpen = () => toggle.getAttribute('aria-expanded') === 'true';

    function setOpen(open, returnFocus = false) {
        const expanded = Boolean(open && mobile.matches);
        header.classList.toggle('pp-menu-expanded', expanded);
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.setAttribute('aria-label', expanded ? 'Cerrar menú' : 'Abrir menú');
        if (returnFocus && mobile.matches) toggle.focus();
    }

    toggle.addEventListener('click', () => setOpen(!isOpen()));
    menu.addEventListener('click', event => {
        if (event.target.closest('a')) setOpen(false, true);
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && isOpen()) setOpen(false, true);
    });
    document.addEventListener('click', event => {
        if (isOpen() && !menu.contains(event.target) && !toggle.contains(event.target)) {
            setOpen(false, menu.contains(document.activeElement));
        }
    });
    // Cerrar al salir del encabezado con el teclado.
    header.addEventListener('focusout', event => {
        if (isOpen() && event.relatedTarget && !header.contains(event.relatedTarget)) setOpen(false);
    });
    mobile.addEventListener('change', () => {
        const active = document.activeElement;
        setOpen(false, mobile.matches && menu.contains(active));
        if (!mobile.matches && active === toggle) menu.querySelector('a')?.focus();
    });
    window.addEventListener('pageshow', () => setOpen(false));

    // Activar solo después de conectar los eventos.
    header.classList.add('pp-menu-ready');
    toggle.hidden = false;
    setOpen(false);
})();
