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

/**
 * translated by
 * @author Mateo Tibaquira [mateo]
 */

// removed
// define('_DZK_TOGGLEALL', 'Eliminar todas las subscripciones a los Temas');

// new for contactlist integration
define('_DZK_PREFS_HASTOBEINSTALLED', 'tiene que estar instalado');
define('_DZK_PREFS_IGNORELISTHANDLINGNOTAVAILABLE', 'Manejo de usuarios ignorados no disponible, ');
define('_DZK_PREFS_IGNORELISTHANDLING', 'Manejo de usuarios ignorados');
define('_DZK_PREFS_STRICT', 'estricto');
define('_DZK_PREFS_MEDIUM', 'medio');
define('_DZK_PREFS_NONE', 'ninguno');
define('_DZK_PREFS_IGNORELISTLEVELS', 'Los usuarios que son ignorados por el autor del tema no pueden responder a ese tema en el nivel "estricto". En medio, ellos pueden responder, pero los mensajes generalmente no van a ser mostrados a los usuarios que ignoran a ese autor. También las notificaciones por correo no serán enviadas. Con sólo dar click en el mensaje, este será mostrado.');
define('_DZK_IGNORELISTHANDLING', 'Configuración de lista de ignorados');
define('_DZK_MANAGEIGNORELIST', 'Manejar mi configuración para mi lista de ignorados');
define('_DZK_PREFS_NOCONFIGPOSSIBLE', 'No se puede configurar la lista de ignorados');
define('_DZK_PREFS_IGNORELISTMYHANDLING', 'Mi manejo individual de usuarios ignorados');
define('_DZK_IGNORELISTSETTINGSUPDATED', 'Configuración de la lista de ignorados actualizada');
define('_DZK_IGNORELISTNOREPLY', 'Disculpa - el usuario que comenzó este tema te ignora y no quiere que seas capaz de escribir respuestas en este tema. Por favor contactal@ para más detalles.');
define('_DZK_SHOWIGNOREDPOSTINGOF', 'Mostrar mensajes ocultos del usuario ignorado');
define('_DZK_CLICKHERE', 'Click aqui');

// changed
define('_DZK_ADMINUSERRANK_INFO', 'Para añadir un nuevo rango simplemente proporciona los campos necesarios y selecciona AÑADIR.<br />Para modificar un rango, cambia los valores en las cajas de texto de la siguiente tabla y dale click al botón de ENVIAR.<br />Para eliminar un rango selecciona la casilla de verificación correspondiente y dale click al botón ENVIAR.');
define('_DZK_PREFS_SENDEMAILSWITHSQLERRORS', 'Enviar correos electrónicos si ocurren errores en la base de datos');
define('_DZK_FORUM_SEQUENCE_DESCRIPTION', 'Puedes usar arrastrar & soltar para manipular la estructura del foro como desees. Una vez finalices, dale click a Guardar para salvar tus cambios.');
define('_DZK_NEW_THREADS', 'Nuevo Tema en el foro');
define('_DZK_ADD_FAVORITE_FORUM', 'Añadir a favoritos');

// new
define('_DZK_SEARCHLENGTHHINT', 'Los foros aceptan búsquedas solamente con una longitud entre %minlen% y %maxlen% caracteres!');
define('_DZK_PREFS_MINSEARCHLENGTH', 'Longitud mínima de las búsquedas (>=1 caracteres)');
define('_DZK_PREFS_MAXSEARCHLENGTH', 'Longitud máxima de las búsquedas (<=50 caracteres))');
define('_DZK_PREFS_SHOWTEXTINSEARCHRESULTS', 'Mostrar el texto en los resultados de la búsqueda<br /><em>Desactiva esta opción en sitios de alto tráfico para mejorar el rendimiento de la búsqueda o cuida de limpiar la tabla de resultados de búsquedas contantemente.</em>');
define('_DZK_ADMINADDNEWRANK', 'Añadir un nuevo rango');
define('_DZK_ADMINDELETERANK', 'Borrar este rango');
define('_DZK_ADMINGENERALOPTIONS', 'Opciones generales');
define('_DZK_ADMINUSERRELATEDOPTIONS', 'Opciones relacionadas al usuario');
define('_DZK_ADMINSECURITYOPTIONS', 'Opciones de seguridad');
define('_DZK_ADMINFEATURESOPTIONS', 'Características');
define('_DZK_DISABLED_INFO', 'El foro está actualmente en mantenimiento, por favor regresa más tarde.');
define('_DZK_CONFIGRESTORED', 'La configuración ha sido restaurada a los valores predeterminados');
define('_DZK_CONFIGCHANGED', 'La configuración ha sido cambiada');
define('_DZK_SIGNATUREUPDATED', 'Firma actualizada');
define('_DZK_TO30_HINT', 'Este paso actualizará pnForum 2.7.1 a Dizkus 3.0 incluyendo todos los cambios necesarios en la base de datos.');
define('_DZK_POSTSAPPEARANCE', 'Apariencia de los mensajes');
define('_DZK_MANAGESIGNATURE', 'Gestionar mi firma');
define('_DZK_PREFS_ENABLESIGNATUREMANAGEMENT', 'Habilitar la gestión de la firma desde las preferencias de usuario de Dizkus');
define('_DZK_SEARCHWHERE', 'Buscar en');
define('_DZK_SEARCH_POSTINGS', 'mensajes');
define('_DZK_SEARCH_AUTHOR', 'autores');

