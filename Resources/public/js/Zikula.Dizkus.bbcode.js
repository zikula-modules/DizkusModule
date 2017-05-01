/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// @todo finish this

console.log('bb code enabled');

(function ($) {
    $(function () {

        // Quote
        $('ul.post_list').on('click', '.quotepostlink', function (e) {
            e.preventDefault();
            var post = $(this).data('post');
            var quote_text = $('#post_content_' + post).text();
            var poster_name = $('#post_poster_name_' + post).text();

            quote(quote_text, poster_name);
        });

        /**
         * Quote a text.
         *
         * @param text
         */
        function quote(text, uname) {
            //text = text.replace(/_____LINEFEED_DIZKUS_____/g, "\n");

            text = '[quote=' + uname + ']' + text + '[/quote]';

            $('#zikula_dizkus_form_topic_reply_post_text').val($('#topic_reply_form_message').val() + text);

            scrollTo("#quickreply");
        }

        /**
         * Scroll to an element.
         *
         * @param selector The element's selector to scroll to.
         * @param time The time to take (in milliseconds), default 1000.
         *
         * @note jQuery does not support .scrollTo() - calculate position manually.
         */
        function bold(text) {
            //text = text.replace(/_____LINEFEED_DIZKUS_____/g, "\n");

            text = '[b]' + text + '[/b]';

            $('#zikula_dizkus_form_topic_reply_post_text').val($('#topic_reply_form_message').val() + text);

            scrollTo("#quickreply");
        }

    });
})(jQuery);