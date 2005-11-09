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
 * french language defines
 * @version $Id$
 * @author various
 * @copyright 2004 by pnForum team
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ************************************************************************
 * Traduction française : Franck Barbenoire, le 18 janvier 2005         *
 * Traduction française : Chestnut, le 11 septembre 2005                *
 ************************************************************************/

//nouveau (sp?, damn, I should have learned french in school, but latin was more interesting :-))

// new 20051105
define('_PNFORUM_PNTOPIC', 'Catégorie (Sujet) PostNuke');
define('_PNFORUM_NOPNTOPIC', 'aucun sujet');
define('_PNFORUM_ALLPNTOPIC', 'tous les sujets');
define('_PNFORUM_PNTOPIC_HINT', '');
define('_PNFORUM_BACKTOSUBMISSION', 'Lien vers l\'Article');
define('_PNFORUM_PREFS_DELETEHOOKACTION', 'Action lorsque l\'extension d\'effacement est appelée');
define('_PNFORUM_PREFS_DELTEHOOKACTIONREMOVE', 'Fermer le sujet');
define('_PNFORUM_PREFS_DELETEHOOKACTIONLOCK', 'Supprimer le sujet');

define('_PNFORUM_AUTOMATICDISCUSSIONSUBJECT', 'Créer automatiquement un sujet');
define('_PNFORUM_AUTOMATICDISCUSSIONMESSAGE', 'Créer automatiquement un sujet des éléments en attente');
define('_PNFORUM_SELECTREFERENCEMODULE', 'Sélectionner le module'); //select hooked module');
define('_PNFORUM_NOHOOKEDMODULES', 'Aucun module trouvé'); //no hooked module found');
define('_PNFORUM_MODULEREFERENCE', 'Référence Module');
define('_PNFORUM_MODULEREFERENCE_HINT', 'Utilisé comme option de commentaires, tous les éléments proposés de ce module auront un sujet associé dans le forum. Cette liste ne contient que les modules pour lesquels le pnForum a été activé.');
define('_PNFORUM_DISCUSSINFORUM', 'Discuter de cette proposition dans le forum');
define('_PNFORUM_FAILEDTODELETEHOOK', 'Echec de la suppression de l\'extension');
define('_PNFORUM_FAILEDTOCREATEHOOK', 'Echec de la création de l\'extension');
define('_PNFORUM_UNKNOWNIMAGE', 'image inconnue');
define('_PNFORUM_PREFS_REMOVESIGNATUREFROMPOST', 'Retirer la signature des utilisateurs des messages');
define('_PNFORUM_ILLEGALMESSAGESIZE', 'Taille de message illégale (max: 65535 caractères');
define('_PNFORUM_PREFS_STRIPTAGSFROMPOST', 'Supprimer le HTML des nouveaux messages (garde le contenu entre les balises [code][/code]');
define('_PNFORUM_USERSONLINE', 'Utilisateurs en ligne');
define('_PNFORUM_BASEDONLASTXMINUTES', 'Données pour les %m% dernières minutes');
define('_PNFORUM_AND', 'et');
define('_PNFORUM_NOHTMLALLOWED', 'Tags HTML interdits (excepté à l\'intérieur des balises [code][/code])');

// alphasorting starts here