define('_DZK_PREFS_ENABLEDISABLE', 'Dizkus está disponible<br /><em>(apagándolo sólo permitirás el acceso de administradores)</em>');
define('_DZK_PREFS_DISABLEDTEXT', 'Escribe la razón por la cual el foro está deshabilitado para los usuarios');
define('_DZK_SOURCEEQUALSTARGETFORUM', 'Error: foro origen debe ser diferente del destino.');
define('_DZK_SOURCEEQUALSTARGETTOPIC', 'Error: tema origen debe ser diferente del objetivo.');
define('_DZK_FOUNDIN', 'encontrado en');
define('_DZK_FORUM_SETTINGS','Ajustes personales por foro');
define('_DZK_HOURSSHORT', 'hrs');
define('_DZK_PREFS_TIMESPANFORCHANGES', 'Permitir cambios en los mensajes dentro de las primeras x horas');
define('_DZK_LATESTRSS', 'RSS');
define('_DZK_VIEWYOURPOSTS', 'Ver tus mensajes');
define('_DZK_PNCATEGORIES', 'Seleccionar categoría');
define('_DZK_LINKTOTHISPOST', 'Enlace a este mensaje');
define('_DZK_SIMILARTOPICS', 'temas similares');
define('_DZK_RANK', 'rango');
define('_DZK_CLICKTOEDIT', 'click para editar');
define('_DZK_EDITSHORT', 'editar');
define('_DZK_GOTOSTART', 'ir al inicio del foro');
define('_DZK_YOUAREHERE', 'Estás aqui');
define('_DZK_CURRENTSORTORDER', 'orden reciente');
define('_DZK_ORDER_ASC', 'envios antiguos primero');
define('_DZK_ORDER_DESC','envios recientes primero');
define('_DZK_CANCEL','cancelar');
define('_DZK_MOVEPOSTSHORT', 'mover');
define('_DZK_SPLITSHORT', 'dividir');
define('_DZK_LOGIN', 'Entrar');
define('_DZK_SEARCHSHORT', 'Buscar');
define('_DZK_SEARCHINCLUDE_BYSCORE', 'por pertinencia');
define('_DZK_MAILTO_NOSUBJECT','Debes proporcionar un asunto para este correo electrónico.');
define('_DZK_NOFORUMSUBSCRIPTIONSFOUND','No se encontraron subscripciones a foros');
define('_DZK_TOGGLEALLFORUMS', 'Eliminar todas las subscripciones a foros');
define('_DZK_TOGGLEALLTOPICS', 'Remover todas las subscripciones a temas');
define('_DZK_THISFUNCTIONNEEDSJAVASCRIPT', 'El panel de administración de Dizkus necesita javascript habilitado!');
define('_DZK_MANAGEFORUMSUBSCRIPTIONS', 'Administrar subscripciones a foros');
define('_DZK_SHOWSUBSCRIPTIONS', 'Mostrar las subscripciones de los usuarios');
define('_DZK_ADMINMANAGESUBSCRIPTIONS', 'Administrar subscripciones');
define('_DZK_ADMINMANAGESUBSCRIPTIONS_INFO', 'Remover the users topic and forum subscriptions');
define('_DZK_REDIRECTINGTONEWTOPIC', '...redireccionando al nuevo tema...');
define('_DZK_PREFS_SHOWNEWTOPICCONFIRMATION', 'Mostrar confirmación cuando un nuevo tema ha sido creado');
define('_DZK_THANKSFORNEWTOPIC', 'Gracias por tu envío');
define('_DZK_CLICKHERETOGOTONEWTOPICORWAITFORREDIRECT', 'Click aqui para ir a el nuevo tema o espera unos segundos para ser redirigido');
define('_DZK_CLICKHERETOGOTONEWTOPIC', 'Click aqui para ir al nuevo tema.');
define('_DZK_CLICKHERETOGOTOFORUM', 'Click aqui para volver al foro');
define('_DZK_EMPTYCATEGORY', 'Esta categoría no contiene ningún foro aún');
define('_DZK_HIDEFORUMS', 'Esconder foros');
define('_DZK_SHOWFORUMS', 'Mostrar foros');
define('_DZK_HIDECATEGORY', 'Esconder categoría');
define('_DZK_SHOWCATEGORY', 'Mostrar categoría');
define('_DZK_HIDEFORUM', 'Esconder foro');
define('_DZK_SHOWFORUM', 'Mostrar foro');
define('_DZK_LOADCATEGORYDATA', 'Cargar datos de la categoría');
define('_DZK_LOADFORUMDATA', 'Cargar datos del foro');
define('_DZK_ADMINREORDERTREE', 'Manipular estructura del foro');
define('_DZK_ADMINREORDERTREE_INFO', 'Aquí puedes reordenar las categorias y los foros');
define('_DZK_REORDERFORUMTREE', 'Reordenar estructura del foro');

define('_DZK_STORINGNEWSORTORDER', '... guardando nuevo orden ...');
define('_DZK_TOGGLEUSERINFO', 'mostrar/ocultar detalles de usuario');
define('_DZK_HIDEUSERINFO', 'esconder detalles de usuario');
define('_DZK_FAVORITESDISABLED', 'favoritos deshabilitados');
define('_DZK_STATUS_NOTCHANGED', 'sin cambios');
define('_DZK_STATUS_CHANGED', 'cambiado');
define('_DZK_STORINGPOST', '... guardando mensaje ...');
define('_DZK_UPDATINGPOST', '... actualizando mensaje ...');
define('_DZK_DELETINGPOST', '... borrando mensaje ...');
define('_DZK_PREPARINGPREVIEW', '... preparando previsualización ...');
define('_DZK_STORINGREPLY', '... guardando respuesta ...');

define('_DZK_CATEGORYOVERVIEW', 'Listado de categorías');
define('_DZK_FORUMSOVERVIEW', 'Listado de foros');

// alphasorting starts here

