/**
 * Zikula.Dizkus.PostManager.js
 *
 * $ based JS
 */

(function ($) {


    $(function () {

        $.ajax({
            type: "POST",
//        data: {
//            action: 'setTitle'
//        },
            dataType: "html",
            url: Routing.generate('zikuladizkusmodule_post_report', {'post': 5}),
            success: function (result) {
                if (result === 'successful') {
                    console.log($("#post_report"));
                    // $('#post_report').html(result.html);
                } else {
                    console.log(result);
                }
            },
            error: function (result) {
                console.log(result.responseText);
                return;
            }
        });

    });




})(jQuery);