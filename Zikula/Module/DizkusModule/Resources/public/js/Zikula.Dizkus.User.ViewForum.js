/**
 * Zikula.Dizkus.User.ViewForum.js
 * 
 * jQuery based JS
 */

jQuery(document).ready(function() {
    // toggle forum favorite state
    jQuery("#forum-favourite").click(modifyForum);
    // toggle forum subscription
    jQuery("#forum-subscription").click(modifyForum);
});

function modifyForum(e) {
    var id = jQuery('#forum_id').val(), action, i = jQuery(this);
    if (i.text() == favouriteForum) {
        action = 'addToFavorites';
    } else if (i.text() == unfavouriteForum) {
        action = 'removeFromFavorites';
    } else if (i.text() == subscribeForum) {
        action = 'subscribe';
    } else if (i.text() == unsubscribeForum) {
        action = 'unsubscribe';
    }


    this.favouritestatus = true;
    var pars = {
        forum: id,
        action: action
    }

    jQuery.ajax({
        type: "POST",
        data: pars,
        url: Zikula.Config.baseURL + "index.php?module=ZikulaDizkusModule&type=ajax&func=modifyForum",
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
            } else {
                alert('Error! Erroneous result from modifyForum request.');
            }
        },
        error: function(result) {
            DizkusShowAjaxError(result.responseJSON.core.statusmsg);
            return;
        }
    });
    e.preventDefault();
}