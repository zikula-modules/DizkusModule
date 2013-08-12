/**
 * Zikula.Dizkus.Admin.ModifyForum.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function(){
    // set up chosen lib
    jQuery('.chzn-select').chosen();
    // on click handlers
    jQuery('#noexternal').click(function(){
         jQuery('#mail2forumField').hide("slow");
         jQuery('#rss2forumField').hide("slow");
         jQuery('#logindata').hide("slow");
    });
    jQuery('#mail2forum').click(function(){
         jQuery('#mail2forumField').show("slow");
         jQuery('#logindata').show("slow");
    });
    jQuery('#rss2forum').click(function(){
         jQuery('#rss2forumField').show("slow");
         jQuery('#logindata').show("slow");
    });
});