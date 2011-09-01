{pageaddvar name="stylesheet" value="modules/Dizkus/style/style.css"}
{readtopforums}
{* readtopforums maxforums=$maxforums USE THIS WITH MAXFORUMS PARAMETER SET IN BLOCK CONFIGURATION*}
{if $topforumscount > 0}
<br />
<strong>{$topforumscount}&nbsp;{gt text="Most-active forums" domain="module_dizkus"}:</strong>
<br />
<ul class="dzk_forumlist">
    {foreach item=topforum from=$topforums}
    <li>
        <a href="{modurl modname=Dizkus type=user func=viewforum forum=$topforum.forum_id}" title="{$topforum.cat_title} :: {$topforum.forum_name}">{$topforum.forum_name}</a>
        ({$topforum.forum_topics}/{$topforum.forum_posts})
    </li>
    {/foreach}
</ul>
{/if}

{* use show_m2f=true as parameter for readlastposts to show mail2forum postings, they are hidden by default *}
{readlastposts}
{* readlastposts maxposts=$maxposts forum_id=$forum_id  USE THIS WITH MAXPOSTS AND FORUM_ID PARAMETERS SET IN BLOCK CONFIGURATION *}
{if $lastpostcount > 0}
<br />
<strong>{$lastpostcount} {gt text="Recent topics" domain="module_dizkus"}:</strong>
<br />
<ul class="dzk_postlist">
    {foreach item=lastpost from=$lastposts}
    <li>
        <a href="{$lastpost.last_post_url_anchor}" title="{$lastpost.cat_title} :: {$lastpost.forum_name} :: {$lastpost.topic_title}">{$lastpost.topic_title|dzkbbsmile|truncate:21}</a><br />
        {gt text='%s reply' plural='%s replies' tag1=$lastpost.topic_replies count=$lastpost.topic_replies domain="module_dizkus"}
        <br />
        {$lastpost.poster_name|profilelinkbyuname}<br />{$lastpost.posted_unixtime|dateformat:'datetimebrief':'':true}
    </li>
    {/foreach}
</ul>
{/if}

{readtopposters}
{* readtopposters maxposters=$maxposters USE THIS WITH MAXPOSTERS PARAMETER SET IN BLOCK CONFIGURATION *}
{if $toppostercount > 0}
<br />
<strong>{$toppostercount} {gt text="Most-active posters" domain="module_dizkus"}:</strong>
<br />
<ul class="dzk_posterlist">
    {foreach item=topposter from=$topposters}
    <li>{$topposter.user_name|profilelinkbyuname}<br />({$topposter.user_posts} {gt text="Posts" domain="module_dizkus"})</li>
    {/foreach}
</ul>
{/if}

{readstatistics}
<br />
<strong>{gt text="Total" domain="module_dizkus"}:</strong>
<br />
<ul class="dzk_statslist">
    <li>{gt text="Categories" domain="module_dizkus"}: {$total_categories}</li>
    <li>{gt text="Forums" domain="module_dizkus"}: {$total_forums}</li>
    <li>{gt text="Topics" domain="module_dizkus"}: {$total_topics}</li>
    <li>{gt text="Posts" domain="module_dizkus"}: {$total_posts}</li>
</ul>