//
// A
//
define('_PNFORUM_ACCOUNT_INFORMATION', 'Information des membres - IP et compte');
define('_PNFORUM_ACTIONS','Actions');
define('_PNFORUM_ACTIVE_FORUMS','forums les plus actifs :');
define('_PNFORUM_ACTIVE_POSTERS','membres les plus actifs :');
define('_PNFORUM_ADD','Ajouter');
define('_PNFORUM_ADDNEWCATEGORY', '-- ajouter une nouvelle catégorie --');
define('_PNFORUM_ADDNEWFORUM', '-- ajouter un nouveau forum --');
define('_PNFORUM_ADD_FAVORITE_FORUM','Ajouter un forum aux favoris');
define('_PNFORUM_ADMINADVANCEDCONFIG', 'Configuration avancée');
define('_PNFORUM_ADMINADVANCEDCONFIG_HINT', 'Attention : De mauvais paramètres peuvent avoir des effets secondaires négatifs. Si vous ne comprenez pas ce qui se passe ici, ne prenez pas de risque et laissez la configuration comme elle est.');
define('_PNFORUM_ADMINADVANCEDCONFIG_INFO', 'Attention, mettre à jour la configuration avancée !');
define('_PNFORUM_ADMINBADWORDS_TITLE','Administration des mots censurés');
define('_PNFORUM_ADMINCATADD','Ajouter une catégorie');
define('_PNFORUM_ADMINCATADD_INFO','Ce lien vous permettra d\'ajouter une nouvelle catégorie dans laquelle ajouter des forums');
define('_PNFORUM_ADMINCATDELETE','Supprimer une catégorie');
define('_PNFORUM_ADMINCATDELETE_INFO','Ce lien vous permettra de supprimer une catégorie de la base de données');
define('_PNFORUM_ADMINCATEDIT','Modifier le titre d\'une catégorie');
define('_PNFORUM_ADMINCATEDIT_INFO','Ce lien vous permettra de modifier le titre d\'une catégorie');
define('_PNFORUM_ADMINCATORDER','Réorganiser les catégories');
define('_PNFORUM_ADMINCATORDER_INFO','Ce lien vous permettra de changer l\'ordre d\'affichage des catégories dans la page d\'index');
define('_PNFORUM_ADMINFORUMADD','Ajouter un forum');
define('_PNFORUM_ADMINFORUMADD_INFO','Ce lien vous emmenera vers vers une page où vous pouvez ajouter un forum à la base de données');
define('_PNFORUM_ADMINFORUMEDIT','Modifier un forum');
define('_PNFORUM_ADMINFORUMEDIT_INFO','Ce lien vous permettra de modifier un forum existant');
define('_PNFORUM_ADMINFORUMOPTIONS','Options des forums');
define('_PNFORUM_ADMINFORUMOPTIONS_INFO','Ce lien vous permettra de modifier les options des forums');
define('_PNFORUM_ADMINFORUMORDER','Réorganiser les forums');
define('_PNFORUM_ADMINFORUMORDER_INFO','Cela vous permet de changer l\'ordre dans lequels les forums sont affichés dans la page d\'index');
define('_PNFORUM_ADMINFORUMSPANEL','Administration de pnForum');
define('_PNFORUM_ADMINFORUMSYNC','Synchroniser les forums par rapport à l\'index des sujets');
define('_PNFORUM_ADMINFORUMSYNC_INFO','Ce lien vous permettra de synchroniser le forum avec l\'index des sujets pour réparer des incohérences qui pourraient exister');
define('_PNFORUM_ADMINHONORARYASSIGN','Affecter un titre honorifique');
define('_PNFORUM_ADMINHONORARYASSIGN_INFO','Ce lien vous permettra d\'affecter des titres honorifiques');
define('_PNFORUM_ADMINHONORARYRANKS','Administrer les titres honorifiques');
define('_PNFORUM_ADMINHONORARYRANKS_INFO','Ici, vous pouvez affecter des titres honorifiques à des membres particuliers.');
define('_PNFORUM_ADMINRANKS','Administrer les notations');
define('_PNFORUM_ADMINRANKS_INFO','Ce lien vous permettra d\'ajouter/modifier/supprimer des notations sur les membres en fonction du nombre de messages postés');
define('_PNFORUM_ADMINUSERRANK_IMAGE','Image');
define('_PNFORUM_ADMINUSERRANK_INFO','Pour modifier une notation, changez les valeurs dans les cases de texte et puis cliquez sur le bouton "Modifier".<BR>Pour supprimer une notation, cliquez sur le bouton "Supprimer".');
define('_PNFORUM_ADMINUSERRANK_INFO2','Utilisez ce formulaire pour ajouter une notation dans la base de données.');
define('_PNFORUM_ADMINUSERRANK_MAX','Nombre max de messages');
define('_PNFORUM_ADMINUSERRANK_MIN','Nombre min de messages');
define('_PNFORUM_ADMINUSERRANK_TITLE','Administration des notations des membres');
define('_PNFORUM_ADMINUSERRANK_TITLE2','Notation');
define('_PNFORUM_ADMIN_SYNC','Synchroniser');
define('_PNFORUM_ASSIGN','Affecter à');
define('_PNFORUM_ATTACHSIGNATURE', 'Attacher ma signature');
define('_PNFORUM_AUTHOR','Auteur');

//
// B
//
define('_PNFORUM_BACKTOFORUMADMIN', 'Retour au forum admin');
define('_PNFORUM_BLOCK_PARAMETERS', 'Paramètres');
define('_PNFORUM_BLOCK_PARAMETERS_HINT', 'liste séparée par des virgules, e.g.. maxposts=5,forum_id=27 ');
define('_PNFORUM_BLOCK_TEMPLATENAME', 'Nom du fichier template');
define('_PNFORUM_BODY','Corps du message');
define('_PNFORUM_BOTTOM','Fin');

//
// C
//
define('_PNFORUM_CANCELPOST','Ne pas envoyer');
define('_PNFORUM_CATEGORIES','Catégories');
define('_PNFORUM_CATEGORY','Catégorie');
define('_PNFORUM_CATEGORYINFO', 'Informations sur la catégorie');
define('_PNFORUM_CHANGE_FORUM_ORDER','Réorganiser les forums');
define('_PNFORUM_CHANGE_POST_ORDER','Réorganiser les messages');
define('_PNFORUM_CHOOSECATWITHFORUMS4REORDER','Sélectionnez une catégorie contenant les forums à réorganiser');
define('_PNFORUM_CHOOSEFORUMEDIT','Sélectionnez le forum à modifier');
define('_PNFORUM_CREATEFORUM_INCOMPLETE','Vous n\'avez pas rempli tous les champs obligatoires du formulaire.<br> Avez vous désigné au moins un modérateur ? Revenez en arrière et corrigez le formulaire');
define('_PNFORUM_CREATESHADOWTOPIC','Créer un sujet caché');
define('_PNFORUM_CURRENT', 'courant');

