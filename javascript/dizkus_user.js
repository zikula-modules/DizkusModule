/**
 * dizkus_user.js
 */

/**
 * @todo All code below needs to be reviewed and either be deleted or refactored! If you refactored it, please move it ABOVE this message.
 * - cmfcmf
 */

/*
Zikula.define('Dizkus');

document.observe('dom:loaded', function() {
    Zikula.Dizkus.User = new Zikula.Dizkus.UserClass();
});

Zikula.Dizkus.UserClass = Class.create(Zikula.Dizkus.BaseClass, {
    initialize: function() {
        this.funcname = '';

        postEditing = false;
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


});
*/