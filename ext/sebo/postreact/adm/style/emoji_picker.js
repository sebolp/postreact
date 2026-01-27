import { Picker } from 'https://cdn.jsdelivr.net/npm/emoji-mart@5.6.0/+esm';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () =>
{
	initEmojiPicker(Picker);
});

function initEmojiPicker(PickerClass)
{
	const pickerContainer = document.getElementById('emoji-picker-container');
	let currentInput = null;

	/*
	 * Configuration for the picker instance.
	 */
	const pickerOptions =
	{
		onEmojiSelect: (selection) =>
		{
			if (currentInput)
			{
				// Insert the selected emoji (native character) into the input
				currentInput.value = selection.native;

				// Optional: Trigger a change event if phpBB needs to detect changes
				currentInput.dispatchEvent(new Event('change'));

				// Hide the picker after selection
				hidePicker();
			}
		},
		locale: 'it', // Optional: Set language to Italian
		previewPosition: 'none' // Optional: Hide the big preview bar at bottom to save space
	};

	// Create the picker instance once
	const picker = new PickerClass(pickerOptions);
	pickerContainer.appendChild(picker);

	// Target all inputs with your specific class
	const inputs = document.querySelectorAll('.pr-input-emoji');

	inputs.forEach(input =>
	{
		input.addEventListener('focus', (e) =>
		{
			currentInput = e.target;
			showPicker(currentInput);
		});

		input.addEventListener('click', (e) =>
		{
			// Also trigger on click in case it's already focused
			currentInput = e.target;
			showPicker(currentInput);
		});
	});

	/*
	 * Function to position and show the picker (FIXED positioning)
	 */
	function showPicker(targetInput)
	{
		// 1. Get visual coordinates relative to the browser window (viewport)
		const rect = targetInput.getBoundingClientRect();
		const pickerRect = pickerContainer.getBoundingClientRect();
		const windowWidth = window.innerWidth;
		const windowHeight = window.innerHeight;

		// 2. Vertical Position
		// Check if there is space below, otherwise show above
		let topPos = rect.bottom + 2; // Default: 2px below input

		// If it goes off the bottom of the screen, flip it above the input
		if (topPos + pickerRect.height > windowHeight)
		{
			topPos = rect.top - pickerRect.height - 2;
		}

		pickerContainer.style.top = topPos + 'px';

		// 3. Horizontal Position
		// Default: Align left with input
		let leftPos = rect.left;

		// Check if it goes off the right edge of the screen
		if (leftPos + pickerRect.width > windowWidth)
		{
			// Align to the right edge of the input instead
			leftPos = rect.right - pickerRect.width;
		}

		// Extra safety: never go off-screen left
		if (leftPos < 0) leftPos = 10;

		pickerContainer.style.left = leftPos + 'px';

		// 4. Show it
		pickerContainer.style.display = 'block';
	}

	/*
	 * Function to hide the picker
	 */
	function hidePicker()
	{
		pickerContainer.style.display = 'none';
	}

	/*
	 * Close picker if clicking outside of it or the input
	 */
	document.addEventListener('click', (e) =>
	{
		const isInput = e.target.classList.contains('pr-input-emoji');
		const isPicker = pickerContainer.contains(e.target);

		if (!isInput && !isPicker)
		{
			hidePicker();
		}
	});
}