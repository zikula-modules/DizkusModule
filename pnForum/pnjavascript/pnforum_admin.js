/**
 * pnforum_admin.js
 *
 * $Id$
 *
 */

var containments = new Array();
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

    pnf_toggleprogressimage(false, result.forum_id);
    Element.update('editforum_' + result.forum_id, result.data);
    Effect.toggle('editforum_' + result.forum_id, 'slide');
    Event.observe('showforum_' + result.forum_id, 'click', function(){hideshowforum(result.forum_id)}, false);
    Event.observe('hideforum_' + result.forum_id, 'click', function(){hideshowforum(result.forum_id)}, false);
    Element.show('hideforum_' + result.forum_id);
    Element.hide('showforum_' + result.forum_id);
    Element.hide('loadforum_' + result.forum_id);
}

function loadcategory(catid)
{
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

function editcategory(originalRequest, json)
{    
    pnf_toggleprogressimage(true, json.cat_id);
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    Element.update('editcategory_' + json.cat_id, json.data);
    Effect.toggle('editcategory_' + json.cat_id, 'slide');
    Event.observe('showcategory_' + json.cat_id, 'click', function(){hideshowcategory(json.cat_id)}, false);
    Event.observe('hidecategory_' + json.cat_id, 'click', function(){hideshowcategory(json.cat_id)}, false);
    Element.hide('loadcategory_' + json.cat_id);
    Element.hide('showcategory_' + json.cat_id);
    Element.show('hidecategory_' + json.cat_id);
}

function storeforum(forumid)
{
    pnf_toggleprogressimage(false, forumid);
    var pars = "module=pnForum&type=admin&func=storeforum&" + Form.serialize('editforumform_'+ forumid);
    var myAjax = new Ajax.Request(
        "index.php", 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: storeforum_response
        });
}

function storeforum_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var json = dejsonize(originalRequest.responseText);
    updateAuthid(json.authid);
    switch(json.action) {
        case 'delete':
            var forum = 'forum_' + json.old_id;
            // hide it
            Effect.toggle(forum, 'slide');
            // check if there are more forums, if not, show place holder
            if($(forum).parentNode.childNodes.length == 3) {
                // 3 = this forum li, emptycategory li  + newforum li
                // after removing it the list will be virtually empty
                Element.show('emptycategory_' + json.cat_id);
                $('deletecategory_' + json.cat_id).style.visibility = '';
            } else {
                $('deletecategory_' + json.cat_id).style.visibility = 'hidden';
            }
            // remove it
            Element.remove(forum);
            break;
        case 'update':
            Element.update('forumtitle_' + json.forum.forum_id, json.forumtitle);
            Element.update('editforum_' + json.forum.forum_id, json.editforumhtml);
            pnf_toggleprogressimage(false, json.forum.forum_id);
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
            $('hideforum_' + json.old_id).id = newhideforum;
            Event.observe(newhideforum, 'click', function(){hideshowforum(json.forum.forum_id)}, false);
            Element.show(newhideforum);
            
            var newshowforum = 'showforum_' + json.forum.forum_id;
            $('showforum_' + json.old_id).id = newshowforum;
            Event.observe(newshowforum, 'click', function(){hideshowforum(json.forum.forum_id)}, false);
            Element.hide(newshowforum);

            Element.remove('canceladdforum_' + json.old_id);
            pnf_toggleprogressimage(false, json.old_id);
            $('progressforumimage_' + json.old_id).id = 'progressforumimage_' + json.forum.forum_id;

            $('deletecategory_' + json.cat_id).style.visibility = 'hidden';
            break;
        default:
            pnf_showajaxerror('storeforum_response(): received illegal action type from server');   
    }
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

