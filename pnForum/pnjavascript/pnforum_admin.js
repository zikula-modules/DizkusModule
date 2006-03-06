/**
 * pnforum_admin.js
 *
 * $Id$
 *
 */

var forumliststatus = false;
var treeorderstatus = false;
var globalhandlers = {
    onCreate: function(){
        if($('pnforum')) {
            $('pnforum').style.cursor = 'wait';
        }       
    },

    onComplete: function() {
        if(Ajax.activeRequestCount == 0){
            if($('pnforum')) {
                $('pnforum').style.cursor = 'auto';
            }
        }
    }
};
Ajax.Responders.register(globalhandlers);


function loadforum(forumid)
{
    pnf_toggleprogressimage(false, forumid);
    //if(Element.visible('editforum_' + forumid) == false) {
    var pars = "module=pnForum&type=admin&func=editforum&forum=" + forumid;
    var myAjax = new Ajax.Request(
        "index.php", 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: editforum
        });
}

function editforum(originalRequest)
{    
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);
    
    Element.update('editforum_' + result.forum_id, result.data);
    Effect.toggle('editforum_' + result.forum_id, 'slide');
    //Element.show('editforum_' + result.forum_id);
    Event.observe('showforum_' + result.forum_id, 'click', function(){hideshowforum(result.forum_id)}, false);
    Event.observe('hideforum_' + result.forum_id, 'click', function(){hideshowforum(result.forum_id)}, false);
    Element.show('hideforum_' + result.forum_id);
    Element.hide('showforum_' + result.forum_id);
    Element.hide('loadforum_' + result.forum_id);
    pnf_toggleprogressimage(false, result.forum_id);
}

function loadcategory(catid)
{
//alert(catid);
    pnf_toggleprogressimage(true, catid);
    var pars = "module=pnForum&type=admin&func=editcategory&cat=" + catid;
    var myAjax = new Ajax.Request(
        "index.php", 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: editcategory
        });
}

function editcategory(originalRequest)
{    
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);
    Element.update('editcategory_' + result.cat_id, result.data);
    Effect.toggle('editcategory_' + result.cat_id, 'slide');
    //Element.show('editcategory_' + result.cat_id);
    Event.observe('showcategory_' + result.cat_id, 'click', function(){hideshowcategory(result.cat_id)}, false);
    Event.observe('hidecategory_' + result.cat_id, 'click', function(){hideshowcategory(result.cat_id)}, false);
    Element.show('hidecategory_' + result.cat_id);
    Element.hide('showcategory_' + result.cat_id);
    Element.hide('loadcategory_' + result.cat_id);

    pnf_toggleprogressimage(true, result.cat_id);
}

function storeforum(forumid)
{
    pnf_toggleprogressimage(false, forumid);
    var pars = "module=pnForum&type=admin&func=storeforum&" + Form.serialize('editforumform_'+ forumid);
//alert(pars.length + ': ' + pars);

    var myAjax = new Ajax.Request(
        "index.php", 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: storeforum_response
        });
}

function storeforum_response(originalRequest, json)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    //var result = dejsonize(originalRequest.responseText);

    updateAuthid(json.authid);
    switch(json.action) {
        case 'delete':
            Element.remove('forum_' + json.old_id);
            break;
        case 'update':
            Element.update('forumtitle_' + json.forum.forum_id, json.forumtitle);
            Element.update('editforum_' + json.forum.forum_id, json.editforumhtml);
            break;
        case 'add':
            var newforumtitle = 'forumtitle_' + json.forum.forum_id;
            $('forumtitle_' + json.old_id).id = newforumtitle; 
            Element.update(newforumtitle, json.forumtitle);

            var neweditforum = 'editforum_' + json.forum.forum_id;
            $('editforum_' + json.old_id).id = neweditforum; 
            Element.update(neweditforum, json.editforumhtml);

            var newforum = 'forum_' + json.forum.forum_id;
            $('forum_' + json.old_id).id = newforum;

            var newhideforum = 'hideforum_' + json.forum.forum_id;
            var newshowforum = 'showforum_' + json.forum.forum_id;
            $('hideforum_' + json.old_id).id = newhideforum;
            $('showforum_' + json.old_id).id = newshowforum;

            Element.remove('canceladdforum_' + json.old_id);
            $('progressforumimage_' + json.old_id).id = 'progressforumimage_' + json.forum.forum_id;

            Event.observe(newshowforum, 'click', function(){hideshowforum(json.forum.forum_id)}, false);
            Event.observe(newhideforum, 'click', function(){hideshowforum(json.forum.forum_id)}, false);
            Element.show(newhideforum);
            Element.hide(newshowforum);
            break;
        default:
            pnf_showajaxerror('storeforum_response(): received illegal action type from server');   
    }
    pnf_toggleprogressimage(false, json.forum.forum_id);
}

