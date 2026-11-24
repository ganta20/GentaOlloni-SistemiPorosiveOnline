
const loginForm = document.getElementById("login-form");
const loginButton = document.getElementById("login-button");
const loginMessage = document.getElementById("login-message");

loginForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    loginMessage.textContent = "";

    if (!email || !password) {
        loginMessage.className = "alert alert-warning mt-3";
        loginMessage.textContent = "Plotësoni të gjitha fushat.";
        return;
    }

    loginButton.disabled = true;
    loginButton.textContent = "Duke u kyçur...";

    try {
        const response = await apiRequest("auth/login", {
            method: "POST",
            body: JSON.stringify({
                email: email,
                password: password
            })
        });

        loginMessage.className = "alert alert-success mt-3";
        loginMessage.textContent =
            "Mirë se vini, " + response.data.full_name + "!";

        localStorage.setItem("user", JSON.stringify(response.data));

        setTimeout(() => {
            window.location.href = "index.html";
        }, 1200);

    } catch (error) {
        loginMessage.className = "alert alert-danger mt-3";
        loginMessage.textContent =
            error.message || "Kyçja dështoi. Provoni përsëri.";

    } finally {
        loginButton.disabled = false;
        loginButton.textContent = "Kyçu";
    }
});