//
// D
//
define('_PNFORUM_DATABASEINUSE', 'Base de données utilisée');
define('_PNFORUM_DATE','Date');
define('_PNFORUM_DELETE','Supprimer ce message');
define('_PNFORUM_DELETETOPIC','Supprimer ce sujet');
define('_PNFORUM_DELETETOPICS','Supprimer les sujets sélectionnés');
define('_PNFORUM_DELETETOPIC_INFO', 'En appuyant sur le bouton de suppression à la fin de ce formulaire, le sujet sélectionné et tous les messages qui en dépendent seront <strong>définitivement</strong> supprimés.');
define('_PNFORUM_DESCRIPTION', 'Description');
define('_PNFORUM_DOWN','Vers le bas');

//
// E
//
define('_PNFORUM_EDITBY','modifié par :');
define('_PNFORUM_EDITDELETE', 'modifier/supprimer');
define('_PNFORUM_EDITFORUMS','Modifier les forums');
define('_PNFORUM_EDITPREFS','Modifier vos préférences');
define('_PNFORUM_EDIT_POST','Modifier le message');
define('_PNFORUM_EMAILTOPICMSG','Bonjour ! Visitez ce site, cela devrait vous intéresser');
define('_PNFORUM_EMAIL_TOPIC', 'envoyer en tant qu\'email');
define('_PNFORUM_EMPTYMSG','Vous devez entrer un message. Les messages vides ne sont pas autorisés. Revenez en arrière et réessayez.');
define('_PNFORUM_ERRORMAILTO', 'Envoyer un rapport de bug');
define('_PNFORUM_ERROROCCURED', 'L\'erreur suivante s\'est produite :');
define('_PNFORUM_ERROR_CONNECT','Erreur de connexion à la base de données !<br>');

//
// F
//
define('_PNFORUM_FAVORITES','Favoris');
define('_PNFORUM_FAVORITE_STATUS','Etat des favoris');
define('_PNFORUM_FORUM','Forum');
define('_PNFORUM_FORUMID', 'ID des forums');
define('_PNFORUM_FORUMINFO', 'Informations sur ce forum');
define('_PNFORUM_FORUMS','Forums');
define('_PNFORUM_FORUMSINDEX','Index du forum');
define('_PNFORUM_FORUM_EDIT_FORUM','Modifier le forum');
define('_PNFORUM_FORUM_EDIT_ORDER','Modifier l\'ordre');
define('_PNFORUM_FORUM_NOEXIST','Erreur - Le forum/sujet que vous avez sélectionné n\'existe pas. Retournez en arrière et réessayez.');
define('_PNFORUM_FORUM_REORDER','Réorganiser');
define('_PNFORUM_FORUM_SEQUENCE_DESCRIPTION','Si vous voulez déplacer un forum d\'une seule position, cliquez sur la flêche vers le haut ou vers le bas. Si un forum a un numéro d\'ordre à 0, il sera affiché par ordre lexicographique. L\'ordre lexicographique a priorité sur l\'ordre numérique.  Cliquez sur le numéro d\'ordre pour le modifier.');

//
// G
//
define('_PNFORUM_GOTOPAGE','Aller à la page');
define('_PNFORUM_GOTO_CAT','aller à la categorie');
define('_PNFORUM_GOTO_FORUM','aller au forum');
define('_PNFORUM_GOTO_LATEST', 'voir le dernier message');
define('_PNFORUM_GOTO_TOPIC','aller au sujet');
define('_PNFORUM_GROUP', 'Groupe');

//
// H
//
define('_PNFORUM_HOMEPAGE','Accueil');
define('_PNFORUM_HONORARY_RANK','Titre honorifique');
define('_PNFORUM_HONORARY_RANKS','Titres honorifiques');
define('_PNFORUM_HOST', 'Hôte');
define('_PNFORUM_HOTNEWTOPIC', 'Sujet chaud contenant des nouveaux messages');
define('_PNFORUM_HOTTHRES','Plus de %d messages');
define('_PNFORUM_HOTTOPIC', 'Sujet chaud');
define('_PNFORUM_HOURS','heures');

//
// I
//
define('_PNFORUM_IMAGE', 'Image');
define('_PNFORUM_IP_USERNAMES', 'Nom des utilisateurs actifs avec leurs totaux et IP');
define('_PNFORUM_ISLOCKED','Le sujet est verrouillé. Pas de nouveau message');

//
// J
//
define('_PNFORUM_JOINTOPICS', 'Fusionner des sujets');
define('_PNFORUM_JOINTOPICS_INFO', 'Fusionner deux sujets');
define('_PNFORUM_JOINTOPICS_TOTOPIC', 'Sujet cible');

//
// L
//
define('_PNFORUM_LAST','dernières');
define('_PNFORUM_LAST24','dernières 24 heures');
define('_PNFORUM_LASTCHANGE','dernière modification le ');
define('_PNFORUM_LASTPOST','Dernier message');
define('_PNFORUM_LASTPOSTSTRING','%s<br />par %s');
define('_PNFORUM_LASTVISIT', 'dernière visite');
define('_PNFORUM_LASTWEEK','semaine dernière');
define('_PNFORUM_LAST_SEEN', 'dernière visite');
define('_PNFORUM_LATEST','Derniers messages');
define('_PNFORUM_LOCKTOPIC','Verrouillez ce sujet');
define('_PNFORUM_LOCKTOPICS','Verrouiller les sujets sélectionnés');
define('_PNFORUM_LOCKTOPIC_INFO', 'Quand vous pressez le bouton de verrouillage à la fin de ce formulaire, le sujet sélectionné sera <strong>verrouillé</strong>. Vous pourrez le déverouiller plus tard.');

