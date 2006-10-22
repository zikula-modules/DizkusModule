<?php
/**
 * $Id: function.adminlink.php 478 2005-12-09 10:22:39Z landseer $
 *
 * Smarty function to set the title for a page
 *
 * This plugin is for PostNuke 0.76x only and utilises a hack
 * based on the way the .7x news module works
 *
 * Example
 *
 * <!--[setforumtitle title=$myvar]-->
 *
 * @author       Mark West
 * @since        1/05/2006
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       null
 *
 * This is a copy of Mark's settitle plugin renamed as setforumtitle to add some pnForum specific stuff  and to void conflicts
 * with the original plugins as far as function names are concerned
 *
 */
function smarty_function_setforumtitle($params, &$smarty)
{
    if (!isset($params['title'])) {
        $smarty->trigger_error('setforumtitle: attribute title required');
        return false;
    }

    $GLOBALS['info']['title'] = strip_tags($params['title']);
	return;
}

?>