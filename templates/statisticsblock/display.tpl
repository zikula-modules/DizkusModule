{pageaddvar name="stylesheet" value="modules/Dizkus/style/style.css"}
{readtopforums params=$statparams}
{if $topforumscount > 0}
<br />
<strong>{$topforumscount}&nbsp;{gt text="Most-active forums" domain="module_dizkus"}:</strong>
<br />
<ul class="dzk_forumlist">
    {foreach item='topforum' from=$topforums}
    <li>
        <a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$topforum.forum_id}" title="{$topforum.cat_title} :: {$topforum.name}">{$topforum.name}</a>
        ({$topforum.topicCount}/{$topforum.postCount})
    </li>
    {/foreach}
</ul>
{/if}

{readlastposts params=$statparams}
{if $lastpostcount > 0}
<br />
<strong>{$lastpostcount} {gt text="Recent topics" domain="module_dizkus"}:</strong>
<br />
<ul class="dzk_postlist">
    {foreach item='lastpost' from=$lastposts}
    <li>
        <a href="{$lastpost.last_post_url_anchor}" title="{$lastpost.cat_title} :: {$lastpost.name} :: {$lastpost.title}">{$lastpost.title|truncate:21}</a><br />
        {gt text='%s reply' plural='%s replies' tag1=$lastpost.replyCount count=$lastpost.replyCount domain="module_dizkus"}
        <br />
        {$lastpost.poster_name|profilelinkbyuname}<br />{$lastpost.posted_time}
    </li>
    {/foreach}
</ul>
{/if}

{readtopposters params=$statparams}
{if $toppostercount > 0}
<br />
<strong>{$toppostercount} {gt text="Most-active posters" domain="module_dizkus"}:</strong>
<br />
<ul class="dzk_posterlist">
    {foreach item='topposter' from=$topposters}
    <li>{$topposter.user_name|profilelinkbyuname}<br />({$topposter.postCount} {gt text="Posts" domain="module_dizkus"})</li>
    {/foreach}
</ul>
{/if}

{readstatistics}{* accepts no parameters *}
<br />
<strong>{gt text="Total" domain="module_dizkus"}:</strong>
<br />
<ul class="dzk_statslist">
    <li>{gt text="Forums" domain="module_dizkus"}: {$total_forums}</li>
    <li>{gt text="Topics" domain="module_dizkus"}: {$total_topics}</li>
    <li>{gt text="Posts" domain="module_dizkus"}: {$total_posts}</li>
    <li>{gt text="Last User" domain="module_dizkus"}: {$last_user}</li>
</ul>
