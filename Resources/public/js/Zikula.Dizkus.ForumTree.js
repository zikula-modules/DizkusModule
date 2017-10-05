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
    Zikula.Dizkus.ForumTree = (function () {
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
            log(Translator.__('Forum init start.'));
//            prepareTree().done(initTree());
            log(Translator.__('Forum init done.'));
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
        function prepareTree()
        {
            log(Translator.__('Tree preparation start.'));
            var def = $.Deferred();
            $('.noajax-actions').remove();
            // define the promise that is called once all element animations are done
            $('.noajax-actions').promise().done(function () {
                def.resolve();
            });
            log(Translator.__('Tree preparation done.'));
            return def.promise();
        }
        ;
        // initialise jstree's
        /*
         * forum Tree
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
            var items = {
                "Create": {
                    "label": Translator.__('Edit forum'),
                    "action": function (obj) {
                        this.create(obj);
                    }
                },
                "Lock": {
                    "label": Translator.__('Lock forum'),
                    "action": function (obj) {
                        this.rename(obj);
                    }
                },
                "Delete": {
                    "label": Translator.__('Delete forum'),
                    "action": function (obj) {
                        this.remove(obj);
                    }
                }
            };

            return items;
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
        Zikula.Dizkus.ForumTree.init();
    });
}
)(jQuery, Routing, Translator);