//
// M
//
define('_PNFORUM_MAIL2FORUM', 'Mail2Forum');
define('_PNFORUM_MAIL2FORUMPOSTS', 'Listes de diffusion');
define('_PNFORUM_MAILTO_NOBODY','Vous devez entrer un message.');
define('_PNFORUM_MAILTO_WRONGEMAIL','Vous n\'avez pas saisi d\'adresse email pour le destinataire ou elle n\'est pas correcte.');
define('_PNFORUM_MANAGETOPICSUBSCRIPTIONS', 'Administrer les inscriptions aux sujets');
define('_PNFORUM_MANAGETOPICSUBSCRIPTIONS_HINT', 'Vous pouvez gérer les inscriptions aux sujets sur cette page.');
define('_PNFORUM_MINSHORT', 'min');
define('_PNFORUM_MODERATE','Modérer');
define('_PNFORUM_MODERATEDBY','Modéré par');
define('_PNFORUM_MODERATE_JOINTOPICS_HINT', 'Si vous désirez fusionner des sujets, sélectionnez ici le sujet cible'); // Error in English file
define('_PNFORUM_MODERATE_MOVETOPICS_HINT','Choisissez le forum cible où déplacer les sujets :');
define('_PNFORUM_MODERATION_NOTICE', 'Requête de Modération');
define('_PNFORUM_MODERATOR','Modérateur');
define('_PNFORUM_MODERATORSOPTIONS', 'Options - Modérateurs');
define('_PNFORUM_MORETHAN','Plus de ');
define('_PNFORUM_MOVED_SUBJECT', 'déplacé');
define('_PNFORUM_MOVEPOST', 'Déplacer un message');
define('_PNFORUM_MOVEPOST_INFO', 'Déplacer un message d\'un sujet à un autre');
define('_PNFORUM_MOVEPOST_TOTOPIC', 'Sujet cible');
define('_PNFORUM_MOVETOPIC','Déplacer ce sujet');
define('_PNFORUM_MOVETOPICS','Déplacer les sujets sélectionnés');
define('_PNFORUM_MOVETOPICTO','Déplacez le sujet vers :');
define('_PNFORUM_MOVETOPIC_INFO', 'Quand vous pressez le bouton de déplacement à la fin de ce formulaire, le sujet sélectionné et les messages qu\'il contient seront <strong>déplacés</strong> vers le forum sélectionné. Note: vous n\'êtes autorisé à déplacer que vers les forums dont vous êtes modérateur. L\'administrateur peut déplacer n\importe quel sujet dans n\importe quel forum.');

