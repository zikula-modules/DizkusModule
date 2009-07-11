<?php
// $Id$

/**
 * @author       Frank Schummertz
 * @modified     Charlie, Carsten Volmer
 * @since        09.11.2006
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 */

function smarty_function_websnapr($params, &$smarty)
{
    $out = "<link rel=\"stylesheet\" href=\"config/plugins/websnapr/websnapr.css\" type=\"text/css\" />\n"
          ."<script type=\"text/javascript\" src=\"config/plugins/websnapr/websnapr.js\"></script>\n"
          ."<script type=\"text/javascript\">\n"
          ."    webSnapr.setbaseurl('" . pnGetBaseURL() . "');\n" 
          ."    webSnapr.setimageuri('" . pnGetBaseURI() . "images/websnapr');\n" 
          ."    webSnapr.addEvent(window, ['load'], webSnapr.init);\n"
          ."</script>\n\n";  

    PageUtil::addVar('rawtext', $out);
}
