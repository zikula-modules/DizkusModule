/**
 * dizkus_admin.js
 *
 * $Id$
 *
 */

var containments = new Array();
var forumliststatus = false;
var treeorderstatus = false;
var globalhandlers = {
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
Ajax.Responders.register(globalhandlers);

document.observe('dom:loaded', function() 
                               {     
                                   Zikula.define('Dizkus');
                                   new DizkusAdmin(); 
                               });

var DizkusAdmin = Class.create(DizkusBase, {
    initialize: function() 
    {
        this.containments = new Array();
        this.forumliststatus = false;
        this.treeorderstatus = false;
        
/*        
        this.globalhandlers = {
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
        Ajax.Responders.register(this.globalhandlers);
*/
        // add some observers
        $$('button.createnewcategory').each(function(el) { el.observe('click', this.editcategory.bind(this, -1))}.bind(this)); /* -1 = new category */
        $$('button.storetreeorder').each(function(el) { el.observe('click', this.storetreeorder.bind(this))}.bind(this));
        
        // add observers to edit category, add forum and edit forum buttons
        $$('button[id^="editcategory"]').each(function(el) 
                                        { 
                                            el.observe('click', this.editcategory.bind(this, el.id.split('_')[1]));
                                        }.bind(this));
        $$('button[id^="editforum"]').each(function(el) 
                                        { 
                                            el.observe('click', this.editforum.bind(this, el.id.split('_')[1], el.id.split('_')[2])); /* cat_id alse needed here */
                                        }.bind(this));
        $$('button[id^="addforum"]').each(function(el) 
                                        { 
                                            el.observe('click', this.editforum.bind(this, -1, el.id.split('_')[1])); /* -1 = new forum, */
                                        }.bind(this));

        // add observers to hide and show forum list buttons
        $$('button[id^="hideforumlist"]').each(function(el) 
                                        { 
                                            el.observe('click', this.toggleforumlist.bind(this, el.id.split('_')[1]));
                                        }.bind(this));
        $$('button[id^="showforumlist"]').each(function(el) 
                                        { 
                                            el.observe('click', this.toggleforumlist.bind(this, el.id.split('_')[1]));
                                        }.bind(this));
  
        // create the sortable
        this.createsortables();
              
    },

    editcategory: function(cat_id)
    {
        this.toggleprogressimage(true, cat_id);
        pars = "module=Dizkus&func=editcategory&cat=" + cat_id;
        myAjax = new Ajax.Request(
            Zikula.Config.baseURL+'ajax.php',
            {
                method: 'post', 
                parameters: pars, 
                onComplete: function(originalRequest)
                            {
                                // show error if necessary
                                if( originalRequest.status != 200 ) {
                                    json = Zikula.ajaxResponseError(originalRequest);
                                    if(json.new == true) {
                                         this.toggleprogressimage(true, -1);
                                    } else {
                                         this.toggleprogressimage(true, json.cat_id);
                                    }
                                    return;
                                }

                                json = Zikula.dejsonize(originalRequest.responseText);
                                Zikula.updateauthids(json.authid);

                                if(json.new == true) {
                                     this.toggleprogressimage(true, -1);
                                    // new category
                                    // copy newcategory li
                                    newnewcategory = $('newcategory').cloneNode(true);
                                    
                                    // update existing newcategory li with data retreved from server
                                    $('neweditcategorycontent').update(json.data);
                                    // and show it
                                    
                                    // add observer for submit button
                                    $('submitcategory_' + json.cat_id).observe('click', this.storecategory.bind(this, json.cat_id));
                                    
                                    // set new id in newcategory li
                                    $('newcategory').id = 'category_' + json.cat_id;
                                    
                                    $('neweditcategorycontent').id = 'editcategorycontent_' + json.cat_id;
                                    
                                    $('newcategorytitle').id = 'categorytitle_' + json.cat_id;
                                    
                                    $('newhidecategory').id = 'hidecategory_' + json.cat_id;
                                    $('hidecategory_' + json.cat_id).observe('click', this.hideshowcategory.bind(this, json.cat_id));
                                    
                                    $('newshowcategory').id = 'showcategory_' + json.cat_id;
                                    $('showcategory_' + json.cat_id).hide().observe('click', this.hideshowcategory.bind(this, json.cat_id));
                                    
                                    $('newhideforumlist').hide().id = 'hideforumlist_' + json.cat_id;
                                    
                                    $('newshowforumlist').hide().id = 'showforumlist_' + json.cat_id;
                                    
                                    $('newprogresscategoryimage').id = 'progresscategoryimage_' + json.cat_id;
                                    
                                    $('newcanceladdcategory').id = 'canceladdcategory_' + json.cat_id;
                                    $('canceladdcategory_' + json.cat_id).observe('click', this.canceladdcategory.bind(this, json.cat_id));
                                    
                                    $('newaddforum').hide().id = 'addforum_' + json.cat_id;
                                    
                                    $('newcid').id = 'cid_' + json.cat_id;
                                    
                                    $('newemptycategory').id = 'emptycategory_' + json.cat_id;
                                    
                                    // new forum li
                                    $('newforum').id = 'newforum_cat' + json.cat_id;
                                    
                                    $('newforumtitle').id = 'forumtitle_cat' + json.cat_id;
                                    
                                    $('newhideforum').id = 'hideforum_cat' + json.cat_id;
                                    
                                    $('newshowforum').id = 'showforum_cat' + json.cat_id;
                                    
                                    $('newcanceladdforum').id = 'canceladdforum_cat' + json.cat_id;
                                    
                                    $('newprogressforumimage').id = 'progressforumimage_cat' + json.cat_id;
                                    
                                    $('neweditforum').id = 'neweditforum_' + json.cat_id;
                                    
                                    // append copied li to the ul - now we can add another new category without 
                                    // needing to store the first one
                                    $('category').appendChild(newnewcategory);
                                    
                                    Effect.toggle('category_' + json.cat_id, 'slide');
                                } else {
                                    this.toggleprogressimage(true, json.cat_id);
                                    // existing category
                                    $('editcategorycontent_' + json.cat_id).update(json.data);
                                    // add observer for submit button
                                    $('submitcategory_' + json.cat_id).observe('click', this.storecategory.bind(this, json.cat_id));
    
                                    Effect.toggle('editcategorycontent_' + json.cat_id, 'slide');
                                    $('editcategory_' + json.cat_id).hide();
                                    $('showcategory_' + json.cat_id).hide().observe('click', this.hideshowcategory.bind(this, json.cat_id));
                                    $('hidecategory_' + json.cat_id).show().observe('click', this.hideshowcategory.bind(this, json.cat_id));
                                }
                            }.bind(this)
            });
    },
 
    canceladdcategory: function(cat_id)
    {
        Effect.toggle('category_' + cat_id, 'slide', { afterFinish: function(cat_id) { $('category_' + cat_id).remove(); }.bind(this, cat_id)});
    },
    
    hideshowcategory: function(cat_id)
    {
        Effect.toggle('editcategorycontent_' + cat_id, 'slide', { afterFinish: function(cat_id) 
                                                                               {
                                                                                   $('hidecategory_' + cat_id).toggle();
                                                                                   $('showcategory_' + cat_id).toggle();
                                                                               }.bind(this, cat_id)});
        return;
    },

    storecategory: function(cat_id)
    {
        this.toggleprogressimage(true, cat_id);
        pars = "module=Dizkus&func=storecategory&" + Form.serialize('editcategoryform_'+ cat_id);
        myAjax = new Ajax.Request(
            Zikula.Config.baseURL+'ajax.php',
            {
                method: 'post', 
                parameters: pars, 
                onComplete: function(originalRequest)
                            {
                                if( originalRequest.status != 200 ) {
                                    json = Zikula.ajaxResponseError(originalRequest);
                                    this.toggleprogressimage(true, json.old_id);
                                    return;
                                }

                                json = Zikula.dejsonize(originalRequest.responseText);
                                Zikula.updateauthids(json.authid);
                            
                                this.toggleprogressimage(true, json.old_id);
                                
                                switch(json.action) {
                                    case 'add':
                                        $('category_' + json.old_id).addClassName('existingcategory').id = 'category_' + json.cat_id;

                                        // add observer for submit button
                                        $('submitcategory_' + json.old_id).id = 'submitcategory_' + json.cat_id;
                                        $('submitcategory_' + json.cat_id).observe('click', this.storecategory.bind(this, json.cat_id));
                                        
                                        $('hidecategory_' + json.old_id).id = 'hidecategory_' + json.cat_id;
                                        $('hidecategory_' + json.cat_id).observe('click', this.hideshowcategory.bind(this, json.cat_id));
                                        
                                        $('showcategory_' + json.old_id).id = 'showcategory_' + json.cat_id;
                                        $('showcategory_' + json.cat_id).observe('click', this.hideshowcategory.bind(this, json.cat_id));
                            
                                        $('categorytitle_' + json.old_id).id = 'categorytitle_' + json.cat_id;
                                        $('categorytitle_' + json.cat_id).update('<a href="' + json.cat_linkurl + '">' + json.cat_title + '</a> (' + json.cat_id + ')');
                                        
                                        $('addforum_' + json.old_id).show().id = 'addforum_' + json.cat_id;
                                        $('addforum_' + json.cat_id).observe('click', this.addforum.bind(this, json.cat_id));
                            
                                        $('hideforumlist_' + json.old_id).show().id = 'hideforumlist_' + json.cat_id;
                                        $('hideforumlist_' + json.cat_id).observe('click', this.toggleforumlist.bind(this, json.cat_id));
                                        
                                        $('showforumlist_' + json.old_id).hide().id = 'showforumlist_' + json.cat_id;
                                        $('showforumlist_' + json.cat_id).observe('click', this.toggleforumlist.bind(this, json.cat_id));
                            
                                        $('progresscategoryimage_' + json.old_id).id = 'progresscategoryimage_' + json.cat_id;
                                        $('progresscategoryimage_' + json.cat_id).style.visibilty = 'hidden';
                            
                                        $('cid_' + json.old_id).show().id = 'cid_' + json.cat_id;
                            
                                        $('emptycategory_' + json.old_id).show().id = 'emptycategory_' + json.cat_id;
                            
                                        $('canceladdcategory_' + json.old_id).remove();
                                        
                                        $('editcategorycontent_' + json.old_id).id = 'editcategorycontent_' + json.cat_id;
                                        $('editcategorycontent_' + json.cat_id).update(json.edithtml);
                            
                                        // new forum li
                                        $('newforum_cat' + json.old_id).id = 'newforum_cat' + json.cat_id;
                                        
                                        $('forumtitle_cat' + json.old_id).id = 'forumtitle_cat' + json.cat_id;
                                        
                                        $('hideforum_cat' + json.old_id).id = 'hideforum_cat' + json.cat_id;
                                        
                                        $('showforum_cat' + json.old_id).id = 'showforum_cat' + json.cat_id;
                                       
                                        $('canceladdforum_cat' + json.old_id).id = 'canceladdforum_cat' + json.cat_id;
                                        
                                        $('progressforumimage_cat' + json.old_id).id = 'progressforumimage_cat' + json.cat_id;
                                        
                                        $('neweditforum_' + json.old_id).id = 'neweditforum_' + json.cat_id;

                                        // recreate sortables
                                        this.createsortables();
                                        
                                        break;
                                    case 'update':
                                        $('categorytitle_' + json.cat_id).update('<a href="' + json.cat_linkurl + '">' + json.cat_title + '</a> (' + json.cat_id + ')');
                                        break;
                                    case 'delete':
                                        $('category_' + json.cat_id).remove();

                                        // recreate sortables
                                        this.createsortables();

                                        break;
                                    default:
                                        dzk_showajaxerror('Error! Unknown action received from server.');
                                }
                            }.bind(this)
            });
        return false;
    },

    editforum: function(forum_id, cat_id)
    {
        if (forum_id == -1) {
            this.toggleprogressimage(true, cat_id);
        } else {
            this.toggleprogressimage(false, forum_id);
        }
        pars = "module=Dizkus&func=editforum&forum_id=" + forum_id;
        if(forum_id == -1) {
            pars += '&cat=' + cat_id;
        }
        myAjax = new Ajax.Request(
            Zikula.Config.baseURL+'ajax.php',
            {
                method: 'post', 
                parameters: pars, 
                onComplete: function(originalRequest)
                            {
                                // show error if necessary
                                if( originalRequest.status != 200 ) {
                                    json = Zikula.ajaxResponseError(originalRequest);
                                    if (json.new == true) {
                                        this.toggleprogressimage(true, json.cat_id);
                                    } else {
                                        this.toggleprogressimage(false, json.forum_id);
                                    }
                                    return;
                                }
                                    
                                json = Zikula.dejsonize(originalRequest.responseText);
                                Zikula.updateauthids(json.authid);
                            
                                if(json.new == true) {
                                    this.toggleprogressimage(true, json.cat_id);

                                    // copy newforum li
                                    newnewforum = $('newforum_cat' + json.cat_id).cloneNode(true);
                                    // update existing newforum li with data retrieved from server
                                    // and show it
                                    $('neweditforumcontent_' + json.cat_id).update(json.data).show().id = 'editforumcontent_' + json.forum_id;

                                    // add some observers
                                    $('submitforum_' + json.forum_id).observe('click', this.storeforum.bind(this, json.forum_id));
                                    $('extsource_1_' + json.forum_id).observe('change', this.showextendedoptions.bind(this, 1, json.forum_id));
                                    $('extsource_2_' + json.forum_id).observe('change', this.showextendedoptions.bind(this, 2, json.forum_id));
                                    $('extsource_3_' + json.forum_id).observe('change', this.showextendedoptions.bind(this, 3, json.forum_id));
                                    
                                    // set new id in newforum li
                                    $('newforum_cat' + json.cat_id).show().id = 'forum_' + json.forum_id;
                                
                                    $('forumtitle_cat' + json.cat_id).id = 'forumtitle_' + json.forum_id;
                                
                                
                                    $('hideforum_cat' + json.cat_id).id = 'hideforum_' + json.forum_id;
                                    $('hideforum_' + json.forum_id).show().observe('click', this.toggleforum.bind(this, json.forum_id));

                                    $('showforum_cat' + json.cat_id).id = 'showforum_' + json.forum_id;
                                    $('showforum_' + json.forum_id).hide().observe('click', this.toggleforum.bind(this, json.forum_id));
                                
                                    $('canceladdforum_cat' + json.cat_id).id = 'canceladdforum_' + json.forum_id;
                                    $('canceladdforum_' + json.forum_id).observe('click', this.canceladdforum.bind(this, json.forum_id, json.cat_id));
                                
                                    $('progressforumimage_cat' + json.cat_id).id = 'progressforumimage_' + json.forum_id;
                                
                                    // append copied li to the ul - now we can add another new forum without 
                                    // needing to store the first one
                                    $('cid_' + json.cat_id).appendChild(newnewforum);
                                    
                                    if($('cid_' + json.cat_id).visible() == false) {
                                        this.toggleforumlist(json.cat_id);
                                    }
                                    
                                    // hide "this category does not contain a forum" message
                                    $('emptycategory_' + json.cat_id).hide();
                                } else {
                                    this.toggleprogressimage(false, json.forum_id);
                                    $('editforumcontent_' + json.forum_id).update(json.data);

                                    // add observer for submit button
                                    $('submitforum_' + json.forum_id).observe('click', this.storeforum.bind(this, json.forum_id));
                                    
                                    Effect.toggle('editforumcontent_' + json.forum_id, 'slide');
                                    $('showforum_' + json.forum_id).hide().observe('click', this.toggleforum.bind(this, json.forum_id));
                                    $('hideforum_' + json.forum_id).show().observe('click', this.toggleforum.bind(this, json.forum_id));
                                    $('editforum_' + json.forum_id + '_' + json.cat_id).hide();
                                }
                            }.bind(this)
            });
    },
    
    toggleforum: function(forum_id)
    {
        Effect.toggle('editforumcontent_' + forum_id, 'slide', { afterFinish: function(forum_id) 
                                                                    {
                                                                        $('hideforum_' + forum_id).toggle();
                                                                        $('showforum_' + forum_id).toggle();
                                                                    }.bind(this, forum_id)});
    },

    canceladdforum: function(forum_id, cat_id)
    {
        // hide it
        Effect.toggle('forum_' + forum_id, 'slide', { afterFinish: function(forum_id, cat_id)
                                                                   {
                                                                       // check if there are more forums, if not, show place holder
                                                                       if($('forum_' + forum_id).parentNode.childNodes.length == 3) {
                                                                           // 3 = this forum li, emptycategory li  + newforum li
                                                                           // after removing it the list will be virtually empty
                                                                           $('emptycategory_' + cat_id).show();
                                                                       }
                                                                       // remove it
                                                                       $('forum_' + forum_id).remove();
                                                                   }.bind(this, forum_id, cat_id)});
    },

    storeforum: function(forum_id)
    {
        this.toggleprogressimage(false, forum_id);
        pars = "module=Dizkus&func=storeforum&" + Form.serialize('editforumform_'+ forum_id);
        myAjax = new Ajax.Request(
            Zikula.Config.baseURL+'ajax.php',
            {
                method: 'post', 
                parameters: pars, 
                onComplete: function(originalRequest)
                            {
                                if( originalRequest.status != 200 ) {
                                    json = Zikula.ajaxResponseError(originalRequest);
                                    this.toggleprogressimage(false, json.old_id);
                                    return;
                                }

                                json = Zikula.dejsonize(originalRequest.responseText);
                                Zikula.updateauthids(json.authid);

                                this.toggleprogressimage(false, json.old_id);
                                switch(json.action) {
                                    case 'delete':
                                        // hide it
                                        Effect.toggle('forum_' + json.old_id, 'slide', { afterFinish: function(forum_id, cat_id)
                                                                                                      {
                                                                                                          // check if there are more forums, if not, show place holder
                                                                                                          if($('forum_' + forum_id).parentNode.childNodes.length == 3) {
                                                                                                              // 3 = this forum li, emptycategory li  + newforum li
                                                                                                              // after removing it the list will be virtually empty
                                                                                                              $('emptycategory_' + cat_id).show();
                                                                                                              $('deletecategory_' + cat_id).style.visibility = '';
                                                                                                          } else {
                                                                                                              $('deletecategory_' + cat_id).style.visibility = 'hidden';
                                                                                                          }
                                                                                                          // remove it
                                                                                                          Element.remove('forum_' + forum_id);
                                                                                                      }.bind(this, json.forum_id, json.cat_id)});
                                        // recreate sortables
                                        this.createsortables();

                                        break;
                                    case 'update':
                                        $('forumtitle_' + json.forum_id).update(json.forumtitle);
                                        $('editforumcontent_' + json.forum_id).update(json.editforumhtml);
                                        break;
                                    case 'add':
                                        $('forumtitle_' + json.old_id).id = 'forumtitle_' + json.forum_id; 
                                        $('forumtitle_' + json.forum_id).update(json.forumtitle);
                            
                                        $('editforumcontent_' + json.old_id).id = 'editforumcontent_' + json.forum_id; 
                                        $('editforumcontent_' + json.forum_id).update(json.editforumhtml);
                            
                                        $('forum_' + json.old_id).addClassName('existingforum').id = 'forum_' + json.forum_id;
                            
                                        $('hideforum_' + json.old_id).show().id = 'hideforum_' + json.forum_id;
                                        $('hideforum_' + json.forum_id).observe('click', this.toggleforum.bind(this, json.forum_id));
                                        
                                        $('showforum_' + json.old_id).hide().id = 'showforum_' + json.forum_id;
                                        $('showforum_' + json.forum_id).observe('click', this.toggleforum.bind(this, json.forum_id));
                            
                                        $('canceladdforum_' + json.old_id).remove();

                                        $('progressforumimage_' + json.old_id).id = 'progressforumimage_' + json.forum_id;
                            
                                        $('deletecategory_' + json.cat_id).style.visibility = 'hidden';
                                        
                                        // recreate sortables
                                        this.createsortables();
                                        
                                        break;
                                    default:
                                        dzk_showajaxerror('Error! \'storeforum_response()\' received illegal action type from server.');   
                                }
                            }.bind(this)
            });
    },
       
    storetreeorder: function()
    {
console.log('store tree order');
    },
        
    toggleforumlist: function(cat_id)
    {
        Effect.toggle('cid_' + cat_id, 'slide', { afterFinish: function(cat_id) 
                                                               {
                                                                   $('hideforumlist_' + cat_id).toggle();
                                                                   $('showforumlist_' + cat_id).toggle();
                                                               }.bind(this, cat_id)});
    },
       
    toggleprogressimage: function(typ, id)
    {
        // typ true = category (id=cat_id), false=forum (id=forum_id)
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

    createsortables: function()
    {
        /* var cids = $$('.dzk_treeforumlist');
        if(cids.length > 0) {
            for(var i=0; i<cids.length; i++) {
                containments[containments.length] = cids[i].id;
            }
        }
        */
        // create containments array
        $$('ul[id^="cid"]').each(function(containment)
                                 {
                                     this.containments[this.containments.length] = containment.id;
                                 }.bind(this));
        
        // now create the sortables per category
        this.containments.each(function(containment)
                               {
                                          
                                   Sortable.create(containment,
                                                   {dropOnEmpty: true,
                                                    only: 'existing',
                                                    handle: 'dzk_handle',
                                                    overlap: 'horizontal',
                                                    containment: this.containments,
                                                    onUpdate: function(containment) 
                                                              {
                                                   //this.showdizkusinfo(storingnewsortorder);
                                                   pars = 'module=Dizkus&func=savetree&' + Sortable.serialize(containment) + '&cat_id=' + containment.id.split('_')[1] + '&authid=' + $F('authid');
                                                   myAjax = new Ajax.Request(
                                                       Zikula.Config.baseURL+'ajax.php',
                                                       {
                                                           method: 'post', 
                                                           parameters: pars, 
                                                           onComplete: function(originalRequest)
                                                                       {
                                                                          //this.hidedizkusinfo();
                                                                          // show error if necessary
                                                                          if( originalRequest.status != 200 ) {
                                                                              json = Zikula.ajaxResponseError(originalRequest);
                                                                              return;
                                                                          }
                                          
                                                                          json = Zikula.dejsonize(originalRequest.responseText);
                                                                          Zikula.updateauthids(json.authid);
                                                                       }.bind(this)
                                                       });

                                               }.bind(this),    
                                     constraint: false
                                    });
                               }.bind(this));
    
        // and now the sortable fr the categories themselves
        Sortable.create("category",
                        { handle: 'dzk_handle',
                          only: 'existing',
                          onUpdate: function()
                                    {
                                        //showdizkusinfo(storingnewsortorder);
                                        pars = 'module=Dizkus&func=savetree&' + Sortable.serialize('category') + '&authid=' + $F('authid');
                                        myAjax = new Ajax.Request(
                                            Zikula.Config.baseURL+'ajax.php',
                                            {
                                                method: 'post', 
                                                parameters: pars, 
                                                onComplete: function(originalRequest)
                                                            {
                                                                //this.hidedizkusinfo();
                                                                // show error if necessary
                                                                if( originalRequest.status != 200 ) {
                                                                    json = Zikula.ajaxResponseError(originalRequest);
                                                                    return;
                                                                }
                                
                                                                json = Zikula.dejsonize(originalRequest.responseText);
                                                                Zikula.updateauthids(json.authid);
                                                            }.bind(this)
                                            });
                                    }.bind(this) 
                        });
    },

    showextendedoptions: function(extsource, forum_id)
    {
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
                $('pnlogindata_' + forumid).hide();
                $('mail2forum_' + forumid).hide();
                $('rss2forum_' + forumid).hide();
        }
    }
});