//
// A
//
define('_DZK_ACCOUNT_INFORMATION', 'IP de los usuarios e información de la cuenta');
define('_DZK_ACTIONS','Acciones');
define('_DZK_ACTIVE_FORUMS','Foros más activos:');
define('_DZK_ACTIVE_POSTERS','Usuarios más activos:');
define('_DZK_ADD','Añadir');
define('_DZK_ADDNEWCATEGORY', '-- añadir nueva categoria --');
define('_DZK_ADDNEWFORUM', '-- añadir nuevo foro --');
define('_DZK_ADMINADVANCEDCONFIG', 'Configuración avanzada');
define('_DZK_ADMINADVANCEDCONFIG_HINT', 'Advertencia: una configuración incorrecta pueden provocar consecuencias no deseadas. Si no entiendes qué hay aqui, déja los valores tal y como están!');
define('_DZK_ADMINADVANCEDCONFIG_INFO', 'Establecer configuración avanzada, cuidado!');
define('_DZK_ADMINBADWORDS_TITLE','Administración de filtro de palabras prohibidas');
define('_DZK_ADMINCATADD','Añadir una categoría');
define('_DZK_ADMINCATADD_INFO','Este enlace te permitirá agregar una nueva categoría en la que podrás crear nuevos foros');
define('_DZK_ADMINCATDELETE','Eliminar a categoría');
define('_DZK_ADMINCATDELETE_INFO','Este enlace permite que elimines cualquier categoría de la base de datos');
define('_DZK_ADMINCATEDIT','Editar título de la categoría');
define('_DZK_ADMINCATEDIT_INFO','Este enlace te permitirá editar el título de una categoría');
define('_DZK_ADMINCATORDER','Reordenar Categorias');
define('_DZK_ADMINCATORDER_INFO','Este enlace te permitirá cambiar el orden en que se muestran las categorías en el inicio del foro');
define('_DZK_ADMINFORUMADD','Añadir foro');
define('_DZK_ADMINFORUMADD_INFO','Este enlace te llevará a una página donde podrás agregar un foro a la base de datos.');
define('_DZK_ADMINFORUMEDIT','Editar foro');
define('_DZK_ADMINFORUMEDIT_INFO','Este enlace te permitirá que edites un foro existente.');
define('_DZK_ADMINFORUMOPTIONS','Configuración');
define('_DZK_ADMINFORUMOPTIONS_INFO','Este enlace te permitirá administrar varias opciones-generales del foro.');
define('_DZK_ADMINFORUMORDER','Reordenar Foros');
define('_DZK_ADMINFORUMORDER_INFO','Esto te permite cambiar el orden en el que los foros son listados');
define('_DZK_ADMINFORUMSPANEL','Administración de Dizkus');
define('_DZK_ADMINFORUMSYNC','Sincronizar índice de foros y temas');
define('_DZK_ADMINFORUMSYNC_INFO','Este enlace te permite sincronizar los índices de los foros y temas para resolver las discrepancias que puedan existir');
define('_DZK_ADMINHONORARYASSIGN','Asignar rangos honorarios');
define('_DZK_ADMINHONORARYASSIGN_INFO','Este enlace te permitirá asignar rangos honorarios a algunos usuarios');
define('_DZK_ADMINHONORARYRANKS','Editar rangos honorarios');
define('_DZK_ADMINHONORARYRANKS_INFO','Este enlace te permite crear rangos especiales para usuarios especiales.');
define('_DZK_ADMINRANKS','Editar rangos de usuario');
define('_DZK_ADMINRANKS_INFO','Este enlace te permitirá administrar diferentes rangos de usuario, dependiendo del número de mensajes.');
define('_DZK_ADMINUSERRANK_IMAGE','Imágen');
define('_DZK_ADMINUSERRANK_INFO2','Utiliza este formulario para agregar un rango a la base de datos.');
define('_DZK_ADMINUSERRANK_MAX','Max. mensajes');
define('_DZK_ADMINUSERRANK_MIN','Min. mensajes');
define('_DZK_ADMINUSERRANK_TITLE','Administración rangos de usuario');
define('_DZK_ADMINUSERRANK_TITLE2','Rango');
define('_DZK_ADMIN_SYNC','Sincronizar');
define('_DZK_ALLPNTOPIC', 'todos los temas');
define('_DZK_AND', 'y');
define('_DZK_ASSIGN','Asignar');
define('_DZK_ATTACHSIGNATURE', 'Añadir mi firma');
define('_DZK_AUTHOR','Autor');
define('_DZK_AUTOMATICDISCUSSIONMESSAGE', 'Temas automáticamente creados para discutir contenidos enviados');
define('_DZK_AUTOMATICDISCUSSIONSUBJECT', 'Tema creado automáticamente');

//
// B
//
define('_DZK_BACKTOFORUMADMIN', 'Volver a la administración del foro');
define('_DZK_BACKTOSUBMISSION', 'Ir a este envio');
define('_DZK_BASEDONLASTXMINUTES', 'Esta lista está basada en los usuarios activos en los últimos %m% minutos.');
define('_DZK_BLOCK_PARAMETERS', 'Parámetros');
define('_DZK_BLOCK_PARAMETERS_HINT', 'lista separada por comas, ej. maxposts=5,forum_id=27 ');
define('_DZK_BLOCK_TEMPLATENAME', 'Nombre de la plantilla');
define('_DZK_BODY','Cuerpo del mensaje');
define('_DZK_BOTTOM','Abajo');

//
// C
//
define('_DZK_CANCELPOST','Cancelar mensaje');
define('_DZK_CATEGORIES','Categorias');
define('_DZK_CATEGORY','Categoría');
define('_DZK_CATEGORYINFO', 'Info. categoría');
define('_DZK_CB_RECENTPOSTS','Mensajes recientes:');
define('_DZK_CHANGE_FORUM_ORDER','Cambiar el orden de los foros');
define('_DZK_CHANGE_POST_ORDER','Cambiar el orden de los mensajes');
define('_DZK_CHOOSECATWITHFORUMS4REORDER','Selecciona la categoría que contiene los foros que deseas reordenar');
define('_DZK_CHOOSEFORUMEDIT','Selecciona el foro a editar');
define('_DZK_CREATEFORUM_INCOMPLETE','No completaste todos los campos requeridas del formulario.<br/> No asignaste por lo menos un moderador? Regresa y corrige/completa el formulario');
define('_DZK_CREATESHADOWTOPIC','Crear tema fantasma');
define('_DZK_CURRENT', 'actual');

