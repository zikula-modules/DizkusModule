/**
 * Zikula.Dizkus.Forum.modify.js
 *
 * jQuery based JS
 */

var Zikula = Zikula || {};
// Dizkus namespace
Zikula.Dizkus = Zikula.Dizkus || {};
// Dizkus Forum
Zikula.Dizkus.Forum = Zikula.Dizkus.Forum || {};

(function ($, Routing, Translator) {
    Zikula.Dizkus.Forum.modify = (function () {
	var settings = {
	    ajax_timeout: 5000
	};
	
        // Init
        function init()
        {
            log(Translator.__('Forum modify init start.'));
	    
	    moderatorUsersSelect();
	    moderatorGroupsSelect(); // not used
	    
            log(Translator.__('Forum modify init done.'));
        }
        ;
	function log(msq) {
	   console.log(msq);
	}
	;
	function moderatorUsersSelect() {
	    var $userModerators = $('.user-moderators');
	    $userModerators.find('.chosen-select').chosen({
		width: '100%',
		no_results_text: Translator.__('Please enter at least 3 characters for search to start. Entered: ') // during ajax search
	    });

	    $userModerators.find('input').autocomplete({
		minLength: 3,
		source: function( request, response ) {
		    $.ajax({
			type: "GET",
			url: Routing.generate('zikulausersmodule_livesearch_getusers', { fragment: request.term }),
			dataType: "json",
			beforeSend: function(){
			    chosenUpdateList($userModerators, '<li><i class="fa fa-circle-o-notch fa-spin"></i> ' + Translator.__('Searching for users starting with') + ' "' + request.term + '" </li>');
			}
		    }).done(function(data) {
			if (data.length > 0) {
			    response($.map( data, function( item ) {
				$userModerators
					.find('select')
					.append('<option value="'+ item.uid +'">' + item.uname + '</option>');
			    }));
			    $userModerators.find('.chosen-select').trigger("chosen:updated");
			} else {
			    chosenUpdateList($userModerators, '<li>' + Translator.__('Sorry users starting with') + ' "' + request.term + '" ' + Translator.__('not found') + '</li>');
			}
		    });
		}
	    });
	    
	}
	;
	function moderatorGroupsSelect() {
	    var $groupModerators = $('.group-moderators');
	    $groupModerators.find('.chosen-select').chosen({
		width: '100%'
	    });
	}
	;	
	function chosenUpdateList($parent, html) {
	    $parent
		.find('ul.chosen-results')
		.html(html);
	}
	;

        //expose actions
        return {
            init: init
        };
    })();
    //autoinit
    $(function () {
        Zikula.Dizkus.Forum.modify.init();
    });
}
)(jQuery, Routing, Translator);



//jQuery(document).ready(function() {
//    // set up chosen lib
//    jQuery('.chzn-select').chosen({
//        width: '100%'
//    });
//    // on click handlers
//    jQuery('#noexternal').click(function() {
//        jQuery('#mail2forumField').hide("slow");
//        jQuery('#rss2forumField').hide("slow");
//        jQuery('#logindata').hide("slow");
//    });
//    jQuery('#mail2forum').click(function() {
//        jQuery('#mail2forumField').show("slow");
//        jQuery('#logindata').show("slow");
//    });
//    jQuery('#rss2forum').click(function() {
//        jQuery('#rss2forumField').show("slow");
//        jQuery('#logindata').show("slow");
//    });
//});

