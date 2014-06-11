{readlastposts params=$params}

{if $lastpostcount > 0}
    <ul>
        <li><strong>{$lastpostcount}&nbsp;{gt text="Recent postings:"}</strong></li>
        {foreach item='lastpost' from=$lastposts}
            <li>{$lastpost.posted_time} <a href="{$lastpost.last_post_url_anchor}">{$lastpost.title|truncate:42}</a> ({$lastpost.poster_name})</li>
        {/foreach}
    </ul>

{else}
    {gt text="No posts"}
{/if}
