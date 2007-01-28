Diggers Plugin 3.0
------------------
(landseer + kafferinge.de, 28.01.2007)
bases on the original version by InvalidResponse

This plugin creates several links to add an url to a number of social bookmark
sites.

new: 
* link to mister-wong.de and technorati.com
* generic support of all modules ;-)
* multilingual

Installation
------------

* copy the plugin to themes/<theme-name>/plugins
* copy the images to themes/<theme-name>/images/icons

* add this to the stylesheet:

p.diggers {
    margin-top: 1em; /* optional */
	padding:5px;
	background:#eee;
	border:1px solid #ccc;
}

p.diggers a {
    margin-left: 15px;
    padding-left: 0 ! important;
    background: none no-repeat bottom left ! important;
}

The last one is needed to avoid unnecessary icons when using a CSS3 feature
which marks external links.

The plugin supports German and English right now, other languages can be added
in the plugin itself. See the top of the file for more information. Please send 
new translations to landseer@pn-cms.de for inclusion in the next version.

There are no more changes necessary in the plugin.

Template changes:
-----------------

Possible usages are:

<p class="diggers"><!--[diggers ]--></p>

--> uses the current url and the configured sitename

<p class="diggers"><!--[diggers title=$title]--></p>

--> uses the current url and the given title, probably the best way to use this
    plugin

<p class="diggers"><!--[diggers title=$title url=$url]--></p>

--> uses the given url and title

Caution: When using the MultiHook with the News module it is most likely that the
title ($info.title) contains html-tags. Use the strip_tags modifier to remove
them:

<p class="diggers"><!--[diggers title=$info.title|strip_tags]--></p>