function storecategory_response(originalRequest, json)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    var json = dejsonize(originalRequest.responseText);

    pnf_toggleprogressimage(true, json.old_id);
    updateAuthid(json.authid);
    
    switch(json.action) {
        case 'add':
            $('category_' + json.old_id).id = 'category_' + json.cat_id;
            
            var newhidecategory = 'hidecategory_' + json.cat_id;
            $('hidecategory_' + json.old_id).id = newhidecategory;
            Event.observe(newhidecategory, 'click', function(){hideshowcategory(json.cat_id)}, false);
            
            var newshowcategory = 'showcategory_' + json.cat_id;
            $('showcategory_' + json.old_id).id = newshowcategory;
            Event.observe(newshowcategory, 'click', function(){hideshowcategory(json.cat_id)}, false);

            var newcategorytitle = 'categorytitle_' + json.cat_id;
            $('categorytitle_' + json.old_id).id = newcategorytitle;
            Element.update(newcategorytitle, '<a href="' + json.cat_linkurl + '">' + json.cat_title + '</a> (' + json.cat_id + ')');
            
            var newaddforum = 'addforum_' + json.cat_id;
            $('addforum_' + json.old_id).id = newaddforum;
            Element.show(newaddforum);
            Event.observe(newaddforum, 'click', function(){addforum(json.cat_id)}, false);

            var newhideforumlist = 'hideforumlist_' + json.cat_id;
            $('hideforumlist_' + json.old_id).id = newhideforumlist;
            Element.show(newhideforumlist);
            Event.observe(newhideforumlist, 'click', function(){hideshowforumlist(json.cat_id)}, false);
            
            var newshowforumlist = 'showforumlist_' + json.cat_id;
            $('showforumlist_' + json.old_id).id = newshowforumlist;
            Element.hide(newshowforumlist);
            Event.observe(newshowforumlist, 'click', function(){hideshowforumlist(json.cat_id)}, false);

            var newprogresscategoryimage = 'progresscategoryimage_' + json.cat_id;
            $('progresscategoryimage_' + json.old_id).id = newprogresscategoryimage;
            $(newprogresscategoryimage).style.visibilty = 'hidden';

            
            var newcid = 'cid_' + json.cat_id;
            $('cid_' + json.old_id).id = newcid;
            Element.show(newcid);

            
            var newemptycategory = 'emptycategory_' + json.cat_id;
            $('emptycategory_' + json.old_id).id = newemptycategory;
            Element.show(newemptycategory);


            Element.remove('canceladdcategory_' + json.old_id);
            var neweditcategory = 'editcategory_' + json.cat_id;          

            $('editcategory_' + json.old_id).id = neweditcategory;
            Element.update(neweditcategory, json.edithtml);

            // new forum li
            var newforum = 'newforum_cat' + json.cat_id;
            $('newforum_cat' + json.old_id).id = newforum;
            
            var newforumtitle = 'forumtitle_cat' + json.cat_id;   
            $('forumtitle_cat' + json.old_id).id = newforumtitle;
            
            var newhideforum = 'hideforum_cat' + json.cat_id;
            $('hideforum_cat' + json.old_id).id = newhideforum;
            
            var newshowforum = 'showforum_cat' + json.cat_id;   
            $('showforum_cat' + json.old_id).id = newshowforum;
           
            var newcanceladdforum = 'canceladdforum_cat' + json.cat_id; 
            $('canceladdforum_cat' + json.old_id).id = newcanceladdforum;
            
            var newprogressforumimage = 'progressforumimage_cat' + json.cat_id;
            $('progressforumimage_cat' + json.old_id).id = newprogressforumimage;
            
            var neweditforum = 'neweditforum_' + json.cat_id; 
            $('neweditforum_' + json.old_id).id = neweditforum;
            
            break;
        case 'update':
            Element.update('categorytitle_' + json.cat_id, '<a href="' + json.cat_linkurl + '">' + json.cat_title + '</a> (' + json.cat_id + ')');
            break;
        case 'delete':
            Element.remove('category_' + json.cat_id);
            break;
        default:
            pnf_showajaxerror('unknown action received from server');
    }
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

    pnf_toggleprogressimage(true, result.cat_id);
    // copy newforum li
    var newnewforum = $('newforum_cat' + result.cat_id).cloneNode(true);

    // update existig newforum li with data retreved from server
    // and show it
    var neweditforum = 'editforum_' + result.forum_id;
    $('neweditforum_' + result.cat_id).id = neweditforum;
    Element.update(neweditforum, result.data);
    Element.show(neweditforum);
    
    // set new id in newforum li
    var newforum = 'forum_' + result.forum_id;
    $('newforum_cat' + result.cat_id).id = newforum;
    Element.show(newforum);

    var newforumtitle = 'forumtitle_' + result.forum_id;
    $('forumtitle_cat' + result.cat_id).id = newforumtitle;

    var newhideforum = 'hideforum_' + result.forum_id;
    $('hideforum_cat' + result.cat_id).id = newhideforum;
    Event.observe(newhideforum, 'click', function(){hideshowforum(result.forum_id)}, false);
    Element.show(newhideforum);

    var newshowforum = 'showforum_' + result.forum_id;
    $('showforum_cat' + result.cat_id).id = newshowforum;
    Event.observe(newshowforum, 'click', function(){hideshowforum(result.forum_id)}, false);
    Element.hide(newshowforum);

    var newcanceladdforum = 'canceladdforum_' + result.forum_id;
    $('canceladdforum_cat' + result.cat_id).id = newcanceladdforum;
    Event.observe(newcanceladdforum, 'click', function(){canceladdforum(result.forum_id, result.cat_id)}, false);

    $('progressforumimage_cat' + result.cat_id).id = 'progressforumimage_' + result.forum_id;

    // append copied li to the ul - now we can add another new forum without 
    // needing to store the first one
    $('cid_' + result.cat_id).appendChild(newnewforum);
    if(Element.visible('cid_' + result.cat_id) == false) {
        hideshowforumlist(result.cat_id);
    }
    Element.hide('emptycategory_' + result.cat_id);
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

