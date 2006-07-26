/*
 * $Id$
 *
 */

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
    document.getElementsByClassName(formtype + '_checkbox').each(function(el) { el.checked = $('all' + formtype).checked;});
}

function CheckCheckAll(formtype) {
    var totalon = 0;
    document.getElementsByClassName(formtype + '_checkbox').each(function(el) { if (el.checked) { totalon++; } });
    $('all' + formtype).checked = (document.getElementsByClassName(formtype + '_checkbox').length==totalon);
}

function dejsonize(jsondata)
{
    var result;
    try {
        result = eval('(' + jsondata + ')');
    } catch(error) {
        alert('illegal JSON response: \n' + error + 'in\n' + jsondata);
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
function pnf_showajaxerror(error)
{
    alert(error);
}

function showpnforuminfo(infotext)
{
    if($('pnforuminformation')) {
        Element.update('pnforuminformation', infotext);
        $('pnforuminformation').style.visibility = 'visible';
    }

}

function hidepnforuminfo()
{
    if($('pnforuminformation')) {
        Element.update('pnforuminformation', '&nbsp;');
        $('pnforuminformation').style.visibility = 'hidden';
    }
}

function pnf_redirect(redirecturl)
{
    window.location.href = redirecturl;
}
