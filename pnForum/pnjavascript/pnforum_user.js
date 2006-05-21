/**
 * pnforum.js
 *
 * $Id$
 *
 */

var editstatus = false;
var replystatus = false;
var editchanged = false;
var lockstatus = false;
var stickystatus = false;
var subscribestatus = false;
var subscribeforumstatus = false;
var favoritestatus = false;
var subjectstatus = false;
var sortorderstatus = false;
var newtopicstatus = false;

var pnf_globalhandlers = {
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

function createnewtopic()
{
    if(newtopicstatus==false) {
        if($F('subject') == '') {
            // no subject
            return;
        }
        if($F('message') == '') {
            // no text
            return;
        }
        
        newtopicstatus = true;
        showpnforuminfo(storingPost);
        
        var pars = 'module=pnForum&type=ajax&func=newtopic' +  
                   '&forum=' + $F('forum') + 
                   '&subject=' + encodeURIComponent($F('subject')) +
                   '&message=' + encodeURIComponent($F('message')) +              
                   '&attach_signature=' + getcheckboxvalue('attach_signature') +            
                   '&subscribe_topic=' + getcheckboxvalue('subscribe_topic') + 
                   '&authid=' + $F('authid');

        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(                              
                        "index.php",                                
                        {                                           
                            method: 'post',                         
                            parameters: pars,                       
                            onComplete: createnewtopic_response
                        }                                           
                        );              
    }
}

function createnewtopic_response(originalRequest)
{
    hidepnforuminfo();

    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        newtopicstatus = false;
        return;
    }

    newtopicstatus = false;
    var json = dejsonize(originalRequest.responseText);

    if((json.confirmation == false) || !$('newtopicconfirmation')) {
        showpnforuminfo(redirecting);
    } else {
        Element.hide('newtopic');
        Element.update('newtopicconfirmation', json.confirmation);
        Element.show('newtopicconfirmation');
    }   
    window.setTimeout("pnf_redirect('" + json.redirect + "');", 3000);
}

function previewnewtopic()
{
    if(newtopicstatus==false) {
        newtopicstatus = true;
        showpnforuminfo(preparingPreview);
        
        var pars = "module=pnForum&type=ajax&func=newtopic" +   
                   "&subject=" + encodeURIComponent($F('subject')) +
                   "&message=" + encodeURIComponent($F('message')) +              
                   "&preview=1" +
                   "&authid=" + $F('authid');
        
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(                              
                        "index.php",                                
                        {                                           
                            method: 'post',                         
                            parameters: pars,                       
                            onComplete: previewnewtopic_response
                        }                                           
                        );              
    }
}

function previewnewtopic_response(originalRequest)
{
    hidepnforuminfo();
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        newtopicstatus = false;
        return;
    }

    var json = dejsonize(originalRequest.responseText);

    updateAuthid(json.authid);
    Element.update('newtopicpreview', json.data);
    Element.show('newtopicpreview');
    newtopicstatus = false;
}

function clearnewtopic()
{
    $('message').value = '';
    $('subject').value = '';
    Element.update('newtopicpreview', '&nbsp;');
    Element.hide('newtopicpreview');
    newtopicstatus = false;
}                        


function changesortorder()
{
    if(sortorderstatus == false) {
        sortorderstatus = true;
        var pars = "module=pnForum&type=ajax&func=changesortorder&authid=" + $F('authid');
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: changesortorder_response
            });
    }
}

function changesortorder_response(originalRequest)
{
    sortorderstatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var json = dejsonize(originalRequest.responseText);
    updateAuthid(json.authid);
    
    switch(json.data) {
        case 'desc':
            Element.hide('sortorder_asc');
            Element.show('sortorder_desc');
            break;
        case 'asc':
            Element.hide('sortorder_desc');
            Element.show('sortorder_asc');
            break;
        default:
             alert('wrong result from changesortorder');
    }
}

function topicsubjectedit(topicid)
{
    if(subjectstatus == false) {
        subjectstatus = true;
        var pars = "module=pnForum&type=ajax&func=edittopicsubject&topic=" + topicid;
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: topicsubjecteditinit
            });
    }
}

