/**
 * dizkus_tools.js
 */
Zikula.define('Dizkus');

Zikula.Dizkus.BaseClass = Class.create({
    initialize: function() { /* nothing to do here atm */ },

    getcheckboxvalue: function(id) {
        if($(id)) {
            if($(id).checked==true) {
                return $(id).value;
            }
            return '';
        }
    },

    /*
     * show an ajax error
     * to-do: beautify this function
     */
    showajaxerror: function(error) {
        alert(error);
    },

    showdizkusinfo: function(infotext) {
        if($('dizkusinformation')) {
            $('dizkusinformation').update(infotext).setStyle({visibility: 'visible'});
        }
    },

    hidedizkusinfo: function() {
        if($('dizkusinformation')) {
            $('dizkusinformation').update('&nbsp;').setStyle({visibility: 'hidden'});
        }
    },

    redirect: function(redirecturl) {
        window.location.href = redirecturl;
    },

    checkAll: function(formtype) {
        $$('.' + formtype + '_checkbox').each(function(el) {
            el.checked = $('all' + formtype).checked;
        });
    },

    checkCheckAll: function(formtype) {
        var totalon = 0;
        $$('.' + formtype + '_checkbox').each(function(el) {
            if (el.checked) {
                totalon++;
            }
        });
        $('all' + formtype).checked = ($$('.' + formtype + '_checkbox').length==totalon);
    }

});
