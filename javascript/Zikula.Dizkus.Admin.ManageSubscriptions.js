

function liveusersearch() {
    $("liveusersearch").removeClassName("z-hide");
    var a=Zikula.Ajax.Request.defaultOptions({
        paramName:"fragment",
        minChars:3
    });
    
    new Ajax.Autocompleter("username","username_choices",Zikula.Config.baseURL+"ajax.php?module=Dizkus&func=getUsers",a)
}