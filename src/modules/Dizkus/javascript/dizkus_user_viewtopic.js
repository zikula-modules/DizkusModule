/**
 * dizkus_user.js
 */

jQuery(document).ready(function () {
    Zikula.define('Dizkus');

    jQuery("#toggletopiclock").click(changeTopicStatus);
    jQuery("#toggletopicsticky").click(changeTopicStatus);
    jQuery("#toggletopicsubscription").click(changeTopicStatus);
    jQuery("#toggletopicsolve").click(changeTopicStatus);

});

function changeTopicStatus(e) {
    var action;
    var i = jQuery(this);
    if (i.text() == lockTopic) {
        action = 'lock';
    } else if (i.text() == unlockTopic) {
        action = 'unlock';
    } else if (i.text() == stickyTopic) {
        action = 'sticky';
    } else if (i.text() == unstickyTopic) {
        action = 'unsticky';
    } else if (i.text() == subscribeTopic) {
        action = 'subscribe';
    } else if (i.text() == unsubscribeTopic) {
        action = 'unsubscribe';
    } else if (i.text() == solveTopic) {
        action = 'solve';
    } else if (i.text() == unsolveTopic) {
        action = 'unsolve';
    } else {
        console.log('Wrong action');
        return;
    }

    jQuery.ajax({
        type: "POST",
        data: {
            topic: jQuery('#topic_id').val(),
            action: action
        },
        url: Zikula.Config.baseURL + "ajax.php?module=Dizkus&func=changeTopicStatus",
        success: function(result) {
            if (result == 'successful') {
                if (action == 'lock') {
                    i.text(unlockTopic);
                } else if (action == 'unlock') {
                    i.text(lockTopic);
                } else if (action == 'sticky') {
                    i.text(unstickyTopic);
                } else if (action == 'unsticky') {
                    i.text(stickyTopic);
                } else if (action == 'subscribe') {
                    i.text(unsubscribeTopic);
                } else if (action == 'unsubscribe') {
                    i.text(subscribeTopic);
                } else if (action == 'solve') {
                    i.text(unsolveTopic);
                    jQuery('#topic_solved').removeClass('z-hide');
                } else if (action == 'unsolve') {
                    i.text(solveTopic);
                    jQuery('#topic_solved').addClass('z-hide');
                }
            } else {
                console.log(result);
                alert('Error! Erroneous result from locking/unlocking action.');
            }
        },
        error: function(result) {
            Zikula.showajaxerror(result);
            return;
        }
    });
    e.preventDefault();
}