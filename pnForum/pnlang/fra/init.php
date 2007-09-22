<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

define('_PNFORUM_WELCOMETOINTERACTIVEUPGRADE', 'Mise à jour pnForum');
define('_PNFORUM_OLDVERSION', 'Ancienne version');
define('_PNFORUM_NEWVERSION', 'Nouvelle version');
define('_PNFORUM_NEXTVERSION', 'Prochaine version');

define('_PNFORUM_BACKUPHINT', 'Créer une sauvegarde de la base avant<br/>de procéder la prochaine étape de mise à jour !');
define('_PNFORUM_UPGRADE_ADDINDEXNOW', 'Créer les index de champs maintenant');
define('_PNFORUM_UPGRADE_ADDINDEXLATER', 'Créer les index manuellement avec phpmyadmin ou autres.');

define('_PNFORUM_TO25_HINT', 'Cette mise à jour contient plusieurs changements au niveau de la base de données incluant la création de deux index de champs améliorant les performances de recherche sur le texte complet. Cela pourrait entraîner des problêmes sur des hébergements mutualisés et une base contenant beaucoup de messages !');
define('_PNFORUM_TO25_FAILED', 'Echec de la mise à jour du pnForum à la version 2.5');

define('_PNFORUM_TO26_HINT', 'Cette mise à jour contient plusieurs changements au niveau de la base de données concernant l\'option des commentaires via le forum.');
define('_PNFORUM_TO26_FAILED', 'Echec de la mise à jour du pnForum à la version 2.6');
