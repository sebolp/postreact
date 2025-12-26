/* POSTREACTION(S) POPUP DISPLAY-HIDE SYS */

document.addEventListener("DOMContentLoaded", function () {
    
    const darken = document.getElementById("darken");

    /* 1. OPEN */
    document.querySelectorAll(".toggle-react-summary").forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            let postId = this.getAttribute("data-post-id");
            
            // SHOW IT
            if (darken) darken.style.display = "block";
            let popup = document.getElementById("phpbb_confirm_" + postId);
            if (popup) popup.style.display = "block";
        });
    });

    /* 2. X CLOSING */
    document.querySelectorAll(".alert_close").forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            let postId = this.getAttribute("data-post-id");
            
            // CLOSE OVERLAY AND POPUP
            if (darken) darken.style.display = "none";
            let popup = document.getElementById("phpbb_confirm_" + postId);
            if (popup) popup.style.display = "none";
        });
    });

    /* 3. OUTSIDE CLOSING */
    if (darken) {
        darken.addEventListener("click", function () {
            // HIDE OVERLAY
            this.style.display = "none";
            
            // HIDE ALL POPUPS OPENED
            document.querySelectorAll(".phpbb_confirm").forEach(popup => {
                popup.style.display = "none";
            });
        });
    }
});