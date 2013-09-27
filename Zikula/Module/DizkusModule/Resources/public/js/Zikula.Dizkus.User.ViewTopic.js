/**
 * Zikula.Dizkus.User.ViewTopic.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function() {
    jQuery("#toggletopiclock").click(changeTopicStatus);
    jQuery("#toggletopicsticky").click(changeTopicStatus);
    jQuery("#toggletopicsubscription").click(changeTopicStatus);
    jQuery("#toggletopicsolve").click(changeTopicStatus);
    // POST EDIT
    hookEditLinks();

    // QUICK REPLY
    hookQuickReplySubmit();
    hookQuickReplyPreview();
    hookQuickReplyCancel();

    // Show cancel button.
    jQuery('#btnCancelQuickReply').removeClass('hidden');
});

function changeTopicStatus(e) {
    var action;
    var i = jQuery(this);
    switch(i.attr('id')) {
        case "toggletopiclock":
            action = i.data('status') == 0 ? 'lock' : 'unlock';
            break;
        case "toggletopicsticky":
            action = i.data('status') == 0 ? 'sticky' : 'unsticky';
            break;
        case "toggletopicsubscription":
            action = i.data('status') == 0 ? 'subscribe' : 'unsubscribe';
            break;
        case "toggletopicsolve":
            action = i.data('status') == 0 ? 'solve' : 'unsolve';
            break;
        default:
            console.log('Wrong action');
            return;
    }

    jQuery.ajax({
        type: "POST",
        data: {
            topic: jQuery('#topic_id').val(),
            action: action
        },
        url: Zikula.Config.baseURL + "index.php?module=ZikulaDizkusModule&type=ajax&func=changeTopicStatus",
        success: function(result) {
            if (result == 'successful') {
                if (action == 'lock') {
                    i.text(unlockTopic);
                    jQuery('#dzk_quickreply').hide("slow");
                } else if (action == 'unlock') {
                    i.text(lockTopic);
                    jQuery('#dzk_quickreply').show("slow");
                } else if (action == 'sticky') {
                    i.text(unstickyTopic);
                } else if (action == 'unsticky') {
                    i.text(stickyTopic);
                } else if (action == 'subscribe') {
                    i.text(unsubscribeTopic);
                } else if (action == 'unsubscribe') {
                    i.text(subscribeTopic);
                } else if (action == 'solve') {
                    i.text(unsolveTopic);
                    jQuery('#topic_solved').removeClass('z-hide');
                } else if (action == 'unsolve') {
                    i.text(solveTopic);
                    jQuery('#topic_solved').addClass('z-hide');
                }
                // invert data-status value
                i.data('status', i.data('status') == 0 ? 1 : 0);
            } else {
                console.log(result);
                alert('Error! Erroneous result from locking/unlocking action.');
            }
        },
        error: function(result) {
            DizkusShowAjaxError(result);
            return;
        }
    });
    e.preventDefault();
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
        url: Zikula.Config.baseURL + "index.php?module=ZikulaDizkusModule&type=ajax&func=changeTopicStatus",
        success: function(result) {
            if (result == 'successful') {
                jQuery('#topicsubjectedit_editor').addClass('z-hide');
                jQuery('#topic_title').text(jQuery('#topicsubjectedit_subject').val());
            } else {
                console.log(result);
                alert('Error! Erroneous result when attempting to change topic title.');
            }
        },
        error: function(result) {
            DizkusShowAjaxError(result);
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
    function cancelQuickReplyHandler() {
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
 * @param text The text to show next to the icon.
 * @param postId If set, the ajax indicator will be shown for a post, else for a quick reply.
 */
function showAjaxIndicator(text, postId) {
    // @todo this image loads too slowly so it doesn't actually show
    var img = '<img width="16" height="16" class="dzk_ajaxindicator" src=Zikula.Config.baseURL+"images/ajax/indicator.white.gif" alt="" />';
    if (postId) {
        jQuery('#dizkusinformation_' + postId).html('<span style="color: red;">' + img + text + '</span>').fadeIn();
    } else {
        jQuery('#dizkusinformation').html(img + text).show();
    }
}

/**
 * Hides an ajax indicator for a post or a quick reply.
 * @param postId If set, the ajax indicator will be hidden for a post, else for a quick reply.
 */
function hideAjaxIndicator(postId) {
    if (postId) {
        jQuery('#dizkusinformation_' + postId).html("").hide();
    } else {
        jQuery('#dizkusinformation').html("").hide();
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

    }, errorHandler = function(request, message, detail) {
        postEditing = false;
        postId = false;
        DizkusShowAjaxError(request.responseText);
    };

    if (!postEditing) {
        postEditing = true;
        postEditingChanged = false;
        postId = id;

        jQuery.ajax('index.php?module=ZikulaDizkusModule&type=ajax&func=editpost', {
            data: {post: postId}
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
    jQuery.ajax('index.php?module=ZikulaDizkusModule&type=ajax&func=updatepost', {
        data: pars
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
 * @returns {boolean} Used to not to submit the normal, non-ajax form.
 */
function createQuickReply() {
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
            subscribe_topic: jQuery('#subscribe_topic').prop('checked') ? 1 : 0,
            preview: 0
        };

        var successHandler = function(result, message, request) {
            var post = result.data.data;

            // clear textarea and reset preview
            cancelQuickReply();

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
        jQuery.ajax('index.php?module=ZikulaDizkusModule&type=ajax&func=reply', {
            data: pars
        }).done(successHandler).fail(errorHandler).always(function() {
            hideAjaxIndicator();
        });
        showAjaxIndicator(zStoringReply+'...');

    }
    return false;
}

/**
 * Shows a preview of the quick reply.
 * @returns {boolean}
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
        jQuery.ajax('index.php?module=ZikulaDizkusModule&type=ajax&func=reply', {
            data: pars
        }).done(successHandler).fail(errorHandler).always(function() {
            hideAjaxIndicator();
        });
        showAjaxIndicator(zPreparingPreview+'...');
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