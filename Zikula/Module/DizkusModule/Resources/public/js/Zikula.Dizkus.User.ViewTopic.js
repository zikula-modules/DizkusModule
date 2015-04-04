/**
 * Zikula.Dizkus.User.ViewTopic.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function() {
    jQuery("#toggletopiclock").attr('href', '#').click(changeTopicStatus);
    jQuery("#toggletopicsticky").attr('href', '#').click(changeTopicStatus);
    jQuery("#toggletopicsubscription").attr('href', '#').click(changeTopicStatus);
    jQuery(".solvetopic").click(changeTopicStatus);
    jQuery(".unsolvetopic").click(changeTopicStatus);
    // POST EDIT
    hookEditLinks();

    // QUICK REPLY
    hookQuickReplySubmit();
    hookQuickReplyPreview();
    hookQuickReplyCancel();

    // Show cancel button.
    jQuery('#btnCancelQuickReply').show();

    jQuery('a.disabled').click(function(e) {
        e.preventDefault();
        //do other stuff when a click happens
    }).hover(function(){
        jQuery(this).css('cursor','not-allowed');
    } , function(){
        jQuery(this).css('cursor','default');
    });
    // toggle visibility of edit icon for topic title
    jQuery('#edittopicsubjectbutton').hover(
        function() {
            if (typeof jQuery('#userAllowedToEdit').val() !== "undefined") {
                jQuery('#edittopicicon').show();
            }
        },
        function() {jQuery('#edittopicicon').hide();}
    );
    if (typeof jQuery('#userAllowedToEdit').val() !== "undefined") {
        jQuery('#edittopicsubjectbutton').addClass('editabletopicheader tooltips').attr('title', clickToEdit).tooltip();
        jQuery('#edittopicsubjectbutton').click(function() { jQuery('#topicsubjectedit_editor').show(); });
        jQuery('#topicsubjectedit_cancel').click(function() { jQuery('#topicsubjectedit_editor').hide(); });
        jQuery("#topicsubjectedit_save").click(changeTopicTitle);
    }
});

function changeTopicStatus(e) {
    e.preventDefault();

    var action;
    var i = jQuery(this);
    action = i.data('action');

    jQuery.ajax({
        type: "POST",
        data: {
            topic: jQuery('#topic_id').val(),
            post: i.data('post'),
            action: action
        },
        url: Routing.generate('zikuladizkusmodule_ajax_changetopicstatus'),
        success: function(result) {
            if (result == 'successful') {
                switch (action) {
                    case 'lock':
                        i.attr('title', unlockTopic).removeClass('fa-lock').addClass('fa-unlock');
                        i.data('action', 'unlock');
                        jQuery('#dzk_quickreply').hide("slow"); // hide quick reply
                        break;
                    case 'unlock':
                        i.attr('title', lockTopic).removeClass('fa-unlock').addClass('fa-lock');
                        i.data('action', 'lock');
                        jQuery('#dzk_quickreply').show("slow"); // show quick reply
                        break;
                    case 'sticky':
                        i.attr('title', unstickyTopic).empty().html(unstickyTopicIcon);
                        i.data('action', 'unsticky');
                        break;
                    case 'unsticky':
                        i.attr('title', stickyTopic).empty().html(stickyTopicIcon);
                        i.data('action', 'sticky');
                        break;
                    case 'subscribe':
                        i.attr('title', unsubscribeTopic).empty().html(unsubscribeTopicIcon);
                        i.data('action', 'unsubscribe');
                        break;
                    case 'unsubscribe':
                        i.attr('title', subscribeTopic).empty().html(subscribeTopicIcon);
                        i.data('action', 'subscribe');
                        break;
                    case 'solve':
                        jQuery('#solutionPost_' + i.data('post')).show();
                        jQuery('.solvetopic').hide();
                        jQuery('#topic_solved').show();
                        jQuery('#topic_unsolved').hide();
                        break;
                    case 'unsolve':
                        jQuery('#solutionPost_' + i.data('post')).hide();
                        jQuery('.solvetopic').show();
                        jQuery('#topic_solved').hide();
                        jQuery('#topic_unsolved').show();
                        break;
                }

                // destroy and recreate tooltip
                i.tooltip('destroy').tooltip();
            } else {
                alert('Error! Erroneous result from changing topic status action.');
            }
        },
        error: function(result) {
            DizkusShowAjaxError(result.responseText);
            return;
        }
    });
}


function changeTopicTitle(e) {

    jQuery.ajax({
        type: "POST",
        data: {
            topic: jQuery('#topic_id').val(),
            title: jQuery('#topicsubjectedit_subject').val(),
            userAllowedToEdit: jQuery('#userAllowedToEdit').val(),
            action: 'setTitle'
        },
        url: Routing.generate('zikuladizkusmodule_ajax_changetopicstatus'),
        success: function(result) {
            if (result == 'successful') {
                jQuery('#topicsubjectedit_editor').hide();
                jQuery('#topic_title').text(jQuery('#topicsubjectedit_subject').val());
            } else {
                console.log(result);
                alert('Error! Erroneous result when attempting to change topic title.');
            }
        },
        error: function(result) {
            DizkusShowAjaxError(result.responseText);
            return;
        }
    });
    e.preventDefault();
}

/**
 * Quote a text.
 *
 * @param text
 */
