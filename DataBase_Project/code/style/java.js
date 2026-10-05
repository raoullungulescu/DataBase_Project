document.addEventListener("DOMContentLoaded", () => {
    const steps = document.querySelectorAll(".step .btn");
    const content = document.getElementById("step-content");

    // Determină pasul curent pe baza URL-ului sau alt indicator
    const pageMap = {
        "shop.php": 0,
        "shoptT.php": 1,
        "comanda.php": 2
    };

    // Obține numele paginii curente din URL
    const currentPage = window.location.pathname.split("/").pop();
    let currentStep = pageMap[currentPage] || 0; // Default: primul pas

    const stepDescriptions = [
        "Acesta este pasul 1: Coșul meu.",
        "Acesta este pasul 2: Detalii comandă.",
        "Acesta este pasul 3: Sumar comandă."
    ];

    function updateStep() {
        // Actualizează stilurile pentru buline
        steps.forEach((step, index) => {
            step.classList.remove("btn-primary", "btn-secondary");
            step.classList.add(index === currentStep ? "btn-primary" : "btn-secondary");
        });

        // Actualizează descrierea curentă
        if (content) content.textContent = stepDescriptions[currentStep];
    }

    updateStep(); // Actualizează la încărcare
});
