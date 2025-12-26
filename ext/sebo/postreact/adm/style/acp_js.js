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

(function($) {
    'use strict';
    $(function() {
        $('#config_display_position').on('change', function() {
            var textContainer = $('#dynamic_position_text');
            var alertContainer = $('#save_alert');
            
            // Mostra alert
            alertContainer.fadeIn();

            // Leggi traduzioni
            var textChecked = $(this).attr('data-lang-checked');     // DESTRA
            var textUnchecked = $(this).attr('data-lang-unchecked'); // SINISTRA

            // Applica testo
            if ($(this).is(':checked')) {
                // Selezionato (Valore 0) -> DESTRA
                textContainer.text(textChecked);
            } else {
                // Non Selezionato (Valore 1) -> SINISTRA
                textContainer.text(textUnchecked);
            }
        });
    });
})(jQuery);