function quote(text) {
    text = text.replace(/_____LINEFEED_DIZKUS_____/g, "\n");

    jQuery('#message').val(jQuery('#message').val() + text);

    scrollTo("#dzk_quickreply");
}

// "Hook" into links / buttons

/**
 * Hook into the post edit links and use ajax instead.
 */
function hookEditLinks() {
    function editPostHandler(event) {
        event.preventDefault();
        var postId = jQuery(event.currentTarget).data('post');
        quickEdit(postId);
    }

    jQuery('.editpostlink').each(
            function() {
                jQuery(this).click(editPostHandler);
            }
    );

}


/**
 * Hook into submit quick reply button and use ajax instead.
 */
function hookQuickReplySubmit() {
    function submitQuickReplyHandler(event) {
        event.preventDefault();
        if (typeof Scribite !== 'undefined') {
            Scribite.renderAllElements();
        }
        createQuickReply();
    }

    jQuery('#btnSubmitQuickReply').each(
            function() {
                jQuery(this).click(submitQuickReplyHandler);
            }
    );

}

/**
 * Hook into preview quick reply button and use ajax instead.
 */
function hookQuickReplyPreview() {
    function previewQuickReplyHandler(event) {
        event.preventDefault();
        previewQuickReply();
    }

    jQuery('#btnPreviewQuickReply').each(
        function() {
            jQuery(this).click(previewQuickReplyHandler);
        }
    );
}

/**
 * Hook into cancel quick reply button.
 */
function hookQuickReplyCancel() {
    function cancelQuickReplyHandler(event) {
        event.preventDefault();
        cancelQuickReply();
    }

    jQuery('#btnCancelQuickReply').each(
        function() {
            jQuery(this).click(cancelQuickReplyHandler);
        }
    );
}


// Quick edit features

/**
 * True if a post is currently edited.
 * @type {boolean}
 */
var postEditing = false;

/**
 * False as long as the user has not changed the post.
 * @type {boolean}
 */
var postEditingChanged = false;

/**
 * The post id of the post currently edited.
 *
 * This is false if no post is edited at the moment.
 */
var postId = false;

/**
 * Shows an ajax indicator for a post or a quick reply.
 * spinner icon is located in the template so text is appended then .show() parent div
 * @param text The text to show next to the icon.
 * @param postId If set, the ajax indicator will be shown for a post, else for a quick reply.
 */
function showAjaxIndicator(text, postId) {
    if (postId) {
        text = '<span id="ajaxindicatortext_' + postId + '">&nbsp;' + text + '</span>';
        jQuery('#dizkusinformation_' + postId).append(text).show();
    } else {
        text = '<span id="ajaxindicatortext_0">&nbsp;' + text + '</span>';
        jQuery('#dizkusinformation_0').append(text).show();
    }
}

/**
 * Hides an ajax indicator for a post or a quick reply.
 * removes appended text then .hide() the parent div
 * @param postId If set, the ajax indicator will be hidden for a post, else for a quick reply.
 */