//
// N
//
define('_PNFORUM_NEWEST_FIRST','Afficher en premier les nouveaux messages');
define('_PNFORUM_NEWPOSTS','Nouveaux messages présents depuis votre dernière visite.');
define('_PNFORUM_NEWTOPIC','nouveau sujet');
define('_PNFORUM_NEW_THREADS','Nouveau sujet');
define('_PNFORUM_NEXTPAGE','Page suivante');
define('_PNFORUM_NEXT_TOPIC','vers le sujet suivant');
define('_PNFORUM_NOAUTH', 'Action interdite');
define('_PNFORUM_NOAUTHPOST','Note: non autorisé à envoyer des commentaires');
define('_PNFORUM_NOAUTH_MODERATE','Vous n\'êtes pas le modérateur de ce forum, vous ne pouvez donc pas exécuter cette action.');
define('_PNFORUM_NOAUTH_TOADMIN', 'Vous n\'avez pas l\'autorisation d\'administrer ce module');
define('_PNFORUM_NOAUTH_TOMODERATE', 'Vous n\'avez pas l\'autorisation de modérer cette catégorie ou ce forum');
define('_PNFORUM_NOAUTH_TOREAD', 'Vous n\'avez pas l\'autorisation de lire cette catégorie ou ce forum');
define('_PNFORUM_NOAUTH_TOSEE', 'Vous n\'avez pas l\'autorisation de voir cette catégorie ou ce forum');
define('_PNFORUM_NOAUTH_TOWRITE', 'Vous n\'avez pas l\'autorisation d\'écrire dans cette catégorie ou ce forum');
define('_PNFORUM_NOCATEGORIES', 'Pas de catégorie');
define('_PNFORUM_NOFAVORITES','Pas de favoris');
define('_PNFORUM_NOFORUMS', 'pas de forum');
define('_PNFORUM_NOJOINTO', 'Aucun sujet cible pour fusionner la sélection');
define('_PNFORUM_NOMODERATORSASSIGNED', 'pas de modérateur désigné');
define('_PNFORUM_NOMOVETO', 'Aucun forum cible où déplacer la sélection');
define('_PNFORUM_NONE', 'aucun');
define('_PNFORUM_NONEWPOSTS','Pas de nouveau message depuis votre dernière visite.');
define('_PNFORUM_NOPOSTLOCK','Vous ne pouvre pas répondre à ce message, le sujet est verrouillé.');
define('_PNFORUM_NOPOSTS','Pas de message');
define('_PNFORUM_NORANK', 'Pas de titre honorifique');
define('_PNFORUM_NORANKSINDATABASE', 'Pas de titre honorifique');
define('_PNFORUM_NORMALNEWTOPIC', 'Sujet normal contenant des nouveaux messages');
define('_PNFORUM_NORMALTOPIC', 'Sujet normal');
define('_PNFORUM_NOSMILES','Il n\'y a pas d\'émoticon dans la base de données');
define('_PNFORUM_NOSPECIALRANKSINDATABASE', 'Aucun rang spécial dans la base de données. Vous pouvez en ajouter un par le formulaire ci-bas.');
define('_PNFORUM_NOSUBJECT', 'Aucun sujet');
define('_PNFORUM_NOTEDIT','Vous ne pouvez pas modifier les messages dont vous n\'êtes pas l\'auteur.');
define('_PNFORUM_NOTIFYBODY1','Forums');
define('_PNFORUM_NOTIFYBODY2','écrit à');
define('_PNFORUM_NOTIFYBODY3','Répondre à ce message : ');
define('_PNFORUM_NOTIFYBODY4','Faire défiler les fils de discussion :');
define('_PNFORUM_NOTIFYBODY5','Vous recevez ce message car vous avez demandé à recevoir les messages du forum : ');
define('_PNFORUM_NOTIFYME', 'M\'avertir lorsqu\'une réponse est envoyée');
define('_PNFORUM_NOTIFYMODBODY1', 'Requête de modération');
define('_PNFORUM_NOTIFYMODBODY2', 'Commentaire');
define('_PNFORUM_NOTIFYMODBODY3', 'Lien au sujet');
define('_PNFORUM_NOTIFYMODERATOR', 'avertir un modérateur');
define('_PNFORUM_NOTIFYMODERATORTITLE', 'Avertir un modérateur à propos d\'un message');
define('_PNFORUM_NOTOPICS','Il n\'y a pas de sujet dans ce forum.');
define('_PNFORUM_NOTOPICSUBSCRIPTIONSFOUND', 'Aucune inscription trouvée');
define('_PNFORUM_NOTSUBSCRIBED','Vous n\'êtes pas inscrit à ce forum');
define('_PNFORUM_NOUSER_OR_POST','Erreur - Ce membre ou ce message n\'existe pas dans la base de données.');
define('_PNFORUM_NO_FORUMS_DB', 'Pas de forum dans la base de données');
define('_PNFORUM_NO_FORUMS_MOVE', 'Aucun autre forum modéré par vous où aller');

//
// O
//
define('_PNFORUM_OFFLINE', 'hors ligne');
define('_PNFORUM_OKTODELETE','Supprimer ?');
define('_PNFORUM_OLDEST_FIRST','Afficher en premier les plus vieux messages');
define('_PNFORUM_ONEREPLY','réponse');
define('_PNFORUM_ONLINE', 'en ligne');
define('_PNFORUM_OPTIONS','Options');
define('_PNFORUM_OR', 'ou');
define('_PNFORUM_OURLATESTPOSTS','Derniers messages du forum');