function storecategory(catid)
{
    pnf_toggleprogressimage(true, catid);
    var pars = "module=pnForum&type=admin&func=storecategory&" + Form.serialize('editcategoryform_'+ catid);
    var myAjax = new Ajax.Request(
        "index.php", 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: storecategory_response
        });
}

function storecategory_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);
    updateAuthid(result.authid);
    
    switch(result.action) {
        case 'add':
            $('category_' + result.old_id).id = 'category_' + result.cat_id;
            
            var newhidecategory = 'hidecategory_' + result.cat_id;
            $('hidecategory_' + result.old_id).id = newhidecategory;
            Event.observe(newhidecategory, 'click', function(){hideshowcategory(result.cat_id)}, false);
            
            var newshowcategory = 'showcategory_' + result.cat_id;
            $('showcategory_' + result.old_id).id = newshowcategory;
            Event.observe(newshowcategory, 'click', function(){hideshowcategory(result.cat_id)}, false);

            var newcategorytitle = 'categorytitle_' + result.cat_id;
            $('categorytitle_' + result.old_id).id = newcategorytitle;
            Element.update(newcategorytitle, '<a href="' + result.cat_linkurl + '">' + result.cat_title + '</a> (' + result.cat_id + ')');
            
            var newaddforum = 'addforum_' + result.cat_id;
            $('addforum_' + result.old_id).id = newaddforum;
            Element.show(newaddforum);

            Element.remove('canceladdcategory_' + result.old_id);

            var neweditcategory = 'editcategory_' + result.cat_id;          
            $('editcategory_' + result.old_id).id = neweditcategory;
            Element.update(neweditcategory, result.edithtml);

            var neweditcategoryform = 'editcategoryform_' + result.cat_id;          
            $('editcategoryform_' + result.old_id).id = neweditcategoryform;
            
            break;
        case 'update':
            Element.update('categorytitle_' + result.cat_id, '<a href="' + result.cat_linkurl + '">' + result.cat_title + '</a> (' + result.cat_id + ')');
            break;
        case 'delete':
            Element.remove('category_' + result.cat_id);
            break;
        default:
            pnf_showajaxerror('unknown action received from server');
    }
    pnf_toggleprogressimage(true, result.cat_id);
}

function addforum(catid)
{
    pnf_toggleprogressimage(true, catid);
    var pars = "module=pnForum&type=admin&func=editforum&forum=-1&cat=" + catid;
    var myAjax = new Ajax.Request(
        "index.php", 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: addforuminit
        });
}

function addforuminit(originalRequest)
{    
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);
    // copy newforum li
    var newnewforum = $('newforum_cat' + result.cat_id).cloneNode(true);
    // update existig newforum li with data retreved from server
    Element.update('neweditforum_' + result.cat_id, result.data);
    // and show it
    Element.show('newforum_cat' + result.cat_id);
    Element.show('neweditforum_' + result.cat_id);
    // set new id in newforum li
    $('newforum_cat' + result.cat_id).id = 'forum_' + result.forum_id;
    $('neweditforum_' + result.cat_id).id = 'editforum_' + result.forum_id;
    var newforumtitle = 'forumtitle_' + result.forum_id;
    var newhideforum = 'hideforum_' + result.forum_id;
    var newshowforum = 'showforum_' + result.forum_id;
    var newcanceladdforum = 'canceladdforum_' + result.forum_id;
    $('forumtitle_cat' + result.cat_id).id = newforumtitle;
    $('hideforum_cat' + result.cat_id).id = newhideforum;
    $('showforum_cat' + result.cat_id).id = newshowforum;
    $('canceladdforum_cat' + result.cat_id).id = newcanceladdforum;
    $('progressforumimage_cat' + result.cat_id).id = 'progressforumimage_' + result.forum_id;
    Event.observe(newshowforum, 'click', function(){hideshowforum(result.forum_id)}, false);
    Event.observe(newhideforum, 'click', function(){hideshowforum(result.forum_id)}, false);
    Event.observe(newcanceladdforum, 'click', function(){canceladdforum(result.forum_id)}, false);
    Element.show(newhideforum);
    Element.hide(newshowforum);
    // append copied li to the ul - now we can add another new forum without 
    // needing to store the first one
    $('cid_' + result.cat_id).appendChild(newnewforum);
    pnf_toggleprogressimage(true, result.cat_id);
}

function addcategory()
{
    var pars = "module=pnForum&type=admin&func=editcategory&cat=new";
    var myAjax = new Ajax.Request(
        "index.php", 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: addcategoryinit
        });
}

