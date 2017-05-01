/**
 * Zikula.Dizkus.TopicManager.js
 *
 * $ based JS
 */


(function ($) {
    $(function () {
        $("#toggletopiclock").attr('href', '#').click(changeTopicStatus);
        $("#toggletopicsticky").attr('href', '#').click(changeTopicStatus);
        $("#toggletopicsubscription").attr('href', '#').click(changeTopicStatus);
        $(".solvetopic").click(changeTopicStatus);
        $(".unsolvetopic").click(changeTopicStatus);

        // POST EDIT
        $('ul.post_list').on('click', '.quotepostlink', function (e) {
            e.preventDefault();
            var post = $(this).data('post');
            var quote_text = $('#post_content_' + post).text();
            var poster_name = $('#post_poster_name_' + post).text();
            quote(quote_text, poster_name);
        });
        $('ul.post_list').on('click', '.editpostlink', function (e) {
            e.preventDefault();
            var post = $(this).data('post');
            quickEdit(post);
            console.log('edit post');
        });


        // QUICK REPLY
        $('#quickreply').on('click', '#topic_reply_form_save', function (e) {
            e.preventDefault();
            if (typeof Scribite !== 'undefined') {
                Scribite.renderAllElements();
            }
            createQuickReply($(this));
        });

        $('#quickreply').on('click', '#quickreply_preview_tab', function (e) {
            //e.preventDefault();
            previewQuickReply($(this));
        });

        $('#quickreply').on('click', '#topic_reply_form_preview', function (e) {
            e.preventDefault();
            previewQuickReply($(this));
        });

        $('#quickreply').on('click', '#topic_reply_form_cancel', function (e) {
            e.preventDefault();
            cancelQuickReply($(this));
        });

//        // Show cancel button.
//        $('#btnCancelQuickReply').show();

        $('a.disabled').click(function (e) {
            e.preventDefault();
            //do other stuff when a click happens
        }).hover(function () {
            $(this).css('cursor', 'not-allowed');
        }, function () {
            $(this).css('cursor', 'default');
        });
        // toggle visibility of edit icon for topic title
        $('#edittopicsubjectbutton').hover(
                function () {
                    if (typeof $('#userAllowedToEdit').val() !== "undefined") {
                        $('#edittopicicon').show();
                    }
                },
                function () {
                    $('#edittopicicon').hide();
                }
        );
        if (typeof $('#userAllowedToEdit').val() !== "undefined") {


            $('#edittopicsubjectbutton').addClass('editabletopicheader tooltips').attr('title', Translator.__('Foo bar baz')).tooltip();
            $('#edittopicsubjectbutton').click(function () {
                $('#topicsubjectedit_editor').show();
            });
            $('#topicsubjectedit_cancel').click(function () {
                $('#topicsubjectedit_editor').hide();
            });
            $("#topicsubjectedit_save").click(changeTopicTitle);
        }
    });

    function changeTopicStatus(e) {
        e.preventDefault();

        var action;
        var i = $(this);
        action = i.data('action');

        $.ajax({
            type: "POST",
            data: {
                topic: $('#topic_id').val(),
                post: i.data('post'),
                action: action
            },
            url: Routing.generate('zikuladizkusmodule_ajax_changetopicstatus'),
            success: function (result) {
                if (result == 'successful') {
                    switch (action) {
                        case 'lock':
                            i.attr('title', unlockTopic).removeClass('fa-lock').addClass('fa-unlock');
                            i.data('action', 'unlock');
                            $('#dzk_quickreply').hide("slow"); // hide quick reply
                            break;
                        case 'unlock':
                            i.attr('title', lockTopic).removeClass('fa-unlock').addClass('fa-lock');
                            i.data('action', 'lock');
                            $('#dzk_quickreply').show("slow"); // show quick reply
                            break;
                        case 'sticky':
                            i.attr('title', unstickyTopic).empty().html(unstickyTopicIcon);
                            i.data('action', 'unsticky');
                            break;
                        case 'unsticky':
                            i.attr('title', stickyTopic).empty().html(stickyTopicIcon);
                            i.data('action', 'sticky');
                            break;
//                        case 'subscribe':
//                            i.attr('title', unsubscribeTopic).empty().html(unsubscribeTopicIcon);
//                            i.data('action', 'unsubscribe');
//                            break;
//                        case 'unsubscribe':
//                            i.attr('title', subscribeTopic).empty().html(subscribeTopicIcon);
//                            i.data('action', 'subscribe');
//                            break;
                        case 'solve':
                            $('#solutionPost_' + i.data('post')).show();
                            $('.solvetopic').hide();
                            $('#topic_solved').show();
                            $('#topic_unsolved').hide();
                            break;
                        case 'unsolve':
                            $('#solutionPost_' + i.data('post')).hide();
                            $('.solvetopic').show();
                            $('#topic_solved').hide();
                            $('#topic_unsolved').show();
                            break;
                    }

                    // destroy and recreate tooltip
                    i.tooltip('destroy').tooltip();
                } else {
                    alert('Error! Erroneous result from changing topic status action.');
                }
            },
            error: function (result) {
                DizkusShowAjaxError(result.responseText);
                return;
            }
        });
    }


    function changeTopicTitle(e) {

        $.ajax({
            type: "POST",
            data: {
                topic: $('#topic_id').val(),
                title: $('#topicsubjectedit_subject').val(),
                userAllowedToEdit: $('#userAllowedToEdit').val(),
                action: 'setTitle'
            },
            url: Routing.generate('zikuladizkusmodule_ajax_changetopicstatus'),
            success: function (result) {
                if (result == 'successful') {
                    $('#topicsubjectedit_editor').hide();
                    $('#topic_title').text($('#topicsubjectedit_subject').val());
                } else {
                    console.log(result);
                    alert('Error! Erroneous result when attempting to change topic title.');
                }
            },
            error: function (result) {
                DizkusShowAjaxError(result.responseText);
                return;
            }
        });
        e.preventDefault();
    }



// Quick edit features

    /**
     * True if a post is currently edited.
     * @type {boolean}
     */
    var postEditing = false;

    /**
     * False as long as the user has not changed the post.
     * @type {boolean}
     */
    var postEditingChanged = false;

    /**
     * The post id of the post currently edited.
     *
     * This is false if no post is edited at the moment.
     */
    var postId = false;

    /**
     * Shows an ajax indicator for a post or a quick reply.
     * spinner icon is located in the template so text is appended then .show() parent div
     * @param text The text to show next to the icon.
     * @param postId If set, the ajax indicator will be shown for a post, else for a quick reply.
     */
    function showAjaxIndicator(text, postId) {
        if (postId) {
            text = '<span id="ajaxindicatortext_' + postId + '">&nbsp;' + text + '</span>';
            $('#dizkusinformation_' + postId).append(text).show();
        } else {
            text = '<span id="ajaxindicatortext_0">&nbsp;' + text + '</span>';
            $('#dizkusinformation_0').append(text).show();
        }
    }

    /**
     * Hides an ajax indicator for a post or a quick reply.
     * removes appended text then .hide() the parent div
     * @param postId If set, the ajax indicator will be hidden for a post, else for a quick reply.
     */
    function hideAjaxIndicator(postId) {
        if (postId) {
            $('#ajaxindicatortext_' + postId).remove();
            $('#dizkusinformation_' + postId).hide();
        } else {
            $('#ajaxindicatortext_0').remove();
            $('#dizkusinformation_0').hide();
        }
    }

    /**
     * Edit a post.
     *
     * @param id The post id.
     */
    function quickEdit(id) {
        var successHandler = function (result, message, request) {
            // Hide post footer
            $('#postingoptions_' + postId).hide();
            // Overwrite posting text.
            $('#postingtext_' + postId).hide().after(result.data);

            // Hide quickreply
            $('#dzk_quickreply').fadeOut();

            // Observe buttons
            $('#postingtext_' + postId + '_edit').keyup(quickEditChanged);
            $('#postingtext_' + postId + '_save').click(quickEditSave);
            $('#postingtext_' + postId + '_cancel').click(quickEditCancel);

            // notify bbSmile & bbCode (uncomfortably tight coupling)
            if (typeof bbsmileLastFocus !== "undefined") {
                bbsmileLastFocus = $('#postingtext_' + postId + '_edit');
            }
            if (typeof bbcodeLastFocus !== "undefined") {
                bbcodeLastFocus = $('#postingtext_' + postId + '_edit');
            }

        }, errorHandler = function (request, message, detail) {
            postEditing = false;
            postId = false;
            DizkusShowAjaxError(request.responseText);
        };

        if (!postEditing) {
            postEditing = true;
            postEditingChanged = false;
            postId = id;

            $.ajax({
                data: {post: postId},
                url: Routing.generate('zikuladizkusmodule_ajax_editpost')
            }).done(successHandler).fail(errorHandler).always(function () {
                hideAjaxIndicator(postId);
            });
            showAjaxIndicator(zLoadingPost + '...', postId);
        }
    }

    /**
     * Tell the user that he has changed the text.
     */
    function quickEditChanged() {
        if (!postEditingChanged) {
            postEditingChanged = true;
            $('#postingtext_' + postId + '_status').html('<span style="color: red;">' + zChanged + '</span>');
        }
    }

    /**
     * Save edited post.
     */
    function quickEditSave() {
        var newPostMsg = $('#postingtext_' + postId + '_edit').val(),
                pars = {
                    postId: postId,
                    message: newPostMsg,
                    attach_signature: ($('#postingtext_' + postId + '_attach_signature').prop('checked')) ? 1 : 0,
                    delete_post: 0 /* Do not use 'delete' here, this is a reserved word. */
                };

        if (!newPostMsg) {
            // no text
            return;
        }

        if ($('#postingtext_' + postId + '_delete').prop('checked')) {
            $('#postingtext_' + postId + '_status').html('<span style="color: red;">' + zDeletingPost + '...</span>');
            pars['delete_post'] = 1;
        } else {
            $('#postingtext_' + postId + '_status').html('<span style="color: red;">' + zUpdatingPost + '...</span>');
        }

        var successHandler = function (result, message, request) {
            var action = result.data.action,
                    redirect = result.data.redirect,
                    newText = result.data.newText;
//        if (message.length > 0) {
//            alert(message);
//        }

            postEditing = false;
            postEditingChanged = false;

            // Remove editor.
            $('#postingtext_' + postId + '_editor').remove();

            if (action === 'deleted') {
                // Remove post
                $('#posting_' + postId).fadeOut();
            } else if (action === 'topic_deleted') {
                // Remove post
                $('#posting_' + postId).fadeOut();
                // Redirect to overview url.
                window.setTimeout("window.location.href='" + redirect + "';", 500);
                return;
            } else {
                // Insert new text.
                $('#postingtext_' + postId).html(newText).show();
            }

            // Show quickreply
            $('#dzk_quickreply').fadeIn();

            // Show post footer
            $('#postingoptions_' + postId).show();
        }, errorHandler = function (request, message, detail) {
            DizkusShowAjaxError(request.responseText);
        };
        $.ajax({
            data: pars,
            url: Routing.generate('zikuladizkusmodule_ajax_updatepost')
        }).done(successHandler).fail(errorHandler);
    }

    /**
     * Cancel editing a post.
     */
    function quickEditCancel() {
        // Show post footer
        $('#postingoptions_' + postId).show();

        // Show post text
        $('#postingtext_' + postId).show();

        // Show quickreply
        $('#dzk_quickreply').fadeIn();

        // Remove post editor
        $('#postingtext_' + postId + '_editor').remove();

        postEditing = false;
        postEditingChanged = false;
    }


// Quick reply features.

    /**
     * True if the user is in the quick reply process.
     * @type {boolean}
     */
    var quickReplying = false;

    /**
     * Saves and shows the new post.
     * @return {boolean} Used to not to submit the normal, non-ajax form.
     */
    function createQuickReply($el) {
        if (!quickReplying) {

            $form = $el.parents('form').first();
            $form.submit(function (e) {
                e.preventDefault();
            });

            var formData = $form.serializeArray();
            formData.push({name: $el.attr('name'), value: 1});

            quickReplying = true;

            var successHandler = function (result, message, request) {
                //cancelQuickReply();
                $('#quickreply').html(result);

                $('ul.post_list').append($('#quickreplynewpost').html());

                quickReplying = false;

                // Hook into edit link to work via ajax.
                //hookEditLinks();

            }, errorHandler = function (request, message, detail) {
                DizkusShowAjaxError(request.responseText);
                quickReplying = false;
            };
            $.ajax({
                data: formData,
                url: Routing.generate('zikuladizkusmodule_topic_replytopic', {'topic': $('#topic_reply_form_topic').val()})
            }).done(successHandler).fail(errorHandler).always(function () {
                hideAjaxIndicator('quickreply');
            });
            showAjaxIndicator(zStoringReply + '...', 'quickreply');

        }
        return false;
    }

    /**
     * Shows a preview of the quick reply.
     * @return {boolean}
     */
    function previewQuickReply($el) {

        if (!quickReplying) {

            $('#previewAjaxStatus').toggleClass('hide');
            $form = $el.parents('form').first();

            var formData = $form.serializeArray();
            formData.push({name: $el.attr('name'), value: 1});

            quickReplying = true;

            var successHandler = function (result, message, request) {
                // Show preview.
                $('#quickreply_preview').html(result);
                $('#quickreply_tabs a[href="#quickreply_preview"]').tab('show');
                // Scroll to preview.
                //scrollTo('#quickreply');
                quickReplying = false;
            }, errorHandler = function (request, message, detail) {
                DizkusShowAjaxError(request.responseText);
                quickReplying = false;
                $('#previewAjaxStatus').toggleClass('hide');
            };
            $.ajax({
                method: "POST",
                dataType: "html",
                data: formData,
                url: Routing.generate('zikuladizkusmodule_post_preview')

            }).done(successHandler).fail(errorHandler).always(function () {
                $('#previewAjaxStatus').toggleClass('hide');
            });
        }
    }

    /**
     * Aborts quick replying by emptying the message field and hiding previews.
     */
    function cancelQuickReply($el) {

        $('#topic_reply_form_message').val("");
        $('#replypreview').addClass('hidden');
        quickReplying = false;
    }

})(jQuery);