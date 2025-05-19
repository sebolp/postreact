document.addEventListener("DOMContentLoaded", function () {
    let deleteLinks = document.querySelectorAll(".delete-reaction");
    let popup = document.getElementById("confirm-delete-reaction");
    let confirmBtn = document.getElementById("confirm-delete");
    let cancelBtn = document.getElementById("cancel-delete");

    let deleteUrl = ""; // Salva l'URL di eliminazione

    deleteLinks.forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault(); // Blocca il comportamento predefinito del link

            deleteUrl = this.getAttribute("data-url"); // Prende l'URL di eliminazione
            popup.style.display = "flex"; // Mostra il popup
        });
    });

    confirmBtn.addEventListener("click", function () {
        if (deleteUrl) {
            window.location.href = deleteUrl; // Esegue la cancellazione
        }
    });

    cancelBtn.addEventListener("click", function () {
        popup.style.display = "none"; // Chiude il popup
    });

    // Chiude il popup se clicchi fuori dal box
    popup.addEventListener("click", function (event) {
        if (event.target === popup) {
            popup.style.display = "none";
        }
    });
});