//
// D
//
define('_DZK_DATABASEINUSE', 'Base de datos en uso');
define('_DZK_DATE','Fecha');
define('_DZK_DELETE','Borrar este mensaje');
define('_DZK_DELETETOPIC','Borrar');
define('_DZK_DELETETOPICS','Borrar temas seleccionados');
define('_DZK_DELETETOPIC_INFO', 'Cuando presiones el botón el tema que has seleccionado y todos sus mensajes relacionados, serán <strong>permanentemente</strong> eliminados.');
define('_DZK_DESCRIPTION', 'Descripción');
define('_DZK_DISCUSSINFORUM', 'Discutir este envio en los foros');
define('_DZK_DOWN','Abajo');

//
// E
//
define('_DZK_EDITBY','editado por:');
define('_DZK_EDITDELETE', 'editar/borrar');
define('_DZK_EDITFORUMS','Editar foros');
define('_DZK_EDITPREFS','Editar tus preferencias');
define('_DZK_EDIT_POST','Editar mensaje');
define('_DZK_EMAILTOPICMSG','Hola! Mira este enlace, creo que será de tu interés, saludos');
define('_DZK_EMAIL_TOPIC', 'Enviar como email');
define('_DZK_EMPTYMSG','Tienes que escribir un mensaje en el post. No puedes publicar un mensaje vacío. Regresa e intenta de nuevo.');
define('_DZK_ERRORLOGGINGIN', 'No se pudo iniciar sesión, nombre de usuario o contraseña incorrectos?');
define('_DZK_ERRORMAILTO', 'Enviar reporte del error');
define('_DZK_ERROROCCURED', 'Ocurrió el siguiente error:');
define('_DZK_ERROR_CONNECT','Error de conexión con la base de datos!<br />');
define('_DZK_EXTENDEDOPTIONSAFTERSAVING', 'Opciones avanzadas estarán disponibles después de grabar');
define('_DZK_EXTERNALSOURCE', 'Fuente externa');
define('_DZK_EXTERNALSOURCEURL_HINT', 'En caso de un canal RSS, proporciona el id del canal dentro del módulo Feeds');

//
// F
//
define('_DZK_FAILEDTOCREATEHOOK', 'Falla al crear hook');
define('_DZK_FAILEDTODELETEHOOK', 'Falla al borrar hook');
define('_DZK_FAVORITES','Favoritos');
define('_DZK_FAVORITE_STATUS','Estado de los favoritos');
define('_DZK_FORUM','Foro');
define('_DZK_FORUMID', 'ID Foros');
define('_DZK_FORUMINFO', 'Info. Foro');
define('_DZK_FORUMS','Foros');
define('_DZK_FORUMSINDEX','Indice foros');
define('_DZK_FORUM_EDIT_FORUM','Editar foro');
define('_DZK_FORUM_EDIT_ORDER','Editar otro');
define('_DZK_FORUM_NOEXIST','Error - El foro/tema seleccionado no existe. Por favor, regresa y prueba de nuevo.');
define('_DZK_FORUM_REORDER','Reordenar');

//
// G
//
define('_DZK_GOTOPAGE','Ir a la pagina');
define('_DZK_GOTO_CAT','ir a la categoría');
define('_DZK_GOTO_FORUM','ir al foro');
define('_DZK_GOTO_LATEST', 'Ver últimos mensajes');
define('_DZK_GOTO_TOPIC','ir al tema');
define('_DZK_GROUP', 'Grupo');

//
// H
//
define('_DZK_HOMEPAGE','Sitio web');
define('_DZK_HONORARY_RANK','Rango honorario');
define('_DZK_HONORARY_RANKS','Rangos honorarios');
define('_DZK_HOST', 'Servidor');
define('_DZK_HOTNEWTOPIC', 'tema activo con nuevos mensajes');
define('_DZK_HOTTHRES','Más de %d mensajes');
define('_DZK_HOTTOPIC', 'tema activo');
define('_DZK_HOURS','horas');

//
// I
//
define('_DZK_ILLEGALMESSAGESIZE', 'Tamaño de mensaje ilegal, máximo 65535 caractéres');
define('_DZK_IMAGE', 'Imágen');
define('_DZK_IP_USERNAMES', 'Los nombres de usuarios que publicaron desde esta IP + cuantos mensajes');
define('_DZK_ISLOCKED','Tema cerrado. No se admiten nuevos mensajes');

//
// J
//
define('_DZK_JOINTOPICS', 'Unir temas');
define('_DZK_JOINTOPICS_INFO', 'Unir dos temas');
define('_DZK_JOINTOPICS_TOTOPIC', 'Tema destino');

//
// L
//
define('_DZK_LAST','últimas');
define('_DZK_LAST24','últimas 24 horas');
define('_DZK_LASTCHANGE','último cambio el');
define('_DZK_LASTPOST','Último mensaje');
define('_DZK_LASTPOSTINGBY', 'último mensaje por');
define('_DZK_LASTPOSTSTRING','%s<br />por %s');
define('_DZK_LASTVISIT', 'última visita');
define('_DZK_LASTWEEK','la semana pasada');
define('_DZK_LAST_SEEN', 'visto el');
define('_DZK_LATEST','Ultimos mensajes');
define('_DZK_LOCKTOPIC','Cerrar este tema');
define('_DZK_LOCKTOPICS','Cerrar los temas seleccionados');
define('_DZK_LOCKTOPIC_INFO', 'Cuando presiones el botón el tema que has seleccionado será <strong>cerrado</strong>. Puedes volver abrirlo cuando quieras.');

