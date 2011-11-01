/**
 * dizkus_admin.js
 */

Zikula.define('Dizkus');

document.observe('dom:loaded', function() {
    Zikula.Dizkus.Admin = new Zikula.Dizkus.AdminClass();
});

Zikula.Dizkus.AdminClass = Class.create(Zikula.Dizkus.BaseClass, {
    initialize: function() {
        this.containments = [];
        this.funcname = '';


        this.globalhandlers = {
            onCreate: function(){
                if($('dizkus')) {
                    $('dizkus').setStyle({
                        cursor: 'wait'
                    });
                }
            },

            onComplete: function() {
                if(Ajax.activeRequestCount == 0){
                    if($('dizkus')) {
                        $('dizkus').setStyle({
                            cursor: 'auto'
                        });
                    }
                }
            }
        };
        Ajax.Responders.register(this.globalhandlers);

        // find out which func we are in, this will help us to identify what needs to be done
        this.funcname = window.location.search.toQueryParams().func;

        switch(this.funcname) {
            case 'reordertree':
                // add some observers
                $$('button.createnewcategory').each(function(el) {
                    el.observe('click', this.createcategory.bind(this, -1))
                }.bind(this)); /* -1 = new category */

                // add observers to edit category, add forum and edit forum buttons
                $$('button[id^="showcategory"]').each(function(el) {
                    el.observe('click', this.hideshowcategory.bind(this, el.id.split('_')[1]));
                }.bind(this));
                $$('button[id^="editforum"]').each(function(el) {
                    el.observe('click', this.editforum.bind(this, el.id.split('_')[1], el.id.split('_')[2])); /* cat_id alse needed here */
                }.bind(this));
                $$('button[id^="addforum"]').each(function(el) {
                    el.observe('click', this.editforum.bind(this, -1, el.id.split('_')[1])); /* -1 = new forum, */
                }.bind(this));

                // add observers to hide and show forum list buttons
                $$('button[id^="hideforumlist"]').each(function(el) {
                    el.observe('click', this.toggleforumlist.bind(this, el.id.split('_')[1]));
                }.bind(this));
                $$('button[id^="showforumlist"]').each(function(el) {
                    el.observe('click', this.toggleforumlist.bind(this, el.id.split('_')[1]));
                }.bind(this));
                $$('button[id^="showcategory"]').each(function(el) {
                    el.show().observe('click', this.hideshowcategory.bind(this, el.id.split('_')[1]));
                }.bind(this));
                $$('button[id^="hidecategory"]').each(function(el) {
                    el.observe('click', this.hideshowcategory.bind(this, el.id.split('_')[1]));
                }.bind(this));
                $$('button[id^="submitcategory"]').each(function(el) {
                    el.observe('click', this.storecategory.bind(this, el.id.split('_')[1]));
                }.bind(this));

                // create the sortable
                this.createsortables();
                break;

            case 'managesubscriptions':
                if ($('alltopic')) {
                    $('alltopic').observe('click', this.checkAll.bind(this, 'topic'));
                }
                $$('input.topic_checkbox').each(function(el) {
                    el.observe('click', this.checkCheckAll.bind(this, 'topic'));
                }.bind(this));
                if ($('allforum')) {
                    $('allforum').observe('click', this.checkAll.bind(this, 'forum'));
                }
                $$('input.forum_checkbox').each(function(el) {
                    el.observe('click', this.checkCheckAll.bind(this, 'forum'));
                }.bind(this));
                break;
            default: // this is an unknown function
        }


    },

    createcategory: function(cat_id) {
        this.toggleprogressimage(true, cat_id);
        var pars = {};
        var myAjax = new Zikula.Ajax.Request(
            'ajax.php?module=Dizkus&func=createcategory',
            {
                method: 'post',
                parameters: pars,
                onComplete: function(req) {
                    // show error if necessary
                    if (!req.isSuccess()) {
                        Zikula.showajaxerror(req.getMessage());
                        this.toggleprogressimage(true, -1);
                        return;
                    }

                    var msg = req.getData();

                    // new category
                    this.toggleprogressimage(true, -1);
                    $('category').insert(msg.tpl);

                    $('hidecategory_' + msg.cat_id).show().observe('click', this.hideshowcategory.bind(this, msg.cat_id));
                    $('showcategory_' + msg.cat_id).hide().observe('click', this.hideshowcategory.bind(this, msg.cat_id));

                    $('hideforumlist_' + msg.cat_id).hide();
                    $('showforumlist_' + msg.cat_id).hide();
                    $('canceladdcategory_' + msg.cat_id).observe('click', this.canceladdcategory.bind(this, msg.cat_id));
                    $('addforum_' + msg.cat_id).hide();

                    $('submitcategory_' + msg.cat_id).observe('click', this.storecategory.bind(this, msg.cat_id));
                }.bind(this)
            }
        );
    },

    canceladdcategory: function(cat_id) {
        Effect.toggle('category_' + cat_id, 'slide', {
            afterFinish: function(cat_id) {
                $('category_' + cat_id).remove();
            }.bind(this, cat_id)
        });
    },

    hideshowcategory: function(cat_id) {
        Effect.toggle('editcategorycontent_' + cat_id, 'slide', {
            afterFinish: function(cat_id) {
                $('hidecategory_' + cat_id).toggle();
                $('showcategory_' + cat_id).toggle();
            }.bind(this, cat_id)
        });
        return;
    },

    storecategory: function(cat_id) {
        this.toggleprogressimage(true, cat_id);
        var pars = Form.serialize('editcategoryform_'+ cat_id);
        var myAjax = new Zikula.Ajax.Request(
            'ajax.php?module=Dizkus&func=storecategory',
            {
                method: 'post',
                parameters: pars,
                onComplete: function(req) {
                    if (!req.isSuccess()) {
                        Zikula.showajaxerror(req.getMessage());
                        var msg = req.getData();
                        this.toggleprogressimage(true, msg.old_id);
                        return;
                    }

                    var msg = req.getData();

                    this.toggleprogressimage(true, msg.old_id);

                    switch(msg.action) {
                        case 'add':
                            $('category_' + msg.old_id).remove();
                            $('category').insert(msg.edithtml);

                            $('hidecategory_' + msg.cat_id).hide().observe('click', this.hideshowcategory.bind(this, msg.cat_id));
                            $('showcategory_' + msg.cat_id).show().observe('click', this.hideshowcategory.bind(this, msg.cat_id));

                            $('hideforumlist_' + msg.cat_id).hide();
                            $('showforumlist_' + msg.cat_id).show();
                            $('addforum_' + msg.cat_id).show();

                            $('submitcategory_' + msg.cat_id).observe('click', this.storecategory.bind(this, msg.cat_id));

                            // recreate sortables
                            this.createsortables();

                            break;
                        case 'update':
                            $('categorytitle_' + msg.cat_id).update('<a href="' + msg.cat_linkurl + '">' + msg.cat_title + '</a> (' + msg.cat_id + ')');
                            break;
                        case 'delete':
                            Effect.toggle('category_' + msg.cat_id, 'slide', {
                                afterFinish: function(cat_id) {
                                    // remove it
                                    $('category_' + cat_id).remove();
                                }.bind(this, msg.cat_id)
                            });
                            // recreate sortables
                            this.createsortables();

                            break;
                        default:
                            Zikula.showajaxerror('Error! Unknown action received from server.');
                    }
                }.bind(this)
            }
        );
        return false;
    },

    editforum: function(forum_id, cat_id) {
        if (forum_id == -1) {
            this.toggleprogressimage(true, cat_id);
        } else {
            this.toggleprogressimage(false, forum_id);
        }
        var pars = {
            'forum_id': forum_id, 
            'cat': cat_id
        };

        var myAjax = new Zikula.Ajax.Request(
            'ajax.php?module=Dizkus&func=editforum',
            {
                method: 'post',
                parameters: pars,
                onComplete: function(req) {
                    // show error if necessary
                    if (!req.isSuccess()) {
                        Zikula.showajaxerror(req.getMessage());
                        var msg = req.getData();
                        if (msg['new'] == true) {
                            this.toggleprogressimage(true, msg.cat_id);
                        } else {
                            this.toggleprogressimage(false, msg.forum_id);
                        }
                        return;
                    }

                    var msg = req.getData();

                    if(msg['new'] == true) {
                        this.toggleprogressimage(true, msg.cat_id);

                        $('cid_' + msg.cat_id).insert(msg.data);

                        $('submitforum_' + msg.forum_id).observe('click', this.storeforum.bind(this, msg.forum_id));
                        $('extsource_0_' + msg.forum_id).observe('change', this.showextendedoptions.bind(this, 0, msg.forum_id));
                        $('extsource_1_' + msg.forum_id).observe('change', this.showextendedoptions.bind(this, 1, msg.forum_id));
                        $('extsource_2_' + msg.forum_id).observe('change', this.showextendedoptions.bind(this, 2, msg.forum_id));

                        $('editforum_' + msg.forum_id).hide();
                        $('hideforum_' + msg.forum_id).show().observe('click', this.toggleforum.bind(this, msg.forum_id));
                        $('showforum_' + msg.forum_id).hide().observe('click', this.toggleforum.bind(this, msg.forum_id));
                        $('canceladdforum_' + msg.forum_id).observe('click', this.canceladdforum.bind(this, msg.forum_id));

                        if($('cid_' + msg.cat_id).visible() == false) {
                            this.toggleforumlist(msg.cat_id);
                        }

                        // hide "this category does not contain a forum" message
                        $('emptycategory_' + msg.cat_id).hide();

                        // recreate sortables
                        this.createsortables();

                    } else {
                        this.toggleprogressimage(false, msg.forum_id);
                        $('editforumcontent_' + msg.forum_id).update(msg.data);

                        // add observer for submit button
                        $('submitforum_' + msg.forum_id).observe('click', this.storeforum.bind(this, msg.forum_id));

                        Effect.toggle('editforumcontent_' + msg.forum_id, 'slide');
                        $('showforum_' + msg.forum_id).hide().observe('click', this.toggleforum.bind(this, msg.forum_id));
                        $('hideforum_' + msg.forum_id).show().observe('click', this.toggleforum.bind(this, msg.forum_id));
                        $('editforum_' + msg.forum_id).hide();
                    }
                }.bind(this)
            }
        );
    },

    toggleforum: function(forum_id) {
        Effect.toggle('editforumcontent_' + forum_id, 'slide', {
            afterFinish: function(forum_id) {
                $('hideforum_' + forum_id).toggle();
                $('showforum_' + forum_id).toggle();
            }.bind(this, forum_id)
        });
    },

    canceladdforum: function(forum_id) {
        // hide it
        Effect.toggle('forum_' + forum_id, 'slide', {
            afterFinish: function(forum_id) {
                // check if there are more forums, if not, show place holder

                if($('forum_' + forum_id).siblings().size() == 1) {
                    // 3 = this forum li, emptycategory li  + newforum li
                    // after removing it the list will be virtually empty
                    var cat_id = $('forum_' + forum_id).parentNode.id.split('_')[1];
                    $('emptycategory_' + cat_id).show();
                }
                // remove it
                $('forum_' + forum_id).remove();
            }.bind(this, forum_id)
        });
    },

    storeforum: function(forum_id) {
        this.toggleprogressimage(false, forum_id);
        var pars = Form.serialize('editforumform_'+ forum_id);
        var myAjax = new Zikula.Ajax.Request(
            'ajax.php?module=Dizkus&func=storeforum',
            {
                method: 'post',
                parameters: pars,
                onComplete: function(req) {
                    if (!req.isSuccess()) {
                        Zikula.showajaxerror(req.getMessage());
                        var msg = req.getData();
                        this.toggleprogressimage(false, msg.old_id);
                        return;
                    }

                    var msg = req.getData();

                    this.toggleprogressimage(false, msg.old_id);
                    switch(msg.action) {
                        case 'delete':
                            // hide it
                            Effect.toggle('forum_' + msg.old_id, 'slide', {
                                afterFinish: function(forum_id, cat_id)

                                {
                                    // check if there are more forums, if not, show place holder
                                    if($('forum_' + forum_id).siblings().size() == 1) {
                                        // 3 = this forum li, emptycategory li  + newforum li
                                        // after removing it the list will be virtually empty
                                        $('emptycategory_' + cat_id).show();
                                        $('deletecategory_' + cat_id).show();
                                    } else {
                                        $('deletecategory_' + cat_id).hide();
                                    }
                                    // remove it
                                    $('forum_' + forum_id).remove;
                                }.bind(this, msg.forum_id, msg.cat_id)
                                });
                            // recreate sortables
                            this.createsortables();

                            break;
                        case 'update':
                            $('forumtitle_' + msg.forum_id).update(msg.forumtitle);
                            break;
                        case 'add':
                            $('forumtitle_' + msg.old_id).id = 'forumtitle_' + msg.forum_id;
                            $('forumtitle_' + msg.forum_id).update(msg.forumtitle);

                            $('editforumcontent_' + msg.old_id).id = 'editforumcontent_' + msg.forum_id;
                            $('editforumcontent_' + msg.forum_id).update(msg.editforumhtml);

                            $('submitforum_' + msg.forum_id).observe('click', this.storeforum.bind(this, msg.forum_id));

                            $('extsource_0_' + msg.forum_id).observe('change', this.showextendedoptions.bind(this, 0, msg.forum_id));
                            $('extsource_1_' + msg.forum_id).observe('change', this.showextendedoptions.bind(this, 1, msg.forum_id));
                            $('extsource_2_' + msg.forum_id).observe('change', this.showextendedoptions.bind(this, 2, msg.forum_id));

                            $('forum_' + msg.old_id).id = 'forum_' + msg.forum_id;

                            $('hideforum_' + msg.old_id).stopObserving();
                            $('hideforum_' + msg.old_id).show().id = 'hideforum_' + msg.forum_id;
                            $('hideforum_' + msg.forum_id).observe('click', this.toggleforum.bind(this, msg.forum_id));

                            $('showforum_' + msg.old_id).stopObserving();
                            $('showforum_' + msg.old_id).hide().id = 'showforum_' + msg.forum_id;
                            $('showforum_' + msg.forum_id).observe('click', this.toggleforum.bind(this, msg.forum_id));

                            $('canceladdforum_' + msg.old_id).remove();

                            $('progressforumimage_' + msg.old_id).id = 'progressforumimage_' + msg.forum_id;

                            $('deletecategory_' + msg.cat_id).hide();

                            // recreate sortables
                            this.createsortables();

                            break;
                        default:
                            Zikula.showajaxerror('Error! \'storeforum_response()\' received illegal action type from server.');
                    }
                }.bind(this)
            }
        );
    },

    toggleforumlist: function(cat_id) {
        Effect.toggle('cid_' + cat_id, 'slide', {
            afterFinish: function(cat_id) {
                $('hideforumlist_' + cat_id).toggle();
                $('showforumlist_' + cat_id).toggle();
            }.bind(this, cat_id)
        });
    },

    toggleprogressimage: function(typ, id) {
        // typ true = category (id=cat_id), false=forum (id=forum_id)
        var imageid;
        if(id != -1) {
            imageid = (typ == true) ? 'progresscategoryimage_' + id : 'progressforumimage_' + id;
        } else {
            imageid = 'progressnewcategoryimage';
        }

        if($(imageid)) {
            if($(imageid).style.visibility == 'hidden') {
                $(imageid).style.visibility = '';
            } else {
                $(imageid).style.visibility = 'hidden';
            }
        }
        return;
    },

    createsortables: function() {
        // create containments array
        this.containments = [];
        $$('ul[id^="cid"]').each(function(containment) {
            this.containments.push(containment.id);
        }.bind(this));

        // now create the sortables per category
        this.containments.each(function(containment) {

            Sortable.create(containment,
            {
                dropOnEmpty: true,
                only: 'existing',
                handle: 'dzk_handle',
                overlap: 'horizontal',
                containment: this.containments,
                onUpdate: function(containment) {
                    this.showdizkusinfo(storingnewsortorder);
                    var pars = Sortable.serialize(containment) + '&cat_id=' + containment.id.split('_')[1];
                    var myAjax = new Zikula.Ajax.Request(
                        'ajax.php?module=Dizkus&func=savetree',
                        {
                            method: 'post',
                            parameters: pars,
                            onComplete: function(req) {
                                // check if the forum list for this category is empty
                                if($$('#'+containment.id+' li[class*=existing]').size() == 0) {
                                    // show message
                                    $('emptycategory_' + containment.id.split('_')[1]).show();
                                } else {
                                    // hide message
                                    $('emptycategory_' + containment.id.split('_')[1]).hide();
                                }
                                this.hidedizkusinfo();
                                // show error if necessary
                                if (!req.isSuccess()) {
                                    Zikula.showajaxerror(req.getMessage());
                                    return;
                                }
                            }.bind(this)
                        }
                    );

                }.bind(this),
                constraint: false
            });
        }.bind(this));

        // and now the sortable for the categories themselves
        Sortable.create("category",
        {
            handle: 'dzk_handle',
            only: 'existing',
            onUpdate: function() {
                this.showdizkusinfo(storingnewsortorder);
                var pars = Sortable.serialize('category');
                var myAjax = new Zikula.Ajax.Request(
                    'ajax.php?module=Dizkus&func=savetree',
                    {
                        method: 'post',
                        parameters: pars,
                        onComplete: function(req) {
                            this.hidedizkusinfo();
                            // show error if necessary
                            if (!req.isSuccess()) {
                                Zikula.showajaxerror(req.getMessage());
                                return;
                            }
                        }.bind(this)
                    }
                );
            }.bind(this)
        });
    },

    showextendedoptions: function(extsource, forum_id) {
        switch(extsource) {
            case 1:
                $('pnlogindata_' + forum_id).show();
                $('mail2forum_' + forum_id).show();
                $('rss2forum_' + forum_id).hide();
                break;
            case 2:
                $('pnlogindata_' + forum_id).show();
                $('mail2forum_' + forum_id).hide();
                $('rss2forum_' + forum_id).show();
                break;
            default:
                $('pnlogindata_' + forum_id).hide();
                $('mail2forum_' + forum_id).hide();
                $('rss2forum_' + forum_id).hide();
        }
    }
});






