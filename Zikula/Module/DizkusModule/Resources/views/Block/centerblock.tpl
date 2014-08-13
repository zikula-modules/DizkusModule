{readlastposts params=$params}
{if $lastpostcount > 0}
    <p>
        <strong>{$lastpostcount}&nbsp;{gt text="Recent postings:" domain="module_zikuladizkusmodule"}</strong>
    </p>
    <ul>
    {foreach item='lastpost' from=$lastposts}
        <li><a href="{modurl modname=$module type='user' func='viewforum' forum=$lastpost.forum_id}">{$lastpost.name}</a>
        <a href="{$lastpost.last_post_url_anchor}">{$lastpost.title}</a>
        {$lastpost.posted_time}
        {$lastpost.poster_name|profilelinkbyuname}</li>
    {/foreach}
    </ul>
{else}
    <p class="text-center">{gt text="No posts" domain="module_dizkus"}</p>
{/if}
<p class="text-center">
    <a style="font-size: 0.8em;" href="{modurl modname=$module type='user' func='index'}" title="{gt text="Go to forum" domain="module_zikuladizkusmodule"}">{gt text="Go to forum" domain="module_zikuladizkusmodule"}</a>
</p>