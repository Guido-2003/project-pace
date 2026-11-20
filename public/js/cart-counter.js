
/* ========================================
   PROJECT PACE — CART COUNTER
======================================== */

(() => {


    const cartLinks = document.querySelectorAll(
        "[data-cart-link]"
    );

    // Crear el icono del carrito
    cartLinks.forEach(link => {
        link.innerHTML = `
            <svg class="cart-icon"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.5"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 aria-hidden="true">

                <path d="M4 7h16l1 14H3L4 7Z"/>
                <path d="M8 9V6a4 4 0 0 1 8 0v3"/>

            </svg>

            <span class="cart-counter" hidden>0</span>
        `;
    });

    // Obtener la cantidad de prendas seleccionadas
    function updateCartCounter() {

        const { quantity } = PaceCart.inspect();

        cartLinks.forEach(link => {

            const counter = link.querySelector(".cart-counter");

            counter.textContent = quantity > 99
                ? "99+"
                : quantity;

            counter.hidden = quantity === 0;

            link.setAttribute(
                "aria-label",
                `Carrito: ${quantity} prendas`
            );

        });
    }

    PaceCart.subscribe(updateCartCounter);
    updateCartCounter();
})();
