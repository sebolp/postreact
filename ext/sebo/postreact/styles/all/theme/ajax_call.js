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

$(document).ready(function() {

    // Funzione per aggiornare il contenuto del popup
    function updatePopupContent(postId, iconId, action, userData, newCount, res) {
        var popup = $('#phpbb_confirm_' + postId);
        
        if (popup.length === 0) {
            return;
        }
        
        var iconContainer = popup.find('.post_react_explain_container[data-icon-id="' + iconId + '"]');
        
        if (action === 'added') {
            if (iconContainer.length) {
                // L'icona esiste già - aggiorna contatore e lista utenti
                var countElement = iconContainer.find('strong');
                var userList = iconContainer.find('.post_react_list');
                
                // Aggiorna il contatore
                countElement.text('(' + newCount + ')');
                
                // Aggiungi l'utente alla lista se non è già presente
                var currentUsers = userList.html();
                var username = userData.username;
                var userColor = userData.user_colour || '';
                
                // Controlla se l'username è già presente nel testo
                if (!currentUsers.includes('>' + username + '<')) {
                    var userSpan = '<span style="color: #' + userColor + '; ' + 
                                  (userColor ? 'font-weight: bold;' : '') + '">' + 
                                  username + '</span>';
                    
                    if (currentUsers.trim()) {
                        userList.append(', ' + userSpan);
                    } else {
                        userList.html(userSpan);
                    }
                }
            } else {
                // L'icona non esiste - crea nuovo container
                var newContainer = createIconContainer(iconId, res, newCount, [userData]);
                popup.find('.alert_text').append(newContainer);
            }
        } else if (action === 'removed') {
            if (iconContainer.length) {
                var countElement = iconContainer.find('strong');
                var userList = iconContainer.find('.post_react_list');
                
                if (newCount <= 0) {
                    // Rimuovi completamente il container se non ci sono più reazioni
                    iconContainer.remove();
                } else {
                    // Aggiorna contatore e rimuovi utente dalla lista
                    countElement.text('(' + newCount + ')');
                    
                    // Rimuovi l'utente dalla lista
                    var username = userData.username;
                    
                    // Trova e rimuovi l'utente specifico
                    var userSpans = userList.find('span');
                    var userRemoved = false;
                    
                    userSpans.each(function() {
                        if ($(this).text() === username) {
                            // Gestisci le virgole - rimuovi la virgola dopo se esiste
                            var nextSibling = this.nextSibling;
                            if (nextSibling && nextSibling.nodeType === 3 && nextSibling.textContent.includes(',')) {
                                nextSibling.remove();
                            } else {
                                // Se non c'è virgola dopo, controlla prima
                                var prevSibling = this.previousSibling;
                                if (prevSibling && prevSibling.nodeType === 3 && prevSibling.textContent.includes(',')) {
                                    prevSibling.remove();
                                }
                            }
                            
                            $(this).remove();
                            userRemoved = true;
                            return false;
                        }
                    });
                }
            }
        }
    }

    function createIconContainer(iconId, res, count, userDetails) {
        var userListHtml = '';
        userDetails.forEach(function(user, index) {
            var userColor = user.user_colour || '';
            userListHtml += '<span style="color: #' + userColor + '; ' + 
                           (userColor ? 'font-weight: bold;' : '') + '">' + 
                           user.username + '</span>';
            if (index < userDetails.length - 1) {
                userListHtml += ', ';
            }
        });
        
        return '<div class="post_react_explain_container" data-icon-id="' + iconId + '">' +
               '<div class="post_react_explain">' +
               '<img src="' + res.icon_url + '" title="' + res.icon_alt + '" alt="' + res.icon_alt + '" width="' + res.icon_width + '" height="' + res.icon_height + '"> ' +
               '<strong>(' + count + ')</strong> ' +
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
                        // AGGIORNA IL POPUP con i dati utente - PRIMA DI TUTTO
                        if (res.user_data) {
                            updatePopupContent(post_id, (res.action === 'removed' ? res.icon_id : icon_id), res.action, res.user_data, res.new_count, res);
                        }
                        
                        var container = $('#post_react_display_' + post_id);
                        var existingIcon = container.find('.img_post_react_display[data-icon-id="' + icon_id + '"]');
                        var bubble = existingIcon.find('.bubble_post_react_display');
                        
                        // -------------------------
                        // Logica ADD_REACTION
                        // -------------------------
                        if(res.action === 'added') {
                            
                            if(existingIcon.length) {
                                // L'icona esiste già - aggiorno solo il contatore
                                if(bubble.length) {
                                    bubble.text(res.new_count || 1);
                                    // Aggiorno le classi CSS in base al nuovo count
                                    bubble.removeClass();
                                    if(res.new_count > 99) bubble.addClass('bubble_post_react_display_bb');
                                    else if(res.new_count > 9) bubble.addClass('bubble_post_react_display_b');
                                    else bubble.addClass('bubble_post_react_display');
                                }
                            } else {
                                // L'icona non esiste - la creo con contatore = 1
                                var iconUrl = res.icon_url || '';
                                if(iconUrl && container.length) {
                                    var html = '<div class="img_post_react_display" data-icon-id="' + icon_id + '">' +
                                               '<div class="bubble_post_react_display">' + (res.new_count || 1) + '</div>' +
                                               '<a href="#" class="toggle-react-summary" data-post-id="' + post_id + '">' +
                                               '<img src="' + iconUrl + '" alt="' + res.icon_alt + '" width="' + res.icon_width + '" height="' + res.icon_height + '">' +
                                               '</a>' +
                                               '</div>';
                                    container.append(html);
                                    
                                    // Aggiungi l'event listener alla nuova icona creata
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
                            
                            // add div reacted
                            var popup = $('#popup-' + post_id);
                            var existingLink = popup.find('a[data-icon_id="' + icon_id + '"]');

                            if(existingLink.length) {
                                // Controlla se il link è già avvolto nel div already_reacted_pr
                                if(!existingLink.closest('.already_reacted_pr').length) {
                                    // Se non è già avvolto, lo avvolgo nel div already_reacted_pr
                                    existingLink.wrap('<div class="already_reacted_pr"></div>');
                                    
                                    // Aggiungi anche il testo "ALREADY_REACTED" se non esiste già
                                    var alreadyReactedText = popup.find('.already_reacted_pr_txt');
                                    if(!alreadyReactedText.length) {
                                        var textHtml = '<div class="already_reacted_pr_txt"><span>' + res.reacted_language + '</span></div>';
                                        popup.find('.popup-content_pr').append(textHtml);
                                    }
                                }
                            }
                        }
                        
                        // -------------------------
                        // Logica REMOVE_REACTION
                        // -------------------------
                        else if(res.action === 'removed') {
                            
                            // Usa res.icon_id (l'icona effettivamente rimossa) invece di icon_id (l'icona cliccata)
                            var removedIconId = res.icon_id;
                            var removedIcon = container.find('.img_post_react_display[data-icon-id="' + removedIconId + '"]');
                            var removedBubble = removedIcon.find('.bubble_post_react_display');
                            
                            if(removedIcon.length) {
                                if(res.new_count <= 0) {
                                    // Se il contatore arriva a 0 o meno, rimuovo l'icona completamente
                                    removedIcon.remove();
                                } else {
                                    // Se il contatore è ancora > 0, aggiorno solo il numero
                                    if(removedBubble.length) {
                                        removedBubble.text(res.new_count);
                                        // Aggiorno le classi CSS in base al nuovo count
                                        removedBubble.removeClass();
                                        if(res.new_count > 99) removedBubble.addClass('bubble_post_react_display_bb');
                                        else if(res.new_count > 9) removedBubble.addClass('bubble_post_react_display_b');
                                        else removedBubble.addClass('bubble_post_react_display');
                                    }
                                }
                            }

                            // remove div reacted - usa l'icona effettivamente rimossa
                            var popup = $('#popup-' + post_id);
                            var alreadyReactedDiv = popup.find('.already_reacted_pr');
                            var linkToUnwrap = alreadyReactedDiv.find('a[data-icon_id="' + removedIconId + '"]');
                            
                            if(linkToUnwrap.length) {
                                // Rimuovi il wrapper already_reacted_pr e mantieni solo il link
                                linkToUnwrap.unwrap();
                                
                                // Controlla se ci sono altri div already_reacted_pr rimasti
                                var remainingReacted = popup.find('.already_reacted_pr');
                                if(!remainingReacted.length) {
                                    // Se non ci sono più icone "già reagite", rimuovi anche il testo
                                    popup.find('.already_reacted_pr_txt').remove();
                                }
                            }
                        }
                        
                        // chiudo il popup
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