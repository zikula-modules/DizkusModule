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
 * @version $Id: global.php,v 1.7 2005/11/23 14:52:31 landseer Exp $
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
define('_PNFORUM_SEARCHINCLUDE_MISSINGPARAMETERS', 'Faltam parámetros para poder realizar la búsqueda');
define('_PNFORUM_NOMOVETO', 'No se ha seleccionado Foro de destino');
define('_PNFORUM_NOJOINTO', 'No se ha seleccionado Tema de destino para la unión');
define('_PNFORUM_SELECTACTION', 'selecionar acción');
define('_PNFORUM_SELECTTARGETFORUM', 'seleccionar Foro de destino');
define('_PNFORUM_SELECTTARGETTOPIC', 'seleccionar Tema de destino');
define('_PNFORUM_OR', 'o');
define('_PNFORUM_MODERATE_JOINTOPICS_HINT', 'Si quiere unir Temas, seleccione el Tema de desino aquí');
define('_PNFORUM_MODERATORSOPTIONS', 'Opciones de los Moderadores');
define('_PNFORUM_JOINTOPICS', 'Unir Temas');
define('_PNFORUM_HOTTOPIC', 'Tema importante');
define('_PNFORUM_HOTNEWTOPIC', 'Tema importante con nuevos mensajes');
define('_PNFORUM_NORMALTOPIC', 'Tema normal');
define('_PNFORUM_NORMALNEWTOPIC', 'Tema normal con nuevos mensajes');
define('_PNFORUM_BLOCK_TEMPLATENAME', 'Nombre del template');
define('_PNFORUM_BLOCK_PARAMETERS', 'Parametros');
define('_PNFORUM_BLOCK_PARAMETERS_HINT', 'lista de valores separados por coma, ejemplo: maxposts=5,forum_id=27 ');
define('_PNFORUM_MODERATE','Moderar');
define('_PNFORUM_SELECTED','Selección');
define('_PNFORUM_STICKYTOPICS','Marcar los Temas seleccionados como Fijos');
define('_PNFORUM_UNSTICKYTOPICS','Liberar los Temas seleccionados (quitar como Fijos)');
define('_PNFORUM_LOCKTOPICS','Cerrar los Temas seleccionados');
define('_PNFORUM_UNLOCKTOPICS','Abrir los Temas seleccionados');
define('_PNFORUM_DELETETOPICS','Borrar los Temas seleccionados');
define('_PNFORUM_MOVETOPICS','Mover los Temas seleccionados');
define('_PNFORUM_MODERATE_MOVETOPICS_HINT','Elige el Foro de destino para mover los Temas:');
define('_PNFORUM_SUBMIT_HINT','CUIDADO: pnForum no te pedirá confirmación! Pinchando en Enviar iniciará inmediatamente la acción seleccionada!!!');
// new
define('_PNFORUM_TOGGLEALL', 'Eliminar todas las subscripciones a los Temas');
define('_PNFORUM_PREFS_HIDEUSERSINFORUMADMIN', 'Ocultar lista de usuarios en la Administracion de pnForum');
define('_PNFORUM_UNKNOWNUSER', '**usuario desconocido**');
define('_PNFORUM_MANAGETOPICSUBSCRIPTIONS_HINT', 'Aquí puedes controlar tus subscripciones.');
define('_PNFORUM_NOTOPICSUBSCRIPTIONSFOUND', 'no se han encontrado subscripciones a ningún Tema');
define('_PNFORUM_MANAGETOPICSUBSCRIPTIONS', 'Controlar subscripciones a los Temas');
define('_PNFORUM_GROUP', 'Grupo');
define('_PNFORUM_NOSPECIALRANKSINDATABASE', 'No se han encontrado Rangos en la Base de Datos. Puedes añadir uno utilizando el formulario que hay a continuación.');
define('_PNFORUM_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX', 'Utilizar búsqueda intensiva ("extended fulltext") en las búsquedas internas');
define('_PNFORUM_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX_HINT', '<i>La búsqueda intensiva permite utilizar parámetros como por ejemplo "+pnforum -skype" para encontrar mensajes que contengan la palabra "pnforum" y que no contengan "skype". Se neceista utilizar al menos MySQL 4.01.</i><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-boolean.html" title="Extended fulltest search in MySQL">Enlace Relacionado</a>.');
define('_PNFORUM_DATABASEINUSE', 'Base de datos actual');
define('_PNFORUM_PREFS_SEARCHWITHFULLTEXTINDEX', 'Permitir búsqueda intensiva ("fulltext") a los usuarios');
define('_PNFORUM_PREFS_SEARCHWITHFULLTEXTINDEX_HINT', '<i>Realizar búsquedas en el foro con "búsqueda intensiva" necesita al menos MySQL 4 o posteriores y no funcionará con tablas de tipo InnoDB. Esta opción normalmente se establece durante la instalación. El resultado de la búsqueda puede estar vacio si las palabras buscadas están en demasiados mensajes. Esta es una "característica" de MySQL.</i><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-search.html" title="Fulltext search in MySQL">Enlace Relacionado</a>.');
define('_PNFORUM_ADMINADVANCEDCONFIG', 'Configuración avanzada');
define('_PNFORUM_ADMINADVANCEDCONFIG_HINT', 'Aviso: Marcar opciones no correctas puede producir efectos no deseados sobre el Foro. Si no entiendes bien lo que significan estas opciones, lo mejor es que las dejes como están!');
define('_PNFORUM_ADMINADVANCEDCONFIG_INFO', 'Establecer configuración avanzada, ten cuidado!!');
define('_PNFORUM_MODERATION_NOTICE', 'Petición de moderación');
define('_PNFORUM_NOTIFYMODERATORTITLE', 'Notificar a un moderador este mensaje');
define('_PNFORUM_REPORTINGUSERNAME', 'Usuario que informa');
define('_PNFORUM_NOTIFYMODBODY1', 'Petición de moderación');
define('_PNFORUM_NOTIFYMODBODY2', 'Comentario');
define('_PNFORUM_NOTIFYMODBODY3', 'Enlace al Tema');
define('_PNFORUM_NOTIFYMODERATOR', 'notificar al moderador');
define('_PNFORUM_JOINTOPICS', 'Unir Temas');
define('_PNFORUM_JOINTOPICS_INFO', 'Unir dos Temas');
define('_PNFORUM_JOINTOPICS_TOTOPIC', 'Tema de destino');
define('_PNFORUM_MOVEPOST', 'Mover mensaje');
define('_PNFORUM_MOVEPOST_INFO', 'Mover un mensaje desde un Tema a otro');
define('_PNFORUM_MOVEPOST_TOTOPIC', 'Tema de destino');
define('_PNFORUM_MAIL2FORUMPOSTS', 'Lista de Correo');
define('_PNFORUM_NOSUBJECT', 'sin asunto');
define('_PNFORUM_PREFS_FAVORITESENABLED', 'Activar Favoritos');
define('_PNFORUM_PREFS_M2FENABLED', 'Activar Mail2Forum');
define('_PNFORUM_POP3TESTRESULTS', 'Resultador del Test Pop3');
define('_PNFORUM_BACKTOFORUMADMIN', 'Volver a la administración de pnForum');
define('_PNFORUM_WRONGPNVERSIONFORMAIL2FORUM', 'Mail2Forum necesita PostNuke .760 o siguientes!');
define('_PNFORUM_MINSHORT', 'min');
define('_PNFORUM_MAIL2FORUM', 'Mail2Forum');
define('_PNFORUM_POP3ACTIVE', 'Mail2Forum activo');
define('_PNFORUM_POP3TEST', 'Realiza un test Pop3 antes de guardar los cambios');
define('_PNFORUM_POP3SERVER', 'Servidor Pop3');
define('_PNFORUM_POP3PORT', 'Puerto Pop3');
define('_PNFORUM_POP3LOGIN', 'Usuario Pop3');
define('_PNFORUM_POP3PASSWORD', 'Contraseña Pop3');
define('_PNFORUM_POP3PASSWORDCONFIRM', 'Confirmacíon de la Contraseña Pop3');
define('_PNFORUM_POP3INTERVAL', 'Comprobar cada');
define('_PNFORUM_POP3MATCHSTRING', 'Regla');
define('_PNFORUM_POP3MATCHSTRINGHINT', 'La regla es una expresión regular que comprueba el asunto del email para evitar posible SPAM. Una regla vacía significa que no se comprueba!');
define('_PNFORUM_PASSWORDNOMATCH', 'Las contraseñas no coinciden, por favor vuelva atrás y escriba las contraseñas correctas');
define('_PNFORUM_POP3PNUSER', 'Usuario de PN');
define('_PNFORUM_POP3PNPASSWORD', 'Contraseña de PN');
define('_PNFORUM_POP3PNPASSWORDCONFIRM', 'Confirmacíon de la Contraseña PN');
// new
define('_PNFORUM_START', 'Inicio');
define('_PNFORUM_PREFS_AUTOSUBSCRIBE', 'Auto subscribe a temas nuevos o mensajes');
//
// alphasorting starts here
//
// A
//
define('_PNFORUM_ACCOUNT_INFORMATION', 'IP de los usuarios e información de la cuenta');
define('_PNFORUM_ACTIONS','Acciones');
define('_PNFORUM_ACTIVE_FORUMS','Top Foros activos:');
define('_PNFORUM_ACTIVE_POSTERS','Top Posteadores activos:');
define('_PNFORUM_ADD_FAVORITE_FORUM','Añadir foro a favoritos');
define('_PNFORUM_ADD','Añadir');
define('_PNFORUM_ADDNEWCATEGORY', '-- añadir nueva categoria --');
define('_PNFORUM_ADDNEWFORUM', '-- añadir nuevo foro --');
define('_PNFORUM_ADMIN_SYNC','Sincronizar');
define('_PNFORUM_ADMINBADWORDS_TITLE','Administración del filtro de palabras prohibidas');
define('_PNFORUM_ADMINCATADD_INFO','Este link te permitirá agregar una nueva categoría donde podrás crear nuevos foros :');
define('_PNFORUM_ADMINCATADD','Añadir en la Categoria');
define('_PNFORUM_ADMINCATDELETE_INFO','Este link permite que elimines cualquier categoría de la base de datos');
define('_PNFORUM_ADMINCATDELETE','Eliminar a Catetegoria');
define('_PNFORUM_ADMINCATEDIT_INFO','Este link te permitirá corrige el título de una categoría');
define('_PNFORUM_ADMINCATEDIT','Editar titulo de la categoría ');
define('_PNFORUM_ADMINCATORDER_INFO','Este link te permitirá cambiar el orden de las categorías (El orden que exhiben en la página del índice)');
define('_PNFORUM_ADMINCATORDER','Reordenar Categorias');
define('_PNFORUM_ADMINFORUMADD_INFO','Este link te llevará a una página donde podrás agregar un foro a la base de datos.');
define('_PNFORUM_ADMINFORUMADD','Añadir Foro');
define('_PNFORUM_ADMINFORUMEDIT_INFO','Este link te permitirá que edites un foro existente.');
define('_PNFORUM_ADMINFORUMEDIT','Editar Foro');
define('_PNFORUM_ADMINFORUMOPTIONS_INFO','Este link te permitirá que administres varias opciones-generales del modulo.');
define('_PNFORUM_ADMINFORUMOPTIONS','Foro Opciones-generales');
define('_PNFORUM_ADMINFORUMORDER_INFO','Esto permite que cambies el orden de tus foros (El orden que exhiben en la página del índice)');
define('_PNFORUM_ADMINFORUMORDER','Reordenar Foros');
define('_PNFORUM_ADMINFORUMSPANEL','pnForum Administración');
define('_PNFORUM_ADMINFORUMSYNC_INFO','Este link permite sincronizar el foro y el índice de temas para resolver las posibles discrepancias que puedan existir');
define('_PNFORUM_ADMINFORUMSYNC','Sincronizar Foro/Indice de Temas');
define('_PNFORUM_ADMINHONORARYASSIGN_INFO','Este link te permitirá que asignes rangos honorarios a los usuarios');
define('_PNFORUM_ADMINHONORARYASSIGN','Asignar rangos honorarios');
define('_PNFORUM_ADMINHONORARYRANKS_INFO','Aquí puedes asignar individualmente rangos  honorarios a usuarios específicos.');
define('_PNFORUM_ADMINHONORARYRANKS','Administrar rangos honorarios');
define('_PNFORUM_ADMINRANKS_INFO','Este link te permitirá administrar los rangos de los usuarios añadir/editar/borrar, dependiendo del número de mensajes.');
define('_PNFORUM_ADMINRANKS','Editar rangos de usuarios');
define('_PNFORUM_ADMINUSERRANK_IMAGE','Imagen');
define('_PNFORUM_ADMINUSERRANK_INFO','Para modificar un rango, cambia simplemente los valores en las cajas de texto,<br/> marca "editar" y el botón "enviar" para salvar los cambios.<br/> Para quitar un rango, marca simplemente "Borrar" al lado del rango y el botón "enviar" para salvar los cambios.');
define('_PNFORUM_ADMINUSERRANK_INFO2','Utiliza esta forma para agregar un rango a la base de datos.');
define('_PNFORUM_ADMINUSERRANK_MAX','Nº Max. mensajes');
define('_PNFORUM_ADMINUSERRANK_MIN','Nº Min. mensajes');
define('_PNFORUM_ADMINUSERRANK_TITLE','Administración rangos de usuarios');
define('_PNFORUM_ADMINUSERRANK_TITLE2','Rango usuario');
define('_PNFORUM_ASSIGN','Asignar');
define('_PNFORUM_ATTACHSIGNATURE', 'Añadir mi firma');
define('_PNFORUM_AUTHOR','Autor');
//
// B
//
define('_PNFORUM_BODY','Cuerpo De Mensaje');
define('_PNFORUM_BOTTOM','Bajar a respuesta rápida');
//
// C
//
define('_PNFORUM_CANCELPOST','Cancelar Mensaje');
define('_PNFORUM_CATEGORIES','Categorias');
define('_PNFORUM_CATEGORY','Categoria');
define('_PNFORUM_CATEGORYINFO', 'Info. Categoria');
define('_PNFORUM_CHANGE_FORUM_ORDER','Cambiar el orden de los foros');
define('_PNFORUM_CHANGE_POST_ORDER','Cambiar el orden de los post');
define('_PNFORUM_CHOOSECATWITHFORUMS4REORDER','Categoría seleccionada que contiene los foros que deseas reordenar');
define('_PNFORUM_CHOOSEFORUMEDIT','Seleccionar foro para editar');
define('_PNFORUM_CREATEFORUM_INCOMPLETE','¿No completaste todas las partes requeridas del form.<br/> no asignaste por lo menos un moderador? Regresa  y corrige/completa');
define('_PNFORUM_CREATESHADOWTOPIC','Crear tema oculto');
define('_PNFORUM_CURRENT', 'Actual');
//
// D
//
define('_PNFORUM_DATE','Fecha');
define('_PNFORUM_DELETE','Borrar este mensaje');
define('_PNFORUM_DELETETOPIC_INFO', 'Cuando presiones el botón el tema que has seleccionado y todos sus mensajes relacionados,  serán <strong>permanentemente</strong> eliminados.');
define('_PNFORUM_DELETETOPIC','Borrar este tema');
define('_PNFORUM_DESCRIPTION', 'Descripción');
define('_PNFORUM_DOWN','Abajo');
//
// E
//
define('_PNFORUM_EDIT_POST','Editar Mensaje');
define('_PNFORUM_EDITBY','editado por:');
define('_PNFORUM_EDITDELETE', 'editar/borrar');
define('_PNFORUM_EDITFORUMS','Editar foros');
define('_PNFORUM_EDITPREFS','Editar tus Preferencias');
define('_PNFORUM_EMAIL_TOPIC', 'Enviar por eMail');
define('_PNFORUM_EMAILTOPICMSG','¡Hola!  Comprueba este enlace, creo que será de tu interés, saludos');
define('_PNFORUM_EMPTYMSG','Tienes que escribir un mensaje en el post.  No puedes añadir un mensaje vacío. Regresa y prueba de nuevo.');
define('_PNFORUM_ERROR_CONNECT','Error de conexión con la base de datos <br />');
define('_PNFORUM_ERRORMAILTO', 'Enviar un aviso del error');
define('_PNFORUM_ERROROCCURED', 'Ocurrió el error siguiente :');
//
// F
//
define('_PNFORUM_FAVORITE_STATUS','Estado de los favoritos');
define('_PNFORUM_FAVORITES','Favoritos');
define('_PNFORUM_FORUM_EDIT_FORUM','Editar Foro');
define('_PNFORUM_FORUM_EDIT_ORDER','Editar Otro');
define('_PNFORUM_FORUM_NOEXIST','Error - el foro/tema que usted seleccionó no existe.  Por favor, regresa y prueba de nuevo.');
define('_PNFORUM_FORUM_REORDER','Reordenar');
define('_PNFORUM_FORUM_SEQUENCE_DESCRIPTION','Si deseas mover un foro a otra posición, solamente marca arriba o abajo con la flecha.  Si un foro tiene un número de orden 0, será pedido alfabéticamente por nombre del foro.  El orden final que se mostrara serán foros alfabéticos (con order=0). Sino serán entonces por orden numérica.  Selecciona para asignar un nuevo orden.');
define('_PNFORUM_FORUM','Foro');
define('_PNFORUM_FORUMID', 'Foros ID');
define('_PNFORUM_FORUMINFO', 'Info. Foro');
define('_PNFORUM_FORUMS','Foros');
define('_PNFORUM_FORUMSINDEX','Indice del foro');
//
// G
//
define('_PNFORUM_GOTO_CAT','Ir a la categoría');
define('_PNFORUM_GOTO_FORUM','Ir al foro');
define('_PNFORUM_GOTO_LATEST', 'Ver ultimos mensajes');
define('_PNFORUM_GOTO_TOPIC','Ir al tema');
define('_PNFORUM_GOTOPAGE','Ir a la pagina');
//
// H
//
define('_PNFORUM_HOMEPAGE','WwW');
define('_PNFORUM_HONORARY_RANK','Rango honorario');
define('_PNFORUM_HONORARY_RANKS','Rangos honorarios');
define('_PNFORUM_HOST', 'Host');
define('_PNFORUM_HOTTHRES','Más que los mensajes de %d');
define('_PNFORUM_HOURS','horas');
//
// I
//
define('_PNFORUM_IMAGE', 'Imagen');
define('_PNFORUM_IP_USERNAMES', 'Nombres de usuarios que conectaron desde esta IP + cuenta');
define('_PNFORUM_ISLOCKED','Tema Cerrado. No se admiten nuevos mensajes');
//
// L
//
define('_PNFORUM_LAST_SEEN', 'Ultima visita');
define('_PNFORUM_LAST','Ultima');
define('_PNFORUM_LAST24','Ultimas 24 horas');
define('_PNFORUM_LASTCHANGE','Ultimos cambios');
define('_PNFORUM_LASTPOST','Ultimo mensaje');
define('_PNFORUM_LASTPOSTSTRING','%s<br />por %s');
define('_PNFORUM_LASTVISIT', 'Ultima visita');
define('_PNFORUM_LASTWEEK','La semana pasada');
define('_PNFORUM_LATEST','Ultimos mensajes');
define('_PNFORUM_LOCKTOPIC_INFO', 'Cuando presiones el botón el tema que has seleccionado será <strong>Cerrado</strong>.  Puedes volver abrirlo cuando quieras.');
define('_PNFORUM_LOCKTOPIC','Cerrar este tema');
//
// M
//
define('_PNFORUM_MAILTO_NOBODY','Tienes que introducir un mensaje.');
define('_PNFORUM_MAILTO_WRONGEMAIL','No añadiste en tus datos una dirección eMail o es una dirección incorrecta.');
define('_PNFORUM_MODERATEDBY','Moderado por');
define('_PNFORUM_MODERATOR','Moderador');
define('_PNFORUM_MORETHAN','Más de');
define('_PNFORUM_MOVED_SUBJECT', 'movido');
define('_PNFORUM_MOVETOPIC_INFO', 'Cuando presiones el botón el tema que has seleccionado y todos sus mensajes relacionados, serán <strong>movidos</strong> al foro que indicaste.  Nota:  Solo podrás  trasladarlo a un foro donde seas moderador.  Se permite al administrador mover cualquier tema a cualquier foro.');
define('_PNFORUM_MOVETOPIC','Mover este tema');
define('_PNFORUM_MOVETOPICTO','Mover el tema a:');
//
// N
//
define('_PNFORUM_NEW_THREADS','Nuevo Tema');
define('_PNFORUM_NEWEST_FIRST','Mostrar primero el ultimo mensaje ');
define('_PNFORUM_NEWPOSTS','Nuevos mensajes desde tu ultima visita.');
define('_PNFORUM_NEWTOPIC','Nuevo tema');
define('_PNFORUM_NEXT_TOPIC','Tema siguiente');
define('_PNFORUM_NEXTPAGE','Pagina siguiente');
define('_PNFORUM_NO_FORUMS_DB', 'Ningún foro en la base de datos');
define('_PNFORUM_NO_FORUMS_MOVE', 'No existen mas foros moderados por ti, mover a ');
define('_PNFORUM_NOAUTH_MODERATE','No eres moderador de este foro por lo cual no puede realizar esta función.');
define('_PNFORUM_NOAUTH_TOADMIN', 'No tienes permisos para administrar este modulo');
define('_PNFORUM_NOAUTH_TOMODERATE', 'No tienes permisos para moderar esta categoría o foro');
define('_PNFORUM_NOAUTH_TOREAD', 'No tienes permisos para leer el contenido de esta categoría o foro');
define('_PNFORUM_NOAUTH_TOSEE', 'No tienes permisos para ver el contenido de esta categoría o foro');
define('_PNFORUM_NOAUTH_TOWRITE', 'No tienes permisos para escribir en esta categoría o foro');
define('_PNFORUM_NOAUTH', 'No tienes permisos para esta acción');
define('_PNFORUM_NOAUTHPOST','Nota: No estas autorizado para comentar los mensajes');
define('_PNFORUM_NOCATEGORIES', 'No existen categorías definidas');
define('_PNFORUM_NOFAVORITES','No existen favoritos definidos');
define('_PNFORUM_NOFORUMS', 'No existen foros definidos');
define('_PNFORUM_NOMODERATORSASSIGNED', 'No tiene moderador asignado');
define('_PNFORUM_NONE', 'ninguno');
define('_PNFORUM_NONEWPOSTS','Ningún mensaje nuevo desde tu ultima visita.');
define('_PNFORUM_NOPOSTLOCK','No puedes responder a este tema/mensaje, esta cerrado.');
define('_PNFORUM_NOPOSTS','Ningún mensaje');
define('_PNFORUM_NORANK', 'Ningún rango');
define('_PNFORUM_NORANKSINDATABASE', 'No existen rangos definidos');
define('_PNFORUM_NOSMILES','No hay smilies en base de datos');
define('_PNFORUM_NOTEDIT','No puedes corregir o editar un mensaje que no sea tuyo.');
define('_PNFORUM_NOTIFYBODY1','Foros');
define('_PNFORUM_NOTIFYBODY2','escribió en');
define('_PNFORUM_NOTIFYBODY3','Puedes responder a este mensaje:');
define('_PNFORUM_NOTIFYBODY4','Puedes leer la respuesta enviada en el hilo:');
define('_PNFORUM_NOTIFYBODY5','Recibes este eMail porque te has suscrito para ser notificado de novedades en los foros :');
define('_PNFORUM_NOTIFYME', 'Notificarme cuando se de una respuesta');
define('_PNFORUM_NOTOPICS','No hay temas en este foro.');
define('_PNFORUM_NOTSUBSCRIBED','No estas subscrito a este foro');
define('_PNFORUM_NOUSER_OR_POST','Error - ningún usuario o mensaje con ese nombre en la base de datos.');
//
// O
//
define('_PNFORUM_OFFLINE', 'Desconectado');
define('_PNFORUM_OKTODELETE','¿Borrar?');
define('_PNFORUM_OLDEST_FIRST','Mostrar primero el mensaje mas antiguo');
define('_PNFORUM_ONEREPLY','Respuesta rápida');
define('_PNFORUM_ONLINE', 'Conectado');
define('_PNFORUM_OPTIONS','Opciones');
define('_PNFORUM_OURLATESTPOSTS','Últimos mensajes del foro');
//
// P
//
define('_PNFORUM_PAGE','Pagina #');
define('_PNFORUM_PERMDENY','¡Acceso denegado!');
define('_PNFORUM_PERSONAL_SETTINGS','Configuración personal');
define('_PNFORUM_POST_GOTO_NEWEST','Ir al mensaje mas reciente');
define('_PNFORUM_POST','Mensaje');
define('_PNFORUM_POSTED','Enviado');
define('_PNFORUM_POSTER','Autor');
define('_PNFORUM_POSTS','Mensajes');
define('_PNFORUM_POWEREDBY', 'Powered by <a href="http://www.pnforum.de/" title="pnForum">pnForum</a> Version');
define('_PNFORUM_PREFS_ASCENDING', 'Ascendente');
define('_PNFORUM_PREFS_CHARSET', 'Charset:<br /><em>(Esto es el charset que se utilizará en encabezdos del correo electrónico)</em>');
define('_PNFORUM_PREFS_DESCENDING', 'Descendente');
define('_PNFORUM_PREFS_EMAIL', 'Dirección Email de:<br /><em>(Ésta es la dirección que aparecerá en cada eMail enviado por los foros)</em>');
define('_PNFORUM_PREFS_FIRSTNEWPOSTICON', 'Icono para el primero mensaje nuevo:');
define('_PNFORUM_PREFS_HOTNEWPOSTSICON', 'Imagen para temas con muchos mensajes nuevos:');
define('_PNFORUM_PREFS_HOTTOPIC', 'Cantidad para considerar tema importante:');
define('_PNFORUM_PREFS_HOTTOPICICON', 'Imagen tema importante:<br /><em>(Tema con muchos mensajes)</em>');
define('_PNFORUM_PREFS_ICONS','<br /><strong>Iconos</strong>');
define('_PNFORUM_PREFS_LOGIP', 'Dirección IP del registado :');
define('_PNFORUM_PREFS_NEWPOSTSICON', 'Imagen para nuevos mensajes:<br /><em>(Carpeta con nuevos mensajes desde la ultima visita del usuario/s)</em>');
define('_PNFORUM_PREFS_NO', 'No');
define('_PNFORUM_PREFS_POSTICON', 'Icono para mensajes nuevos:');
define('_PNFORUM_PREFS_POSTSORTORDER', 'Mostrar los mensajes en orden :');
define('_PNFORUM_PREFS_POSTSPERPAGE', 'Mensajes por página:<br /><em>(Éste es el número de mensajes por tema que serán mostrados.  15 por defecto.)</em>');
define('_PNFORUM_PREFS_RANKLOCATION', 'Localizacion de las imagenes de Rangos :');
define('_PNFORUM_PREFS_RESTOREDEFAULTS', 'Recuperar ajustes por defecto');
define('_PNFORUM_PREFS_SAVE', 'Guardar');
define('_PNFORUM_PREFS_SIGNATUREEND', 'Final del formato de la firma:');
define('_PNFORUM_PREFS_SIGNATURESTART', 'Comienzo del formato de la firma:');
define('_PNFORUM_PREFS_SLIMFORUM', 'Ocultar categorías con un sólo foro ');
//define('_PNFORUM_PREFS_SMILIELOCATION', 'Localización de los Smiles :');
define('_PNFORUM_PREFS_TOPICICON', 'Imagen para temas:');
define('_PNFORUM_PREFS_TOPICSPERPAGE', 'Temas por foro:<br /><em>(Éste es el número de temas por foro que serán mostrados.  15 por defecto.)</em>');
define('_PNFORUM_PREFS_YES', 'Sí');
define('_PNFORUM_PREVIEW','Vista Previa');
define('_PNFORUM_PREVIOUS_TOPIC','Tema anterior');
define('_PNFORUM_PREVPAGE','Página previa');
define('_PNFORUM_PRINT_POST','Imprimir mensaje');
define('_PNFORUM_PRINT_TOPIC','Imprimir tema');
define('_PNFORUM_PROFILE', 'Perfil del usuario');
//
// Q
//
define('_PNFORUM_QUICKREPLY', 'Respuesta rápida');
define('_PNFORUM_QUICKSELECTFORUM','- Seleccionar Foro -');
//
// R
//
define('_PNFORUM_RECENT_POST_ORDER', 'Mensajes recientes ordenados en el tema ');
define('_PNFORUM_RECENT_POSTS','Temas recientes:');
define('_PNFORUM_REG_SINCE', 'Registrado');
define('_PNFORUM_REGISTER','Registrar');
define('_PNFORUM_REGISTRATION_NOTE','Nota: Los usuarios registrados pueden participar en el foro activamente, subscribirse a foros o temas, recibir notificaciones sobre nuevos mensajes y mucho más...');
define('_PNFORUM_REMOVE_FAVORITE_FORUM','Eliminar este foro de favoritos ');
define('_PNFORUM_REMOVE', 'Eliminar');
define('_PNFORUM_REORDER','Reordenar');
define('_PNFORUM_REORDERCATEGORIES','Reordenar categorias');
define('_PNFORUM_REORDERFORUMS','Reordenar foros');
define('_PNFORUM_REPLACE_WORDS','Substituya las palabras');
define('_PNFORUM_REPLIES','Respuestas');
define('_PNFORUM_REPLY_POST','Responder a');
define('_PNFORUM_REPLY', 'Responder');
define('_PNFORUM_REPLYLOCKED', 'cerrado');
define('_PNFORUM_REPLYQUOTE', 'citar');
define('_PNFORUM_RETURNTOTOPIC', 'Regresar al tema');
//
// S
//
define('_PNFORUM_SAVEPREFS','Guardar preferencias ');
define('_PNFORUM_SEARCH','Buscar');
define('_PNFORUM_SEARCHALLFORUMS', 'todos los foros');
define('_PNFORUM_SEARCHAND','todas las palabras [Y]');
define('_PNFORUM_SEARCHBOOL', 'Conexión');
define('_PNFORUM_SEARCHFOR','Buscar por');
define('_PNFORUM_SEARCHINCLUDE_ALLTOPICS', 'todos');
define('_PNFORUM_SEARCHINCLUDE_AUTHOR','Autor');
define('_PNFORUM_SEARCHINCLUDE_BYDATE','por fecha');
define('_PNFORUM_SEARCHINCLUDE_BYFORUM','por foro');
define('_PNFORUM_SEARCHINCLUDE_BYTITLE','por titulo');
define('_PNFORUM_SEARCHINCLUDE_DATE','Fecha');
define('_PNFORUM_SEARCHINCLUDE_FORUM','Categoria y foro');
define('_PNFORUM_SEARCHINCLUDE_HITS', 'resultados por página');
define('_PNFORUM_SEARCHINCLUDE_LIMIT', 'Número de resultados por página');
define('_PNFORUM_SEARCHINCLUDE_MISSINGPARAMETERS', 'Buscar mensajes según parámetros  ');
define('_PNFORUM_SEARCHINCLUDE_NEWWIN','Mostrar en una nueva ventana');
define('_PNFORUM_SEARCHINCLUDE_NOENTRIES','No se encontraron mensajes en los foros');
define('_PNFORUM_SEARCHINCLUDE_NOLIMIT', 'sin limites');
define('_PNFORUM_SEARCHINCLUDE_ORDER','Orden');
define('_PNFORUM_SEARCHINCLUDE_REPLIES','Respuestas');
define('_PNFORUM_SEARCHINCLUDE_RESULTS','Foros');
define('_PNFORUM_SEARCHINCLUDE_TITLE','Buscar foros');
define('_PNFORUM_SEARCHINCLUDE_VIEWS','Opiniónes');
define('_PNFORUM_SEARCHOR','palabras solas [O]');
define('_PNFORUM_SEARCHRESULTSFOR','Buscar resultados por ');
define('_PNFORUM_SELECTEDITCAT','Seleccionar categoría ');
define('_PNFORUM_SEND_PM', 'Enviar MP');
define('_PNFORUM_SENDTO','Enviar para');
define('_PNFORUM_SEPARATOR','&nbsp;::&nbsp;');
define('_PNFORUM_SETTING', 'Ajustes');
define('_PNFORUM_SHADOWTOPIC_MESSAGE', 'Se ha movido el mensaje original <a title="movido" href="%s">aquí</a>.');
define('_PNFORUM_SHOWALLFORUMS','Mostrar todos los foros');
define('_PNFORUM_SHOWFAVORITES','Mostrar favoritos');
define('_PNFORUM_SMILES','Smilies:');
define('_PNFORUM_SPLIT','Partir');
define('_PNFORUM_SPLITTOPIC_INFO','Esto partirá el tema antes de seleccionar el mensaje ');
define('_PNFORUM_SPLITTOPIC_NEWTOPIC','Asunto para el nuevo tema');
define('_PNFORUM_SPLITTOPIC','Partir tema');
define('_PNFORUM_STATSBLOCK','Mesajes totales:');
define('_PNFORUM_STATUS', 'Estado');
define('_PNFORUM_STICKY', 'Destacados');
define('_PNFORUM_STICKYTOPIC_INFO', 'Cuando pulses el botón fijar, el tema seleccionado se convertirá a <strong>\'fijo o PostIt\'</strong>. Puedes despegarlo cuando quieras.');
define('_PNFORUM_STICKYTOPIC','Marcar este tema como fijo');
define('_PNFORUM_SUBJECT_MAX','(máx. 100 símbolos)');
define('_PNFORUM_SUBJECT','Asunto');
define('_PNFORUM_SUBMIT','Enviar');
define('_PNFORUM_SUBSCRIBE_FORUM', 'subscribir foro');
define('_PNFORUM_SUBSCRIBE_STATUS','Estado de la Subscripción');
define('_PNFORUM_SUBSCRIBE_TOPIC','Subscribir tema');
define('_PNFORUM_SYNC_FORUMINDEX', 'Índice del foro sincronizado ');
define('_PNFORUM_SYNC_POSTSCOUNT', 'Contador de mensajes sincronizado ');
define('_PNFORUM_SYNC_TOPICS', 'Temas sincronizados');
define('_PNFORUM_SYNC_USERS', 'Usuarios de PostNuke y pnForum sincronizados');
//
// T
//
define('_PNFORUM_TODAY','hoy');
define('_PNFORUM_TOP','Top');
define('_PNFORUM_TOPIC_NOEXIST','Error - El tema que ha seleccionado no existe. Por favor vuelva atrás y intente de nuevo.');
define('_PNFORUM_TOPIC_STARTER','comenzado por');
define('_PNFORUM_TOPIC','Tema');
define('_PNFORUM_TOPICLOCKED','Tema cerrado');
define('_PNFORUM_TOPICS','Temas');
define('_PNFORUM_TOTAL','Total');
//
// U
//
define('_PNFORUM_UALASTWEEK', 'La semana pasada, sin respuesta');
define('_PNFORUM_UNLOCKTOPIC_INFO', 'Cuando pulses el botón el tema <strong>cerrado</strong> dejara de serlo y pasara a ocupar tu puesto en el foro. Podrás cerrarlo de nuevo cundo quieras.');
define('_PNFORUM_UNLOCKTOPIC','Abrir este tema');
define('_PNFORUM_UNREGISTERED','Usuario no registrado');
define('_PNFORUM_UNSTICKYTOPIC_INFO', 'Cuando pulses el botón el tema <strong>\'fijo\'</strong> dejara de serlo y pasara a ocupar tu puesto en el foro . Podrás fijar de nuevo cundo quieras.');
define('_PNFORUM_UNSTICKYTOPIC','Liberar Tema');
define('_PNFORUM_UNSUBSCRIBE_FORUM','Cancelar subscripción al foro');
define('_PNFORUM_UNSUBSCRIBE_TOPIC','Cancelar subscripción al tema');
define('_PNFORUM_UP','Subir página');
define('_PNFORUM_UPDATE','Actualizado');
define('_PNFORUM_USER_IP', 'IP del usuario');
define('_PNFORUM_USERNAME','Nombre de usuario');
define('_PNFORUM_USERS_RANKS','Rangos de usuario');
//
// V
//
define('_PNFORUM_VIEW_IP', 'Ver IP');
define('_PNFORUM_VIEWIP', 'Ver IP');
define('_PNFORUM_VIEWS','Visitado');
define('_PNFORUM_VISITCATEGORY', 'visite esta categoría');
define('_PNFORUM_VISITFORUM', 'visite este foro');
define('_PNFORUM_YESTERDAY','ayer');
?>