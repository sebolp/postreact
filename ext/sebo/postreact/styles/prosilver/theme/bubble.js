/* open */
document.addEventListener("DOMContentLoaded", function () {
	document.querySelectorAll(".toggle-react-summary").forEach(button => {
		button.addEventListener("click", function (e) {
			e.preventDefault();
			let postId = this.getAttribute("data-post-id");
			document.getElementById("darken").style.display = "block";
			document.getElementById("phpbb_confirm_" + postId).style.display = "block";
		});
	});
});

/* close */
document.addEventListener("DOMContentLoaded", function () {
	document.querySelectorAll(".alert_close").forEach(link => {
		link.addEventListener("click", function (e) {
			e.preventDefault(); // Evita il comportamento predefinito del link
			let postId = this.getAttribute("data-post-id");
			document.getElementById("darken").style.display = "none";
			document.getElementById("phpbb_confirm_" + postId).style.display = "none";
		});
	});
});
