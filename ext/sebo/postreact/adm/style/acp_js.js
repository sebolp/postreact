document.addEventListener("DOMContentLoaded", function ()
{
	let popup = document.getElementById("confirm-delete-reaction");

	// Stop execution if the popup HTML element is not present on this specific page
	if (!popup)
	{
		return;
	}

	let deleteLinks = document.querySelectorAll(".delete-reaction");
	
	// Select buttons using the new specific class instead of the data attribute
	let formButtons = document.querySelectorAll(".js-trigger-popup");
	
	let confirmBtn = document.getElementById("confirm-delete");
	let cancelBtn = document.getElementById("cancel-delete");

	// Variables to store the pending action
	let pendingDeleteUrl = "";
	let pendingForm = null;
	let pendingButton = null;

	// Handle reaction links
	deleteLinks.forEach(link =>
	{
		link.addEventListener("click", function (event)
		{
			event.preventDefault(); // Block default link behavior

			pendingDeleteUrl = this.getAttribute("data-url"); // Get deletion URL
			
			// Reset form variables to avoid conflicts
			pendingForm = null;
			pendingButton = null;

			popup.style.display = "flex"; // Show popup
		});
	});

	// Handle purge form buttons
	formButtons.forEach(button =>
	{
		button.addEventListener("click", function (event)
		{
			event.preventDefault(); // Block default form submission

			pendingButton = this;
			pendingForm = this.closest("form");
			
			// Reset URL variable to avoid conflicts
			pendingDeleteUrl = ""; 

			popup.style.display = "flex"; // Show popup
		});
	});

	// Action on confirm
	confirmBtn.addEventListener("click", function ()
	{
		if (pendingDeleteUrl)
		{
			// Execute link redirection
			window.location.href = pendingDeleteUrl; 
		}
		else if (pendingForm && pendingButton)
		{
			// Execute form submission
			let hiddenInput = document.createElement("input");
			
			hiddenInput.type = "hidden";
			hiddenInput.name = pendingButton.name;
			hiddenInput.value = pendingButton.value;
			
			// Append the clicked button data and submit
			pendingForm.appendChild(hiddenInput);
			pendingForm.submit();
		}
	});

	// Action on cancel
	cancelBtn.addEventListener("click", function ()
	{
		popup.style.display = "none"; // Close popup
	});

	// Close popup if clicking outside the box
	popup.addEventListener("click", function (event)
	{
		if (event.target === popup)
		{
			popup.style.display = "none";
		}
	});
});

(function($)
{
	'use strict';
	
	$(function()
	{
		$('#config_display_position').on('change', function()
		{
			var textContainer = $('#dynamic_position_text');
			var alertContainer = $('#save_alert');
			
			// Show alert
			alertContainer.fadeIn();

			// Read translations
			var textChecked = $(this).attr('data-lang-checked');     // RIGHT
			var textUnchecked = $(this).attr('data-lang-unchecked'); // LEFT

			// Apply text
			if ($(this).is(':checked'))
			{
				// Checked (Value 0) -> RIGHT
				textContainer.text(textChecked);
			}
			else
			{
				// Unchecked (Value 1) -> LEFT
				textContainer.text(textUnchecked);
			}
		});
	});
})(jQuery);