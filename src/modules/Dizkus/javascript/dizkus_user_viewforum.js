/**
 * dizkus_user.js
 */

jQuery(document).ready(function () {
    Zikula.define('Dizkus');

    // toogle forum favorite state
    jQuery("#toggleforumfavourite").click(function (e) {
        var id = jQuery('#forum_id').val();
        var action;

        if (jQuery('#toggleforumfavourite').text() == favouriteForum) {
            action = 'add';
        } else {
            action = 'remove';
        }
        this.favouritestatus = true;
        var pars = {
            forum: id,
            action: action
        }
        //Ajax.Responders.register(this.dzk_globalhandlers);

        jQuery.ajax({
            type: "POST",
            data: pars,
            url: Zikula.Config.baseURL + "ajax.php?module=Dizkus&func=toggleForumFavouriteState",
            success: function(result) {
                if (result == 'successful') {
                    if (action == 'add') {
                        jQuery('#toggleforumfavourite').text(unfavouriteForum);
                    } else {
                        jQuery('#toggleforumfavourite').text(favouriteForum);
                    }
                } else {
                    alert('Error! Erroneous result from favourite addition/removal.');
                }
            },
            error: function(result) {
                Zikula.showajaxerror(result);
                return;
            }
        });
        e.preventDefault();
    });
});