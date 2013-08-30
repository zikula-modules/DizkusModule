/**
 * Zikula.Dizkus.User.TopicDelete.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function() {
    jQuery('#sendReason').click(function() {
        if (jQuery('#sendReason').is(':checked')) {
            jQuery('#diskus_reason_container').show("slow");
        } else {
            jQuery('#diskus_reason_container').hide("slow");
        }
    });
});