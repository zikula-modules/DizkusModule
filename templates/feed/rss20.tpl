<?xml version="1.0" encoding="{charset}"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>{$sitename|safetext} - {$forum_name|safetext}</title>
        <link>{$forum_link|safetext}</link>
        <description>{$sitename|safetext} - {$forum_name|safetext}</description>
        <language>{$current_language}</language>
        <pubDate>{$current_date}</pubDate>
        <lastBuildDate>{$current_date}</lastBuildDate>
        <docs>http://blogs.law.harvard.edu/tech/rss</docs>
        <generator>Dizkus {$dizkusinfo.version}</generator>
        <webMaster>{$adminmail|safetext} ({$adminmail|safetext})</webMaster>
        {foreach item=post from=$posts}
        <item>
            <title>{$post.topic_title|safetext}</title>
            <link>{$post.last_post_url|safetext}</link>
            <guid>{$post.last_post_url|safetext}</guid>
            <description>{$post.cat_title|safetext} :: {$post.forum_name|safetext}</description>
            <pubDate>{$post.rsstime}</pubDate>
            <category>{$post.cat_title|safetext} :: {$post.forum_name|safetext}</category>
        </item>
        {/foreach}
    </channel>
</rss>
