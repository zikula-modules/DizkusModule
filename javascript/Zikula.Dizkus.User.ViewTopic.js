/**
 * Scroll to an element.
 *
 * @param selector The element's selector to scroll to.
 *
 * @note jQuery does not support .scrollTo() - calculate position manually.
 */
function scrollTo(selector, time) {
    if (!time) {
        time = 1000;
    }
    jQuery('html, body').animate({
        scrollTop: jQuery(selector).offset().top
    }, time);
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
        function () {
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
        createQuickReply();
    }

    jQuery('#btnSubmitQuickReply').each(
        function () {
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
        function () {
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
        function () {
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
 * @param postId If set, the ajax indicator will be shown for a post, else for a quick reply.
 * @todo Add real indicator.
 */
function showAjaxIndicator(postId) {
    jQuery('body').css({'background': 'red'});
}

/**
 * Hides an ajax indicator for a post or a quick reply.
 * @param postId If set, the ajax indicator will be hidden for a post, else for a quick reply.
 * @todo Add real indicator.
 */
function hideAjaxIndicator(postId) {
    jQuery('body').css({'background': 'green'});
}

/**
 * Edit a post.
 *
 * @param id The post id.
 */
function quickEdit(id) {
    var successHandler = function (result, message, request) {
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

    }, errorHandler = function (request, message, detail) {
        postEditing = false;
        postId = false;
        alert(message + ": " + detail);
    };

    if (!postEditing) {
        postEditing = true;
        postEditingChanged = false;
        postId = id;

        jQuery.ajax('ajax.php?module=Dizkus&type=ajax&func=editpost', {
            data: {post: postId}
        }).done(successHandler).fail(errorHandler).always(function () {hideAjaxIndicator(postId); });
        showAjaxIndicator(postId);
    }
}

/**
 * Tell the user that he has changed the text.
 */
function quickEditChanged() {
    if (!postEditingChanged) {
        postEditingChanged = true;
        jQuery('#postingtext_' + postId + '_status').html('<span style="color: red;">' + Zikula.__('Changed') + '</span>');
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
            delete: false
        };

    if (!newPostMsg) {
        // no text
        return;
    }

    if (jQuery('#postingtext_' + postId + '_delete').prop('checked')) {
        jQuery('#postingtext_' + postId + '_status').html('<span style="color: red;">' + Zikula.__('Deleting post...') + '</span>');
        pars['delete'] = 1;
    } else {
        jQuery('#postingtext_' + postId + '_status').html('<span style="color: red;">' + Zikula.__('Updating post...') + '</span>');
    }

    var successHandler = function (result, message, request) {
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
    }, errorHandler = function (request, message, detail) {
        alert(message + ": " + detail);
    };
    jQuery.ajax('ajax.php?module=Dizkus&type=ajax&func=updatepost', {
        data: pars
    }).done(successHandler).fail(errorHandler).always(function () {hideAjaxIndicator(postId); });
    showAjaxIndicator(postId);
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
        //this.showdizkusinfo(this.indicatorimage + ' ' + storingReply);
        var pars = {
            topic: jQuery('#topic').val(),
            message: message,
            attach_signature: jQuery('#attach_signature').prop('checked') ? 1 : 0,
            subscribe_topic: jQuery('#subscribe_topic').prop('checked') ? 1 : 0,
            preview: 0
        };

        var successHandler = function (result, message, request) {
            var post = result.data.data;

            //this.hidedizkusinfo();

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
            jQuery('ul.javascriptpostingoptions').each(function () {
                jQuery(this).removeClass('hidden');
            });

            quickReplying = false;

            // Hook into edit link to work via ajax.
            hookEditLinks();

        }, errorHandler = function (request, message, detail) {
            alert(message + ": " + detail);
            quickReplying = false;
        };
        jQuery.ajax('ajax.php?module=Dizkus&type=ajax&func=reply', {
            data: pars
        }).done(successHandler).fail(errorHandler).always(function () {hideAjaxIndicator(); });
        showAjaxIndicator();

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
        //this.showdizkusinfo(this.indicatorimage + ' ' + preparingPreview);

        var pars = {
            topic: jQuery('#topic').val(),
            message: message,
            attach_signature: jQuery('#attach_signature').prop('checked') ? 1 : 0,
            preview: 1
        };

        var successHandler = function (result, message, request) {
            //this.hidedizkusinfo();

            // Show preview.
            jQuery('#quickreplypreview').html(result.data.data).removeClass('hidden');

            // Scroll to preview.
            scrollTo('#quickreplypreview');

            quickReplying = false;
        }, errorHandler = function (request, message, detail) {
            alert(message + ": " + detail);
            quickReplying = false;
        };
        jQuery.ajax('ajax.php?module=Dizkus&type=ajax&func=reply', {
            data: pars
        }).done(successHandler).fail(errorHandler).always(function () {hideAjaxIndicator(); });
        showAjaxIndicator();
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