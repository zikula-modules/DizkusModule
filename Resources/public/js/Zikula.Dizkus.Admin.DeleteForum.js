/**
 * Zikula.Dizkus.Admin.DeleteForum.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function(){
    jQuery('#action').change(function() {
        if (jQuery('#action').val() == '0') {
            jQuery('#destinationSelector').hide("slow");
        } else {
            jQuery('#destinationSelector').show("slow");
        }
    });
});