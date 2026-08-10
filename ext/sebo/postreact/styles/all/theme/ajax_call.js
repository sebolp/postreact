function getRootPath() {
	var scripts = document.getElementsByTagName('script');
	var scriptPath = '';

	for (var i = 0; i < scripts.length; i++) {
		if (scripts[i].src.includes('ajax_call.js')) {
			scriptPath = scripts[i].src;
			break;
		}
	}

	if (!scriptPath) {
		return '/';
	}

	var parts = scriptPath.split('/');
	var rootPathParts = [];

	var extIndex = parts.indexOf('ext');
	if (extIndex !== -1) {
		rootPathParts = parts.slice(0, extIndex);
	} else {
		return '/';
	}

	return rootPathParts.join('/') + '/';
}

var root_path = getRootPath();

// Escapes a value for safe insertion into HTML text/attributes.
function escapeHtml(value) {
	if (value === null || value === undefined) {
		return '';
	}
	return String(value)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;');
}

// Validates a colour value before it is used inside a style attribute.
// Only accepts 3, 4, 6 or 8 digit hex colours (with or without a leading '#').
// Anything else is discarded, since it is not a legitimate phpBB user_colour value.
function sanitizeHexColor(value) {
	if (!value) {
		return '';
	}
	var hex = String(value).replace(/^#/, '');
	return /^[0-9A-Fa-f]{3}([0-9A-Fa-f]{3}([0-9A-Fa-f]{2})?)?$/.test(hex) ? hex : '';
}

$(document).ready(function() {

	// Update popup content
	function updatePopupContent(postId, iconId, action, userData, newCount, res) {
		var popup = $('#phpbb_confirm_' + postId);

		if (popup.length === 0) {
			return;
		}

		var iconContainer = popup.find('.post_react_explain_container[data-icon-id="' + iconId + '"]');

		if (action === 'added') {
			if (iconContainer.length) {
				// Icon already exists - update counter and user list
				var countElement = iconContainer.find('strong');
				var userList = iconContainer.find('.post_react_list');

				// Update the counter
				countElement.text('(' + newCount + ')');

				// Add the user to the list if not already present
				var currentUsers = userList.html();
				var username = userData.username;
				var userColor = sanitizeHexColor(userData.user_colour);
				var safeUsername = escapeHtml(username);

				// Check whether the username is already present in the text
				if (!currentUsers.includes('>' + safeUsername + '<')) {
					var userSpan = '<span style="color: #' + userColor + '; ' +
								  (userColor ? 'font-weight: bold;' : '') + '">' +
								  safeUsername + '</span>';

					if (currentUsers.trim()) {
						userList.append(', ' + userSpan);
					} else {
						userList.html(userSpan);
					}
				}
			} else {
				// Icon does not exist - create a new container
				var newContainer = createIconContainer(iconId, res, newCount, [userData]);
				popup.find('.alert_text').append(newContainer);
			}
		} else if (action === 'removed') {
			if (iconContainer.length) {
				var countElement = iconContainer.find('strong');
				var userList = iconContainer.find('.post_react_list');

				if (newCount <= 0) {
					// Remove the container entirely if there are no reactions left
					iconContainer.remove();
				} else {
					// Update counter and remove the user from the list
					var username = userData.username;

					// Find and remove the specific user
					var userSpans = userList.find('span');

					userSpans.each(function() {
						if ($(this).text() === username) {
							// Handle commas - remove the trailing comma if present
							var nextSibling = this.nextSibling;
							if (nextSibling && nextSibling.nodeType === 3 && nextSibling.textContent.includes(',')) {
								nextSibling.remove();
							} else {
								// If there is no trailing comma, check before
								var prevSibling = this.previousSibling;
								if (prevSibling && prevSibling.nodeType === 3 && prevSibling.textContent.includes(',')) {
									prevSibling.remove();
								}
							}

							$(this).remove();
							return false;
						}
					});

					if (countElement.length) {
						countElement.text('(' + newCount + ')');
					}
				}
			}
		}
	}

	function createIconContainer(iconId, res, count, userDetails) {
		var userListHtml = '';
		userDetails.forEach(function(user, index) {
			var userColor = sanitizeHexColor(user.user_colour);
			var safeUsername = escapeHtml(user.username);
			userListHtml += '<span style="color: #' + userColor + '; ' +
						   (userColor ? 'font-weight: bold;' : '') + '">' +
						   safeUsername + '</span>';
			if (index < userDetails.length - 1) {
				userListHtml += ', ';
			}
		});

		var safeIconId = escapeHtml(iconId);
		var safeIconUrl = escapeHtml(res.icon_url);
		var safeIconAlt = escapeHtml(res.icon_alt);
		var safeIconWidth = escapeHtml(res.icon_width);
		var safeIconHeight = escapeHtml(res.icon_height);
		var safeCount = escapeHtml(count);

		return '<div class="post_react_explain_container" data-icon-id="' + safeIconId + '">' +
			   '<div class="post_react_explain">' +
			   '<img src="' + safeIconUrl + '" title="' + safeIconAlt + '" alt="' + safeIconAlt + '" width="' + safeIconWidth + '" height="' + safeIconHeight + '"> ' +
			   '<strong>(' + safeCount + ')</strong> ' +
			   '<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>' +
			   '</div>' +
			   '<div class="post_react_list">' +
			   userListHtml +
			   '</div>' +
			   '</div>';
	}

	$('a.post-react').on('click', function(e) {
		e.preventDefault();

		var post_id  = $(this).data('post_id');
		var topic_id = $(this).data('topic_id');
		var icon_id  = $(this).data('icon_id');
		var icon_height  = $(this).data('icon_height');
		var icon_width  = $(this).data('icon_width');
		var icon_alt  = $(this).data('icon_alt');
		var reacted_language = $(this).data('reacted_language');

		$.ajax({
			/*url: root_path + 'postreact/ajax',*/
			url: postreact_ajax_url, /*to be tested*/
			method: 'POST',
			dataType: 'text',
			data: { post_id, topic_id, icon_id, icon_alt, icon_height, icon_width, reacted_language},
			success: function(response) {
				try {
					var res = JSON.parse(response);

					if(res.success) {
						// Update the popup with user data - BEFORE anything else
						if (res.user_data) {
							updatePopupContent(post_id, (res.action === 'removed' ? res.icon_id : icon_id), res.action, res.user_data, res.new_count, res);
						}

						var container = $('#post_react_display_' + post_id);
						var existingIcon = container.find('.img_post_react_display[data-icon-id="' + icon_id + '"]');
						var bubble = existingIcon.find('.bubble_post_react_display');

						// -------------------------
						// ADD_REACTION logic
						// -------------------------
						if(res.action === 'added') {

							if(existingIcon.length) {
								// Icon already exists - only update the counter
								if(bubble.length) {
									bubble.text(res.new_count || 1);
									// Update CSS classes based on the new count
									bubble.removeClass();
									if(res.new_count > 99) bubble.addClass('bubble_post_react_display_bb');
									else if(res.new_count > 9) bubble.addClass('bubble_post_react_display_b');
									else bubble.addClass('bubble_post_react_display');
								}
							} else {
								// Icon does not exist - create it with counter = 1
								var iconUrl = res.icon_url || '';
								if(iconUrl && container.length) {
									var safeIconId = escapeHtml(icon_id);
									var safeIconUrl = escapeHtml(iconUrl);
									var safeIconAlt = escapeHtml(res.icon_alt);
									var safeIconWidth = escapeHtml(res.icon_width);
									var safeIconHeight = escapeHtml(res.icon_height);
									var safePostId = escapeHtml(post_id);
									var safeCount = escapeHtml(res.new_count || 1);

									var html = '<div class="img_post_react_display" data-icon-id="' + safeIconId + '">' +
											   '<div class="bubble_post_react_display">' + safeCount + '</div>' +
											   '<a href="#" class="toggle-react-summary" data-post-id="' + safePostId + '">' +
											   '<img src="' + safeIconUrl + '" alt="' + safeIconAlt + '" width="' + safeIconWidth + '" height="' + safeIconHeight + '">' +
											   '</a>' +
											   '</div>';
									container.append(html);
									container.removeClass('pr-empty');

									// Attach the event listener to the newly created icon
									var newToggleButton = container.find('.img_post_react_display[data-icon-id="' + icon_id + '"] .toggle-react-summary');
									if(newToggleButton.length) {
										newToggleButton.on('click', function(e) {
											e.preventDefault();
											let postId = this.getAttribute("data-post-id");
											document.getElementById("darken").style.display = "block";
											document.getElementById("phpbb_confirm_" + postId).style.display = "block";
										});
									}
								}
							}

							// add "already reacted" div
							var popup = $('#popup-' + post_id);
							var existingLink = popup.find('a[data-icon_id="' + icon_id + '"]');

							if(existingLink.length) {
								// Check whether the link is already wrapped in already_reacted_pr
								if(!existingLink.closest('.already_reacted_pr').length) {
									// If not wrapped yet, wrap it in already_reacted_pr
									existingLink.wrap('<div class="already_reacted_pr"></div>');

									// Also add the "ALREADY_REACTED" text if it does not exist yet
									var alreadyReactedText = popup.find('.already_reacted_pr_txt');
									if(!alreadyReactedText.length) {
										var textHtml = '<div class="already_reacted_pr_txt"><span>' + escapeHtml(res.reacted_language) + '</span></div>';
										popup.find('.popup-content_pr').append(textHtml);
									}
								}
							}
						}

						// -------------------------
						// REMOVE_REACTION logic
						// -------------------------
						else if(res.action === 'removed') {

							// Use res.icon_id (the icon actually removed) instead of icon_id (the icon clicked)
							var removedIconId = res.icon_id;
							var removedIcon = container.find('.img_post_react_display[data-icon-id="' + removedIconId + '"]');
							var removedBubble = removedIcon.find('.bubble_post_react_display');

							if(removedIcon.length) {
								if(res.new_count <= 0) {
									// If the counter reaches 0 or less, remove the icon entirely
									removedIcon.remove();
									// If no reaction icons are left and we're not in emoji-level mode,
									// hide the container again so it doesn't reserve empty space
									if(container.data('emoji-level') != 1 && container.find('.img_post_react_display[data-icon-id]').length === 0) {
										container.addClass('pr-empty');
									}
								} else {
									// If the counter is still > 0, only update the number
									if(removedBubble.length) {
										removedBubble.text(res.new_count);
										// Update CSS classes based on the new count
										removedBubble.removeClass();
										if(res.new_count > 99) removedBubble.addClass('bubble_post_react_display_bb');
										else if(res.new_count > 9) removedBubble.addClass('bubble_post_react_display_b');
										else removedBubble.addClass('bubble_post_react_display');
									}
								}
							}

							// remove "already reacted" div - use the icon actually removed
							var popup = $('#popup-' + post_id);
							var alreadyReactedDiv = popup.find('.already_reacted_pr');
							var linkToUnwrap = alreadyReactedDiv.find('a[data-icon_id="' + removedIconId + '"]');

							if(linkToUnwrap.length) {
								// Remove the already_reacted_pr wrapper and keep only the link
								linkToUnwrap.unwrap();

								// Check whether any already_reacted_pr divs remain
								var remainingReacted = popup.find('.already_reacted_pr');
								if(!remainingReacted.length) {
									// If no "already reacted" icons remain, also remove the text
									popup.find('.already_reacted_pr_txt').remove();
								}
							}
						}

						// close the popup
						document.querySelectorAll('.popup_pr').forEach(p => p.style.display = 'none');

					} else {
						alert(res.message);
					}

				} catch(e) {
					alert(phpbb.lang('POSTREACTION_JSON_ERROR'));
				}
			},
			error: function(xhr, status, error) {
				alert(phpbb.lang('POSTREACTION_AJAX_ERROR'));
			}
		});
	});

});