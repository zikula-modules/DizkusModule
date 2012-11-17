/**
 * dizkus_user.js
 */


Zikula.define('Dizkus');

document.observe('dom:loaded', function() {
    Zikula.Dizkus.User = new Zikula.Dizkus.UserClass();
});

Zikula.Dizkus.UserClass = Class.create(Zikula.Dizkus.BaseClass, {
    initialize: function() {
        this.funcname = '';

        this.editstatus = false;
        this.replystatus = false;
        this.editchanged = false;
        this.lockstatus = false;
        this.stickystatus = false;
        this.subscribestatus = false;
        this.subscribeforumstatus = false;
        this.subjectstatus = false;
        this.sortorderstatus = false;
        this.newtopicstatus = false;
        this.toggleforumdisplay = false;

        // global setting of combination effect
        this.comboeffect = 'slide';
        this.comboparams = {
            duration: 1
        };

        this.indicatorimage = '<img src="' + Zikula.baseURL + 'modules/Dizkus/images/ajaxindicator.gif" alt="" />';

        this.dzk_globalhandlers = {
            onCreate: function() {
                $$('.dzk_detachable').each(function(el){
                    el.disabled = true;
                });
                if($('dizkus')) {
                    $('dizkus').style.cursor = 'wait';
                }
            },

            onComplete: function() {
                if(Ajax.activeRequestCount == 0){
                    $$('.dzk_detachable').each(function(el){
                        el.disabled = false;
                    });
                    if($('dizkus')) {
                        $('dizkus').style.cursor = 'auto';
                    }
                }
            }
        };

        // find out which func we are in, this will help us to identify what needs to be done
        var urlParts = document.URL.split('/');
        var numUrlParts = urlParts.length;
        var urlPart = '';
        for (var i = 0; i < numUrlParts; i++) {
            urlPart = urlParts[i];
            if (urlPart == 'newtopic' || urlPart == 'viewforum' || urlPart == 'viewtopic' || urlPart == 'prefs' || urlPart == 'moderateforum' || urlPart == 'topicsubscriptions') {
                this.funcname = urlPart;
                break;
            }
        }
        if (this.funcname == '') {
            this.funcname = window.location.search.toQueryParams().func;
        }

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
            case 'viewtopic':
                $('dzk_javascriptareatopic').removeClassName('hidden');

                if($('dzk_quickreply')) {
                    $('quickreplybuttons').removeClassName('hidden');
                    $('nonajaxquickreplybuttons').remove();
                    $('quickreplyform').action = 'javascript:void(0);';
                }
                $$('ul.javascriptpostingoptions').each(function(el) {
                    el.removeClassName('hidden');
                });

                // find out the topic subscription status
                var toggletopicsubscriptionbuttonid = $$('a[id^="toggletopicsubscriptionbutton"]');
                if (toggletopicsubscriptionbuttonid.size() > 0) {
                    this.toggletopicsubscriptionbuttonid = toggletopicsubscriptionbuttonid.first().id;
                    this.topic_subscribed = (this.toggletopicsubscriptionbuttonid.split('_')[2] == 'subscribed' ? true : false);
                    $(this.toggletopicsubscriptionbuttonid).observe('click', this.toggletopicsubscription.bind(this));
                }

                // find out the lock status
                var toggletopiclockbutton = $$('a[id^="toggletopiclockbutton"]');
                if (toggletopiclockbutton.size() > 0) {
                    this.toggletopiclockbuttonid = toggletopiclockbutton.first().id;
                    this.topic_locked = (this.toggletopiclockbuttonid.split('_')[2] == 'locked' ? true : false);
                    $(this.toggletopiclockbuttonid).observe('click', this.toggletopiclock.bind(this));
                }

                // find out the sticky status
                var toggletopicstickybutton = $$('a[id^="toggletopicstickybutton"]');
                if (toggletopicstickybutton.size() > 0) {
                    this.toggletopicstickybuttonid = toggletopicstickybutton.first().id;
                    this.topic_sticky = (this.toggletopicstickybuttonid.split('_')[2] == 'locked' ? true : false);
                    $(this.toggletopicstickybuttonid).observe('click', this.toggletopicsticky.bind(this));
                }

                // find out if the topic is editable
                var edittopicsubjectbuttonid = $$('span[id^="edittopicsubjectbutton"]');
                if (edittopicsubjectbuttonid.size() > 0) {
                    this.edittopicsubjectbuttonid = edittopicsubjectbuttonid.first().id;
                    $(this.edittopicsubjectbuttonid).observe('click', this.edittopicsubject.bind(this));
                }

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
                $$('a[id^="quotebutton"]').each(function(el) {
                    el.observe('click', this.createQuote.bind(this, el.id));
                }.bind(this));

                // add observers to edit buttons per post
                $$('a[id^="editbutton"]').each(function(eb) {
                    eb.observe('click', this.quickEdit.bind(this, eb.id));
                }.bind(this));

                // find out if the contactlist ignoring stuff is there
                $$('a[id^="hidelink_posting"]').each(function(el) {
                    el.observe('click', this.toggleposting.bind(this, el.id.split('_')[2]));
                }.bind(this));
                break;
            case 'topicsubscriptions':
                $('alltopic').observe('click', this.checkAll.bind(this, 'topic'));
                $$('input.topic_checkbox').each(function(el) {
                    el.observe('click', this.checkCheckAll.bind(this, 'topic'));
                }.bind(this));
                break;
            default:
                return;
        }
    },

    toggleposting: function(post_id) {
        $('posting_{$post.post_id}').toggle();
        $('hidelink_posting_{$post.post_id}').toggle();
    },



    toggleforumsubscription: function(toggleforumsubscriptionbuttonid) {
        if(this.subscribeforumstatus == false) {
            this.subscribeforumstatus = true;
            var pars = {
                forum: toggleforumsubscriptionbuttonid.split('_')[1]
            }
            //            Ajax.Responders.register(this.dzk_globalhandlers);
            var myAjax = new Zikula.Ajax.Request(
                Zikula.Config.baseURL + "ajax.php?module=Dizkus&func=toggleforumsubscription",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(req) {
                        this.subscribeforumstatus = false;

                        // show error if necessary
                        if (!req.isSuccess()) {
                            Zikula.showajaxerror(req.getMessage());
                            return;
                        }

                        var msg = req.getData();

                        if (msg.data == 'subscribed') {
                            $(toggleforumsubscriptionbuttonid).update(unsubscribeForum);
                        } else if (msg.data == 'unsubscribed') {
                            $(toggleforumsubscriptionbuttonid).update(subscribeForum);
                        } else {
                            alert('Error! Erroneous result from subscription/unsubscription action.');
                        }
                    }.bind(this)
                }
            );
        }
    },



    quickEdit: function(editpostlinkid) {
        if(this.editstatus == false) {
            this.editstatus = true;
            this.editchanged = false;
            this.post_id = editpostlinkid.split('_')[1];
            var pars = {
                post: this.post_id
            }
            // Ajax.Responders.register(this.dzk_globalhandlers);
            var myAjax = new Zikula.Ajax.Request(
                Zikula.Config.baseURL + "ajax.php?module=Dizkus&func=editpost",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(req) {
                        // show error if necessary
                        if (!req.isSuccess()) {
                            Zikula.showajaxerror(req.getMessage());
                            this.editstatus = false;
                            return;
                        }

                        var msg = req.getData();

                        // hide posting options, update authid
                        $('postingoptions_' + this.post_id).hide();

                        // hide quickreply
                        if ($('dzk_quickreply')) {
                            Effect.toggle($('dzk_quickreply'), this.comboeffect, this.comboparams);
                        }

                        // add inline editor
                        $('postingtext_' + this.post_id).insert({after: msg.data});

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

                        $('postingtext_' + this.post_id + '_edit').observe('keyup', this.quickEditchanged.bind(this));
                        $('postingtext_' + this.post_id + '_save').observe('click', this.quickEditsave.bind(this));
                        $('postingtext_' + this.post_id + '_cancel').observe('click', this.quickEditcancel.bind(this));
                    }.bind(this)
                }
            );
        }
    },

    quickEditchanged: function() {
        if(this.editchanged == false) {
            this.editchanged = true;
            $('postingtext_' + this.post_id + '_status').update('<span style="color: red;">' + statusChanged + '</span>');
        }
    },

    quickEditsave: function() {

       if($F('postingtext_' + this.post_id + '_edit').blank() == true) {
            // no text
            return;
        }
        var pars = {
            post: this.post_id,
            message: $F('postingtext_' + this.post_id + '_edit'),
            attach_signature: this.getcheckboxvalue('postingtext_' + this.post_id + '_attach_signature')
        }

        if($('postingtext_' + this.post_id + '_delete') && $('postingtext_' + this.post_id + '_delete').checked == true) {
            $('postingtext_' + this.post_id + '_status').update('<span style="color: red;">' + deletingPost + '</span>');
            pars['delete'] =1;
        } else {
            $('postingtext_' + this.post_id + '_status').update('<span style="color: red;">' + updatingPost + '</span>');
        }

        // Ajax.Responders.register(this.dzk_globalhandlers);
        var myAjax = new Zikula.Ajax.Request(
            Zikula.Config.baseURL + "ajax.php?module=Dizkus&func=updatepost",
            {
                method: 'post',
                parameters: pars,
                onComplete: function(req) {
                    this.editstatus = false;
                    this.editchanged = false;

                    // show error if necessary
                    if (!req.isSuccess()) {
                        Zikula.showajaxerror(req.getMessage());
                        return;
                    }

                    var msg = req.getData();

                    $('postingtext_' + this.post_id + '_editor').remove();



                    if(msg.action == 'deleted') {
                        $('posting_' + this.post_id).remove();
                    } else if (msg.action == 'topic_deleted') {
                        window.setTimeout("window.location.href='" + msg.redirect + "';", 500);
                        return;
                    } else {
                        $('postingtext_' + this.post_id).update(msg.post_text).show();
                    }

                    //  hide quickreply
                    if($('dzk_quickreply')) {
                        Effect.toggle($('dzk_quickreply'), this.comboeffect, this.comboparams);
                    }
                }.bind(this)
            }
        );


        $('postingoptions_' + this.post_id + '').show();
    },

    quickEditcancel: function() {
        $('postingoptions_' + this.post_id).show();
        $('postingtext_' + this.post_id + '_editor').remove();
        this.editstatus = false;
        this.editchanged = false;

        // unhide quickreply
        if($('dzk_quickreply')) {
            Effect.toggle($('dzk_quickreply'), this.comboeffect, this.comboparams);
        }
    },


    createnewtopic: function(event) {
        if (this.newtopicstatus==false) {
            if (($F('subject').blank() == true) || ($F('message').blank() == true)){
                // no subject and/or message
                if (event) Event.stop(event);
                return;
            }

            this.newtopicstatus = true;
            this.showdizkusinfo(this.indicatorimage + ' ' + storingPost);

            var pars = {
                forum: $F('forum'),
                subject: $F('subject'),
                message: $F('message'),
                attach_signature: this.getcheckboxvalue('attach_signature'),
                subscribe_topic: this.getcheckboxvalue('subscribe_topic')
            }
            //            Ajax.Responders.register(this.dzk_globalhandlers);
            var myAjax = new Zikula.Ajax.Request(
                Zikula.Config.baseURL + 'ajax.php?module=Dizkus&func=newtopic',
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function (req) {
                        this.hidedizkusinfo();
                        this.newtopicstatus = false;

                        // show error if necessary
                        if (!req.isSuccess()) {
                            Zikula.showajaxerror(req.getMessage());
                            return;
                        }

                        var msg = req.getData();

                        if ($('myuploadframe') && $('btnUpload') && msg.uploadauthid) {
                            // is this used?
                            var newTopicUpload = true;
                            var newTopicRedirect = msg.redirect;
                            $('MediaAttach_redirect').value = msg.uploadredirect;
                            $('MediaAttach_objectid').value = msg.uploadobjectid;
                            $('btnUpload').click();
                        }

                        if (msg.confirmation == false || !$('newtopicconfirmation')) {
                            this.showdizkusinfo(redirecting);
                        } else {
                            $('dzk_newtopic').hide();
                            $('newtopicconfirmation').update(msg.confirmation).show();
                        }
                        window.setTimeout("window.location.href='" + msg.redirect + "';", 3000);
                    }.bind(this)

                }
            );
        }
        if (event) Event.stop(event);
    },


    previewnewtopic: function(event) {
        if (this.newtopicstatus==false) {
            this.newtopicstatus = true;
            this.showdizkusinfo(this.indicatorimage + ' ' + preparingPreview);

            var pars = {
                forum: $F('forum'),
                subject: $F('subject'),
                message: $F('message'),
                attach_signature: this.getcheckboxvalue('attach_signature'),
                preview: 1
            }

            //            Ajax.Responders.register(this.dzk_globalhandlers);
            var myAjax = new Zikula.Ajax.Request(
                Zikula.Config.baseURL + 'ajax.php?module=Dizkus&func=newtopic',
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function (req) {
                        this.hidedizkusinfo();
                        this.newtopicstatus = false;

                        // show error if necessary
                        if (!req.isSuccess()) {
                            Zikula.showajaxeError(req.getMessage());
                            this.newtopicstatus = false;
                            if (event) Event.stop(event);
                            return;
                        }

                        var msg = req.getData();
                        $('newtopicpreview').update(msg.data).show();
                        if (event) Event.stop(event);
                    }.bind(this)
                }
            );
            if (event) {
                Event.stop(event);
            }
        }
    },

    cancelnewtopic: function() {
        $('message').clear();
        $('subject').clear();
        $('newtopicpreview').update('&nbsp;').hide();
        this.newtopicstatus = false;
    },

    createQuote: function(quotelinkid) {
        // check if the user highlighted a text portion and quote this instead of loading the
        // posting text from the server
        var selection, quotetext;
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
            var pars = {
                post: quotelinkid.split('_')[1]
            }
            Ajax.Responders.register(this.dzk_globalhandlers);
            var myAjax = new Zikula.Ajax.Request(
                Zikula.Config.baseURL + "ajax.php?module=Dizkus&func=preparequote",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(req) {
                        if(!req.isSuccess()) {
                            Zikula.showajaxerror(req.getMessage());
                            return;
                        }

                        var oldvalue = $F('message');
                        if(oldvalue.length != 0) {
                            oldvalue += '\n';
                        }

                        var msg = req.getData();
                        $('message').setValue(oldvalue + msg.message  + '\n').focus();
                    }.bind(this)
                }
            );
            return;
        }

        oldvalue = $F('message');
        if(oldvalue.length != 0) {
            oldvalue += '\n';
        }
        $('message').setValue(oldvalue + '[quote]' + quotetext  + '[/quote]\n').focus();
    },

    createQuickReply: function(event) {
        if(this.replystatus==false) {
            if ($F('message').blank() == true){
                // no subject and/or message
                if (event) Event.stop(event);
                return false;
            }

            this.replystatus = true;
            this.showdizkusinfo(this.indicatorimage + ' ' + storingReply);
            var pars = {
                topic: $F('topic'),
                message: $F('message'),
                attach_signature: this.getcheckboxvalue('attach_signature'),
                subscribe_topic: this.getcheckboxvalue('subscribe_topic')
            }
            //          Ajax.Responders.register(this.dzk_globalhandlers);
            var myAjax = new Zikula.Ajax.Request(
                Zikula.Config.baseURL + "ajax.php?module=Dizkus&func=reply",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(req) {
                        this.hidedizkusinfo();

                        // show error if necessary
                        if (!req.getMessage()) {
                            Zikula.showajaxeError(req.getMessage());
                            this.replystatus = false;
                            if (event) {
                                Event.stop(event)
                            }
                            return;
                        }

                        var msg = req.getData();

                        // clear textarea and reset preview
                        this.cancelQuickReply()

                        // show new posting
                        $('quickreplyposting').update(msg.data).removeClassName('hidden');
                        // add observers to quote buttons per post
                        var quotebutton = $('posting_'+msg.post_id).down('a[id^="quotebutton"]');
                        quotebutton.observe('click', this.createQuote.bind(this, quotebutton.id));
                        // add observers to edit buttons per post
                        var editbutton = $('posting_'+msg.post_id).down('a[id^="editbutton"]');
                        editbutton.observe('click', this.quickEdit.bind(this, editbutton.id));

                        // prepare everything for another quick reply
                        $('quickreplyposting').insert({after: '<li id="new_quickreplyposting"></li>'});
                        // clear old id
                        $('quickreplyposting').id = '';
                        // rename new id
                        $('new_quickreplyposting').id = 'quickreplyposting';
                        // enable js options in quickreply
                        $$('ul.javascriptpostingoptions').each(function(el) {
                            el.removeClassName('hidden');
                        });

                        if ($('myuploadframe') && $('btnUpload') && msg.uploadauthid) {
                            Zikula.updateauthids(msg.uploadauthid);
                            $('btnUpload').click();
                        }

                        this.replystatus = false;
                    }.bind(this)
                });
        }
        if (event) {
            Event.stop(event);
        }
        return false;
    },

    previewQuickReply: function(event) {
        if(this.replystatus==false) {
            if ($F('message').blank() == true){
                // no subject and/or message
                if (event) Event.stop(event);
                return;
            }
            this.replystatus = true;
            this.showdizkusinfo(this.indicatorimage + ' ' + preparingPreview);

            var pars = {
                topic: $F('topic'),
                message: $F('message'),
                attach_signature: this.getcheckboxvalue('attach_signature'),
                preview: 1
            }

            // Ajax.Responders.register(this.dzk_globalhandlers);
            var myAjax = new Zikula.Ajax.Request(
                Zikula.Config.baseURL + "ajax.php?module=Dizkus&func=reply",
                {
                    method: 'post',
                    parameters: pars,
                    onComplete: function(req) {
                        this.hidedizkusinfo();

                        // show error if necessary
                        if (!req.isSuccess()) {
                            Zikula.showajaxerror(req.getMessage());
                            this.replystatus = false;
                            if (event) Event.stop(event);
                            return;
                        }

                        var msg = req.getData();
                        $('quickreplypreview').update(msg.data).removeClassName('hidden');
                        this.replystatus = false;
                    //if (event) Event.stop(event);
                    }.bind(this)
                }
            );
        }

        if (event) {
            Event.stop(event);
        }
    },

    cancelQuickReply: function(event) {
        $('message').clear();
        $('quickreplypreview').update('&nbsp;').addClassName('hidden');
        this.replystatus = false;
    }

});
