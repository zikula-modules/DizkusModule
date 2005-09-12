<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                               *
 ************************************************************************
 * Modified version of:                                                 *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License                                                              *
 ************************************************************************
 * This program is free software; you can redistribute it and/or modify *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation; either version 2 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * This program is distributed in the hope that it will be useful,      *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with this program; if not, write to the Free Software          *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 *
 * USA                                                                  *
 ************************************************************************
 *
 * english language defines
 * @version $Id$
 * @author various
 * @copyright 2004 by pnForum team
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

define('_PNFORUM_WELCOMETOINTERACTIVEUPGRADE', 'Mise à jour pnForum');
define('_PNFORUM_OLDVERSION', 'Ancienne version');
define('_PNFORUM_NEWVERSION', 'Nouvelle version');
define('_PNFORUM_NEXTVERSION', 'Prochaine version');

define('_PNFORUM_BACKUPHINT', 'Créer une sauvegarde de la base avant<br/>de procéder la prochaine étape de mise à jour !');
define('_PNFORUM_UPGRADE_ADDINDEXNOW', 'Créer les index de champs maintenant');
define('_PNFORUM_UPGRADE_ADDINDEXLATER', 'Créer les index manuellement avec phpmyadmin ou autres.');

define('_PNFORUM_TO25_HINT', 'Cette mise à jour contient plusieurs changements au niveau de la base de données incluant la création de deux index de champs améliorant les performances de recherche sur le texte complet. Cela pourrait entraîner des problêmes sur des hébergements mutualisés et une base contenant beaucoup de messages !');
define('_PNFORUM_TO25_FAILED', 'Echec de la mise à jour du pnForum à la version 2.5');

?>