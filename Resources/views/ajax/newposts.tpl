{readlastposts params=$params}

{if $lastpostcount > 0}
    <dl>
        <dt><strong>{$lastpostcount}&nbsp;{gt text="Recent postings:"}</strong></dt>
        {foreach item='lastpost' from=$lastposts}
            <dd>{$lastpost.posted_time} <a href="{$lastpost.last_post_url_anchor}">{$lastpost.title|truncate:42}</a> ({$lastpost.poster_name})</dd>
        {/foreach}
    </dl>

{else}
    {gt text="No posts"}
{/if}
