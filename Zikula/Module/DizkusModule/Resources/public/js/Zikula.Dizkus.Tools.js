/**
 * Zikula.Dizkus.Tools.js
 *
 * jQuery based JS
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
}
;

/**
 * rewrite of Zikula.js::Zikula.showajaxerror from Prototype to jQuery
 * Shows an error message with alert().
 *
 * @todo beautify this
 *
 * @param {String} errortext The text to show.
 *
 * @return void
 */
function DizkusShowAjaxError(errortext) {
    if ((jQuery.type(errortext) === "string") && errortext.isJSON()) {
        var decoded = jQuery.parseJSON(errortext); //errortext.evalJSON(true);
        if (decoded.core && decoded.core.statusmsg) {
            if (jQuery.type(decoded.core.statusmsg) === "object") {
                if (jQuery.type(decoded.core.statusmsg) !== "array") {
                    decoded.core.statusmsg = decoded.core.statusmsg.val();
                }
                errortext = decoded.core.statusmsg.join("\n");
            } else {
                errortext = decoded.core.statusmsg;
            }
        }
    } else if (jQuery.type(errortext) === "array") {
        errortext = errortext.join("\n");
    } else if (jQuery.type(errortext) === "object") {
        errortext = errortext.val().join("\n");
    }
    if (errortext) {
        alert(errortext);
    }
    return;
}
;

/**
 * helper function to determine if a string is JSON formatted
 * taken from https://github.com/DarkMantisCS/isJSON
 * @license - no license documented. Assuming PD
 */
(function($) {
    $.isJSON = function(json) {
        json = json.replace(/\\["\\\/bfnrtu]/g, '@');
        json = json.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']');
        json = json.replace(/(?:^|:|,)(?:\s*\[)+/g, '');
        return (/^[\],:{}\s]*$/.test(json));
    };
    $.fn.isJSON = function() {
        var json = this;
        if (jQuery(json).is(":input")) {
            json = jQuery(json).val();
            json = new String(json);
            return jQuery.isJSON(json);
        } else {
            throw new SyntaxError("$(object).isJSON only accepts fields!");
        }
    };
    String.prototype.isJSON = function() {
        var y = this;
        return jQuery.isJSON(y);
    };
})(jQuery);