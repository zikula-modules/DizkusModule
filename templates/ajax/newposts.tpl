{* use show_m2f=true as parameter for readlastposts to show mail2forum postings, there are hidden per default *}
{readlastposts maxposts=$maxposts}

{if $lastpostcount > 0}
<dl>
    <dt><strong>{$lastpostcount}&nbsp;{gt text="Recent postings:"}</strong></dt>
    {foreach item=lastpost from=$lastposts}
    <dd>{$lastpost.posted_unixtime|dateformat:'datetimebrief'} <a href="{$lastpost.last_post_url_anchor}">{$lastpost.topic_title|truncate:42}</a> ({$lastpost.poster_name})</dd>
    {/foreach}
</dl>

{else}
{gt text="No posts"}
{/if}
