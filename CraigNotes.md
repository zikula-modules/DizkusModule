Craig's Notes
=============

 - The forum last post uses the FIRST post from the last topic
 - Subforum is showing last post of parent, not self
 - Last post links should go direct to post?
 - Main page should show subforums
 - total posts, topics not including subforums
 - it may be better to enforce 'delete' cascade behavior on Topics then change
   the delete routine. This would also affect the topic join routine
 - Dizkus_Form_Handler_Admin_DeleteForum doesn't delete anything
 - Paginator - Fabian had double-entities because he was having trouble with 
   the paginator. When I eliminate the double entities, this may cause problems
   with the paginator. this needs to be solved for Topics, Posts
 - installer/upgrade routine is incomplete. Also exists in Admin controller an
   `m()` method which appears to be part of an upgrade routine. should this be
   moved to the installer?
 - There appear to be several places in the installer where Core Categories are
   to be used for some purpose. No idea what this is for...
 - most of the table columns have a prefix that should be removed
   for example: `dizkus_forums` table has: forum_id, forum_name, forum_desc, etc
   these should be changed to `id`, `name`, `desc`, etc
   also in many other tables!
 - Post entity should not contain forum_id
 - sub forums do not show a folder icon
 - red/grey folder icons are problematic
 - who's online footer is not working at all.
 - in replypreview.tpl the $reply.poster_data.online var is undefined
 - when viewing preview of reply, a "1" appears at the top of the window
 - should consider marking Topic::posts as "EXTRA-LAZY"
 - Dizkus_Entity_Topic::topic_poster should be converted to ForumUser assoc entity
 - post pager is messed up - off by one


Old tables
----------

Dizkus 3.1 had the following tables
 - dizkus_categories - NOT USED IN DZ4
 - dizkus_forum_mods (used in Moderators entity, which isn't installed)
       (and also used in Moderator_User entity, which is installed)
 - dizkus_forums (used in Forum entity)
 - dizkus_posts (used in Post entity)
 - dizkus_posts_text (was apparently 'obsolete' in v3.1) - NOT USED IN DZ4
 - dizkus_ranks (used in Rank entity)
 - dizkus_subscription (used in ForumSubscription entity)
 - dizkus_topics (used in Topic entity)
 - dizkus_users (used in Poster entity)
 - dizkus_topic_subscription (used in TopicSubscription entity)
 - dizkus_forum_favorites (used in Favorites entity)

New Tables
----------

 - dizkus_forum_mods_group (used in Moderator_Group entity)
