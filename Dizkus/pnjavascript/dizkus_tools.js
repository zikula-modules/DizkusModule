// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

function getcheckboxvalue(id)
{
    if($(id)) {
        if($(id).checked==true) {
            return $(id).value;
        }
        return '';
    }
}

function CheckAll(formtype) {
    $$('.' + formtype + '_checkbox').each(function(el) { el.checked = $('all' + formtype).checked;});
}

function CheckCheckAll(formtype) {
    var totalon = 0;
    $$('.' + formtype + '_checkbox').each(function(el) { if (el.checked) { totalon++; } });
    $('all' + formtype).checked = ($$('.' + formtype + '_checkbox').length==totalon);
}

function dejsonize(jsondata)
{
    var result;
    try {
        result = eval('(' + jsondata + ')');
    } catch(error) {
        alert('Error! Illegal JSON response: \n' + error + 'in\n' + jsondata);
    }
    return result;
}

function updateAuthid(authid)
{
    if(authid.length != 0) {
        for(var i=0; i<document.forms.length; i++) {
            for(var j=0; j<document.forms[i].elements.length; j++) {
                if(document.forms[i].elements[j].type=='hidden' && document.forms[i].elements[j].name=='authid') {
                    document.forms[i].elements[j].value = authid;
                }
            }
        }
    }
}

/*
 * show an ajax error
 * to-do: beautify this function
 */
function dzk_showajaxerror(error)
{
    alert(error);
}

function showdizkusinfo(infotext)
{
    if($('dizkusinformation')) {
        Element.update('dizkusinformation', infotext);
        $('dizkusinformation').style.visibility = 'visible';
    }

}

function hidedizkusinfo()
{
    if($('dizkusinformation')) {
        Element.update('dizkusinformation', '&nbsp;');
        $('dizkusinformation').style.visibility = 'hidden';
    }
}

function dzk_redirect(redirecturl)
{
    window.location.href = redirecturl;
}
