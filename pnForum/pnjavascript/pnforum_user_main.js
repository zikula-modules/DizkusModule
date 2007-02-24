/**
 * pnforum.js
 *
 * $Id$
 *
 */

Event.observe(window, 'load', 
    function()
    {
        if($A(document.getElementsByClassName('openclosecategory')).length == 0) {
            // no categories defined - return now
            return;
        }
        
        // define a rule to delete a menuitem when the trashcan icon is clicked
        var ruleset = {
        	'#pnf_maincategorylist .openclosecategory' : function(catlink){
                catlink.href = 'javascript:void(0);';
                Event.observe(catlink, 
                              'click', 
                              function()
                              { 
                                  var thiscat = catlink.id.split('_')[1];
                                  $A(document.getElementsByClassName('category')).each(
                                      function(cat)
                                      {
                                          if(cat.id == 'category_' + thiscat) {
                                              cat.toggleClassName('hidden');
                                          } else {
                                              Element.addClassName(cat, 'hidden');
                                          }
                                      }
                                      );
                                  return;
                              },
                              false);
        	}
        };
        
        // register the ruleset
        Behaviour.register(ruleset);
        
        // apply the ruleset to all existing delete buttons
        Behaviour.apply();
    }, 
    false);


