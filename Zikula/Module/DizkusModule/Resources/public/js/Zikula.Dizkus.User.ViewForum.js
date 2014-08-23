/**
 * Zikula.Dizkus.User.ViewForum.js
 * 
 * jQuery based JS
 */

jQuery(document).ready(function() {
    jQuery("#forum-favourite").attr('href', '#').click(modifyForum);
    jQuery("#forum-subscription").attr('href', '#').click(modifyForum);
});

function modifyForum(e) {
    e.preventDefault();
    var action;
    var i = jQuery(this);
    i.prepend(" <i class='fa fa-cog fa-spin text-danger'></i>");
    switch(i.attr('id')) {
        case 'forum-subscription':
            action = i.data('status') == 0 ? 'subscribe' : 'unsubscribe';
            break;
        case 'forum-favourite':
            action = i.data('status') == 0 ? 'addToFavorites' : 'removeFromFavorites';
            break;
        default:
            console.log('Wrong action');
            return;
    }

    jQuery.ajax({
        type: "POST",
        data: {
            forum: jQuery('#forum_id').val(),
            action: action
        },
        url: Routing.generate('zikuladizkusmodule_ajax_modifyforum'),
        success: function(result) {
            if (result == 'successful') {
                if (action == 'addToFavorites') {
                    i.text(unfavouriteForum);
                } else if (action == 'removeFromFavorites') {
                    i.text(favouriteForum);
                } else if (action == 'subscribe') {
                    i.text(unsubscribeForum);
                } else if (action == 'unsubscribe') {
                    i.text(subscribeForum);
                }
                // invert data-status value
                i.data('status', i.data('status') == 0 ? 1 : 0);
            } else {
                alert('Error! Erroneous result from modifyForum request.');
            }
        },
        error: function(result) {
            DizkusShowAjaxError(result.responseJSON.core.statusmsg);
            return;
        }
    });
}