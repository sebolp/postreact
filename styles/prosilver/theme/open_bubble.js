document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.react-toggle').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const popup = document.getElementById(targetId);

            // close other pupus
            document.querySelectorAll('.popup_pr').forEach(p => {
                if (p !== popup) p.style.display = 'none';
            });

            // Toggle
            popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
        });
    });

    // Close out
    document.addEventListener('click', function (e) {
        const isToggle = e.target.closest('.react-toggle');
        const isPopup = e.target.closest('.popup_pr');
        if (!isToggle && !isPopup) {
            document.querySelectorAll('.popup_pr').forEach(p => p.style.display = 'none');
        }
    });
});