//
// M
//
define('_DZK_MAIL2FORUM', 'Mail2Forum');
define('_DZK_MAIL2FORUMPOSTS', 'Listas de correo');
define('_DZK_MAILTO_NOBODY','Tienes que introducir un mensaje.');
define('_DZK_MAILTO_WRONGEMAIL','No añadiste en tus datos la dirección de correo electrónico de destino o es una dirección incorrecta.');
define('_DZK_MANAGETOPICSUBSCRIPTIONS', 'Gestionar subscripciones a temas');
define('_DZK_MANAGETOPICSUBSCRIPTIONS_HINT', 'Aqui puedes administrar tus subscripciones a temas del foro.');
define('_DZK_MINSHORT', 'min');
define('_DZK_MODERATE','Moderar');
define('_DZK_MODERATEDBY','Moderado por');
define('_DZK_MODERATE_JOINTOPICS_HINT', 'Si quieres unir temas, selecciona el tema de destino aqui');
define('_DZK_MODERATE_MOVETOPICS_HINT','Escoge el foro de destino para mover los temas:');
define('_DZK_MODERATION_NOTICE', 'Solicitud de moderación');
define('_DZK_MODERATOR','Moderador');
define('_DZK_MODERATORSOPTIONS', 'Opciones para moderadores');
define('_DZK_MODULEREFERENCE', 'Referencia a módulo');
define('_DZK_MODULEREFERENCE_HINT', 'Usada para los comentarios, todos los temas enviados por este módulo van a este foro. Esta lista sólo contiene los módulos que tienen activado el hook de Dizkus.');
define('_DZK_MORETHAN','Más de');
define('_DZK_MOVED_SUBJECT', 'movido');
define('_DZK_MOVEPOST', 'Mover mensaje');
define('_DZK_MOVEPOST_INFO', 'Mover un mensaj de un tema a otro');
define('_DZK_MOVEPOST_TOTOPIC', 'Tema destino');
define('_DZK_MOVETOPIC','Mover este tema');
define('_DZK_MOVETOPICS','Mover temas seleccionados');
define('_DZK_MOVETOPICTO','Mover el tema a:');
define('_DZK_MOVETOPIC_INFO', 'Cuando presiones el botón el tema que has seleccionado y todos sus mensajes relacionados, serán <strong>movidos</strong> al foro que indicaste.  Nota: Solo podrás trasladarlo a un foro donde seas moderador. Sólo los administradores pueden mover cualquier tema a cualquier foro.');

//
// N
//
define('_DZK_NEWEST_FIRST','Mostrar primero el ultimo mensaje ');
define('_DZK_NEWPOSTS','Nuevos mensajes desde tu ultima visita.');
define('_DZK_NEWTOPIC','Nuevo tema');
define('_DZK_NEXTPAGE','Pagina siguiente');
define('_DZK_NEXT_TOPIC','Tema siguiente');
define('_DZK_NOAUTH', 'No tienes permisos para esta acción');
define('_DZK_NOAUTHPOST','Nota: no estas autorizado para publicar comentarios');
define('_DZK_NOAUTH_MODERATE','No eres moderador de este foro por lo cual no puede realizar esta acción.');
define('_DZK_NOAUTH_TOADMIN', 'No tienes permisos para administrar este modulo');
define('_DZK_NOAUTH_TOMODERATE', 'No tienes permisos para moderar esta categoría o foro');
define('_DZK_NOAUTH_TOREAD', 'No tienes permisos para leer el contenido de esta categoría o foro');
define('_DZK_NOAUTH_TOSEE', 'No tienes permisos para ver el contenido de esta categoría o foro');
define('_DZK_NOAUTH_TOWRITE', 'No tienes permisos para escribir en esta categoría o foro');
define('_DZK_NOCATEGORIES', 'No existen categorías definidas');
define('_DZK_NOEXTERNALSOURCE', 'No hay fuentes externas');
define('_DZK_NOFAVORITES','No hay favoritos');
define('_DZK_NOFORUMS', 'No hay foros definidos');
define('_DZK_NOHOOKEDMODULES', 'No se encontraron módulos enganchados');
define('_DZK_NOHTMLALLOWED', 'No se permiten etiquetas HTML (sólo dentro de [code][/code])');
define('_DZK_NOJOINTO', 'No se seleccionó un tema de destino para la unión');
define('_DZK_NOMODERATORSASSIGNED', 'No tiene moderador asignado');
define('_DZK_NOMOVETO', 'No se especificó el foro de destino para mover los seleccionados');
define('_DZK_NONE', 'ninguno');
define('_DZK_NONEWPOSTS','Ningún mensaje nuevo desde tu ultima visita.');
define('_DZK_NOPNTOPIC', 'sin tema');
define('_DZK_NOPOSTLOCK','No puedes responder a este tema/mensaje, esta cerrado.');
define('_DZK_NOPOSTS','Ningún mensaje');
define('_DZK_NORANK', 'sin rango');
define('_DZK_NORANKSINDATABASE', 'No hay rangos definidos');
define('_DZK_NORMALNEWTOPIC', 'tema normal con nuevos mensajes');
define('_DZK_NORMALTOPIC', 'tema normal');
define('_DZK_NOSMILES','No hay smilies en base de datos');
define('_DZK_NOSPECIALRANKSINDATABASE', 'No hay rangos especiales definidos. Puedes añadir uno desde el siguiente formulario.');
define('_DZK_NOSUBJECT', 'sin asunto');
define('_DZK_NOTEDIT','No puedes editar un mensaje que no sea tuyo.');
define('_DZK_NOTIFYBODY1','Foros');
define('_DZK_NOTIFYBODY2','escrito el');
define('_DZK_NOTIFYBODY3','Responder a este mensaje:');
define('_DZK_NOTIFYBODY4','Leer el hilo:');
define('_DZK_NOTIFYBODY5','Recibes este eMail porque te has suscrito para ser notificado de novedades en los foros:');
define('_DZK_NOTIFYBODY6', 'Enlace para gestionar mis subscripciones a temas y foros:');
define('_DZK_NOTIFYME', 'Notificarme cuando se de una respuesta');
define('_DZK_NOTIFYMODBODY1', 'Petición de moderación');
define('_DZK_NOTIFYMODBODY2', 'Comentario');
define('_DZK_NOTIFYMODBODY3', 'Enlace al tema');
define('_DZK_NOTIFYMODERATOR', 'denunciar');
define('_DZK_NOTIFYMODERATOR_INFO', 'Los moderadores serán notificados del mensaje seleccionado.<br />Las razones consideradas importantes son<br /><dl><dd>violaciones a derechos de autor</dd><dd>insultos personales</dd><dd>etc.</dd></dl>pero no<dl><dd>fallas ortográficas</dd><dd>opiniones diferentes acerca de un tema</dd><dd>etc.</dd></dl><br /><br />Comentario:');
define('_DZK_NOTIFYMODERATOR_TITLE', 'Notificar al moderador de un mensaje');
define('_DZK_NOTOPICS','No hay temas en este foro.');
define('_DZK_NOTOPICSUBSCRIPTIONSFOUND', 'No se encontraron subscripciones a temas');
define('_DZK_NOTSUBSCRIBED','No estas subscrito a este foro');
define('_DZK_NOUSER_OR_POST','Error - No existe usuario o mensaje con ese nombre en la base de datos.');
define('_DZK_NO_FORUMS_DB', 'No hay foros definidos');
define('_DZK_NO_FORUMS_MOVE', 'No hay más foros que moderes para mover');