//
// P
//
define('_PNFORUM_PAGE','Page #');
define('_PNFORUM_PASSWORDNOMATCH', 'Les mots de passe ne correspondent pas, s.v.p. revenez en arrière pour corriger');
define('_PNFORUM_PERMDENY','Accès refusé !');
define('_PNFORUM_PERSONAL_SETTINGS','Préférences');
define('_PNFORUM_POP3ACTIVE', 'Mail2Forum activé');
define('_PNFORUM_POP3INTERVAL', 'Interval de recherche de message');
define('_PNFORUM_POP3LOGIN', 'Login Pop3');
define('_PNFORUM_POP3MATCHSTRING', 'Règle');
define('_PNFORUM_POP3MATCHSTRINGHINT', 'La règle est une expression régulière que doit contenir le sujet des mails pour éviter le spam. Aucune vérification si la règle est vide !');
define('_PNFORUM_POP3PASSWORD', 'Mot de passe Pop3');
define('_PNFORUM_POP3PASSWORDCONFIRM', 'Confirmer le mot de passe Pop3');
define('_PNFORUM_POP3PORT', 'Port');
define('_PNFORUM_POP3SERVER', 'Serveur Pop3');
define('_PNFORUM_PNPASSWORD', 'Mot de passe PN');
define('_PNFORUM_PNPASSWORDCONFIRM', 'Confirmer le mot de passe PN');
define('_PNFORUM_PNUSER', 'Pseudo PN');
define('_PNFORUM_POP3TEST', 'Effectuer le test Pop3 après la sauvegarde');
define('_PNFORUM_POP3TESTRESULTS', 'Resultats du test Pop3');
define('_PNFORUM_POST','Envoyer');
define('_PNFORUM_POSTED','Envoyé');
define('_PNFORUM_POSTER','Expéditeur');
define('_PNFORUM_POSTS','Messages');
define('_PNFORUM_POST_GOTO_NEWEST','aller au message le plus récent dans ');
define('_PNFORUM_POWEREDBY', 'Généré par <a href="http://www.pnforum.de/" title="pnForum">pnForum</a> Version');
define('_PNFORUM_PREFS_ASCENDING', 'Ascendant');
define('_PNFORUM_PREFS_AUTOSUBSCRIBE', 'Inscription automatique aux nouveaux sujets et messages');
define('_PNFORUM_PREFS_CHARSET', 'Jeu de caractères par défaut :<br /><em>(c\'est le jeu de caractères utilisé dans les en-têtes des emails)</em>');
define('_PNFORUM_PREFS_DESCENDING', 'Descendant');
define('_PNFORUM_PREFS_EMAIL', 'Adresse email de l\'expéditeur :<br /><em>(apparaîtra dans tous les emails envoyés par le forum)</em>');
define('_PNFORUM_PREFS_FAVORITESENABLED', 'Favoris activés');
define('_PNFORUM_PREFS_FIRSTNEWPOSTICON', 'Icône d\'envoi du premier message :');
define('_PNFORUM_PREFS_HIDEUSERSINFORUMADMIN', 'Cacher les utilisateurs dans le forum admin');
define('_PNFORUM_PREFS_HOTNEWPOSTSICON', 'Icône de sujet contenant beaucoup de messages dont beaucoup sont nouveaux :');
define('_PNFORUM_PREFS_HOTTOPIC', 'Nombre de messages au delà duquel un sujet est considéré comme brûlant :');
define('_PNFORUM_PREFS_HOTTOPICICON', 'Icône de sujet brûlant :<br /><em>(sujet contenant beaucoup de messages)</em>');
define('_PNFORUM_PREFS_ICONS','<br /><strong>Icônes</strong>');
define('_PNFORUM_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX', 'Utiliser la recherche étendue dans la recherche interne');
define('_PNFORUM_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX_HINT', '<i>La recherche étendue permet l\'utilisation de paramètres comme "+pnforum -skype" pour trouver les messages contenant "pnforum" mais ne contenant pas "skype". Minimum requis : MySQL 4.01.</i><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-boolean.html" title="Recherche étendue sur MySQL">Recherche étendue sur MySQL</a>.');
define('_PNFORUM_PREFS_LOGIP', 'Enregistrer les adresses IP :');
define('_PNFORUM_PREFS_M2FENABLED', 'Mail2Forum activé');
define('_PNFORUM_PREFS_NEWPOSTSICON', 'Icône de nouveaux messages :<br /><em>(sujet contenant de nouveaux messages depuis la dernière visite)</em>');
define('_PNFORUM_PREFS_NO', 'Non');
define('_PNFORUM_PREFS_POSTICON', 'Icône d\'envoi de message :');
define('_PNFORUM_PREFS_POSTSORTORDER', 'Ordre de tri des messages :');
define('_PNFORUM_PREFS_POSTSPERPAGE', 'Nombre de messages par page :<br /><em>(c\'est le nombre de messages d\'un sujet qui seront affichés par page. La valeur par défaut est 15)</em>');
define('_PNFORUM_PREFS_RANKLOCATION', 'Emplacement des icônes de notation :');
define('_PNFORUM_PREFS_REMOVESIGNATUREFROMPOST', 'Retirer la signature des utilisateurs sur les messages (Affichage)');
define('_PNFORUM_PREFS_RESTOREDEFAULTS', 'Restaurer les valeurs par défaut');
define('_PNFORUM_PREFS_SAVE', 'Enregistrer');
define('_PNFORUM_PREFS_SEARCHWITHFULLTEXTINDEX', 'Rechercher en utilisant la recherche par index');
define('_PNFORUM_PREFS_SEARCHWITHFULLTEXTINDEX_HINT', '<i>La recherche utilisant les index de texte complet requiert min. MySQL 4 ou plus récent et ne fonctionne pas avec les bases InnoDB. Ce drapeau sera normalement utilisé lors de l\'installation lorsque les index auront été créés. Le résultat de la recherche pourrait être vide si la requête est présente dans un trop gran nombre de messages. C\'est une "fonctionnalité" MySQL.</i><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-search.html" title="Fulltext search in MySQL">Recherche Texte complet MySQL</a>.');
define('_PNFORUM_PREFS_SIGNATUREEND', 'Marque de fin de signature :');
define('_PNFORUM_PREFS_SIGNATURESTART', 'Marque de début de signature :');
define('_PNFORUM_PREFS_SLIMFORUM', 'Masquer les catégories contenant un seul forum');
define('_PNFORUM_PREFS_TOPICICON', 'Icône de sujet :');
define('_PNFORUM_PREFS_TOPICSPERPAGE', 'Nombre de sujets par page :<br /><em>(c\'est le nombre de sujets d\'un forum qui seront affichés par page. La valeur par défaut est 15)</em>');
define('_PNFORUM_PREFS_YES', 'Oui');
define('_PNFORUM_PREVIEW','Aperçu');
define('_PNFORUM_PREVIOUS_TOPIC','vers le sujet précédent');
define('_PNFORUM_PREVPAGE','Page précédente');
define('_PNFORUM_PRINT_POST','Imprimer le message');
define('_PNFORUM_PRINT_TOPIC','Imprimer le sujet');
define('_PNFORUM_PROFILE', 'Données personnelles');

