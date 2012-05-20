

function liveusersearch() {
    $("liveusersearch").removeClassName("z-hide");
    /*$("modifyuser").observe("click",function() {
        window.location.href=Zikula.Config.entrypoint+"?module=users&type=admin&func=modify&uname="+$F("username")
    });
    $("deleteuser").observe("click",function(){
        window.location.href=Zikula.Config.entrypoint+"?module=users&type=admin&func=deleteusers&uname="+$F("username")
    });*/
    var a=Zikula.Ajax.Request.defaultOptions({paramName:"fragment",minChars:3,afterUpdateElement:function(b){
        
        }
    });
    
    new Ajax.Autocompleter("username","username_choices",Zikula.Config.baseURL+"ajax.php?module=Dizkus&func=getUsers",a)
};