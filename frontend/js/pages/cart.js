
function getCart() {
    return JSON.parse(localStorage.getItem("cart")) || [];
}

function saveCart(cart) {
    localStorage.setItem("cart", JSON.stringify(cart));
}

function addToCart(product) {
    const cart = getCart();

    const existingProduct = cart.find(
        item => Number(item.id) === Number(product.id)
    );

    if (existingProduct) {
        if (existingProduct.quantity >= Number(product.stock)) {
            alert("Nuk ka më produkte në stok!");
            return;
        }
        existingProduct.quantity++;
    } else {
        if (Number(product.stock) < 1) {
            alert("Ky produkt nuk është në stok!");
            return;
        }

        cart.push({
            id: Number(product.id),
            name: product.name,
            price: Number(product.price),
            stock: Number(product.stock),
            quantity: 1
        });
    }

    saveCart(cart);
    alert("Produkti u shtua në shportë!");
}

function displayCart() {
    const container = document.getElementById("cart-container");
    const totalElement = document.getElementById("cart-total");
    const cart = getCart();

    container.innerHTML = "";

    if (cart.length === 0) {
        container.textContent = "Shporta juaj është bosh.";
        totalElement.textContent = "0.00 €";
        return;
    }

    let total = 0;

    cart.forEach((product, index) => {
        const subtotal = product.price * product.quantity;
        total += subtotal;

        const item = document.createElement("div");
        item.className = "product-card mb-3";

        const name = document.createElement("h5");
        name.textContent = product.name;

        const price = document.createElement("p");
        price.textContent = `Çmimi: ${product.price.toFixed(2)} €`;

        const quantity = document.createElement("p");
        quantity.textContent = `Sasia: ${product.quantity}`;

        const subtotalText = document.createElement("p");
        subtotalText.textContent = `Nëntotali: ${subtotal.toFixed(2)} €`;

        const decrease = document.createElement("button");
        decrease.className = "btn btn-secondary me-2";
        decrease.textContent = "-";
        decrease.onclick = () => changeQuantity(index, -1);

        const increase = document.createElement("button");
        increase.className = "btn btn-secondary me-2";
        increase.textContent = "+";
        increase.onclick = () => changeQuantity(index, 1);

        const remove = document.createElement("button");
        remove.className = "btn btn-danger";
        remove.textContent = "Hiq";
        remove.onclick = () => removeProduct(index);

        item.append(name, price, quantity, subtotalText,
                    decrease, increase, remove);

        container.appendChild(item);
    });

    totalElement.textContent = total.toFixed(2) + " €";
}

function changeQuantity(index, amount) {
    const cart = getCart();
    const newQuantity = cart[index].quantity + amount;

    if (newQuantity < 1) return;

    if (newQuantity > cart[index].stock) {
        alert("Nuk ka mjaftueshëm produkte në stok!");
        return;
    }

    cart[index].quantity = newQuantity;
    saveCart(cart);
    displayCart();
}

function removeProduct(index) {
    const cart = getCart();
    cart.splice(index, 1);
    saveCart(cart);
    displayCart();
}

document.addEventListener("DOMContentLoaded", () => {
    displayCart();

    document.getElementById("clear-cart").addEventListener("click", () => {
        localStorage.removeItem("cart");
        displayCart();
    });
});
