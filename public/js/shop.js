/* PROJECT PACE — SHOP ALL PIECES
   Reutiliza `products` y `availabilityStatus()` desde main.js.
   No modifica el carrito ni las cantidades de stock.
*/

const shopSearch = document.getElementById("shop-search");
const shopDrop = document.getElementById("shop-drop");
const shopSort = document.getElementById("shop-sort");
const shopGrid = document.getElementById("shop-products");
const shopCount = document.getElementById("shop-count");
const shopEmpty = document.getElementById("shop-empty");

function normalizeSearch(value) {
    return String(value)
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();
}


// Las opciones de drop salen del catálogo real: 08, 07, ... 01.
const dropNumbers = [...new Set(products.map(product => product.dropNumber))]
    .sort((a, b) => b - a);

for (const number of dropNumbers) {
    const option = document.createElement("option");
    option.value = String(number);
    option.textContent = `DROP ${String(number).padStart(2, "0")}`;
    shopDrop.appendChild(option);
}


function filteredShopProducts() {
    const query = normalizeSearch(shopSearch.value);
    const selectedDrop = shopDrop.value;

    const filtered = products.filter(product => {
        const matchesName = normalizeSearch(product.name).includes(query);
        const matchesDrop = selectedDrop === "all" ||
            product.dropNumber === Number(selectedDrop);
        return matchesName && matchesDrop;
    });

    switch (shopSort.value) {
        case "oldest":
            filtered.sort((a, b) => a.dropNumber - b.dropNumber || a.id - b.id);
            break;
        default:
            filtered.sort((a, b) => b.dropNumber - a.dropNumber || a.id - b.id);
    }

    return filtered;
}

function renderShop() {
    const visibleProducts = filteredShopProducts();
    shopGrid.innerHTML = visibleProducts.map(product => createProductCard(product, true)).join("");

    const amount = visibleProducts.length;
    shopCount.textContent = `${amount} ${amount === 1 ? "PIEZA" : "PIEZAS"}`;
    shopEmpty.hidden = amount !== 0;
    shopGrid.hidden = amount === 0;
}

function resetShopFilters() {
    shopSearch.value = "";
    shopDrop.value = "all";
    shopSort.value = "newest";
    renderShop();
}

shopSearch.addEventListener("input", renderShop);
shopDrop.addEventListener("change", renderShop);
shopSort.addEventListener("change", renderShop);
document.getElementById("shop-reset").addEventListener("click", resetShopFilters);
document.getElementById("shop-empty-reset").addEventListener("click", resetShopFilters);
document.getElementById("shop-filters").addEventListener("submit", event => event.preventDefault());

renderShop();