function topicsubjecteditinit(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        subjectstatus = false;
        return;
    }
    
    var result = dejsonize(originalRequest.responseText);

    var topicsubjectID = 'topicsubject_' + result.topic_id;

    Element.hide(topicsubjectID);
    updateAuthid(result.authid);

    new Insertion.After($(topicsubjectID), result.data);    
}

function topicsubjecteditcancel(topicid)
{
    var topicsubjectID = 'topicsubject_' + topicid;

    Element.remove(topicsubjectID + '_editor');
    Element.show(topicsubjectID);
    subjectstatus = false;
}

function topicsubjecteditsave(topicid)
{
    var topicsubjectID = 'topicsubject_' + topicid;
    var editID = topicsubjectID + '_edit';
    var authID = topicsubjectID + '_authid';
    if($F(editID) == '') {
        // no text
        return;
    }

    var pars = "module=pnForum&type=ajax&func=updatetopicsubject" +   
               "&topic=" + topicid +
               "&subject=" + encodeURIComponent($F(editID)) +
               "&authid=" + $F(authID);
    Ajax.Responders.register(pnf_globalhandlers);
    var myAjax = new Ajax.Request(                              
                    "index.php",                                
                    {                                           
                        method: 'post',                         
                        parameters: pars,                       
                        onComplete: topicsubjecteditsave_response
                    }                                           
                    );              

}

function topicsubjecteditsave_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        subjectstatus = false;
        return;
    }

    var result = dejsonize(originalRequest.responseText);
    var topicsubjectID = 'topicsubject_' + result.topic_id;

    Element.remove(topicsubjectID + '_editor');
    updateAuthid(result.authid);
    
    Element.update(topicsubjectID + '_content', result.topic_title);
    Element.show(topicsubjectID);

    subjectstatus = false;
}

function toggleuserinfo(postid)
{
    var userinfoID = 'posting_' + postid + '_userinfo';
    var postingtextID = 'postingtext_' + postid;
    
    if(Element.visible(userinfoID) == false) {
        Element.removeClassName(postingtextID, 'postingtext_big');
        Element.addClassName(postingtextID, 'postingtext_small');
        Element.show(userinfoID);
    } else {
        Element.hide(userinfoID);
        Element.removeClassName(postingtextID, 'postingtext_small');
        Element.addClassName(postingtextID, 'postingtext_big');
    } 
    Event.observe(postingtextID, 'click', function(){toggleuserinfo(postid)}, false);       
}

function addremovefavorite(forumid, mode)
{
    if(favoritestatus == false) {
        favoritestatus = true;
        var pars = "module=pnForum&type=ajax&func=addremovefavorite&forum=" + forumid + "&mode=" + mode;
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: addremovefavorite_response
            });
    }
}

function addremovefavorite_response(originalRequest)
{
    favoritestatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.newmode) {
        case 'added':
            Element.hide('addfavoritebutton_' + result.forum_id);
            Element.show('removefavoritebutton_' + result.forum_id);
            break;
        case 'removed':
            Element.hide('removefavoritebutton_' + result.forum_id);
            Element.show('addfavoritebutton_' + result.forum_id);
            break;
        default:
             alert('wrong result from add/remove favorite');
    }
}

function subscribeunsubscribeforum(forumid, mode)
{
    if(subscribeforumstatus == false) {
        subscribeforumstatus = true;
        var pars = "module=pnForum&type=ajax&func=subscribeunsubscribeforum&forum=" + forumid + "&mode=" + mode;
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: subscribeunsubscribeforum_response
            });
    }
}

function subscribeunsubscribeforum_response(originalRequest)
{
    subscribeforumstatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.newmode) {
        case 'subscribed':
            Element.hide('subscribeforumbutton_' + result.forum_id);
            Element.show('unsubscribeforumbutton_' + result.forum_id);
            break;
        case 'unsubscribed':
            Element.hide('unsubscribeforumbutton_' + result.forum_id);
            Element.show('subscribeforumbutton_' + result.forum_id);
            break;
        default:
             alert('wrong result from subscribe/unsubscribe');
    }
}

function subscribeunsubscribetopic(topicid, mode)
{
    if(subscribestatus == false) {
        subscribestatus = true;
        var pars = "module=pnForum&type=ajax&func=subscribeunsubscribetopic&topic=" + topicid + "&mode=" + mode;
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: subscribeunsubscribetopic_response
            });
    }
}

