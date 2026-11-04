
const API_URL = "http://localhost/porosi/backend/api";

async function apiRequest(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_URL}/${endpoint}`, {
            ...options,
            headers: {
                "Content-Type": "application/json",
                ...options.headers
            }
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || "Ndodhi një gabim gjatë kërkesës.");
        }

        return result;

    } catch (error) {
        console.error("API Error:", error);
        throw error;
    }
}