//
// O
//
define('_DZK_OFFLINE', 'Desconectado');
define('_DZK_OKTODELETE','Borrar?');
define('_DZK_OLDEST_FIRST','Mostrar primero el mensaje mas antiguo');
define('_DZK_ONEREPLY','responder');
define('_DZK_ONLINE', 'conectado');
define('_DZK_OPTIONS','Opciones');
define('_DZK_OR', 'or');
define('_DZK_OURLATESTPOSTS','Últimos mensajes del foro');

//
// P
//
define('_DZK_PAGE','Pagina #');
define('_DZK_PASSWORD', 'Contraseña');
define('_DZK_PASSWORDNOMATCH', 'Las contraseñas no concuerdan, por favor vuelva atrás y escriba las contraseñas correctas');
define('_DZK_PERMDENY','¡Acceso denegado!');
define('_DZK_PERSONAL_SETTINGS','Ajustes personales');
define('_DZK_PNPASSWORD', 'Contraseña ZK');
define('_DZK_PNPASSWORDCONFIRM', 'Confirmación contraseña ZK');
define('_DZK_PNTOPIC', 'Tema Zikula');
define('_DZK_PNTOPIC_HINT', '');
define('_DZK_PNUSER', 'Nombre de usuario ZK');
define('_DZK_POP3ACTIVE', 'Mail2Forum activo');
define('_DZK_POP3INTERVAL', 'Comprobar cada');
define('_DZK_POP3LOGIN', 'Autenticación pop3');
define('_DZK_POP3MATCHSTRING', 'Regla');
define('_DZK_POP3MATCHSTRINGHINT', 'La regla es una expresión regular que comprueba el asunto del email para evitar posible SPAM. Una regla vacía significa que no hay comprobación!');
define('_DZK_POP3PASSWORD', 'Contraseña pop3');
define('_DZK_POP3PASSWORDCONFIRM', 'Confirmación de contraseña pop3');
define('_DZK_POP3PORT', 'Puerto pop3');
define('_DZK_POP3SERVER', 'Servidor pop3');
define('_DZK_POP3TEST', 'Realiza una prueba pop3 después de guardar los cambios');
define('_DZK_POP3TESTRESULTS', 'Resultados de la prueba pop3');
define('_DZK_POST','Mensaje');
define('_DZK_POST_GOTO_NEWEST','Ir al mensaje mas reciente');
define('_DZK_POSTED','Enviado');
define('_DZK_POSTER','Autor');
define('_DZK_POSTS','Mensajes');
define('_DZK_POWEREDBY', 'Soportado por <a href="http://www.dizkus.com/" title="Dizkus">Dizkus</a> Versión');
define('_DZK_PREFS_ASCENDING', 'Ascendente');
define('_DZK_PREFS_AUTOSUBSCRIBE', 'Autosubscribirse a nuevos temas o mensajes');
define('_DZK_PREFS_CHARSET', 'Codificación:<br /><em>(esta es la codificación que se usará en los encabezdos de los correos electrónicos)</em>');
define('_DZK_PREFS_DELETEHOOKACTION', 'Acción a ser ejecutada cuando se invoque deletehook');
define('_DZK_PREFS_DELETEHOOKACTIONLOCK', 'cerrar tema');
define('_DZK_PREFS_DELETEHOOKACTIONREMOVE', 'borrar tema');
define('_DZK_PREFS_DESCENDING', 'Descendente');
define('_DZK_PREFS_EMAIL', 'Dirección Email de:<br /><em>(esta es la dirección que aparecerá en cada eMail enviado por los foros)</em>');
define('_DZK_PREFS_FAVORITESENABLED', 'Favoritos activados');
define('_DZK_PREFS_FIRSTNEWPOSTICON', 'Icono para el primero mensaje nuevo:');
define('_DZK_PREFS_HIDEUSERSINFORUMADMIN', 'Ocultar usuarios en la administración del foro');
define('_DZK_PREFS_HOTNEWPOSTSICON', 'Imagen para temas nuevo mensajes o con muchos:');
define('_DZK_PREFS_HOTTOPIC', 'Umbral para considerar el tema importante:');
define('_DZK_PREFS_HOTTOPICICON', 'Imagen para temas importante:<br /><em>(tema con muchos mensajes)</em>');
define('_DZK_PREFS_ICONS','<br /><strong>Iconos</strong>');
define('_DZK_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX', 'Utilizar búsqueda intensiva ("extended fulltext") en las búsquedas internas');
define('_DZK_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX_HINT', '<i>La búsqueda intensiva permite utilizar parámetros como por ejemplo "+dizkus -skype" para encontrar mensajes que contengan la palabra "dizkus" y que no contengan "skype". Se neceista utilizar al menos MySQL 4.01.</i><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-boolean.html" title="Extended fulltest search in MySQL">Enlace Relacionado</a>.');
define('_DZK_PREFS_LOGIP', 'Dirección IP del registado :');
define('_DZK_PREFS_NO', 'No');
define('_DZK_PREFS_M2FENABLED', 'Mail2Forum activado');
define('_DZK_PREFS_POSTSORTORDER', 'Mostrar los mensajes en orden:');
define('_DZK_PREFS_POSTSPERPAGE', 'Mensajes por página:<br /><em>(este es el número de mensajes por tema que serán mostrados. 15 por defecto.)</em>');
define('_DZK_PREFS_RANKLOCATION', 'Localización de las imagenes de Rangos:');
define('_DZK_PREFS_REMOVESIGNATUREFROMPOST', 'Remover las firmas de los usuarios de los mensajes');
define('_DZK_PREFS_RESTOREDEFAULTS', 'Restaurar valores predeterminados');
define('_DZK_PREFS_RSS2FENABLED', 'RSS2Forum activado');
define('_DZK_PREFS_SAVE', 'Guardar');
define('_DZK_PREFS_SEARCHWITHFULLTEXTINDEX', 'usar búsqueda intensiva ("fulltext") en los foros');
define('_DZK_PREFS_SEARCHWITHFULLTEXTINDEX_HINT', '<i>Realizar búsquedas en el foro con "búsqueda intensiva" necesita al menos MySQL 4 o posteriores y no funcionará con tablas de tipo InnoDB. Esta opción normalmente se establece durante la instalación. El resultado de la búsqueda puede estar vacio si las palabras buscadas están en demasiados mensajes. Esta es una "característica" de MySQL.</i><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-search.html" title="Fulltext search in MySQL">Enlace Relacionado</a>.');
define('_DZK_PREFS_SIGNATUREEND', 'Final del formato de la firma:');
define('_DZK_PREFS_SIGNATURESTART', 'Comienzo del formato de la firma:');
define('_DZK_PREFS_SLIMFORUM', 'Ocultar la vista de categorías con una sola categoría');
define('_DZK_PREFS_STRIPTAGSFROMPOST', 'Remover todas las etiquetas de los nuevos mensajes (no altera los contenidos dentro de [code][/code]');
define('_DZK_PREFS_TOPICICON', 'Imagen para temas:');
define('_DZK_PREFS_TOPICSPERPAGE', 'Temas por foro:<br /><em>(este es el número de temas por foro que serán mostrados. 15 por defecto.)</em>');
define('_DZK_PREFS_YES', 'Sí');
define('_DZK_PREVIEW','Previsualizar');
define('_DZK_PREVIOUS_TOPIC','Tema anterior');
define('_DZK_PREVPAGE','Página previa');
define('_DZK_PRINT_POST','Imprimir mensaje');
define('_DZK_PRINT_TOPIC','Imprimir tema');
define('_DZK_PROFILE', 'Perfil del usuario');

