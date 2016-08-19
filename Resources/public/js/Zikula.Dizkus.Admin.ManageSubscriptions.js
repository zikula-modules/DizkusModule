/**
 * Zikula.Dizkus.Admin.ManageSubscriptions.js
 *
 * JQUERY based JS
 */

jQuery(document).ready(function() {
    jQuery('#username').autocomplete({
        serviceUrl: Routing.generate('zikuladizkusmodule_ajax_getusers'),
        paramName: 'fragment',
        onSearchStart: function(query) {
            this.preventDefault;
        },
        onSelect: function(suggestion) {
            window.location.href = Routing.generate('zikuladizkusmodule_admin_managesubscriptions', {uid: suggestion.data}, true);
        }
    });
    jQuery('#alltopic').click(function() {
        DizkusToggleInput('.topicsubscriptions', jQuery(this).prop('checked'));
    });
    jQuery('#allforums').click(function() {
        DizkusToggleInput('.forumsubscriptions', jQuery(this).prop('checked'));
    });
});