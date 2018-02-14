/**
 * Zikula.Dizkus.Post.latest.js
 *
 * jQuery based JS
 */
var Zikula = Zikula || {};
// Dizkus namespace
Zikula.Dizkus = Zikula.Dizkus || {};
// Dizkus Post
Zikula.Dizkus.Post = Zikula.Dizkus.Post || {};

(function ($) {
    Zikula.Dizkus.Post.latest = (function () {
	
	var $form = null;
	
	// Init
        function init()
        {
	    $form = $('#latest-form');
	    bindEvents();
        };
	
	// bind events
	function bindEvents() {
	    // clean empty fields
	    $form.submit(onSubmit);
	    $form.find('input[name="hours"]').on('input', clearSince);
	    $form.find('input[name="since"]').on('click change', clearHours);
	};
	
	/**
	 * On submit
	 */
	function onSubmit() {
	    if ($form.find('input[name="hours"]').val() !== '') {
		clearSince();
	    }
	    clearEmpty();
	    
	    return true;
	};
	
	function clearEmpty() {
	    $form.find(":input").filter(function(){ return !this.value; }).attr("disabled", "disabled");
	};
	
	function clearSince() {
	    $form.find('input[name="since"]').each( function (){
		$(this).prop('checked', false).closest("label").removeClass("active");
	    });
	};
	
	function clearHours() {
	    $form.find('input[name="hours"]').val('');
	};
	
        //expose actions
        return {
            init: init
        };
    })();

    //autoinit
    $(function () {
        Zikula.Dizkus.Post.latest.init();
    });
    
})(jQuery);
