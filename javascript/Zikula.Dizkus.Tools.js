/**
 * Zikula.Dizkus.Tools.js
 *
 * jQuery based JS*
 */

/**
 * Scroll to an element.
 *
 * @param selector The element's selector to scroll to.
 * @param time The time to take (in milliseconds), default 1000.
 *
 * @note jQuery does not support .scrollTo() - calculate position manually.
 */
function scrollTo(selector, time) {
    var toPos = jQuery(selector).offset().top;
    if (!time) {
        // Scroll with the same speed always.
        time = Math.abs(jQuery(document).scrollTop() - jQuery(selector).offset().top);
    }
    jQuery('html, body').animate({
        scrollTop: toPos
    }, time);
}

/**
 * Show an ajax error.
 *
 * requires Zikula.js which employs prototype-based JS for the showajaxerror() function
 *
 * @param request
 * @param message
 * @param detail
 */
function showAjaxError(request, message, detail) {
    Zikula.showajaxerror(request.responseText);
}

/**
 * rewrite of Zikula.js::Zikula.toggleInput from Prototype to jQuery
 * Allows to check, uncheck or toggle given checkbox or radio inputs.
 *
 * If selector param is container ID all inputs of type radio or checkbox are affected.
 * If you need more specific conditions - use CSS selector for inputs (eg 'form.class input[type=radio]')
 *
 * example (sets all to state of '#checkallcheckbox'):
 *     jQuery('#checkallcheckbox').click(function() {
 *        DizkusToggleInput('.mycheckboxesclass', jQuery(this).prop('checked'));
 *    });
 *
 * @param {String} selector Container ID or CSS selector for inputs
 * @param {Boolean} [value=null] True to check, false to uncheck. Leave null to toggle status
 *
 * @return void
 */
function DizkusToggleInput(selector, value) {
    if (jQuery(selector)) {
        if (value === undefined) {
            // no value supplied, toggle values
            jQuery(selector).each(function() {
                var currentVal = jQuery(this).prop("checked");
                jQuery(this).prop("checked", !currentVal);
            });
        } else {
            // value supplied, set all to value
            jQuery(selector).prop("checked", value);
        }
    }
};