//
// Q
//
define('_PNFORUM_QUICKREPLY', 'Réponse rapide');
define('_PNFORUM_QUICKSELECTFORUM','- selectionner -');

//
// R
//
define('_PNFORUM_RECENT_POSTS','sujets récents :');
define('_PNFORUM_RECENT_POST_ORDER', 'Ordre des messages récents dans les sujets');
define('_PNFORUM_REGISTER','S\'enregistrer');
define('_PNFORUM_REGISTRATION_NOTE','Note: les membres peuvent s\'inscrire pour recevoir les nouveaux messages');
define('_PNFORUM_REG_SINCE', 'enregistré depuis');
define('_PNFORUM_REMOVE', 'supprimer');
define('_PNFORUM_REMOVE_FAVORITE_FORUM','Supprimer des favoris');
define('_PNFORUM_REORDER','Réorganiser');
define('_PNFORUM_REORDERCATEGORIES','Réorganiser les catégories');
define('_PNFORUM_REORDERFORUMS','Réorganiser les forums');
define('_PNFORUM_REPLACE_WORDS','Remplacer les mots');
define('_PNFORUM_REPLIES','Réponses');
define('_PNFORUM_REPLY', 'répondre');
define('_PNFORUM_REPLYLOCKED', 'verrouillé');
define('_PNFORUM_REPLYQUOTE', 'citer');
define('_PNFORUM_REPLY_POST','Répondre à ');
define('_PNFORUM_REPORTINGUSERNAME', 'Rapporter un utilisateur');
define('_PNFORUM_RETURNTOTOPIC', 'Retour au sujet');

//
// S
//
define('_PNFORUM_SAVEPREFS','Enregistrer vos préférences');
define('_PNFORUM_SEARCH','Recherche');
define('_PNFORUM_SEARCHALLFORUMS', 'tous les forums');
define('_PNFORUM_SEARCHAND','tous les mots [AND]');
define('_PNFORUM_SEARCHBOOL', 'condition logique');
define('_PNFORUM_SEARCHFOR','Chercher');
define('_PNFORUM_SEARCHINCLUDE_ALLTOPICS', 'tous');
define('_PNFORUM_SEARCHINCLUDE_AUTHOR','Auteur');
define('_PNFORUM_SEARCHINCLUDE_BYDATE','par date');
define('_PNFORUM_SEARCHINCLUDE_BYFORUM','par forum');
define('_PNFORUM_SEARCHINCLUDE_BYTITLE','par titre');
define('_PNFORUM_SEARCHINCLUDE_DATE','Date');
define('_PNFORUM_SEARCHINCLUDE_FORUM','Catégorie et forum');
define('_PNFORUM_SEARCHINCLUDE_HITS', 'clics');
define('_PNFORUM_SEARCHINCLUDE_LIMIT', 'Limiter la recherche à');
define('_PNFORUM_SEARCHINCLUDE_MISSINGPARAMETERS', 'paramètres de recherche manquant');
define('_PNFORUM_SEARCHINCLUDE_NEWWIN','Afficher dans une nouvelle fenêtre');
define('_PNFORUM_SEARCHINCLUDE_NOENTRIES','Aucun message dans les forums');
define('_PNFORUM_SEARCHINCLUDE_NOLIMIT', 'Pas de limite');
define('_PNFORUM_SEARCHINCLUDE_ORDER','Ordre');
define('_PNFORUM_SEARCHINCLUDE_REPLIES','Réponses');
define('_PNFORUM_SEARCHINCLUDE_RESULTS','Forums');
define('_PNFORUM_SEARCHINCLUDE_TITLE','Chercher dans les forums');
define('_PNFORUM_SEARCHINCLUDE_VIEWS','Affichages');
define('_PNFORUM_SEARCHOR','un seul mot [OR]');
define('_PNFORUM_SEARCHRESULTSFOR','Résultats de la recherche');
define('_PNFORUM_SELECTACTION', 'sélectionner une action');
define('_PNFORUM_SELECTED','Sélection');
define('_PNFORUM_SELECTEDITCAT','Selectionnez une categorie');
define('_PNFORUM_SELECTTARGETFORUM', 'sélectionner un forum cible');
define('_PNFORUM_SELECTTARGETTOPIC', 'sélectionner un sujet cible');
define('_PNFORUM_SENDTO','Envoyer à');
define('_PNFORUM_SEND_PM', 'Envoyer un message privé');
define('_PNFORUM_SEPARATOR','&nbsp;::&nbsp;');
define('_PNFORUM_SETTING', 'Préférences');
define('_PNFORUM_SHADOWTOPIC_MESSAGE', 'Le message original a été déplacé <a title="moved" href="%s">ici</a>.');
define('_PNFORUM_SHOWALLFORUMS','Afficher tous les forums');
define('_PNFORUM_SHOWFAVORITES','Afficher les favoris');
define('_PNFORUM_SMILES','Emoticons :');
define('_PNFORUM_SPLIT','Scinder');
define('_PNFORUM_SPLITTOPIC','Scinder le sujet');
define('_PNFORUM_SPLITTOPIC_INFO','Cela scindera le sujet avant le message sélectionné.');
define('_PNFORUM_SPLITTOPIC_NEWTOPIC','Sujet du nouveau message');
define('_PNFORUM_START', 'Racine');
define('_PNFORUM_STATSBLOCK','Nombre total de messages :');
define('_PNFORUM_STATUS', 'Status');
define('_PNFORUM_STICKY', 'Collé');
define('_PNFORUM_STICKYTOPIC','Coller ce sujet');
define('_PNFORUM_STICKYTOPICS','Coller les sujets sélectionnés');
define('_PNFORUM_STICKYTOPIC_INFO', 'Quand vous pressez le bouton de collage à la fin du formulaire, le sujet sera <strong>collé</strong>. Vous pourrez le décoller ultérieurement.');
define('_PNFORUM_SUBJECT','Sujet');
define('_PNFORUM_SUBJECT_MAX','(pas plus de 100 symboles)');
define('_PNFORUM_SUBMIT','Envoyer');
define('_PNFORUM_SUBMIT_HINT','ATTENTION : pnForum ne vous demandera pas de confirmation ! Cliquer sur Soumettre débutera immédiatement l\'action sélectionnée!');
define('_PNFORUM_SUBSCRIBE_FORUM', 's\'inscrire au forum');
define('_PNFORUM_SUBSCRIBE_STATUS','Etat de vos inscriptions');
define('_PNFORUM_SUBSCRIBE_TOPIC','s\'inscrire au sujet');
define('_PNFORUM_SYNC_FORUMINDEX', 'Index du forum synchronisé');
define('_PNFORUM_SYNC_POSTSCOUNT', 'Compteur de sujets synchronisé');
define('_PNFORUM_SYNC_TOPICS', 'Sujets synchronisés');
define('_PNFORUM_SYNC_USERS', 'Membres de PostNuke et pnForum synchronisés');

