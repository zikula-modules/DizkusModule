Craig's Notes
=============

 - total posts, topics not including subforums
 - it may be better to enforce 'delete' cascade behavior on Topics then change
   the delete routine. This would also affect the topic join routine
 - Paginator - Fabian had double-entities because he was having trouble with 
   the paginator. When I eliminate the double entities, this may cause problems
   with the paginator. this needs to be solved for Topics, Posts
 - installer/upgrade routine is incomplete. Also exists in Admin controller an
   `m()` method which appears to be part of an upgrade routine. should this be
   moved to the installer?
 - most of the table columns have a prefix that should be removed
   for example: `dizkus_forums` table has: forum_id, forum_name, forum_desc, etc
   these should be changed to `id`, `name`, `desc`, etc
   also in many other tables!
 - red/grey folder icons are problematic
 - who's online footer is not working at all.
 - should consider marking Topic::posts as "EXTRA-LAZY"
 - restructure forum tree so that all "category" forums have the same ultimate
   parent instead of NULL. This ultimate parent would not be displayed?
