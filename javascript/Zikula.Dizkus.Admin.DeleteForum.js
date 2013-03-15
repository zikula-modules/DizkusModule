jQuery(document).ready(function(){
    jQuery('#action').change(function(){
        if (jQuery('#action').val() == '0') {
            jQuery('#destinationSelector').hide("slow");
            jQuery('#destinationSelector').hide("slow");
        } else {
            jQuery('#destinationSelector').show("slow");
            jQuery('#destinationSelector').show("slow");
        }
    })
});