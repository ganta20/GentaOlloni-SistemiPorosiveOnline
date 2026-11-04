
document.addEventListener("DOMContentLoaded", loadProducts);

async function loadProducts() {
    const container = document.getElementById("products-container");

    container.textContent = "Duke u ngarkuar produktet...";

    try {
        const response = await apiRequest("products");
        const products = response.data;

        container.innerHTML = "";

        if (!products || products.length === 0) {
            container.textContent = "Nuk ka produkte për momentin.";
            return;
        }

        products.forEach(product => {
            const column = document.createElement("div");
            column.className = "col-md-4 col-lg-3";

            const card = document.createElement("div");
            card.className = "product-card";

            const name = document.createElement("h5");
            name.textContent = product.name;

            const description = document.createElement("p");
            description.textContent =
                product.description || "Pa përshkrim";

            const price = document.createElement("p");
            price.className = "product-price";
            price.textContent = Number(product.price).toFixed(2) + " €";

            const stock = document.createElement("p");
            stock.textContent = "Stoku: " + product.stock;

            card.append(name, description, price, stock);
            column.appendChild(card);
            container.appendChild(column);
        });

    } catch (error) {
        container.textContent =
            "Nuk mund të ngarkohen produktet. Ju lutem provoni përsëri.";
    }
}