//
// T
//
define('_PNFORUM_TODAY','aujourd\'hui');
define('_PNFORUM_TOGGLEALL', 'Supprimer toutes les inscriptions aux sujets');
define('_PNFORUM_TOP','Top');
define('_PNFORUM_TOPIC','Sujet');
define('_PNFORUM_TOPICLOCKED', 'Sujet verrouillé');
define('_PNFORUM_TOPICS','Sujets');
define('_PNFORUM_TOPIC_NOEXIST','Erreur - Le sujet sélectionné n\'existe pas. Retournez en arrière et faites un autre essai.');
define('_PNFORUM_TOPIC_STARTER','commencé par');
define('_PNFORUM_TOTAL','Total');

//
// U
//
define('_PNFORUM_UALASTWEEK', 'dernière semaine, sans réponse');
define('_PNFORUM_UNKNOWNIMAGE', 'image inconnue');
define('_PNFORUM_UNKNOWNUSER', '**utilisateur inconnu**');
define('_PNFORUM_UNLOCKTOPIC','Déverrouiller ce sujet');
define('_PNFORUM_UNLOCKTOPICS','Déverrouiller les sujets sélectionnés');
define('_PNFORUM_UNLOCKTOPIC_INFO', 'Quand vous pressez le bouton de déverrouillage à la fin du formulaire, le sujet sélectionné sera <strong>déverouillé</strong>. Vous pourrez le verrouiller à nouveau ultérieurement.');
define('_PNFORUM_UNREGISTERED','Utilisateur non enregistré');
define('_PNFORUM_UNSTICKYTOPIC','Sujet non collé');
define('_PNFORUM_UNSTICKYTOPICS','Décoller les sujets sélectionnés');
define('_PNFORUM_UNSTICKYTOPIC_INFO', 'Quand vous pressez le bouton de décollage à la fin de ce formulaire, le sujet sélectionné sera <strong>décollé</strong>. Vous pourrez le recoller ultérieurement.');
define('_PNFORUM_UNSUBSCRIBE_FORUM','se désinscrire du forum');
define('_PNFORUM_UNSUBSCRIBE_TOPIC','se désinscrire du sujet');
define('_PNFORUM_UP','Vers le haut');
define('_PNFORUM_UPDATE','Mettre à jour');
define('_PNFORUM_USEBBCODE','Cliquez sur les boutons pour ajouter <a href="modules.php?op=modload&amp;name=Messages&amp;file=bbcode_ref">BBCode</a> à votre message :');
define('_PNFORUM_USERNAME','Nom d\'utilisateur');
define('_PNFORUM_USERS_RANKS','Notation des membres');
define('_PNFORUM_USER_IP', 'IP du Membre');

//
// V
//
define('_PNFORUM_VIEWIP', 'voir l\'adresse IP');
define('_PNFORUM_VIEWS','Affichages');
define('_PNFORUM_VIEW_IP', 'Voir l\'adresse IP');
define('_PNFORUM_VISITCATEGORY', 'visitez cette categorie');
define('_PNFORUM_VISITFORUM', 'visitez ce forum');

//
// W
//
define('_PNFORUM_WHATISBBCODE', 'Le BBCode, c\'est quoi ?');
define('_PNFORUM_WRONGPNVERSIONFORMAIL2FORUM', 'Minimum requis pour le Mail2Forum : PostNuke .760 et plus !');

//
// Y
//
define('_PNFORUM_YESTERDAY','hier');

?>
