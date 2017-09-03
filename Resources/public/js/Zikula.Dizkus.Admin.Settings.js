/**
 * Zikula.Dizkus.Admin.Settings.js
 *
 * $ based JS
 */

// in case not exists
var Zikula = Zikula || {};
// Dizkus namespace
Zikula.Dizkus = Zikula.Dizkus || {};
// Admin namespace
Zikula.Dizkus.Admin = Zikula.Dizkus.Admin || {};
(function ($) {
//Upgrade module
    Zikula.Dizkus.Admin.Settings = (function () {
// Init
        var data = {
            log: null
        };
        var settings = {
//            users_limit: 50,
//            topics_limit: 25,
//            posts_limit: 50,
//            other_limit: 50,
//            ajax_timeout: 8000
        }
        ;
        function init()
        {
//            if (parseInt($('#upgrade3_enabled').val()) === 0) {
//
//                return;
//            }
//            initTrees();
//            log('Upgrade init done.');


            $('[data-toggle=popover]:not([data-popover-content])').popover();

            $('[data-toggle=popover][data-popover-content]').popover({
                html: true,
                content: function () {
                    var content = $(this).attr("data-popover-content");
                    return $(content).children(".popover-body").html();
                },
                title: function () {
                    var title = $(this).attr("data-popover-content");
                    return $(title).children(".popover-heading").html();
                }
            });


        }
        ;
        function readSettings()
        {
//            settings.users_limit = parseInt($("#users_limit").val());
//            settings.topics_limit = parseInt($("#topics_limit").val());
//            settings.posts_limit = parseInt($("#posts_limit").val());
//            settings.other_limit = parseInt($("#other_limit").val());
//            settings.ajax_timeout = parseInt($("#ajax_timeout").val());
//            log('Upgrade settings updated.');
        }
        ;
        function log(log)
        {
//            if (log === '') {
//            } else if (log === null) {
//            } else if (log.constructor === Array) {
//                $('#logBox').prepend(log.join('&#xA;') + '&#xA;');
//            } else {
//                $('#logBox').prepend(log + '&#xA;');
//            }
        }
        ;
        // initialise jstree's
        // due to forum data nature upgrade use jstree as ui base
        function initTrees() {
//            $('#tables_check').jstree({});
//            initUsersTree();
//            initForumTree();
//            initOtherTree();
        }
        ;

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
        Zikula.Dizkus.Admin.Settings.init();
    });
}
)(jQuery);