//
// Q
//
define('_DZK_QUICKREPLY', 'Respuesta rápida');
define('_DZK_QUICKSELECTFORUM','- seleccionar -');

//
// R
//
define('_DZK_RECENT_POSTS','Temas recientes:');
define('_DZK_RECENT_POST_ORDER', 'Orden de los mensajes recientes al ver el tema');
define('_DZK_REGISTER','Registrar');
define('_DZK_REGISTRATION_NOTE','Nota: Los usuarios registrados pueden participar en el foro activamente, subscribirse a foros o temas, recibir notificaciones sobre nuevos mensajes y mucho más...');
define('_DZK_REG_SINCE', 'Registrado');
define('_DZK_REMEMBERME', 'Recordarme');
define('_DZK_REMOVE', 'Eliminar');
define('_DZK_REMOVE_FAVORITE_FORUM','Eliminar este foro de favoritos');
define('_DZK_REORDER','Reordenar');
define('_DZK_REORDERCATEGORIES','Reordenar categorias');
define('_DZK_REORDERFORUMS','Reordenar foros');
define('_DZK_REPLACE_WORDS','Reemplazar palabras');
define('_DZK_REPLIES','Respuestas');
define('_DZK_REPLY', 'Responder');
define('_DZK_REPLYLOCKED', 'cerrado');
define('_DZK_REPLYQUOTE', 'citar');
define('_DZK_REPLY_POST','Responder a');
define('_DZK_REPORTINGUSERNAME', 'Reportando usuario');
define('_DZK_RETURNTOTOPIC', 'Regresar al tema');
define('_DZK_RSS2FORUM', 'RSS2Forum');
define('_DZK_RSS2FORUMPOSTS', 'Canales RSS');
define('_DZK_RSSMODULENOTAVAILABLE', '<span style="color: red;">Módulo Feeds no disponible!</span>');
define('_DZK_RSS_SUMMARY', 'Resumen');

