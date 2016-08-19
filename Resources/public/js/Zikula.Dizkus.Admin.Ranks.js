/**
 * Zikula.Dizkus.Admin.Ranks.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function() {
    var rankImagePath = jQuery('#rankImagesPath').val();
    jQuery('#newrank_image').change(function() {
        jQuery('#newimage').attr('src', rankImagePath + '/' + jQuery(this).val());
    });
    jQuery('.rankimageselect').change(function() {
        var id = '#image' + jQuery(this).data('id');
        jQuery(id).attr('src', rankImagePath + '/' + jQuery(this).val());
    });
});
