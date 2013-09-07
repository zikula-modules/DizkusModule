/**
 * Zikula.Dizkus.Admin.ManageSubscriptions.js
 *
 * JQUERY based JS
 */

jQuery(document).ready(function() {
    jQuery('#username').autocomplete({
        serviceUrl: Zikula.Config.baseURL + "ajax.php?module=ZikulaDizkusModule&type=ajax&func=getUsers",
        paramName: 'fragment',
        onSelect: function(suggestion) {
            console.log(suggestion);
            window.location.href = Zikula.Config.baseURL + "index.php?module=ZikulaDizkusModule&type=admin&func=managesubscriptions&uid=" + suggestion.data;
        }
    });
    jQuery('#alltopic').click(function() {
        DizkusToggleInput('.topicsubscriptions', jQuery(this).prop('checked'));
    });
    jQuery('#allforums').click(function() {
        DizkusToggleInput('.forumsubscriptions', jQuery(this).prop('checked'));
    });
});