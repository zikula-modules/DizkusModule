<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

define('_DZK_WELCOMETOINTERACTIVEUPGRADE', 'Mise à jour Dizkus');
define('_DZK_OLDVERSION', 'Ancienne version');
define('_DZK_NEWVERSION', 'Nouvelle version');
define('_DZK_NEXTVERSION', 'Prochaine version');

define('_DZK_BACKUPHINT', 'Créer une sauvegarde de la base avant<br/>de procéder la prochaine étape de mise à jour !');
define('_DZK_UPGRADE_ADDINDEXNOW', 'Créer les index de champs maintenant');
define('_DZK_UPGRADE_ADDINDEXLATER', 'Créer les index manuellement avec phpmyadmin ou autres.');

define('_DZK_TO25_HINT', 'Cette mise à jour contient plusieurs changements au niveau de la base de données incluant la création de deux index de champs améliorant les performances de recherche sur le texte complet. Cela pourrait entraîner des problêmes sur des hébergements mutualisés et une base contenant beaucoup de messages !');
define('_DZK_TO25_FAILED', 'Echec de la mise à jour du Dizkus à la version 2.5');

define('_DZK_TO26_HINT', 'Cette mise à jour contient plusieurs changements au niveau de la base de données concernant l\'option des commentaires via le forum.');
define('_DZK_TO26_FAILED', 'Echec de la mise à jour du Dizkus à la version 2.6');
