//
// AjaxSupportPackage 6.08
//


0. What
-------
This package contains a collection of javascript libraries that can be used
for rich internet sites in PostNuke:
prototype 1.5.0_rc0     http://prototype.conio.net
scriptaculous 1.6.1     http://script.aculo.us
behaviour 1.1           http://bennolan.com/behaviour/behaviour.js
fieldvalidation 1.5.3   http://tetlaw.id.au/view/blog/really-easy-field-validation-with-prototype/
resizable               http://lists.rubyonrails.org/pipermail/rails-spinoffs/2006-April/003645.html
If you think, something valuable is missing, contact me (see #3)

1. Why
------
There are more and more modules that require those libs now. While .8 comes
with them out of the box, every module devs has to maintain his own copies
for now. This might lead different versions used in different modules.
So I created this package to minimize these problems.
Right now (Aug 2006) the following modules use Ajax and/or the supplied effect libs:
Formicula (beginning with 1.0)
MultiHook (beginning with 2.0)
pnForum (beginning with 2.7)
pnMessages (beginning with 1.0)
...
(if I missed some, email me)

2. How
------
The zip contains a javascript/ajax folder. Just copy this ajax subfolder to the
main javascript folder of your PostNuke installation and you are ready to go.
No need to change a theme unless you want to, it is the modules problem to load
the libraries needed by adding script-Tags in the templates or filling the
global $additional_header array.

3. Who
------
Compiled by Frank Schummertz, frank.schummertz@landseer-stuttgart.de
