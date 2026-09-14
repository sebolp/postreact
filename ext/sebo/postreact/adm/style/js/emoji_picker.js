const { Picker } = EmojiMart;

document.addEventListener('DOMContentLoaded', () =>
{
	initEmojiPicker(Picker).catch((err) =>
	{
		console.error('PostReaction: emoji picker init failed', err);
	});
});

async function initEmojiPicker(PickerClass)
{
	const pickerContainer = document.getElementById('emoji-picker-container');
	let currentInput = null;

	/*
	 * Get language from phpBB Twig data attribute
	 */
	let langCode = pickerContainer.getAttribute('data-lang');

	/*
	 * Fallback to browser/OS language if phpBB language is missing
	 */
	if (!langCode)
	{
		langCode = navigator.language || navigator.userLanguage || 'en';
	}

	/*
	 * Extract the first 2 characters (e.g., 'en-gb' becomes 'en', 'it' remains 'it')
	 */
	const shortLang = langCode.substring(0, 2);

	/*
	 * Load the bundled emoji dataset (pinned version, shipped with the
	 * extension) instead of letting emoji-mart fetch @emoji-mart/data@latest
	 * from jsdelivr at runtime.
	 */
	const dataUrl = pickerContainer.getAttribute('data-emoji-data-url');
	const emojiData = await fetch(dataUrl).then((response) => response.json());

	/*
	 * Try to load the bundled translation for the current language.
	 * If it isn't among the ones we ship (404), silently fall back to
	 * English, which is built into the picker bundle already — no
	 * further request is made in that case.
	 */
	let i18n = null;
	if (shortLang !== 'en')
	{
		const i18nBaseUrl = pickerContainer.getAttribute('data-emoji-i18n-base-url');
		try
		{
			const response = await fetch(i18nBaseUrl + shortLang + '.json');
			if (response.ok)
			{
				i18n = await response.json();
			}
		}
		catch (err)
		{
			// Bundled translation missing or unreadable: fall back to English
			i18n = null;
		}
	}

	/*
	 * Configuration for the picker instance.
	 */
	const pickerOptions =
	{
		data: emojiData,
		locale: i18n ? shortLang : 'en',
		onEmojiSelect: (selection) =>
		{
			if (currentInput)
			{
				// Insert the selected emoji (native character) into the input
				currentInput.value = selection.native;

				// Trigger a change event if phpBB needs to detect changes
				currentInput.dispatchEvent(new Event('change'));

				// Hide the picker after selection
				hidePicker();
			}
		},
		previewPosition: 'none'
	};

	if (i18n)
	{
		pickerOptions.i18n = i18n;
	}

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