function addcategoryinit(originalRequest, json)
{    
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    // copy newcategory li
    var newnewcategory = $('newcategory').cloneNode(true);
    
    // update existing newcategory li with data retreved from server
    Element.update('neweditcategory', json.data);
    // and show it
    
    // set new id in newcategory li
    var newcategory = 'category_' + json.cat_id;
    $('newcategory').id = newcategory;

    var neweditcategory = 'editcategory_' + json.cat_id;
    $('neweditcategory').id = neweditcategory;
    Element.show(neweditcategory);

    $('newcategorytitle').id = 'categorytitle_' + json.cat_id;

    var newhidecategory = 'hidecategory_' + json.cat_id;
    $('newhidecategory').id = newhidecategory;
    Event.observe(newhidecategory, 'click', function(){hideshowcategory(json.cat_id)}, false);
    Element.show(newhidecategory);
    
    var newshowcategory = 'showcategory_' + json.cat_id;
    $('newshowcategory').id = newshowcategory;
    Event.observe(newshowcategory, 'click', function(){hideshowcategory(json.cat_id)}, false);
    Element.hide(newshowcategory);

    var newhideforumlist = 'hideforumlist_' + json.cat_id;
    $('newhideforumlist').id = newhideforumlist;
    Element.hide(newhideforumlist);
    
    var newshowforumlist = 'showforumlist_' + json.cat_id;
    $('newshowforumlist').id = newshowforumlist;
    Element.hide(newshowforumlist);

    var newprogresscategoryimage = 'progresscategoryimage_' + json.cat_id;
    $('newprogresscategoryimage').id = newprogresscategoryimage;
    
    var newcanceladdcategory = 'canceladdcategory_' + json.cat_id;
    $('newcanceladdcategory').id = newcanceladdcategory;
    Event.observe(newcanceladdcategory, 'click', function(){canceladdcategory(json.cat_id)}, false);
    
    var newaddforum = 'addforum_' + json.cat_id;
    $('newaddforum').id = newaddforum;
    Element.hide(newaddforum);

    var newcid = 'cid_' + json.cat_id;
    $('newcid').id = newcid;
    
    var newemptycategory = 'emptycategory_' + json.cat_id;
    $('newemptycategory').id = newemptycategory;

    // new forum li
    var newforum = 'newforum_cat' + json.cat_id;
    $('newforum').id = newforum;
    
    var newforumtitle = 'forumtitle_cat' + json.cat_id;   
    $('newforumtitle').id = newforumtitle;
    
    var newhideforum = 'hideforum_cat' + json.cat_id;
    $('newhideforum').id = newhideforum;
    
    var newshowforum = 'showforum_cat' + json.cat_id;   
    $('newshowforum').id = newshowforum;
    
    var newcanceladdforum = 'canceladdforum_cat' + json.cat_id; 
    $('newcanceladdforum').id = newcanceladdforum;
    
    var newprogressforumimage = 'progressforumimage_cat' + json.cat_id;
    $('newprogressforumimage').id = newprogressforumimage;
    
    var neweditforum = 'neweditforum_' + json.cat_id; 
    $('neweditforum').id = neweditforum;

    // append copied li to the ul - now we can add another new category without 
    // needing to store the first one
    $('category').appendChild(newnewcategory);

    Effect.toggle(newcategory, 'slide');
}

