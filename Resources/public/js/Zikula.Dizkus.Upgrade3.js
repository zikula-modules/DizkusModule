/**
 * Zikula.Dizkus.Upgrade3.js
 *
 * $ based JS
 */

// in case not exists
var Zikula = Zikula || {};
// Dizkus namespace
Zikula.Dizkus = Zikula.Dizkus || {};
(function ($) {
//Upgrade module
    Zikula.Dizkus.Upgrade3 = (function () {
// Init
        var data = {
            log: null
        };
        var settings = {
            users_limit: 50,
            topics_limit: 15,
            posts_limit: 30,
            other_limit: 50,
            ajax_timeout: 10000
        }
        ;
        function init()
        {
            if (parseInt($('#upgrade3_enabled').val()) === 0) {

                return;
            }
            initTrees();
            log('Upgrade init done.');
        }
        ;
        function readSettings()
        {
            settings.users_limit = parseInt($("#users_limit").val());
            settings.topics_limit = parseInt($("#topics_limit").val());
            settings.posts_limit = parseInt($("#posts_limit").val());
            settings.other_limit = parseInt($("#other_limit").val());
            settings.ajax_timeout = parseInt($("#ajax_timeout").val());
            log('Upgrade settings updated.');
        }
        ;
        function log(log)
        {
            if (log === '') {
            } else if (log === null) {
            } else if (log.constructor === Array) {
                $('#logBox').prepend(log.join('&#xA;') + '&#xA;');
            } else {
                $('#logBox').prepend(log + '&#xA;');
            }
        }
        ;
        // initialise jstree's
        // due to forum data nature upgrade use jstree as ui base
        function initTrees() {
            $('#tables_check').jstree({});
            initUsersTree();
            initForumTree();
            initOtherTree();
        }
        ;
        function initUsersTree() {
            // init tree with node event handlers
            $('#users_check')
                    .bind('loaded.jstree', function (e, data) {
                    })
                    .jstree({
                    })
                    .on('after_close.jstree', function (e, data) {
                    })
                    .on('after_open.jstree', function (e, data) {
                    })
                    .on('ready.jstree', function (e, data) {
                    })
                    ;
            // add tree data source
            $('#users_check').jstree(true).settings.core.data = {
                'url': Routing.generate('zikuladizkusmodule_upgrade3_usersstatus'),
                "dataType": "json",
                "dataFilter": function (response) {
                    res = JSON.parse(response);
                    data.tree = res.tree;
                    data.source = res.source;
                    log('User status loaded.');
                    if (data.source.users.old.toImport.length === 0
                            && data.source.ranks.toImport.length === 0) {
                        $("#import_users").removeClass('btn-default').addClass('btn-success');
                        $("#remove_users").removeClass('hide disabled');
                        $("#recover_forum_tree").removeClass('btn-default disabled').addClass('btn-primary');
                    } else {
                        $("#find_users").removeClass('btn-primary').addClass('btn-success');
                        $("#import_users").removeClass('disabled btn-default').addClass('btn-primary');
                    }
                    return JSON.stringify(data.tree);
                }
                ,
                'data': function (node) {
                    return node;
                }
            };
            // users tree bind buttons actions
            $("#find_users").click(function (e) {
                e.preventDefault();
                getUsersStatus();
                $("#find_users").addClass('disabled');
            });
            $("#import_users").click(function (e) {
                e.preventDefault();
                startUsersImport(data);
                $("#import_users").addClass('disabled');
            });
            $("#remove_users").click(function (e) {
                e.preventDefault();
                removeContent('users');
                $("#remove_users").addClass('disabled');
            });
            $("#find_users").removeClass('btn-default').addClass('btn-primary');
        }
        ;
        // simple load fresh data on open
        function getUsersStatus() {
            $("#users_check").jstree("close_node", $("#users_check_root"));
            $("#users_check").jstree("open_node", $("#users_check_root"));
        }

        function startUsersImport(data) {
            readSettings();
            // import started indicator
            $("#users_check_root").addClass('jstree-loading');
            $("#users_legend").prepend($(".progress").first().clone());
            $("#users_legend").find('.progress').removeClass('hide');
            /*
             * 1. import tables in order
             *   ranks - needed for users import !
             *   users,
             *   posts,
             *   topics
             */
            usersImport(data.source.ranks).done(function () {
                usersImport(data.source.users.old).done(function () {
//                    usersImport(data.source.users.posters).done(function () {
//                        data.source = 'topics';
//                        usersImport(data).done(function (data) {
//                            //Users done thank you!
                    //$("#users_legend").find('.info').text('Imported ' + data.page + ' users. Total users: ' + data.pages + '').css('color', '#fff');
                    $("#users_legend").find('.progress-bar').addClass('progress-bar-success');
                    $("#users_check_root").removeClass('jstree-loading').find('i').first().css('background-position', '-3px -66px');
                    $("#users_check_root").jstree("close_node", '#users_check_root');
                    $("#import_users").removeClass('btn-primary').addClass('btn-success');
                    // here we should inform about that this step is done
                    $("#recover_forum_tree").removeClass('btn-default disabled').addClass('btn-primary');
                    //$("#import_forum_tree").removeClass('disabled');
//                        });
//                });
                });
            });
        }

        function usersImport(data) {
            //console.log(data);
            var def = $.Deferred();
            if (data.toImport.length === 0) {
                def.resolve(data);
                return def.promise();
            }
            var $node = $("#" + data.source);
            $node.addClass('jstree-loading');
            def.progress(function (data) {
                var percent = 100 * data.page / data.pages;
                $("#users_legend").find('.progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
                $("#users_legend").find('.info').text('Importing ' + data.page + ' page from ' + data.pages).css('color', '#000');
            });
            def.done(function (data) {
                $node.removeClass('jstree-loading').find('a').find('i').removeClass('fa-orange').addClass('fa-green');
            });
            data.page = 0; // first page 0-49
            data.pageSize = settings.users_limit;
            data.pages = 0; // we do not know yet

            (function loop(data, def) {
                if (data.page < data.pages || data.pages === 0) {
                    importAjax(Routing.generate('zikuladizkusmodule_upgrade3_usersimport'), data).done(function (data) {
                        data.page++;
                        def.notify(data);
                        loop(data, def);
                    });
                } else {
                    def.resolve(data);
                }
            })(data, def);
            return def.promise();
        }

        /*
         * forum Tree
         */
        function initForumTree() {
            $('#forum_tree')
                    .jstree({})
                    .on('after_close.jstree', function (e, data) {
                    })
                    .on('after_open.jstree', function (e, data) {
                    })
                    .on('ready.jstree', function (e, data) {
                    })
                    .on('changed.jstree', function (e, data) {
                    })
                    .on('select_node.jstree', function (e, data) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        return false;
                    });
            $('#forum_tree')
                    .jstree(true)
                    .settings
                    .core
                    .data = {
                        'url': Routing.generate('zikuladizkusmodule_upgrade3_forumtreestatus'),
                        "dataType": "json",
                        "dataFilter": function (response) {
                            res = JSON.parse(response);
                            data.tree = res.tree;
                            data.total = res.total;
                            log('Forum tree loaded.');
                            if (data.total.current > 1) {
                                $("#remove_forum_tree").removeClass('hide disabled');
                            } else {
                                $("#recover_forum_tree").removeClass('btn-primary').addClass('btn-success');
                                $("#import_forum_tree").removeClass('disabled').addClass('btn-primary');
                            }

                            return JSON.stringify(prepateTreeData(data.tree));
                            ;
                        }
                        ,
                        'data': function (node) {
                            return {'id': node.id, 'text': node.name};
                        }
                    };
            $(".jstree-node").bind('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            $(".jstree-icon").bind('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            $("#recover_forum_tree").click(function (e) {
                e.preventDefault();
                getForumTree();
                $("#recover_forum_tree").addClass('disabled');
            });
            $("#import_forum_tree").click(function (e) {
                e.preventDefault();
                startForumImport();
                $("#import_forum_tree").addClass('disabled');
            });
            $("#remove_forum_tree").click(function (e) {
                e.preventDefault();
                removeContent('forum');
                $("#remove_forum_tree").addClass('disabled');
            });
        }
        ;
        function getForumTree() {
            $("#forum_tree").jstree("open_node", $("#forum_tree_root"));
        }

        function prepateTreeData(tree) {
            tree = prepareNodeData(tree);
            return tree;
        }

        function prepareNodeData(node) {
            node.text = node.name;
            for (var child in node.children) {
                node.children[child] = prepareNodeData(node.children[child]);
            }
            return node;
        }

        function startForumImport() {
            readSettings();
            // import started indicator
            $("#forum_tree_root").addClass('jstree-loading');
            /*
             * 1. import tables in order
             * 2. keep eye on ids because some are changing
             */
            forumImport(data).done(function () {
                $("#forum_tree_root").jstree("close_node", '#forum_tree_root');
                //enable other import button
                $("#import_forum_tree").addClass('disabled btn-success');
                $("#recover_other").removeClass('disabled').addClass('btn-primary');
            });
        }

        function forumImport(data) {

            var def = $.Deferred();
            $("#forum_legend").prepend($(".progress").first().clone());
            $("#forum_legend").find('.progress').removeClass('hide');
            def.progress(function (data) {
                log(data.log);
                data.log = '';
//                console.log(data);
                if (data.open) {
                    $("#" + data.open).addClass('jstree-loading');
                    $("#forum_tree_root").jstree("open_node", '#' + data.open);
                    data.open = false;
                }
                if (data.close) {
                    $("#forum_tree_root").jstree("close_node", '#' + data.close);
                    data.close = false;
                }
                if (data.ok) {
                    $("#" + data.ok).removeClass('jstree-loading').find('i').first().css('background-position', '-3px -66px');
                    data.ok = false;
                }

                //status
                var category = data.category_index;
                category = category + 1;
                var categories_txt = 'Categories: ' + category + '/' + data.tree.children.length;
                var forum = data.forum_index;
                forum = forum + 1;
                var forums_txt = 'Forums: ' + forum + '/' + data.tree.children[data.category_index].children.length;
                var topics_page = data.topics_page;
                topics_page = topics_page + 1;
                var topics_text = 'Topics: ' + data.topics_imported + '/' + data.total.topics + ' '; //' page:' + topics_page + ' pages: ' + data.topics_pages + ' (page size: ' + data.topics_limit + ')';
                var posts_text = 'Posts: ' + data.posts_imported + '/' + data.total.posts + ' '; //' page:' + topics_page + ' pages: ' + data.topics_pages + ' (page size: ' + data.topics_limit + ')';
                $("#forum_legend").find('.info').text(categories_txt + ' ' + forums_txt + ' ' + topics_text + ' ' + posts_text).css('color', '#000');
                //progress
                var done = category + forum + data.topics_imported + data.posts_imported;
                var total = data.total.forums + data.total.topics + data.total.posts;
                var percent = 100 * done / total;
                $("#forum_legend").find('.progress-bar').css('width', percent + '%').attr('aria-valuenow', percent).attr('aria-valuemax', total);
                $("#forum_legend").find('.count').text(' [' + done + ' / ' + total + '] ').css('color', '#000');
            }
            );
            def.done(function (data) {
                $("#forum_tree_root").removeClass('jstree-loading').find('i').first().css('background-position', '-3px -66px');
                console.log('full done!');
            });
            data.node = data.tree;
            data.category_index = 0;
            data.forum_index = 0;
            data.topic = null;
            data.topic_index = null;
            data.topics_page = 0;
            data.topics_limit = settings.topics_limit;
            data.topics_total = null;
            data.topics_imported = 0;
            data.post = null;
            data.posts_page = 0;
            data.posts_pages = 0;
            data.posts_limit = settings.posts_limit;
            data.posts_total = null;
            data.posts_imported = 0;
            data.status = {};
            data.open = data.node.id;
            def.notify(data);
            (function loop(data, def) {
                importAjax(Routing.generate('zikuladizkusmodule_upgrade3_forumtreeimport'), data).done(function (data) {
                    def.notify(data);
                    if (data.node.lvl === 0) {
                        //data node is root pick category do import
                        if (data.tree.children.length > data.category_index + 1) {
                            data.node = data.tree.children[data.category_index];
                        } else {
                            // no categories
                            def.resolve(data);
                            return;
                        }
                        // open selected category
                        // last forum last topic last post page will close it
                        data.open = data.node.id;
                        def.notify(data);
                    } else if (data.node.lvl === 1) {
                        //data node is category select forum
                        if (data.node.children.length > data.forum_index + 1) {
                            data.node = data.node.children[data.forum_index];
                            // open forum that we will work on
                            data.open = data.node.id;
                            def.notify(data);
                        } else {
                            // no more forums in cat
                            data.forum_index = 0;
                            if (data.tree.children.length > data.category_index + 1) {
                                //next category
                                data.ok = data.node.id;
                                def.notify(data);
                                data.node = data.tree.children[data.category_index++];
                                data.open = data.node.id;
                                def.notify(data);
                            } else {
                                //no more cats all done
                                data.ok = data.tree.id;
                                data.close = data.tree.id;
                                def.notify(data);
                                def.resolve(data);
                                return;
                            }
                        }
                    } else if (data.node.lvl === 2) {
                        if (data.topics_pages > data.topics_page) {
                            //fires on each topic page
                            //data.total.done = data.total.done + data.posts_imported + data.topics_limit;
                            //def.notify(data);
                            //data.posts_imported = 0;
                            data.topics_page++;
                        } else if (data.tree.children[data.category_index].children.length > data.forum_index + 1) {
                            //fires when topics ends but there is still forum to do in category
                            //data.total.done = data.total.done + data.topics_total;
                            data.topics_page = 0;
                            data.topic_index = null;
                            data.topics_total = null;
                            data.ok = data.node.id;
                            data.forum_index++;
                            data.node = data.tree.children[data.category_index].children[data.forum_index];
                            data.open = data.node.id;
                            def.notify(data);
                        } else if (data.tree.children.length > data.category_index + 1) {
                            //fires when category forum ends
                            //data.total.done = data.total.done + 1;
                            data.forum_index = 0;
                            data.topics_page = 0;
                            data.topic_index = null;
                            data.topics_total = null;
                            data.ok = data.tree.children[data.category_index].id;
                            data.close = data.tree.children[data.category_index].id;
                            data.category_index++;
                            data.node = data.tree.children[data.category_index];
                            data.open = data.node.id;
                            def.notify(data);
                        } else {
                            //fires when categories are done
                            data.ok = data.tree.children[data.category_index].children[data.forum_index].id;
                            def.notify(data);
                            data.ok = data.tree.children[data.category_index].id;
                            data.close = data.tree.children[data.category_index].id;
                            def.notify(data);
                            data.ok = data.tree.id;
                            data.close = data.tree.id;
                            def.notify(data);
                            def.resolve(data);
                            return;
                        }
                    } else if (data.node.lvl === 3) {
                        if (data.posts_pages > data.posts_page + 1) {
                            data.posts_page++;
                            def.notify(data);
                        } else {
                            data.node.lvl = 2;
                            data.posts_page = 0;
                            data.posts_pages = 0;
                            data.posts_total = 0;
                            data.topic = null;
                            def.notify(data);
                        }
                    } else {
                        console.log('wrong level');
                        return;
                    }
                    loop(data, def);
                });
            }
            )(data, def);
            return def.promise();
        }

        function initOtherTree() {
            readSettings();
            // init tree with node event handlers
            $('#other_check')
                    .bind('loaded.jstree', function (e, data) {
                    })
                    .jstree({
                    })
                    .on('after_close.jstree', function (e, data) {
                    })
                    .on('after_open.jstree', function (e, data) {
                    })
                    .on('ready.jstree', function (e, data) {
                    })
                    ;
            // add tree data source
            $('#other_check').jstree(true).settings.core.data = {
                'url': Routing.generate('zikuladizkusmodule_upgrade3_otherstatus'),
                "dataType": "json",
                "dataFilter": function (response) {
                    res = JSON.parse(response);
                    data.tree = res.tree;
                    data.source = res.source;
                    log('Other status loaded.');
                    if (data.source.favorites.toImport.length === 0
                            && data.source.moderators_users.toImport.length === 0
                            && data.source.moderators_groups.toImport.length === 0
                            && data.source.forum_subscriptions.toImport.length === 0
                            && data.source.topic_subscriptions.toImport.length === 0
                            ) {
                        $("#import_other").removeClass('btn-default').addClass('btn-success');
                        $("#remove_other").removeClass('hide disabled');
                        $("#recover_other").removeClass('btn-default disabled').addClass('btn-primary');
                    } else {
                        $("#recover_other").removeClass('btn-primary').addClass('btn-success');
                        $("#import_other").removeClass('disabled btn-default').addClass('btn-primary');
                    }
                    return JSON.stringify(data.tree);
                }
                ,
                'data': function (node) {
                    return node;
                }
            };
            // other tree bind buttons actions
            $("#recover_other").click(function (e) {
                e.preventDefault();
                getOtherStatus();
                $("#recover_other").addClass('disabled');
            });
            $("#import_other").click(function (e) {
                e.preventDefault();
                startOtherImport(data);
                $("#import_other").addClass('disabled');
            });
            $("#remove_other").click(function (e) {
                e.preventDefault();
                removeContent('other');
                $("#remove_other").addClass('disabled');
            });
//            $("#recover_other").removeClass('btn-default').addClass('btn-primary');
        }
        ;
        // simple load fresh data on open
        function getOtherStatus() {
            $("#other_check").jstree("close_node", $("#other_tree_root"));
            $("#other_check").jstree("open_node", $("#other_tree_root"));
        }

        function startOtherImport(data) {
            // import started indicator
            $("#other_tree_root").addClass('jstree-loading');
            $("#other_legend").prepend($(".progress").first().clone());
            $("#other_legend").find('.progress').removeClass('hide');
            var itemsToDo = data.source.favorites.toImport.length
                    + data.source.moderators_users.toImport.length
                    + data.source.moderators_groups.toImport.length
                    + data.source.forum_subscriptions.toImport.length
                    + data.source.topic_subscriptions.toImport.length
                    ;
            $("#other_legend").find('.progress-bar').attr('aria-valuemax', itemsToDo);
            $("#other_legend").find('.progress-bar').data("imported", 0);
            $("#other_legend").find('.progress-bar').data("rejected", 0);
            $("#other_legend").find('.count').text(' [ imported: ' + 0 + ' / rejected: ' + 0 + ' / total: ' + itemsToDo + '] ').css('color', '#000');
            otherImport(data.source.favorites).done(function () {
                otherImport(data.source.moderators_users).done(function () {
                    otherImport(data.source.moderators_groups).done(function () {
                        otherImport(data.source.forum_subscriptions).done(function () {
                            otherImport(data.source.topic_subscriptions).done(function () {
                                $("#other_legend").find('.progress-bar').addClass('progress-bar-success');
                                $("#other_tree_root").removeClass('jstree-loading').find('i').first().css('background-position', '-3px -66px');
                                $("#other_tree_root").jstree("close_node", '#other_tree_root');
                                $("#import_other").removeClass('btn-primary').addClass('btn-success');
                                $("#recover_other").removeClass('btn-default').addClass('btn-success');
                                $("#remove_other").removeClass('hide disabled');
                            });
                        });
                    });
                });
            });
        }

        function otherImport(data) {
            //console.log(data);
            var def = $.Deferred();
            if (data.toImport.length === 0) {
                def.resolve(data);
                return def.promise();
            }
            var $node = $("#" + data.source);
            $node.addClass('jstree-loading');
            def.progress(function (data) {
                var imported = parseInt($("#other_legend").find('.progress-bar').data("imported")) + data.imported;
                var rejected = parseInt($("#other_legend").find('.progress-bar').data("rejected")) + data.rejected;
                $("#other_legend").find('.progress-bar').data("imported", imported);
                $("#other_legend").find('.progress-bar').data("rejected", rejected);
                var done = parseInt($("#other_legend").find('.progress-bar').attr("aria-valuenow")) + data.imported + data.rejected;
                var total = parseInt($("#other_legend").find('.progress-bar').attr("aria-valuemax"));
                var percent = 100 * done / total;
                $("#other_legend").find('.progress-bar').css('width', percent + '%').attr('aria-valuenow', done).attr('aria-valuemax', total);
                $("#other_legend").find('.count').text(' [ imported: ' + imported + ' / rejected: ' + rejected + ' / total: ' + total + '] ');
            });
            def.done(function (data) {
                $node.removeClass('jstree-loading').find('a').find('i').removeClass('fa-orange').addClass('fa-green');
            });
            data.page = 0; // first page 0-49
            data.pageSize = settings.other_limit;
            data.pages = 0; // we do not know yet
            (function loop(data, def) {
                if (data.page < data.pages || data.pages === 0) {
                    importAjax(Routing.generate('zikuladizkusmodule_upgrade3_otherimport'), data).done(function (data) {
                        data.page++;
                        def.notify(data);
                        loop(data, def);
                    });
                } else {
                    def.resolve(data);
                }
            })(data, def);
            return def.promise();
        }

        // simple load fresh data on open
        function removeContent(source) {
            //confirmation dialog
            alert('Only manual content removal available at the moment :)');
            importAjax(Routing.generate('zikuladizkusmodule_upgrade3_removecontent'), source).done(function (data) {
                console.log(data);
            });
        }

        //ajax util
        function importAjax(url, data) {
            console.log(data);
            return $.ajax({
                type: 'POST',
                url: url,
                data: JSON.stringify(data),
                timeout: settings.ajax_timeout,
                contentType: "application/json",
                dataType: 'json'
            });
        }

        //return this and init when ready
        return {
            init: init
        };
    })();
    $(function () {
        Zikula.Dizkus.Upgrade3.init();
    });
}
)(jQuery);
