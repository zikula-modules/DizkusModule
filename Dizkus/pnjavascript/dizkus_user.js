/**
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

// global setting of combination effect
var comboeffect = 'slide';
var comboparams = {duration: 1};

var indicatorimage = '<img src="' + document.location.pnbaseURL + 'modules/Dizkus/pnimages/ajaxindicator.gif" alt="" />';

var dzk_globalhandlers = {
    onCreate: function(){
        if($('dizkus')) {
            $('dizkus').style.cursor = 'wait';
        }
    },

    onComplete: function() {
        if(Ajax.activeRequestCount == 0){
            if($('dizkus')) {
                $('dizkus').style.cursor = 'auto';
            }
        }
    }
};

function createnewtopic(event)
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
        showdizkusinfo(indicatorimage + ' ' + storingPost);

        var pars = 'module=Dizkus&func=newtopic' +
                   '&forum=' + $F('forum_id') +
                   '&subject=' + encodeURIComponent($F('subject')) +
                   '&message=' + encodeURIComponent($F('message')) +
                   '&attach_signature=' + getcheckboxvalue('attach_signature') +
                   '&subscribe_topic=' + getcheckboxvalue('subscribe_topic') +
                   '&authid=' + $F('authid');

        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
                        document.location.pnbaseURL+'ajax.php',
                        {
                            method: 'post',
                            parameters: pars,
                            onComplete: createnewtopic_response
                        }
                        );
    }
    if (event) Event.stop(event);
}

function createnewtopic_response(originalRequest)
{
    hidedizkusinfo();
    newtopicstatus = false;
    
    // show error if necessary
    if( originalRequest.status != 200 ) {
        dzk_showajaxerror(originalRequest.responseText);
        return;
    }

    var json = dejsonize(originalRequest.responseText);

    if (json.error) {
        dzk_showajaxerror(json.error);
        updateAuthid(json.authid);
        return;
    }

    if ($('myuploadframe') && $('btnUpload') && json.uploadauthid) {
        newTopicUpload = true;
        newTopicRedirect = json.redirect;
        $('MediaAttach_redirect').value = json.uploadredirect;
        $('MediaAttach_objectid').value = json.uploadobjectid;
        updateAuthid(json.uploadauthid);
        $('btnUpload').click();
    }

    if (json.confirmation == false || !$('newtopicconfirmation')) {
        showdizkusinfo(redirecting);
    } else {
        Element.hide('dzk_newtopic');
        Element.update('newtopicconfirmation', json.confirmation);
        Element.show('newtopicconfirmation');
    }
    window.setTimeout("dzk_redirect('" + json.redirect + "');", 3000);
}

function previewnewtopic()
{
    if(newtopicstatus==false) {
        newtopicstatus = true;
        showdizkusinfo(indicatorimage + ' ' + preparingPreview);

        var pars = "module=Dizkus&func=newtopic" +
                   "&subject=" + encodeURIComponent($F('subject')) +
                   "&message=" + encodeURIComponent($F('message')) +
                   "&attach_signature=" + getcheckboxvalue('attach_signature') +
                   "&preview=1" +
                   "&authid=" + $F('authid');

        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
                        document.location.pnbaseURL+'ajax.php',
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
    hidedizkusinfo();
    // show error if necessary
    if( originalRequest.status != 200 ) {
        json = dejsonize(originalRequest.responseText);
        updateAuthid(json.authid);
        dzk_showajaxerror(json.data);
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
        var pars = "module=Dizkus&func=changesortorder&authid=" + $F('authid');
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+"ajax.php",
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
        dzk_showajaxerror(originalRequest.responseText);
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
        var pars = "module=Dizkus&func=edittopicsubject&topic=" + topicid;
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+"ajax.php",
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
        dzk_showajaxerror(originalRequest.responseText);
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

    var pars = "module=Dizkus&func=updatetopicsubject" +
               "&topic=" + topicid +
               "&subject=" + encodeURIComponent($F(editID)) +
               "&authid=" + $F(authID);
    Ajax.Responders.register(dzk_globalhandlers);
    var myAjax = new Ajax.Request(
                    document.location.pnbaseURL+"ajax.php",
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
        dzk_showajaxerror(originalRequest.responseText);
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
        var pars = "module=Dizkus&func=addremovefavorite&forum=" + forumid + "&mode=" + mode;
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+"ajax.php",
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
        dzk_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);

    if(['added', 'removed'].include(result.newmode)) {
        $('addfavoritebutton_'  + result.forum_id).toggleClassName('hidden');
        $('removefavoritebutton_'  + result.forum_id).toggleClassName('hidden');
    } else {
         alert('wrong result from add/remove favorite');
    }
}

function subscribeunsubscribeforum(forumid, mode)
{
    if(subscribeforumstatus == false) {
        subscribeforumstatus = true;
        var pars = "module=Dizkus&func=subscribeunsubscribeforum&forum=" + forumid + "&mode=" + mode;
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+"ajax.php",
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
        dzk_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);

    if(['subscribed', 'unsubscribed'].include(result.newmode)) {
        $('subscribeforumbutton_'  + result.forum_id).toggleClassName('hidden');
        $('unsubscribeforumbutton_'  + result.forum_id).toggleClassName('hidden');        
    } else {
         alert('wrong result from subscribe/unsubscribe');
    }
}

function subscribeunsubscribetopic(topicid, mode)
{
    if(subscribestatus == false) {
        subscribestatus = true;
        var pars = "module=Dizkus&func=subscribeunsubscribetopic&topic=" + topicid + "&mode=" + mode;
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+"ajax.php",
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
        dzk_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);

    if(['subscribed', 'unsubscribed'].include(result.data)) {
        $('subscribetopicbutton').toggleClassName('hidden');
        $('unsubscribetopicbutton').toggleClassName('hidden');
    } else {
         alert('wrong result from subscribe/unsubscribe');
    }
}

function stickyunstickytopic(topicid, mode)
{
    if(stickystatus == false) {
        stickystatus = true;
        var pars = "module=Dizkus&func=stickyunstickytopic&topic=" + topicid + "&mode=" + mode;
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+"ajax.php",
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
        dzk_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);

    if(['sticky', 'unsticky'].include(result.data)) {
        $('stickytopicbutton').toggleClassName('hidden');
        $('unstickytopicbutton').toggleClassName('hidden');
    } else {
         alert('wrong result from sticky/unsticky');
    }
}

function lockunlocktopic(topicid, mode)
{
    if(lockstatus == false) {
        lockstatus = true;
        var pars = "module=Dizkus&func=lockunlocktopic&topic=" + topicid + "&mode=" + mode;
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+"ajax.php",
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
        dzk_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);

    if(['locked', 'unlocked'].include(result.data)) {
        $('locktopicbutton').toggleClassName('hidden');
        $('unlocktopicbutton').toggleClassName('hidden');
    } else {
         alert('wrong result from lock/unlock');
    }
}

function quickEdit(postid)
{
    if(editstatus == false) {
        editstatus = true;
        editchanged = false;
        var pars = "module=Dizkus&func=editpost&post=" + postid;
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+"ajax.php",
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
        dzk_showajaxerror(originalRequest.responseText);
        editstatus = false;
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    var postingtextID    = 'postingtext_' + result.post_id;
    var postinguserID    = 'posting_' + result.post_id + '_userinfo';
    var postingoptionsID = 'postingoptions_' + result.post_id;
    
    $(postingtextID).hide();
    $(postinguserID).hide();
    $(postingoptionsID).hide();
    updateAuthid(result.authid);
    
    // hide quickreply
    if($('dzk_quickreply')) {
        Effect.toggle($('dzk_quickreply'), comboeffect, comboparams);
    }

    new Insertion.After($(postingtextID), result.data);
    $('bbcode_'+postingtextID+'_edit').removeClassName('hidden');
    $$('.bbsmile_smilies').each(function(el) {
        el.removeClassName('bbsmile_smilies');
    });
    if($('smiliemodal')) {
        new Control.Modal($('smiliemodal'), {});
    }

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
    var postingoptionsID = 'postingoptions_' + postid;
    
    $(postingtextID).show();
    $(postinguserID).show();
    $(postingoptionsID).show();
    $(postingtextID + '_editor').remove();
    editstatus = false;

    // unhide quickreply
    if($('dzk_quickreply')) {
        Effect.toggle($('dzk_quickreply'), comboeffect, comboparams);
    }
}
function quickEditsave(postid)
{
    var postingtextID = 'postingtext_' + postid;
    var statusID = postingtextID + '_status';
    var postingoptionsID = 'postingoptions_' + postid;
    var deletepost;
    var editID = postingtextID + '_edit';
    var authID = postingtextID + '_authid';
    var sigID = postingtextID + '_attach_signature';

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
    var pars = 'module=Dizkus&func=updatepost' +
               '&post=' + postid +
               deletepost +
               '&message=' + encodeURIComponent($F(editID)) +
               '&authid=' + $F(authID) +
               '&attach_signature=' + getcheckboxvalue(sigID);

    Ajax.Responders.register(dzk_globalhandlers);
    var myAjax = new Ajax.Request(
                    document.location.pnbaseURL+"ajax.php",
                    {
                        method: 'post',
                        parameters: pars,
                        onComplete: quickEditsave_response
                    }
                    );

    $(postingoptionsID).show();

}

function quickEditsave_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) {
        dzk_showajaxerror(originalRequest.responseText);
        editstatus = false;
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    var postingtextID = 'postingtext_' + result.post_id;
    var postingobjID = 'posting_' + result.post_id;
    var postinguserID = postingobjID + '_userinfo';
    updateAuthid(result.authid);

    $(postingtextID + '_editor').remove();

    if(result.action == 'deleted') {
        $(postingobjID).remove();
    } else {
        $(postingtextID).update(result.post_text).show();
        $(postinguserID).show();
    }
    editstatus = false;

    // hide quickreply
    if($('dzk_quickreply')) {
        Effect.toggle($('dzk_quickreply'), comboeffect, comboparams);
    }
}

function createQuote(postid)
{
    // check if the user highlighted a text portion and quote this instead of loading the
    // posting text from the server
    var selection;
    if( window.getSelection )
    {
        selection = window.getSelection();
        if(selection) {
            quotetext = selection+ '';
            if(selection.anchorNode) {
                this.parentObj = selection.anchorNode.parentNode;
            }
        }
    }
    // opera
    else if( document.getSelection )
    {
        selection = document.getSelection();
        if(selection) {
            quotetext = selection;
            this.parentObj = selection.parent;
        }
    }
    // internet explorer
    else if(document.selection.createRange) {
        selection = document.selection.createRange();
        if(selection) {
            quotetext = selection.text;
            this.parentObj = selection.parentElement();
        }
    }
    quotetext.strip();
    if(quotetext.length == 0) {
        // read the messages text using ajax
        var pars = "module=Dizkus&func=preparequote&post=" + postid;
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL+"ajax.php",
            {
                method: 'post',
                parameters: pars,
                onComplete: createQuoteInit
            });
        return;
    }

    var oldvalue = $('message').value;
    if(oldvalue.length != 0) {
        oldvalue += '\n\n';
    }
    $('message').value = oldvalue + '[quote]' + quotetext  + '[/quote]\n';
    Field.focus('message');
    return;
}

function createQuoteInit(originalRequest)
{
    if( originalRequest.status != 200 ) {
        dzk_showajaxerror(originalRequest.responseText);
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

function createQuickReply(event)
{
    if(replystatus==false) {
        if($F('message') == '') {
            return;
        }
        replystatus = true;
        showdizkusinfo(indicatorimage + ' ' + storingReply);

        var pars = 'module=Dizkus&func=reply' +
                   '&topic=' + $F('topic') +
                   '&message=' + encodeURIComponent($F('message')) +
                   '&attach_signature=' + getcheckboxvalue('attach_signature') +
                   '&subscribe_topic=' + getcheckboxvalue('subscribe_topic') +
                   '&authid=' + $F('authid');
        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
                        document.location.pnbaseURL+"ajax.php",
                        {
                            method: 'post',
                            parameters: pars,
                            onComplete: createQuickReply_response
                        }
                        );
    }
    if (event) Event.stop(event);
}

function createQuickReply_response(originalRequest)
{
    hidedizkusinfo();

    // show error if necessary
    if( originalRequest.status != 200 ) {
        dzk_showajaxerror(originalRequest.responseText);
        replystatus = false;
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    updateAuthid(result.authid);

    // clear textarea and reset preview
    clearQuickReply()

    // show new posting
    $('quickreplyposting').update(result.data).removeClassName('hidden');

    // prepare everything for another quick reply
    new Insertion.After('quickreplyposting', '<li id="new_quickreplyposting"></li>');
    // clear old id
    $('quickreplyposting').id = '';
    // rename new id
    $('new_quickreplyposting').id = 'quickreplyposting';
    // enable js options in quickreply
    $$('ul.javascriptpostingoptions').each(function(el) { el.removeClassName('hidden'); });

    if ($('myuploadframe') && $('btnUpload') && result.uploadauthid) {
        updateAuthid(result.uploadauthid);
        $('btnUpload').click();
        updateAuthid(result.authid);
    }

    replystatus = false;

}

function previewQuickReply()
{
    if(replystatus==false) {
        replystatus = true;
        showdizkusinfo(indicatorimage + ' ' + preparingPreview);

        var pars = "module=Dizkus&func=reply" +
                   "&topic=" + $F('topic') +
                   "&message=" + encodeURIComponent($F('message')) +
                   "&attach_signature=" + getcheckboxvalue('attach_signature') +
                   "&preview=1" +
                   "&authid=" + $F('authid');

        Ajax.Responders.register(dzk_globalhandlers);
        var myAjax = new Ajax.Request(
                        document.location.pnbaseURL+"ajax.php",
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
    hidedizkusinfo();

    // show error if necessary
    if( originalRequest.status != 200 ) {
        dzk_showajaxerror(originalRequest.responseText);
        replystatus = false;
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    updateAuthid(result.authid);
    $('quickreplypreview').update(result.data).removeClassName('hidden');
    replystatus = false;
}

function clearQuickReply()
{
    $('message').value = '';
    $('quickreplypreview').update('&nbsp;').addClassName('hidden');
    replystatus = false;
}
