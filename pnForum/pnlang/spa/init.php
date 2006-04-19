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
 * @version $Id: init.php,v 1.1 2005/09/13 12:09:53 landseer Exp $
 * @author various
 * @copyright 2004 by pnForum team
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 * 
 ************************************************************************
 * spanish language defines - Traslate by el_cuervo dev-posnuke.com
 * http://www.dev-postnuke.com - Soporte y ayuda en español
 ***********************************************************************/

define('_PNFORUM_WELCOMETOINTERACTIVEUPGRADE', 'Actualización de pnForum');
define('_PNFORUM_OLDVERSION', 'Versión antigua');
define('_PNFORUM_NEWVERSION', 'Nueva versión');
define('_PNFORUM_NEXTVERSION', 'Siguiente versión');

define('_PNFORUM_BACKUPHINT', 'Realice una copia de seguridad de su base de datos antes<br/>de realizar esta actualización!');
define('_PNFORUM_UPGRADE_ADDINDEXNOW', 'Creando los Indices en la base de datos');
define('_PNFORUM_UPGRADE_ADDINDEXLATER', 'Crear los Indices manualmente con phpmyadmin, etc.');

define('_PNFORUM_TO25_HINT', 'Esta actualización realiza varios cambios en la base de datos incluyendo la creación de dos índices para permitir aumentar la eficacia del sistema de búsquedas. Esto puede producir problemas en hostings compartidos donde existan foros con un gran número de mensajes en su base de datos!');
define('_PNFORUM_TO25_FAILED', 'La Actualización a pnForum 2.5 ha fallado!!!');

?>