function hideAjaxIndicator(postId) {
    if (postId) {
        jQuery('#ajaxindicatortext_' + postId).remove();
        jQuery('#dizkusinformation_' + postId).hide();
    } else {
        jQuery('#ajaxindicatortext_0').remove();
        jQuery('#dizkusinformation_0').hide();
    }
}

/**
 * Edit a post.
 *
 * @param id The post id.
 */
function quickEdit(id) {
    var successHandler = function(result, message, request) {
        // Hide post footer
        jQuery('#postingoptions_' + postId).hide();
        // Overwrite posting text.
        jQuery('#postingtext_' + postId).hide().after(result.data);

        // Hide quickreply
        jQuery('#dzk_quickreply').fadeOut();

        // Observe buttons
        jQuery('#postingtext_' + postId + '_edit').keyup(quickEditChanged);
        jQuery('#postingtext_' + postId + '_save').click(quickEditSave);
        jQuery('#postingtext_' + postId + '_cancel').click(quickEditCancel);

        // notify bbSmile & bbCode (uncomfortably tight coupling)
        if (typeof bbsmileLastFocus !== "undefined") {
            bbsmileLastFocus = jQuery('#postingtext_' + postId + '_edit');
        }
        if (typeof bbcodeLastFocus !== "undefined") {
            bbcodeLastFocus = jQuery('#postingtext_' + postId + '_edit');
        }

    }, errorHandler = function(request, message, detail) {
        postEditing = false;
        postId = false;
        DizkusShowAjaxError(request.responseText);
    };

    if (!postEditing) {
        postEditing = true;
        postEditingChanged = false;
        postId = id;

        jQuery.ajax({
            data: {post: postId},
            url: Routing.generate('zikuladizkusmodule_ajax_editpost')
        }).done(successHandler).fail(errorHandler).always(function() {
            hideAjaxIndicator(postId);
        });
        showAjaxIndicator(zLoadingPost+'...', postId);
    }
}

/**
 * Tell the user that he has changed the text.
 */
function quickEditChanged() {
    if (!postEditingChanged) {
        postEditingChanged = true;
        jQuery('#postingtext_' + postId + '_status').html('<span style="color: red;">' + zChanged + '</span>');
    }
}

/**
 * Save edited post.
 */
function quickEditSave() {
    var newPostMsg = jQuery('#postingtext_' + postId + '_edit').val(),
            pars = {
        postId: postId,
        message: newPostMsg,
        attach_signature: (jQuery('#postingtext_' + postId + '_attach_signature').prop('checked')) ? 1 : 0,
        delete_post: 0 /* Do not use 'delete' here, this is a reserved word. */
    };

    if (!newPostMsg) {
        // no text
        return;
    }

    if (jQuery('#postingtext_' + postId + '_delete').prop('checked')) {
        jQuery('#postingtext_' + postId + '_status').html('<span style="color: red;">' + zDeletingPost + '...</span>');
        pars['delete_post'] = 1;
    } else {
        jQuery('#postingtext_' + postId + '_status').html('<span style="color: red;">' + zUpdatingPost + '...</span>');
    }

    var successHandler = function(result, message, request) {
        var action = result.data.action,
            redirect = result.data.redirect,
            newText = result.data.newText;
//        if (message.length > 0) {
//            alert(message);
//        }

        postEditing = false;
        postEditingChanged = false;

        // Remove editor.
        jQuery('#postingtext_' + postId + '_editor').remove();

        if (action === 'deleted') {
            // Remove post
            jQuery('#posting_' + postId).fadeOut();
        } else if (action === 'topic_deleted') {
            // Remove post
            jQuery('#posting_' + postId).fadeOut();
            // Redirect to overview url.
            window.setTimeout("window.location.href='" + redirect + "';", 500);
            return;
        } else {
            // Insert new text.
            jQuery('#postingtext_' + postId).html(newText).show();
        }

        // Show quickreply
        jQuery('#dzk_quickreply').fadeIn();

        // Show post footer
        jQuery('#postingoptions_' + postId).show();
    }, errorHandler = function(request, message, detail) {
        DizkusShowAjaxError(request.responseText);
    };
    jQuery.ajax({
        data: pars,
        url: Routing.generate('zikuladizkusmodule_ajax_updatepost')
    }).done(successHandler).fail(errorHandler);
}

