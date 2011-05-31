{pageaddvar name="stylesheet" value="modules/Dizkus/style/style.css"}

{readlastposts forum_id=$forum_id maxposts=$maxposts}
{if $lastpostcount > 0}
<table class="z-datatable">
    <tr>
        <td colspan="4">
            <strong>{$lastpostcount}&nbsp;{gt text="Recent postings:" domain="module_dizkus"}</strong>
        </td>
    </tr>
    {foreach item=lastpost from=$lastposts}
    <tr class="{cycle values='z-odd,z-even'}">
        <td><a href="{modurl modname=Dizkus type=user func=viewforum forum=$lastpost.forum_id}">{$lastpost.forum_name}</a></td>
        <td><a href="{$lastpost.last_post_url_anchor}">{$lastpost.topic_title}</a></td>
        <td>{$lastpost.posted_unixtime|dateformat:'datetimebrief':'':true}</td>
        <td>{$lastpost.poster_name|profilelinkbyuname}</td>
    </tr>
    {/foreach}
</table>
{/if}
