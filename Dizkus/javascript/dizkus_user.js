/**
 *
 * $Id$
 *
 */


Event.observe(window, 'load', function() {
    Zikula.define('Dizkus');
    if($$('.dzk_texpand').size() != 0) {
        $$('.dzk_texpand').each(function(el){
            new Texpand(el, {autoShrink: false, shrinkOnBlur:false});
        });
    }
});

document.observe('dom:loaded', function() { new DizkusUser(); });

var DizkusUser = Class.create(DizkusBase, {
    initialize: function() 
    {
        this.funcname = '';
        
        this.editstatus = false;
        this.replystatus = false;
        this.editchanged = false;
        this.lockstatus = false;
        this.stickystatus = false;
        this.subscribestatus = false;
        this.subscribeforumstatus = false;
        this.favouritestatus = false;
        this.subjectstatus = false;
        this.sortorderstatus = false;
        this.newtopicstatus = false;
        this.toggleforumdisplay = false;

        // global setting of combination effect
        this.comboeffect = 'slide';
        this.comboparams = {duration: 1};

        this.indicatorimage = '<img src="' + Zikula.baseURL + 'modules/Dizkus/images/ajaxindicator.gif" alt="" />';

        this.dzk_globalhandlers = {
            onCreate: function(){
                $$('.dzk_img').each(function(el){ el.disabled = true; });
                if($('dizkus')) {
                    $('dizkus').style.cursor = 'wait';
                }
            },
        
            onComplete: function() {
                if(Ajax.activeRequestCount == 0){
                    $$('.dzk_img').each(function(el){ el.disabled = false; });
                    if($('dizkus')) {
                        $('dizkus').style.cursor = 'auto';
                    }
                }
            }
        };

        // find out which func we are in, this will help us to identify what needs to be done
        this.funcname = window.location.search.toQueryParams().func;

        switch (this.funcname) {
            case 'newtopic':
                $('newtopicbuttons').show();
                $('nonajaxnewtopicbuttons').remove();
                if($('nonajaxnewtopicpreview')) {
                    $('nonajaxnewtopicpreview').remove();
                }
                $('newtopicform').action = '';
            
                $('btnCreateNewTopic').observe('click', this.createnewtopic.bind(this));
                $('btnPreviewNewTopic').observe('click', this.previewnewtopic.bind(this));
                $('btnCancelNewTopic').observe('click', this.cancelnewtopic.bind(this));
                break;
            case 'viewforum':
                $('dzk_javascriptareaforum').removeClassName('hidden');
                
                // find out the forum subscription status
                toggleforumsubscriptionbutton = $$('a[id^="toggleforumsubscriptionbutton"]').first();
                toggleforumsubscriptionbutton.observe('click', this.toggleforumsubscription.bind(this, toggleforumsubscriptionbutton.id));

                // find out the forum favourite status
                toggleforumfavouritebutton = $$('a[id^="toggleforumfavouritebutton"]').first();
                toggleforumfavouritebutton.observe('click', this.toggleforumfavourite.bind(this, toggleforumfavouritebutton.id));
            
                break;
            case 'viewtopic':
                $('dzk_javascriptareatopic').removeClassName('hidden');
                
                if($('dzk_quickreply')) {
                    $('quickreplybuttons').removeClassName('hidden');
                    $('nonajaxquickreplybuttons').remove();
                    $('quickreplyform').action = 'javascript:void(0);';
                }
                $$('ul.javascriptpostingoptions').each(function(el) { el.removeClassName('hidden'); });

                // find out the topic subscription status
                this.toggletopicsubscriptionbuttonid = $$('a[id^="toggletopicsubscriptionbutton"]').first().id;
                this.topic_subscribed = (this.toggletopicsubscriptionbuttonid.split('_')[2] == 'subscribed' ? true : false);
                $(this.toggletopicsubscriptionbuttonid).observe('click', this.toggletopicsubscription.bind(this));

                // find out the lock status
                this.toggletopiclockbuttonid = $$('a[id^="toggletopiclockbutton"]').first().id;
                this.topic_locked = (this.toggletopiclockbuttonid.split('_')[2] == 'locked' ? true : false);
                $(this.toggletopiclockbuttonid).observe('click', this.toggletopiclock.bind(this));

                // find out the sticky status
                this.toggletopicstickybuttonid = $$('a[id^="toggletopicstickybutton"]').first().id;
                this.topic_sticky = (this.toggletopicstickybuttonid.split('_')[2] == 'locked' ? true : false);
                $(this.toggletopicstickybuttonid).observe('click', this.toggletopicsticky.bind(this));

                // find out if the topic is editable
                this.edittopicsubjectbuttonid = $$('span[id^="edittopicsubjectbutton"]').first().id;
                $(this.edittopicsubjectbuttonid).observe('click', this.edittopicsubject.bind(this));

                if($('btnCreateQuickReply')) {
                    $('btnCreateQuickReply').observe('click', this.createQuickReply.bind(this));
                }
                if($('btnPreviewQuickReply')) {
                    $('btnPreviewQuickReply').observe('click', this.previewQuickReply.bind(this));
                }
                if($('btnCancelQuickReply')) {
                    $('btnCancelQuickReply').observe('click', this.cancelQuickReply.bind(this));
                }

                // add observers to quote buttons per post
                $$('a[id^="quotebutton"]').each(function(el) 
                                                { 
                                                    el.observe('click', this.createQuote.bind(this, el.id));
                                                }.bind(this));
                                                
                // add observers to edit buttons per post
                $$('a[id^="editbutton"]').each(function(eb) 
                                               { 
                                                   eb.observe('click', this.quickEdit.bind(this, eb.id));
                                               }.bind(this));

                // find out if the contactlist ignoring stuff is there
                $$('a[id^="hidelink_posting"]').each(function(el) 
                                                { 
                                                    el.observe('click', this.toggleposting.bind(this, el.id.split('_')[2]));
                                                }.bind(this));
                break;
            case 'prefs':
                $('sortorder').observe('click', this.changesortorder.bind(this)).removeClassName('hidden');
                if ($('forumdisplaymode')) {
                    $('forumdisplaymode').observe('click', this.toggledisplay.bind(this)).removeClassName('hidden');
                }

                // add some observers
                $$('a[id^="toggleforumsubscriptionbutton"]').each(function(el) 
                                                                  {
                                                                      el.observe('click', this.toggleforumsubscription.bind(this, el.id));
                                                                  }.bind(this));
                $$('a[id^="toggleforumfavouritebutton"]').each(function(el) 
                                                               {
                                                                   el.observe('click', this.toggleforumfavourite.bind(this, el.id));
                                                               }.bind(this));
                break;
            case 'moderateforum':
            case 'topicsubscriptions':
                $('alltopic').observe('click', this.checkAll.bind(this, 'topic'));
                $$('input.topic_checkbox').each(function(el) 
                                                { 
                                                    el.observe('click', this.checkCheckAll.bind(this, 'topic'));
                                                }.bind(this));
                break;
            default:
                return;
        }
    },
    
    toggleposting: function(post_id)
    {
        $('posting_{$post.post_id}').toggle();
        $('hidelink_posting_{$post.post_id}').toggle();
        return;
    },
    
    toggleforumfavourite: function(toggleforumfavouritebuttonid) 
    {
        if(this.favouritestatus == false) {
            this.favouritestatus = true;
            pars = "module=Dizkus&func=toggleforumfavourite&forum=" + toggleforumfavouritebuttonid.split('_')[1];
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    this.favouritestatus = false;
                                    
                                    // show error if necessary
                                    if (originalRequest.status != 200) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        return;
                                    }
                                    
                                    json = Zikula.dejsonize(originalRequest.responseText);

                                    if (json.data == 'added') {
                                        $(toggleforumfavouritebuttonid).update(unfavouriteForum);
                                    } else if (json.data == 'removed') {
                                        $(toggleforumfavouritebuttonid).update(favouriteForum);
                                    } else {
                                         alert('Error! Erroneous result from favourite addition/removal.');
                                    }

                                }.bind(this)
                });
        }
    },
    
    toggleforumsubscription: function(toggleforumsubscriptionbuttonid)
    {
        if(this.subscribeforumstatus == false) {
            this.subscribeforumstatus = true;
            pars = "module=Dizkus&func=toggleforumsubscription&forum=" + toggleforumsubscriptionbuttonid.split('_')[1]
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL + "ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    this.subscribeforumstatus = false;
                                
                                    // show error if necessary
                                    if (originalRequest.status != 200) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        return;
                                    }
                                
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                
                                    if (json.data == 'subscribed') {
                                        $(toggleforumsubscriptionbuttonid).update(unsubscribeForum);
                                    } else if (json.data == 'unsubscribed') {
                                        $(toggleforumsubscriptionbuttonid).update(subscribeForum);
                                    } else {
                                         alert('Error! Erroneous result from subscription/unsubscription action.');
                                    }
                                }.bind(this)
                });
        }
    },
 
    changesortorder: function()
    {
        if(this.sortorderstatus == false) {
            this.sortorderstatus = true;
            pars = "module=Dizkus&func=changesortorder&authid=" + $F('authid');
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    this.sortorderstatus = false;
                                
                                    // show error if necessary
                                    if( originalRequest.status != 200 ) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        return;
                                    }
                                
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                    Zikula.updateauthids(json.authid);
                                
                                    switch(json.data) {
                                        case 'desc':
                                            $('sortorder_asc').addClassName('hidden');
                                            $('sortorder_desc').removeClassName('hidden'); 
                                            break;
                                        case 'asc':
                                            $('sortorder_desc').addClassName('hidden');
                                            $('sortorder_asc').removeClassName('hidden');
                                            break;
                                        default:
                                             alert('wrong result from changesortorder');
                                    }
                                }.bind(this)
                });
        }
    },

    toggledisplay: function()
    {
        if(this.toggleforumdisplay == false) {
            this.toggleforumdisplay = true;
            pars = "module=Dizkus&func=changeforumdisplay&authid=" + $F('authid');
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    this.toggleforumdisplay = false;
                                
                                    // show error if necessary
                                    if( originalRequest.status != 200 ) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        return;
                                    }
                                
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                    Zikula.updateauthids(json.authid);
                                
                                    if(json.data == true) {
                                        $('favorites_true').removeClassName('hidden');
                                        $('favorites_false').addClassName('hidden'); 

                                    } else {
                                        $('favorites_true').addClassName('hidden');
                                        $('favorites_false').removeClassName('hidden');
                                    }
                                }.bind(this)
                });
        }
    },
    
    quickEdit: function(editpostlinkid)
    {
        if(this.editstatus == false) {
            this.editstatus = true;
            this.editchanged = false;
            this.post_id = editpostlinkid.split('_')[1];
            pars = "module=Dizkus&func=editpost&post=" + this.post_id;
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    // show error if necessary
                                    if( originalRequest.status != 200 ) {
                                        json.Zikula.ajaxResponseError(originalRequest);
                                        this.editstatus = false;
                                        return;
                                    }
                                
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                
                                    // hide posting options, update authid                                
                                    $('postingoptions_' + this.post_id).hide();
                                    Zikula.updateauthids(json.authid);

                                    // hide quickreply
                                    if ($('dzk_quickreply')) {
                                        Effect.toggle($('dzk_quickreply'), this.comboeffect, this.comboparams);
                                    }
                                
                                    // add inline editor
                                    new Insertion.After($('postingtext_' + this.post_id), json.data);
                                    
                                    if ($('bbcode_postingtext_' + this.post_id + '_edit')) {
                                        $('bbcode_postingtext_' + this.post_id + '_edit').removeClassName('hidden');
                                    }
                                    if ($$('postingtext_' + this.post_id + '_editor .bb_standardsmilies')) {
                                        $$('.bbsmile_smilies').each(function(el) {
                                            el.removeClassName('bbsmile_smilies');
                                        });
                                        if($('smiliemodal')) {
                                            new Control.Modal($('smiliemodal'), {});
                                        }
                                    }
                                
                                    $$('.dzk_texpand').each(function(el){
                                      new Texpand(el, {autoShrink: true, shrinkOnBlur:false, expandOnFocus: false, expandOnLoad: true });
                                    });
                               
                                    $('postingtext_' + this.post_id + '_edit').observe('keyup', this.quickEditchanged.bind(this));
                                    $('postingtext_' + this.post_id + '_save').observe('click', this.quickEditsave.bind(this));
                                    $('postingtext_' + this.post_id + '_cancel').observe('click', this.quickEditcancel.bind(this));
                                }.bind(this)
                });
        }
        
    },
    
    quickEditchanged: function()
    {
        if(this.editchanged == false) {
            this.editchanged = true;
            $('postingtext_' + this.post_id + '_status').update('<span style="color: red;">' + statusChanged + '</span>');
        }
        return;
    },
    
    quickEditsave: function()
    {
        if($F('postingtext_' + this.post_id + '_edit').blank() == true) {
            // no text
            return;
        }
    
        pars = 'module=Dizkus&func=updatepost' +
               '&post=' + this.post_id +
               '&message=' + encodeURIComponent($F('postingtext_' + this.post_id + '_edit')) +
               '&authid=' + $F('postingtext_' + this.post_id + '_authid') +
               '&attach_signature=' + this.getcheckboxvalue('postingtext_' + this.post_id + '_attach_signature');

        if($('postingtext_' + this.post_id + '_delete') && $('postingtext_' + this.post_id + '_delete').checked == true) {
            $('postingtext_' + this.post_id + '_status').update('<span style="color: red;">' + deletingPost + '</span>');
            pars += '&delete=1';
        } else {
            $('postingtext_' + this.post_id + '_status').update('<span style="color: red;">' + updatingPost + '</span>');
        }
    
        Ajax.Responders.register(this.dzk_globalhandlers);
        myAjax = new Ajax.Request(
            Zikula.Config.baseURL+"ajax.php",
            {
                method: 'post',
                parameters: pars,
                onComplete: function(originalRequest) 
                            {
                                this.editstatus = false;
                                this.editchanged = false;
                                
                                // show error if necessary
                                if( originalRequest.status != 200 ) {
                                    json = Zikula.ajaxResponseError(originalRequest);
                                    return;
                                }

                                json = Zikula.dejsonize(originalRequest.responseText);
                                Zikula.updateauthids(json.authid);
                                                                    
                                $('postingtext_' + this.post_id + '_editor').remove();
                            
                                if(json.action == 'deleted') {
                                    $('posting_' + this.post_id).remove();
                                } else if (json.action == 'topic_deleted') {
                                    window.setTimeout("window.location.href='" + json.redirect + "';", 500);
                                    return;
                                } else {
                                    $('postingtext_' + this.post_id).update(json.post_text).show();
                                }
                            
                                //  hide quickreply
                                if($('dzk_quickreply')) {
                                    Effect.toggle($('dzk_quickreply'), this.comboeffect, this.comboparams);
                                }
                            }.bind(this)
            });
    
        $('postingoptions_' + this.post_id + '').show();
    },
    
    quickEditcancel: function()
    {
        $('postingoptions_' + this.post_id).show();
        $('postingtext_' + this.post_id + '_editor').remove();
        this.editstatus = false;
        this.editchanged = false;
    
        // unhide quickreply
        if($('dzk_quickreply')) {
            Effect.toggle($('dzk_quickreply'), this.comboeffect, this.comboparams);
        }
    },
       
    edittopicsubject: function()
    {
        if(this.subjectstatus == false) {
            this.subjectstatus = true;
            pars = "module=Dizkus&func=edittopicsubject&topic=" + this.edittopicsubjectbuttonid.split('_')[1];
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    // show error if necessary
                                    if( originalRequest.status != 200 ) {
                                        json.Zikula.ajaxResponseError(originalRequest);
                                        this.subjectstatus = false;
                                        return;
                                    }
                                
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                
                                    $(this.edittopicsubjectbuttonid).hide();
                                    Zikula.updateauthids(json.authid);
                                
                                    new Insertion.After($(this.edittopicsubjectbuttonid), json.data);
                                    $('topicsubjectedit_save').observe('click', this.topicsubjecteditsave.bind(this));
                                    $('topicsubjectedit_cancel').observe('click', this.topicsubjecteditcancel.bind(this));

                                }.bind(this)
                });
        }
    },
    
    topicsubjecteditsave: function()
    {
        if($F('topicsubjectedit_subject').blank() == true) {
            // no text
            return;
        }
    
        pars = "module=Dizkus&func=updatetopicsubject" +
               "&topic=" + this.edittopicsubjectbuttonid.split('_')[1] +
               "&subject=" + encodeURIComponent($F('topicsubjectedit_subject')) +
               "&authid=" + $F('topicsubjectedit_authid');
        Ajax.Responders.register(this.dzk_globalhandlers);
        myAjax = new Ajax.Request(
            Zikula.Config.baseURL+"ajax.php",
            {
                method: 'post',
                parameters: pars,
                onComplete: function(originalRequest)
                            {
                                this.subjectstatus = false;
                                
                                // show error if necessary
                                if( originalRequest.status != 200 ) {
                                    json = Zikula.ajaxResponseError(originalRequest);
                                    return;
                                }
                            
                                json = Zikula.dejsonize(originalRequest.responseText);
                            
                                $('topicsubjectedit_editor').remove();
                                Zikula.updateauthids(json.authid);
                                $(this.edittopicsubjectbuttonid).update(json.topic_title).show();
                                //$(this.edittopicsubjectbuttonid).show();
                            }.bind(this)
            });
    },
    
    topicsubjecteditcancel: function()
    {
        $('topicsubjecteditor').remove();
        $(this.edittopicsubjectbuttonid).show();
        this.subjectstatus = false;
        return false;
    },
    
    toggletopicsticky: function()
    {
        if(this.stickystatus == false) {
            this.stickystatus = true;
            pars = "module=Dizkus&func=stickyunstickytopic&topic=" + this.toggletopicstickybuttonid.split('_')[1] + "&mode=" + ((this.topic_sticky == false) ? 'sticky' : 'unsticky');
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    this.stickystatus = false;
                                    
                                    // show error if necessary
                                    if( originalRequest.status != 200 ) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        return;
                                    }
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                
                                    if(['sticky', 'unsticky'].include(json.data)) {
                                        if (this.topic_sticky == false) {
                                            this.topic_sticky = true;
                                            $(this.toggletopicstickybuttonid).update(unstickyTopic);
                                        } else {
                                           this.topic_sticky = false;
                                            $(this.toggletopicstickybuttonid).update(stickyTopic);
                                        }
                                    } else {
                                         alert('Error! Erroneous result from sticky/unsticky action.');
                                    }
                                }.bind(this)
                });
        }
    },
    
    toggletopiclock: function()
    {
        if(this.lockstatus == false) {
            this.lockstatus = true;
            pars = "module=Dizkus&func=lockunlocktopic&topic=" + this.toggletopiclockbuttonid.split('_')[1] + "&mode=" + ((this.topic_locked == false) ? 'lock' : 'unlock');
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest) 
                                {
                                    this.lockstatus = false;

                                    // show error if necessary
                                    if (originalRequest.status != 200) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        return;
                                    }
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                
                                    if(['locked', 'unlocked'].include(json.data)) {
                                        if (this.topic_locked == false) {
                                            this.topic_locked = true;
                                            $(this.toggletopiclockbuttonid).update(unlockTopic);
                                        } else {
                                           this.topic_locked = false;
                                            $(this.toggletopiclockbuttonid).update(lockTopic);
                                        }
                                    } else {
                                         alert('Error! Erroneous result from locking/unlocking action.');
                                    }
                                }.bind(this)
                });
        }
    },
    
    toggletopicsubscription: function() 
    {
        if(this.subscribestatus == false) {
            this.subscribestatus = true;
            pars = "module=Dizkus&func=subscribeunsubscribetopic&topic=" + this.toggletopicsubscriptionbuttonid.split('_')[1] + "&mode=" + ((this.topic_subscribed == false) ? 'subscribe' : 'unsubscribe');
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    this.subscribestatus = false;
                                    
                                    // show error if necessary
                                    if (originalRequest.status != 200) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        return;
                                    }
                                    
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                    if(['subscribed', 'unsubscribed'].include(json.data)) {
                                        if (this.topic_subscribed == false) {
                                            this.topic_subscribed = true;
                                            $(this.toggletopicsubscriptionbuttonid).update(unsubscribeTopic);
                                        } else {
                                           this.topic_subscribed = false;
                                            $(this.toggletopicsubscriptionbuttonid).update(subscribeTopic);
                                        }
                                    } else {
                                         alert('Error! Erroneous result from subscription/unsubscription action.');
                                    }
                                }.bind(this)
                });
        }



    },
    
    createnewtopic: function(event)
    {
        if (this.newtopicstatus==false)
        {
            if (($F('subject').blank() == true) || ($F('message').blank() == true)){
                // no subject and/or message
                if (event) Event.stop(event);
                return;
            }
        
            this.newtopicstatus = true;
            this.showdizkusinfo(this.indicatorimage + ' ' + storingPost);
    
            pars = 'module=Dizkus&func=newtopic' +
                   '&forum=' + $F('forum') +
                   '&subject=' + encodeURIComponent($F('subject')) +
                   '&message=' + encodeURIComponent($F('message')) +
                   '&attach_signature=' + this.getcheckboxvalue('attach_signature') +
                   '&subscribe_topic=' + this.getcheckboxvalue('subscribe_topic') +
                   '&authid=' + $F('authid');
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+'ajax.php',
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function (originalRequest)
                                {
                                    this.hidedizkusinfo();
                                    this.newtopicstatus = false;
                                
                                    // show error if necessary
                                    if (originalRequest.status != 200) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        return;
                                    }
                                
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                    Zikula.updateauthids(json.authid);
                                
                                    if ($('myuploadframe') && $('btnUpload') && json.uploadauthid) {
                                        newTopicUpload = true;
                                        newTopicRedirect = json.redirect;
                                        $('MediaAttach_redirect').value = json.uploadredirect;
                                        $('MediaAttach_objectid').value = json.uploadobjectid;
                                        this.updateAuthid(json.uploadauthid);
                                        $('btnUpload').click();
                                    }
                                
                                    if (json.confirmation == false || !$('newtopicconfirmation')) {
                                        this.showdizkusinfo(redirecting);
                                    } else {
                                        $('dzk_newtopic').hide();
                                        $('newtopicconfirmation').update(json.confirmation).show();
                                    }
                                    window.setTimeout("window.location.href='" + json.redirect + "';", 3000);
                                }.bind(this)
   
                });
        }
        if (event) Event.stop(event);
    },
      
    previewnewtopic: function(event)
    {
        if (this.newtopicstatus==false) {
            this.newtopicstatus = true;
            this.showdizkusinfo(this.indicatorimage + ' ' + preparingPreview);
    
            pars = "module=Dizkus&func=newtopic" +
                   '&forum=' + $F('forum') +        
                   "&subject=" + encodeURIComponent($F('subject')) +
                   "&message=" + encodeURIComponent($F('message')) +
                   "&attach_signature=" + this.getcheckboxvalue('attach_signature') +
                   "&preview=1" +
                   "&authid=" + $F('authid');

            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+'ajax.php',
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function (originalRequest)
                                {
                                    this.hidedizkusinfo();
                                    this.newtopicstatus = false;

                                    // show error if necessary
                                    if( originalRequest.status != 200 ) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        this.newtopicstatus = false;
                                        if (event) Event.stop(event);
                                        return;
                                    }
                                
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                    Zikula.updateauthids(json.authid);
                                    $('newtopicpreview').update(json.data).show();
                                    if (event) Event.stop(event);
                                }.bind(this)
                });
            if (event) Event.stop(event);
        }
    },

    cancelnewtopic: function()
    {
        $('message').clear();
        $('subject').clear();
        $('newtopicpreview').update('&nbsp;').hide();
        this.newtopicstatus = false;
        return;
    },

    createQuote: function(quotelinkid)
    {
        // check if the user highlighted a text portion and quote this instead of loading the
        // posting text from the server
        var selection;
        if (window.getSelection) {
            quotetext = window.getSelection()+'';
        } else if (document.getSelection) {
            // opera
            quotetext = document.getSelection()+'';
        } else if (document.selection.createRange) {
            // internet explorer
            selection = document.selection.createRange();
            if(selection) {
                quotetext = selection.text;
            }
        }
        quotetext.strip();
        if(quotetext.length == 0) {
            // read the messages text using ajax
            pars = "module=Dizkus&func=preparequote&post=" + quotelinkid.split('_')[1];
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest) 
                                {
                                    if( originalRequest.status != 200 ) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        if (event) Event.stop(event);
                                        return;
                                    }
                                
                                    oldvalue = $F('message');
                                    if(oldvalue.length != 0) {
                                        oldvalue += '\n';
                                    }
                                    
                                    result = Zikula.dejsonize(originalRequest.responseText);
                                    $('message').setValue(oldvalue + result.message  + '\n').focus();
                                    return;                    
                                }.bind(this)
                });
            return;
        }
        
        oldvalue = $F('message');
        if(oldvalue.length != 0) {
            oldvalue += '\n';
        }
        $('message').setValue(oldvalue + '[quote]' + quotetext  + '[/quote]\n').focus();

        return;
    },
    
    createQuickReply: function(event)
    {
        if(this.replystatus==false) {
            if ($F('message').blank() == true){
                // no subject and/or message
                if (event) Event.stop(event);
                return false;
            }

            this.replystatus = true;
            this.showdizkusinfo(this.indicatorimage + ' ' + storingReply);
            pars = 'module=Dizkus&func=reply' +
                   '&topic=' + $F('topic') +
                   '&message=' + encodeURIComponent($F('message')) +
                   '&attach_signature=' + this.getcheckboxvalue('attach_signature') +
                   '&subscribe_topic=' + this.getcheckboxvalue('subscribe_topic') +
                   '&authid=' + $F('authid');
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    this.hidedizkusinfo();
                                
                                    // show error if necessary
                                    if( originalRequest.status != 200 ) {
                                        json = Zikula.ajaxResponseError(originalRequest);
                                        this.replystatus = false;
                                        if (event) Event.stop(event);
                                        return;
                                    }
                                
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                    Zikula.updateauthids(json.authid);
                                
                                    // clear textarea and reset preview
                                    this.cancelQuickReply()
                                
                                    // show new posting
                                    $('quickreplyposting').update(json.data).removeClassName('hidden');
                                
                                    // prepare everything for another quick reply
                                    new Insertion.After('quickreplyposting', '<li id="new_quickreplyposting"></li>');
                                    // clear old id
                                    $('quickreplyposting').id = '';
                                    // rename new id
                                    $('new_quickreplyposting').id = 'quickreplyposting';
                                    // enable js options in quickreply
                                    $$('ul.javascriptpostingoptions').each(function(el) { el.removeClassName('hidden'); });
                                
                                    if ($('myuploadframe') && $('btnUpload') && json.uploadauthid) {
                                        Zikula.updateauthids(json.uploadauthid);
                                        $('btnUpload').click();
                                        Zikula.updateauthids(json.authid);
                                    }
                                
                                    this.replystatus = false;
                                }.bind(this)
                });
        }
        if (event) Event.stop(event); 
        return false;
    },
    
    previewQuickReply: function(event)
    {
        if(this.replystatus==false) {
            if ($F('message').blank() == true){
                // no subject and/or message
                if (event) Event.stop(event);
                return;
            }
            this.replystatus = true;
            this.showdizkusinfo(this.indicatorimage + ' ' + preparingPreview);
        
            pars = "module=Dizkus&func=reply" +
                   "&topic=" + $F('topic') +
                   "&message=" + encodeURIComponent($F('message')) +
                   "&attach_signature=" + this.getcheckboxvalue('attach_signature') +
                   "&preview=1" +
                   "&authid=" + $F('authid');
        
            Ajax.Responders.register(this.dzk_globalhandlers);
            myAjax = new Ajax.Request(
                Zikula.Config.baseURL+"ajax.php",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(originalRequest)
                                {
                                    this.hidedizkusinfo();
                                    
                                    // show error if necessary
                                    if( originalRequest.status != 200 ) {
                                        Zikula.ajaxResponseError(originalRequest);
                                        this.replystatus = false;
                                        if (event) Event.stop(event);
                                        return;
                                    }
                                    
                                    json = Zikula.dejsonize(originalRequest.responseText);
                                    Zikula.updateauthids(json.authid);
                                    $('quickreplypreview').update(json.data).removeClassName('hidden');
                                    this.replystatus = false;
                                    //if (event) Event.stop(event);
                                }.bind(this)
                });
        }

        if (event) Event.stop(event);
        return;
    },

    cancelQuickReply: function(event)
    {
        $('message').clear();
        $('quickreplypreview').update('&nbsp;').addClassName('hidden');
        this.replystatus = false;
        return;
    }
    
       
});
