/**
 * Zikula.Dizkus.ForumTree.js
 *
 * $ based JS
 */
var Zikula = Zikula || {};
// Dizkus namespace
Zikula.Dizkus = Zikula.Dizkus || {};
(function ($, Routing, Translator) {
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
            log(Translator.__('Forum tree init start.'));

            startListeners();

            log(Translator.__('Forum tree init done.'));
        }
        ;
        function readSettings()
        {
//            settings.ajax_timeout = parseInt($("#ajax_timeout").val());
            log(Translator.__('Forum tree settings updated.'));
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
        function startListeners()
        {
            $(".js-switch").bind('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                log('work');
                prepareTree().done(initTree());
                $(this).addClass('disabled');
            });

            log(Translator.__('Forum tree listeners started.'));
        }
        ;
        function prepareTree()
        {
            log(Translator.__('Tree preparation start.'));
            var def = $.Deferred();
            $('.noajax-actions').remove();
            // define the promise that is called once all elements are removed
            $('.noajax-actions').promise().done(function () {
                def.resolve();
            });
            $('.forum-title').attr('class', 'forum-title');
            log(Translator.__('Tree preparation done.'));
            return def.promise();
        }
        ;
        /*
         * Forum Tree
         *
         * initialise jstree's
         */
        function initTree() {
            $('#forum_tree')
                    .jstree({
                        'core': {
                            'check_callback': true
                        }
                        ,
                        "plugins": [
                            "checkbox",
                            "dnd",
                            "contextmenu"
                        ],
                        'checkbox': {
                            'keep_selected_style': false,
                            'three_state': false,
                            'cascade': ''
                        },
                        'contextmenu': {
                            select_node: false,
                            items: itemMenu
                        }
                    });
        }
        ;

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

        function itemMenu(node)
        {
            log(node);
            var items = {
                "Modify": {
                    "label": Translator.__('Modify'),
                    "icon": 'fa fa-pencil',
                    "action": function (obj) {
                        log(obj);
                    }
                },
                "Lock": {
                    "label": Translator.__('Lock'),
                    "icon": 'fa fa-lock',
                    "action": function (obj) {
                        log(obj);
                    }
                },
                "Sync": {
                    "label": Translator.__('Sync'),
                    "icon": 'fa fa-refresh',
                    "action": function (obj) {
                        log(obj);
                    }
                },
                "Delete": {
                    "label": Translator.__('Delete'),
                    "icon": 'fa fa-trash',
                    "separator_before": true,
                    "action": function (obj) {
                        log(obj);
                    }
                }
            };

            return items;
        }

        //ajax util
        function importAjax(url, data) {
            return $.ajax({
                type: 'POST',
                url: url,
                data: JSON.stringify(data),
                timeout: settings.ajax_timeout,
                contentType: "application/json",
                dataType: 'json'
            });
        }

        //expose actions
        return {
            init: init
        };
    })();
    //autoinit
    $(function () {
        Zikula.Dizkus.Sync.init();
    });
}
)(jQuery, Routing, Translator);