function addcategoryinit(originalRequest)
{    
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    // copy newcategory li
    var newnewcategory = $('newcategory').cloneNode(true);
    // update existing newcategory li with data retreved from server
    Element.update('neweditcategory', result.data);
    // and show it
    Element.show('neweditcategory');
    Effect.toggle('newcategory', 'slide');
    //Element.show('newcategory');
    // set new id in newcategory li
    var newcategory = 'category_' + result.cat_id;
    $('newcategory').id = newcategory;

    $('neweditcategory').id = 'editcategory_' + result.cat_id;
    $('newcategorytitle').id = 'categorytitle_' + result.cat_id;

    var newhidecategory = 'hidecategory_' + result.cat_id;
    var newshowcategory = 'showcategory_' + result.cat_id;
    var newcanceladdcategory = 'canceladdcategory_' + result.cat_id;
    var newaddforum = 'addforum_' + result.cat_id;

    $('newhidecategory').id = newhidecategory;
    $('newshowcategory').id = newshowcategory;
    $('newcanceladdcategory').id = newcanceladdcategory;
    $('newaddforum').id = newaddforum;

    Event.observe(newshowcategory, 'click', function(){hideshowcategory(result.cat_id)}, false);
    Event.observe(newhidecategory, 'click', function(){hideshowcategory(result.cat_id)}, false);
    Event.observe(newcanceladdcategory, 'click', function(){canceladdcategory(result.cat_id)}, false);
    Element.show(newhidecategory);
    Element.hide(newshowcategory);
    Element.hide(newaddforum);
    // append copied li to the ul - now we can add another new category without 
    // needing to store the first one
    $('category').appendChild(newnewcategory);
    Element.scrollTo(newcategory);
}

function canceladdcategory(catid)
{
    Effect.toggle('category_' + catid, 'slide');
    Element.remove('category_' + catid);
}

function canceladdforum(forumid)
{
    Effect.toggle('forum_' + forumid, 'slide');
    Element.remove('forum_' + forumid);
}

function storetreeorder(containmentsarray)
{
    if(treeorderstatus == false) {
        showpnforuminfo(storingnewsortorder);
        treeorderstatus = true
        var pars = 'module=pnForum&type=admin&func=reordertreesave&' + Sortable.serialize('category');
        for(var j=0; j < containmentsarray.length; j++) {
            pars = pars + '&' + Sortable.serialize(containmentsarray[j]);
        }
        pars = pars + '&authid=' + $F('authid');

        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: storetreeorder_response
            });
    }
    
}

function storetreeorder_response(originalRequest, json)
{
    hidepnforuminfo();
    treeorderstatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    updateAuthid(json.authid);
}


function hideshowforum(forumid)
{
//    Element.toggle('editforum_' + forumid);
    Effect.toggle('editforum_' + forumid, 'slide');
    Element.toggle('hideforum_' + forumid);
    Element.toggle('showforum_' + forumid);
}

function hideshowcategory(catid)
{
//    Element.toggle('editcategory_' + catid);
    Effect.toggle('editcategory_' + catid, 'slide');
    Element.toggle('hidecategory_' + catid);
    Element.toggle('showcategory_' + catid);
}

function storenewforumorder()
{
    if(forumliststatus == false) {
        showpnforuminfo(storingnewsortorder);
        forumliststatus = true;
        var pars = 'module=pnForum&type=admin&func=newforumordersave&' + Sortable.serialize('forums') + '&cat_id=' + $F('cat_id') + '&authid=' + $F('authid');
//alert(pars);
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: storenewforumorder_response
            });
    }
    
}

function storenewforumorder_response(originalRequest)
{
    hidepnforuminfo();
    forumliststatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);
    updateAuthid(result.authid);
}

function sortforuminit(containmentsarray)
{
    for(var j=0; j < containmentsarray.length; j++) {
        Sortable.create(containmentsarray[j],
                        {dropOnEmpty: true,
                         containment: containmentsarray,
                         constraint: false
                        });
    }
    Sortable.create("category");
}

function showpnforuminfo(infotext)
{
    Element.update('pnforuminformation', infotext);
    $('pnforuminformation').style.visibility = 'visible';
    
}

function hidepnforuminfo()
{
    Element.update('pnforuminformation', '&nbsp;');
    $('pnforuminformation').style.visibility = 'hidden';
}

function dejsonize(jsondata)
{
    var result;
    try {
        result = eval('(' + jsondata + ')');
    } catch(error) {
        alert('illegal JSON response: ' + error + 'in\n' + jsondata);
    }
    return result;
}

/*
 * show an ajax error
 * to-do: beautify this function
 */
function pnf_showajaxerror(error)
{
    alert(error);
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

function pnf_toggleprogressimage(typ, id)
{
    // typ true = category (id=cat_id), false=forum (id=forum_id)
    var imageid;
    if(typ==true) {
        imageid = 'progresscategoryimage_' + id;
    } else {
        imageid = 'progressforumimage_' + id;
    }        
    if($(imageid)) {
        if($(imageid).style.visibility == 'hidden') {
            $(imageid).style.visibility = '';
        } else {
            $(imageid).style.visibility = 'hidden';
        }
    }

}