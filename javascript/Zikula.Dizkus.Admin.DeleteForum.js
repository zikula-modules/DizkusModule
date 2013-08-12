/**
 * Zikula.Dizkus.Admin.DeleteForum.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function(){
    jQuery('#action').change(function() {checkActionValue()});
    function checkActionValue() {
        if (jQuery('#action').val() == '0') {
            jQuery('#destinationSelector').hide("slow");
            jQuery('#destinationSelector').hide("slow");
        } else {
            jQuery('#destinationSelector').show("slow");
            jQuery('#destinationSelector').show("slow");
        }
    };
    // check on form load
    checkActionValue();
});