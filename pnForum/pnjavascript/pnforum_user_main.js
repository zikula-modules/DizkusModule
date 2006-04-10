/**
 * pnforum.js
 *
 * $Id$
 *
 */

function open(thiscat)
{
    $A(document.getElementsByClassName('category')).each(
        function(cat)
        {
            if(cat.id == 'category_' + thiscat) {
                if(Element.hasClassName(cat, 'hidden')) {
                    Element.removeClassName(cat, 'hidden');
                } else {
                    Element.addClassName(cat, 'hidden');
                }
            } else {
                Element.addClassName(cat, 'hidden');
            }
        }
        );
    return;
}

