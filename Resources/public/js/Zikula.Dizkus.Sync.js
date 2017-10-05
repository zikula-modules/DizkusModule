/**
 * Zikula.Dizkus.Sync.js
 *
 * $ based JS
 */

// in case not exists
var Zikula = Zikula || {};
// Dizkus namespace
Zikula.Dizkus = Zikula.Dizkus || {};
(function ($, Routing, Translator) {
//Upgrade module
    Zikula.Dizkus.Sync = (function () {
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
            log(Translator.__('Sync init start.'));
            initTree();
            log(Translator.__('Sync init done.'));
        }
        ;
        function readSettings()
        {
//            settings.ajax_timeout = parseInt($("#ajax_timeout").val());
            log(Translator.__('Sync settings updated.'));
        }
        ;
        function log(log)
        {
            console.log(log);
        }
        ;
        function dataLog(log)
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
        /*
         * forum Tree
         */
        function initTree() {
            $('#sync_tree')
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



            $('#sync_tree')
                    .jstree(true)
                    .settings
                    .core
                    .data = {
                        'url': Routing.generate('zikuladizkusmodule_sync_forumtree'),
                        "dataType": "json",
                        "dataFilter": function (response) {
                            res = JSON.parse(response);
                            data.tree = res.tree;
                            data.total = res.total;
                            console.log(data);
//                            data.excluded = res.excluded;
//                            data.total.topics = data.total.topics - data.excluded.topics.length;
//                            data.total.posts = data.total.posts - data.excluded.posts.length;
//                            $("#forum_legend").prepend($(".progress").first().clone());
//                            $("#forum_legend").find('.progress').removeClass('hide');
//                            $("#forum_legend")
//                                    .find('.info')
//                                    .text(Translator.__('Forums (+categories):') + ' '
//                                            + data.total.forums + ' '
//                                            + Translator.__('Topics:') + ' '
//                                            + data.total.topics + ' '
//                                            + Translator.__('Posts:') + ' '
//                                            + data.total.posts)
//                                    .css('color', '#000');
//                            log(Translator.__('To import:') + $("#forum_legend").find('.info').text());
//                            log(Translator.__('Forum tree loaded.'));
//                            if (data.total.current > 1) {
//                                log(Translator.__('Error! Content detected in target tables.'));
//                                $("#remove_forum_tree").removeClass('hide disabled');
//                            } else {
//                                $("#recover_forum_tree").removeClass('btn-primary').addClass('btn-success');
//                                $("#import_forum_tree").removeClass('disabled').addClass('btn-primary');
//                            }
//                            $('<p id="rejected_topics_items" title="' + Translator.__('Rejected topics') + '" class="text-muted small"></p>')
//                                    .prepend('<i class="fa fa-hashtag " aria-hidden="true"></i> ' + Translator.__('Rejected topics') + ' <br />')
//                                    .appendTo($('#import_rejected'));
//                            $.each(data.excluded.topics, function (index, item) {
//                                var reason = decodeRejectedReason(item.reason);
//                                $('#rejected_topics_items')
//                                        .append('<span title="' + reason + '" class="text-muted small">\n\
//                            <i class="fa fa-hashtag text-danger" aria-hidden="true"></i><span class="rejected_id">' + item.id + ' ' + reason + ' </span></span>');
//                            });
//                            $('<p id="rejected_posts_items" title="' + Translator.__('Rejected posts') + '" class="text-muted small"></p>')
//                                    .prepend('<i class="fa fa-hashtag " aria-hidden="true"></i> ' + Translator.__('Rejected posts') + ' <br />')
//                                    .appendTo($('#import_rejected'));
//                            $.each(data.excluded.posts, function (index, item) {
//                                var reason = decodeRejectedReason(item.reason);
//                                $('#rejected_posts_items')
//                                        .append('<span title="' + reason + '" class="text-muted small">\n\
//                            <i class="fa fa-hashtag text-danger" aria-hidden="true"></i><span class="rejected_id">' + item.id + ' ' + reason + ' </span></span>');
//                            });

                            return JSON.stringify(prepateTreeData(data.tree));
                            ;
                        }
                        ,
                        'data': function (node) {
                            return {'id': node.id, 'text': node.name};
                        }
                    };
//            $(".jstree-node").bind('click', function (e) {
//                e.preventDefault();
//                e.stopPropagation();
//                return false;
//            });
//            $(".jstree-icon").bind('click', function (e) {
//                e.preventDefault();
//                e.stopPropagation();
//                return false;
//            });
        }
        ;
//        function getForumTree() {
//            $("#forum_tree").jstree("open_node", $("#forum_tree_root"));
//        }
//
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

        //ajax util
        function importAjax(url, data) {
//            console.log(data);
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
        Zikula.Dizkus.Sync.init();
    });
}
)(jQuery, Routing, Translator);
