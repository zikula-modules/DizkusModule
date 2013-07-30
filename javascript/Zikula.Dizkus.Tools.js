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
 * @param request
 * @param message
 * @param detail
 */
function showAjaxError(request, message, detail) {
    Zikula.showajaxerror(request.responseText);
}