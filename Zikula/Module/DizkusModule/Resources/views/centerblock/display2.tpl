{pageaddvar name="stylesheet" value="modules/Dizkus/style/style.css"}

{readlastposts params=$params}
{if $lastpostcount > 0}
    <table class="z-datatable">
        <tr>
            <td colspan="4">
                <strong>{$lastpostcount}&nbsp;{gt text="Recent postings:" domain="module_dizkus"}</strong>
            </td>
        </tr>
        {foreach item='lastpost' from=$lastposts}
            <tr class="{cycle values='z-odd,z-even'}">
                <td><a href="{modurl modname=$module type='user' func='viewforum' forum=$lastpost.forum_id}">{$lastpost.name}</a></td>
                <td><a href="{$lastpost.last_post_url_anchor}">{$lastpost.title}</a></td>
                <td>{$lastpost.posted_time}</td>
                <td>{$lastpost.poster_name|profilelinkbyuname}</td>
            </tr>
        {/foreach}
    </table>
{/if}