function subscribeunsubscribetopic_response(originalRequest)
{
    subscribestatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.data) {
        case 'subscribed':
            Element.hide('subscribetopicbutton');
            Element.show('unsubscribetopicbutton');
            break;
        case 'unsubscribed':
            Element.hide('unsubscribetopicbutton');
            Element.show('subscribetopicbutton');
            break;
        default:
             alert('wrong result from subscribe/unsubscribe');
    }
}

function stickyunstickytopic(topicid, mode)
{
    if(stickystatus == false) {
        stickystatus = true;
        var pars = "module=pnForum&type=ajax&func=stickyunstickytopic&topic=" + topicid + "&mode=" + mode;
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: stickyunstickytopic_response
            });
    }
}

function stickyunstickytopic_response(originalRequest)
{
    stickystatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.data) {
        case 'sticky':
            Element.hide('stickytopicbutton');
            Element.show('unstickytopicbutton');
            break;
        case 'unsticky':
            Element.hide('unstickytopicbutton');
            Element.show('stickytopicbutton');
            break;
        default:
             alert('wrong result from sticky/unsticky');
    }
}

function lockunlocktopic(topicid, mode)
{
    if(lockstatus == false) {
        lockstatus = true;
        var pars = "module=pnForum&type=ajax&func=lockunlocktopic&topic=" + topicid + "&mode=" + mode;
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: lockunlocktopic_response
            });
    }
}

function lockunlocktopic_response(originalRequest)
{
    lockstatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.data) {
        case 'locked':
            Element.hide('locktopicbutton');
            Element.show('unlocktopicbutton');
            break;
        case 'unlocked':
            Element.hide('unlocktopicbutton');
            Element.show('locktopicbutton');
            break;
        default:
             alert('wrong result from lock/unlock');
    }
}

function quickEdit(postid)
{
    if(editstatus == false) {
        editstatus = true;
        editchanged = false;
        var pars = "module=pnForum&type=ajax&func=editpost&post=" + postid;
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: quickEditInit
            });
    }
}

function quickEditInit(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        editstatus = false;
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    var postingtextID = 'postingtext_' + result.post_id;
    var postinguserID = 'posting_' + result.post_id + '_userinfo';
    
    Element.hide(postingtextID);
    Element.hide(postinguserID);
    updateAuthid(result.authid);

    new Insertion.After($(postingtextID), result.data); 

    Resizable.initialize('postingtext_' + result.post_id + '_edit');

    Event.observe(postingtextID + '_edit',   'keyup', function(){quickEditchanged(result.post_id)}, false);      
    Event.observe(postingtextID + '_save',   'click',  function(){quickEditsave(result.post_id)}, false);
    Event.observe(postingtextID + '_cancel', 'click',  function(){quickEditcancel(result.post_id)}, false);
}

function quickEditchanged(postid)
{
    if(editchanged == false) {
        editchanged = true;
        var postingtextstatusID = 'postingtext_' + postid + '_status';
        Element.update(postingtextstatusID, '<span style="color: red;">' + statusChanged + '</span>');
    }
    return;
}

function quickEditcancel(postid)
{
    var postingtextID = 'postingtext_' + postid;
    var postinguserID = 'posting_' + postid + '_userinfo';
    Element.show(postingtextID);
    Element.show(postinguserID);
    Element.remove(postingtextID + '_editor');
    editstatus = false;
}
function quickEditsave(postid)
{
    var postingtextID = 'postingtext_' + postid;
    var statusID = postingtextID + '_status';
    var deletepost;
    var editID = postingtextID + '_edit';
    var authID = postingtextID + '_authid';
    
    if($F(editID) == '') {
        // no text
        return;
    }

    if($(postingtextID + '_delete') && $(postingtextID + '_delete').checked == true) {
        Element.update(statusID, '<span style="color: red;">' + deletingPost + '</span>');
        deletepost = '&delete=1';
    } else {
        Element.update(statusID, '<span style="color: red;">' + updatingPost + '</span>');
        deletepost = '';
    }
    var pars = "module=pnForum&type=ajax&func=updatepost" +   
               "&post=" + postid +
               deletepost +
               "&message=" + encodeURIComponent($F(editID)) +
               "&authid=" + $F(authID);

    Ajax.Responders.register(pnf_globalhandlers);
    var myAjax = new Ajax.Request(                              
                    "index.php",                                
                    {                                           
                        method: 'post',                         
                        parameters: pars,                       
                        onComplete: quickEditsave_response
                    }                                           
                    );              
    
}

