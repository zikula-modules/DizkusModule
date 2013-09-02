{pageaddvar name="stylesheet" value="modules/Dizkus/style/style.css"}

{readlastposts params=$params}
{if $lastpostcount > 0}
    <p class="dzk_centerblockheader">
        <strong>{$lastpostcount}&nbsp;{gt text="Recent postings:" domain="module_dizkus"}</strong>
    </p>

    <table class="z-datatable">
        <tbody>
            {foreach item='lastpost' from=$lastposts}
                <tr class="{cycle values='z-odd,z-even'}">
                    <td><a href="{modurl modname=$module type='user' func='viewforum' forum=$lastpost.forum_id}">{$lastpost.name}</a></td>
                    <td><a href="{$lastpost.last_post_url_anchor}">{$lastpost.title}</a></td>
                    <td>{$lastpost.posted_time}</td>
                    <td>{$lastpost.poster_name|profilelinkbyuname}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>

{else}
    <p class="z-center">{gt text="No posts" domain="module_dizkus"}</p>
{/if}
<p class="z-center">
    <a style="font-size: 0.8em;" href="{modurl modname=$module type='user' func='index'}" title="{gt text="Go to forum" domain="module_dizkus"}">{gt text="Go to forum" domain="module_dizkus"}</a>
</p>