/**
 * Cancel editing a post.
 */
function quickEditCancel() {
    // Show post footer
    jQuery('#postingoptions_' + postId).show();

    // Show post text
    jQuery('#postingtext_' + postId).show();

    // Show quickreply
    jQuery('#dzk_quickreply').fadeIn();

    // Remove post editor
    jQuery('#postingtext_' + postId + '_editor').remove();

    postEditing = false;
    postEditingChanged = false;
}


// Quick reply features.

/**
 * True if the user is in the quick reply process.
 * @type {boolean}
 */
var quickReplying = false;

/**
 * Saves and shows the new post.
 * @return {boolean} Used to not to submit the normal, non-ajax form.
 */
function createQuickReply() {
    if (!quickReplying) {
        var message = jQuery('#message').val();
        if (!message) {
            return false;
        }

        quickReplying = true;
        var pars = {};
        // assign all form input to function parameters - allows hooked content to validate from unknown form input
        jQuery(":input").each(function(){
            var input = jQuery(this);
            if (typeof input.attr('name') != undefined) {
                pars[input.attr('name')] = input.val();
            } else {
                pars[input.attr('id')] = input.val();
            }
        });

        pars['attach_signature'] = jQuery('#attach_signature').prop('checked') ? 1 : 0;
        pars['subscribe_topic'] = jQuery('#subscribe_topic').prop('checked') ? 1 : 0;
        pars['preview'] = 0;

        var successHandler = function(result, message, request) {
            cancelQuickReply();
            var post = result.data.data;

            // show new posting
            jQuery('#quickreplyposting').html(post).removeClass('hidden');

            // Scroll to new posting.
            scrollTo('#quickreplyposting');

            // prepare everything for another quick reply
            jQuery('#quickreplyposting').after('<li id="new_quickreplyposting"></li>');
            // clear old id
            jQuery('#quickreplyposting').prop('id', '');
            // rename new id
            jQuery('#new_quickreplyposting').prop('id', 'quickreplyposting');
            // enable js options in quickreply
            jQuery('ul.javascriptpostingoptions').each(function() {
                jQuery(this).removeClass('hidden');
            });

            quickReplying = false;

            // Hook into edit link to work via ajax.
            hookEditLinks();

        }, errorHandler = function(request, message, detail) {
            DizkusShowAjaxError(request.responseText);
            quickReplying = false;
        };
        jQuery.ajax({
            data: pars,
            url: Routing.generate('zikuladizkusmodule_ajax_reply')
        }).done(successHandler).fail(errorHandler).always(function() {
            hideAjaxIndicator('quickreply');
        });
        showAjaxIndicator(zStoringReply+'...', 'quickreply');

    }
    return false;
}

/**
 * Shows a preview of the quick reply.
 * @return {boolean}
 */
function previewQuickReply() {
    if (!quickReplying) {
        var message = jQuery('#message').val();
        if (!message) {
            return false;
        }

        quickReplying = true;

        var pars = {
            topic: jQuery('#topic').val(),
            message: message,
            attach_signature: jQuery('#attach_signature').prop('checked') ? 1 : 0,
            preview: 1
        };

        var successHandler = function(result, message, request) {
            // Show preview.
            jQuery('#quickreplypreview').html(result.data.data).removeClass('hidden');

            // Scroll to preview.
            scrollTo('#quickreplypreview');

            quickReplying = false;
        }, errorHandler = function(request, message, detail) {
            DizkusShowAjaxError(request.responseText);
            quickReplying = false;
        };
        jQuery.ajax({
            data: pars,
            url: Routing.generate('zikuladizkusmodule_ajax_reply')

        }).done(successHandler).fail(errorHandler).always(function() {
            hideAjaxIndicator('quickreply');
        });
        showAjaxIndicator(zPreparingPreview + '...', 'quickreply');
    }
}

/**
 * Aborts quick replying by emptying the message field and hiding previews.
 */
function cancelQuickReply() {
    jQuery('#message').val("");
    jQuery('#quickreplypreview').addClass('hidden');
    quickReplying = false;
}