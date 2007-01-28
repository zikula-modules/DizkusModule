<?php
/*

Usage

<!--[diggers ]-->
--> uses current url + sitename

<!--[diggers title=$info.title]-->
--> uses current url and given title

<!--[diggers title=$info.title url=$info.url]-->
--> uses given url and title

Displays "diggers", links to news and bookmark services for specified article

*/

/** start of poor man's language file **/
$lang = pnUserGetLang();
switch($lang) {
    case 'deu':
            define('_DIGGER_ADD', 'Beitrag %title% bei %service% hinzufügen');
            break;
    case 'eng':
    default:
            define('_DIGGER_ADD', 'Add %title% at %service%');
}
/** end of poor man's language file **/


function smarty_function_diggers($params, &$smarty) 
{
        extract($params); 
        
        if(!isset($title) || empty($title)) {
            $title = pnConfigGetVar('sitename');
        }
        
        if(!isset($url) || empty($url)) {
            // get the recent url
            $url = pnGetCurrentURL();
        }

        $theme = pnUserGetTheme();
        $imagepath = 'themes/' . pnVarPrepForOS($theme) . '/images/icons/';

        $link_title = pnVarPrepForDisplay("'" . $title . "'");
        $title = urlencode(pnVarPrepForDisplay($title));
        $url   = urlencode($url);
        
        //create array of possible options
        $options = array(
                       'Mister Wong'    => array('icon' => 'wong.gif',       'url' => 'http://www.mister-wong.de/index.php?action=addurl&amp;bm_url=%s&amp;bm_description=%s'), 
                       'Technorati'     => array('icon' => 'techfav.gif',    'url' => 'http://technorati.com/faves?add=%s'), 
                       'Digg'           => array('icon' => 'digg.png',       'url' => 'http://digg.com/submit?phase=2&amp;url=%s&amp;title=%s'), 
                       'del.icio.us'    => array('icon' => 'delicious.png',  'url' => 'http://del.icio.us/post?url=%s&amp;title=%s'), 
                       'ma.gnolia'      => array('icon' => 'magnolia.png',   'url' => 'http://ma.gnolia.com/bookmarklet/add?url=%s&amp;title=%s'), 
                       'Furl'           => array('icon' => 'furl.png',       'url' => 'http://www.furl.net/storeIt.jsp?u=%s&amp;t=%s'), 
                       'Newsvine'       => array('icon' => 'newsvine.png',   'url' => 'http://www.newsvine.com/_tools/seed&amp;save?u=%s&amp;h=%s'), 
                       'Reddit'         => array('icon' => 'reddit.png',     'url' => 'http://reddit.com/submit?url=%s&amp;title=%s'), 
                       'Yahoo MyWeb'    => array('icon' => 'yahoomyweb.png', 'url' => 'http://myweb2.search.yahoo.com/myresults/bookmarklet?u=%s&amp;t=%s'), 
                       'Blinkbits'      => array('icon' => 'blinkbits.png',  'url' => 'http://www.blinkbits.com/bookmarklets/save.php?v=1&amp;source_url=%s&amp;title=%s'), 
                       'Google'         => array('icon' => 'google.gif',     'url' => 'http://fusion.google.com/add?feedurl=%s&amp;title=%s'), 
                       'Simpy'          => array('icon' => 'simpy.png',      'url' => 'http://www.simpy.com/simpy/LinkAdd.do?href=%s&amp;title=%s'), 
                       'Blogmarks'      => array('icon' => 'blogmarks.png',  'url' => 'http://blogmarks.net/my/new.php?mini=1&amp;simple=1&amp;url=%s&amp;title=%s')
                       );
        $return = '';
        foreach($options as $servicename => $service) {
            $titlepart = _DIGGER_ADD;
            $titlepart = str_replace('%service%', $servicename, $titlepart);
            $titlepart = str_replace('%title%',   $link_title,  $titlepart);
            $return .= '<a href="' . sprintf($service['url'], $url, $title) . '" title="' . $titlepart . '"><img src="' . $imagepath . $service['icon'] . '" alt="' . $servicename . ' icon" /></a>';
        }

        if (isset($params['assign'])) {
            $smarty->assign($params['assign'], $return);
        } else {    
            return $return;
        }
}
?>