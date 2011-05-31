<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN"
 "http://my.netscape.com/publish/formats/rss-0.91.dtd">

<!--
Possible parameters for backforum.php

-> cat_id    read new postings in this category only
-> forum_id  read new postings in this forum only
-> username  read new postings from this user only
             search sequence: cat_id, forum_id, username
-> count     number of postings to read, default 10
-> feed      type of feed to deliver, e.g. atom which needs a template
             atom.tpl to work
             default: rss20

Dizkus permission still apply! The forum access will be tested against
an unregistered user.

IMPORTANT HINT:
===============
Do not configure Renderer to expose template information for debugging purposes if
you want to use templated RSS feeds! This will result in an XML error message:

XML Parsing Error: xml declaration not at start of external entity
Location: http://www.example.com/backforum.php
Line Number 3, Column 1:<?xml version="1.0" encoding="utf-8"?>

If you have to do this kind of debugging, do not forget to switch it off as
soon as possible!

-->

<rss version="0.91">
<channel>
<title>{$sitename|safetext} - {$forum_name|safetext}</title>
<link>{$forum_link|safetext}</link>
<description>{$sitename|safetext} - {$forum_name|safetext}</description>
<webMaster>{$adminmail|safetext}</webMaster>

{foreach item=post from=$posts}
    <item>
    <title>{$post.topic_title|safetext}</title>
    <link>{$post.last_post_url|safetext}</link>
    <description>{$post.cat_title|safetext} :: {$post.forum_name|safetext}</description>
    </item>
{/foreach}

</channel>
</rss>