function canceladdcategory(catid)
{
    Effect.toggle('category_' + catid, 'slide');
    Element.remove('category_' + catid);
}

function canceladdforum(forumid, catid)
{
    var forum = 'forum_' + forumid;
    // hide it
    Effect.toggle(forum, 'slide');
    // check if there are more forums, if not, show place holder
    if($(forum).parentNode.childNodes.length == 3) {
        // 3 = this forum li, emptycategory li  + newforum li
        // after removing it the list will be virtually empty
        Element.show('emptycategory_' + catid);
    }
    // remove it
    Element.remove(forum);
}

function storetreeorder()
{
    if(treeorderstatus == false) {
        showpnforuminfo(storingnewsortorder);
        treeorderstatus = true
        var pars = 'module=pnForum&type=admin&func=reordertreesave&' + Sortable.serialize('category');
        for(var j=0; j < containments.length; j++) {
            pars = pars + '&' + Sortable.serialize(containments[j]);
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
    Effect.toggle('editforum_' + forumid, 'slide');
    Element.toggle('hideforum_' + forumid);
    Element.toggle('showforum_' + forumid);
    return;
}

function hideshowcategory(catid)
{
    Effect.toggle('editcategory_' + catid, 'slide');
    Element.toggle('hidecategory_' + catid);
    Element.toggle('showcategory_' + catid);
    return;
}

function hideshowforumlist(catid)
{
    Effect.toggle('cid_' + catid, 'slide');
    Element.toggle('hideforumlist_' + catid);
    Element.toggle('showforumlist_' + catid);
}

function storenewforumorder()
{
    if(forumliststatus == false) {
        showpnforuminfo(storingnewsortorder);
        forumliststatus = true;
        var pars = 'module=pnForum&type=admin&func=newforumordersave&' + Sortable.serialize('forums') + '&cat_id=' + $F('cat_id') + '&authid=' + $F('authid');
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: storenewforumorder_response
            });
    }
    
}

function storenewforumorder_response(originalRequest, json)
{
    hidepnforuminfo();
    forumliststatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    updateAuthid(json.authid);
}

function create_sortables()
{
    /* var cids = $$('.pnf_treeforumlist');
    if(cids.length > 0) {
        for(var i=0; i<cids.length; i++) {
            containments[containments.length] = cids[i].id;
        }
    }
    */
    // create containments array
    $$('.pnf_treeforumlist').each(
        function(containment)
        {
            containments[containments.length] = containment.id;
        }
        );
    
    containments.each(
    function(containment)
    {
        Sortable.create(containment,
                        {dropOnEmpty: true,
                         handle: 'pnf_handle',
                         overlap: 'horizontal',
                         containment: containments,
                         onUpdate: function() 
                             {
                             containments.each(
                                function(containment)
                                {
                                    // value is cid_X, but we need the X only
                                    if(containment != 'newcid') {
                                        var temp = containment.split('_');
                                        if($(containment).childNodes.length == 2) {
                                            Element.show('emptycategory_' + temp[1]);
                                        } else {
                                            Element.hide('emptycategory_' + temp[1]);
                                        }
                                    }
                                }
                                )
                             },    
                         constraint: false
                        });
    
    if($(containment).childNodes.length == 2) {
            if(containment != 'newcid') {
                var temp = containment.split('_');
                Element.show('emptycategory_' + temp[1]);
            }
        }
    });

    Sortable.create("category",
                    { 
                      handle: 'pnf_handle' 
                    });

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
    return;
}

function showextendedoptions(extsource, forumid)
{
    switch(extsource) {
        case 1:
            Element.show('pnlogindata_' + forumid);
            Element.show('mail2forum_' + forumid);
            Element.hide('rss2forum_' + forumid);
            break;
        case 2:
            Element.show('pnlogindata_' + forumid);
            Element.hide('mail2forum_' + forumid);
            Element.show('rss2forum_' + forumid);
            break
        default:
            Element.hide('pnlogindata_' + forumid);
            Element.hide('mail2forum_' + forumid);
            Element.hide('rss2forum_' + forumid);
    }
}