function quickEditsave_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        editstatus = false;
        return;
    }

    var result = dejsonize(originalRequest.responseText);
    
    var postingtextID = 'postingtext_' + result.post_id;
    var postingobjID = 'posting_' + result.post_id;
    var postinguserID = postingobjID + '_userinfo';
    updateAuthid(result.authid);
    
    Element.remove(postingtextID + '_editor');
    
    if(result.action == 'deleted') {
        Element.remove(postingobjID);
    } else {
        Element.update(postingtextID, result.post_text);
        Element.show(postingtextID);
        Element.show(postinguserID);
    }
    editstatus = false;
}

function createQuote(postid)
{
    var pars = "module=pnForum&type=ajax&func=preparequote&post=" + postid;
    Ajax.Responders.register(pnf_globalhandlers);
    var myAjax = new Ajax.Request(
        "index.php", 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: createQuoteInit
        });
    
}

function createQuoteInit(originalRequest)
{
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }    

    var oldvalue = $('message').value;
    if(oldvalue.length != 0) {
        oldvalue += '\n\n';
    }
    var result = dejsonize(originalRequest.responseText);
    
    $('message').value = oldvalue + result.message  + '\n';
    Field.focus('message');
}

function createQuickReply()
{
    if(replystatus==false) {
        if($F('message') == '') {
            return;
        }
        replystatus = true;
        showpnforuminfo(storingReply);
        
        var pars = 'module=pnForum&type=ajax&func=reply' +   
                   '&topic=' + $F('topic') +
                   '&message=' + encodeURIComponent($F('message')) +              
                   '&attach_signature=' + getcheckboxvalue('attach_signature') +            
                   '&subscribe_topic=' + getcheckboxvalue('subscribe_topic') + 
                   '&authid=' + $F('authid');
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(                              
                        "index.php",                                
                        {                                           
                            method: 'post',                         
                            parameters: pars,                       
                            onComplete: createQuickReply_response
                        }                                           
                        );              
    }
}

function createQuickReply_response(originalRequest)
{
    hidepnforuminfo();

    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        replystatus = false;
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    updateAuthid(result.authid);
    
    // clear textarea
    $('message').value = '';
    
    // reset preview
    Element.update('quickreplypreview', '&nbsp;');
    Element.hide('quickreplypreview');
    
    // show new posting
    Element.update('quickreplyposting', result.data);
    Element.show('quickreplyposting');
    
    // prepare everything for another quick reply
    new Insertion.After('quickreplyposting', '<li id="new_quickreplyposting"></li>');
    // clear old id
    $('quickreplyposting').id = '';
    // rename new id
    $('new_quickreplyposting').id = 'quickreplyposting';

    replystatus = false;

}

function previewQuickReply()
{
    if(replystatus==false) {
        replystatus = true;
        showpnforuminfo(preparingPreview);
        
        var pars = "module=pnForum&type=ajax&func=reply" +   
                   "&topic=" + $F('topic') +
                   "&message=" + encodeURIComponent($F('message')) +              
                   "&preview=1" +
                   "&authid=" + $F('authid');
        
        Ajax.Responders.register(pnf_globalhandlers);
        var myAjax = new Ajax.Request(                              
                        "index.php",                                
                        {                                           
                            method: 'post',                         
                            parameters: pars,                       
                            onComplete: previewQuickReply_response
                        }                                           
                        );              
    }
}

function previewQuickReply_response(originalRequest)
{
    hidepnforuminfo();

    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        replystatus = false;
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    updateAuthid(result.authid);
    Element.update('quickreplypreview', result.data);
    Element.show('quickreplypreview');
    replystatus = false;
}

function clearQuickReply()
{
    $('message').value = '';
    Element.update('quickreplypreview', '&nbsp;');
    Element.hide('quickreplypreview');
    replystatus = false;
}                        