//
// S
//
define('_DZK_SAVEPREFS','Guardar preferencias');
define('_DZK_SEARCH','Buscar');
define('_DZK_SEARCHALLFORUMS', 'todos los foros');
define('_DZK_SEARCHAND','todas las palabras [Y]');
define('_DZK_SEARCHBOOL', 'Conexión');
define('_DZK_SEARCHFOR','Buscar por');
define('_DZK_SEARCHINCLUDE_ALLTOPICS', 'todos');
define('_DZK_SEARCHINCLUDE_AUTHOR','Autor');
define('_DZK_SEARCHINCLUDE_BYDATE','por fecha');
define('_DZK_SEARCHINCLUDE_BYFORUM','por foro');
define('_DZK_SEARCHINCLUDE_BYTITLE','por titulo');
define('_DZK_SEARCHINCLUDE_DATE','Fecha');
define('_DZK_SEARCHINCLUDE_FORUM','Categoria y foro');
define('_DZK_SEARCHINCLUDE_HITS', 'resultados por página');
define('_DZK_SEARCHINCLUDE_LIMIT', 'Número de resultados por página');
define('_DZK_SEARCHINCLUDE_MISSINGPARAMETERS', 'Buscar mensajes según parámetros');
define('_DZK_SEARCHINCLUDE_NEWWIN','Mostrar en una nueva ventana');
define('_DZK_SEARCHINCLUDE_NOENTRIES','No se encontraron mensajes en los foros');
define('_DZK_SEARCHINCLUDE_NOLIMIT', 'sin limites');
define('_DZK_SEARCHINCLUDE_ORDER','Orden');
define('_DZK_SEARCHINCLUDE_REPLIES','Respuestas');
define('_DZK_SEARCHINCLUDE_RESULTS','Foros');
define('_DZK_SEARCHINCLUDE_TITLE','Buscar foros');
define('_DZK_SEARCHINCLUDE_VIEWS','Lecturas');
define('_DZK_SEARCHOR','palabras individuales [O]');
define('_DZK_SEARCHRESULTSFOR','Buscar resultados por');
define('_DZK_SELECTACTION', 'Selecciona una acción');
define('_DZK_SELECTED','Selección');
define('_DZK_SELECTEDITCAT','Seleccionar categoría');
define('_DZK_SELECTREFERENCEMODULE', 'seleccionar módulo enganchado');
define('_DZK_SELECTRSSFEED', 'Seleccionar canal RSS');
define('_DZK_SELECTTARGETFORUM', 'seleccionar foro objetivo');
define('_DZK_SELECTTARGETTOPIC', 'seleccionar tema objetivo');
define('_DZK_SENDTO','Enviar a');
define('_DZK_SEND_PM', 'Enviar MP');
define('_DZK_SEPARATOR','&nbsp;>&nbsp;');
define('_DZK_SETTING', 'Ajustes');
define('_DZK_SHADOWTOPIC_MESSAGE', 'El mensaje original ha sido movido <a title="moved" href="%s">aquí</a>.');
define('_DZK_SHOWALLFORUMS','Mostrar todos los foros');
define('_DZK_SHOWFAVORITES','Mostrar favoritos');
define('_DZK_SMILES','Emoticonos:');
define('_DZK_SPLIT','Dividir');
define('_DZK_SPLITTOPIC','Dividir tema');
define('_DZK_SPLITTOPIC_INFO','Esto dividirá el tema antes del mensaje seleccionado.');
define('_DZK_SPLITTOPIC_NEWTOPIC','Asunto para el nuevo tema');
define('_DZK_START', 'Inicio');
define('_DZK_STATSBLOCK','Total de mesajes:');
define('_DZK_STATUS', 'Estado');
define('_DZK_STICKY', 'Destacar');
define('_DZK_STICKYTOPIC','Marcar este tema como destacado');
define('_DZK_STICKYTOPICS','Marcar los temas seleccionados como destacados');
define('_DZK_STICKYTOPIC_INFO', 'Cuando pulses el botón Destacar, el tema seleccionado se convertirá a <strong>\'fijo o PostIt\'</strong>. Puedes volverlo normal cuando quieras.');
define('_DZK_SUBJECT','Asunto');
define('_DZK_SUBJECT_MAX','(máx. 100 caractéres)');
define('_DZK_SUBMIT','Enviar');
define('_DZK_SUBMIT_HINT','ADVERTENCIA: Dizkus no te preguntará por confirmación! Al dar click en Enviar se ejecutará de inmediato la acción seleccionada!');
define('_DZK_SUBSCRIBE_FORUM', 'subscribirme al foro');
define('_DZK_SUBSCRIBE_STATUS','Estado de la Subscripción');
define('_DZK_SUBSCRIBE_TOPIC','Subscribirme al tema');
define('_DZK_SYNC_FORUMINDEX', 'Índice del foro sincronizado');
define('_DZK_SYNC_POSTSCOUNT', 'Contador de mensajes sincronizado');
define('_DZK_SYNC_TOPICS', 'Temas sincronizados');
define('_DZK_SYNC_USERS', 'Usuarios de Zikula y Dizkus sincronizados');

//
// T
//
define('_DZK_TODAY','hoy');
define('_DZK_TOP','Ir arriba');
define('_DZK_TOPIC','Tema');
define('_DZK_TOPICLOCKED','Tema cerrado');
define('_DZK_TOPICS','Temas');
define('_DZK_TOPIC_NOEXIST','Error - El tema que ha seleccionado no existe. Por favor vuelva atrás e intente de nuevo.');
define('_DZK_TOPIC_STARTER','comenzado por');
define('_DZK_TOTAL','Total');

//
// U
//
define('_DZK_UALASTWEEK', 'la semana pasada, sin respuesta');
define('_DZK_UNKNOWNIMAGE', 'imágen desconocida');
define('_DZK_UNKNOWNUSER', '**usuario desconocido**');
define('_DZK_UNLOCKTOPIC','Abrir este tema');
define('_DZK_UNLOCKTOPICS','Abrir los temas seleccionados');
define('_DZK_UNLOCKTOPIC_INFO', 'Cuando pulses el botón Abrir al final de este formulario el tema que has seleccionado será <strong>desbloqueado</strong>. Podrás cerrarlo de nuevo cuando quieras.');
define('_DZK_UNREGISTERED','Usuario no registrado');
define('_DZK_UNSTICKYTOPIC','No destacar tema');
define('_DZK_UNSTICKYTOPICS','Dejar de destacar los temas seleccionados');
define('_DZK_UNSTICKYTOPIC_INFO', 'Cuando presiones el botón No destacar al final de este formulario el tema que has seleccionado será <strong>\'no destacado\'</strong>. Puedes destacarlo de nuevo cuando quieras.');
define('_DZK_UNSUBSCRIBE_FORUM','Cancelar subscripción al foro');
define('_DZK_UNSUBSCRIBE_TOPIC','Cancelar subscripción al tema');
define('_DZK_UP','Subir página');
define('_DZK_UPDATE','Actualizar');
define('_DZK_USERLOGINTITLE', 'Esta funcionalidad solamente es para usuarios registrados');
define('_DZK_USERNAME','Nombre de usuario');
define('_DZK_USERSONLINE', 'Usuarios en línea');
define('_DZK_USERS_RANKS','Rangos de usuario');
define('_DZK_USER_IP', 'IP de usuario');

//
// V
//
define('_DZK_VIEWIP', 'ver dirección ip');
define('_DZK_VIEWS','Lecturas');
define('_DZK_VIEW_IP', 'Ver IP');
define('_DZK_VISITCATEGORY', 'visitar esta category');
define('_DZK_VISITFORUM', 'visitar este forum');

//
// W
//
define('_DZK_WRITTENON', 'escrito el');

//
// Y
//
define('_DZK_YESTERDAY','ayer');
