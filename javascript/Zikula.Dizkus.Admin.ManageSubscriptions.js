/**
 * Zikula.Dizkus.Admin.ManageSubscriptions.js
 *
 * PROTOTYPE based JS
 */



function liveusersearch() {
    $("liveusersearch").removeClassName("z-hide");
    var options = Zikula.Ajax.Request.defaultOptions({
        paramName: 'fragment',
        minChars: 3
    });
    
    new Ajax.Autocompleter("username", "username_choices", Zikula.Config.baseURL + "ajax.php?module=Dizkus&type=ajax&func=getUsers", options);
}