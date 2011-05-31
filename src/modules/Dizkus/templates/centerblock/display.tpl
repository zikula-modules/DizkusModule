{pageaddvar name="stylesheet" value="modules/Dizkus/style/style.css"}

{* use show_m2f=true as parameter for readlastposts to show mail2forum postings, there are hidden per default *}
{readlastposts maxposts=$maxposts}
{*readlastposts maxposts=$maxposts forum_id=$forum_id USE THIS WITH FORUM_ID PARAMETERS SET IN BLOCK CONFIGURATION*}
{if $lastpostcount > 0}
<p class="dzk_centerblockheader">
    <strong>{$lastpostcount}&nbsp;{gt text="Recent postings:" domain="module_dizkus"}</strong>
</p>

<table class="z-datatable">
    <tbody>
        {foreach item=lastpost from=$lastposts}
        <tr class="{cycle values='z-odd,z-even'}">
            <td><a href="{modurl modname=Dizkus type=user func=viewforum forum=$lastpost.forum_id}">{$lastpost.forum_name}</a></td>
            <td><a href="{$lastpost.last_post_url_anchor}">{$lastpost.topic_title}</a></td>
            <td>{$lastpost.posted_unixtime|dateformat:'datetimebrief':'':true}</td>
            <td>{$lastpost.poster_name|profilelinkbyuname}</td>
        </tr>
        {/foreach}
    </tbody>
</table>

{else}
<p class="z-center">{gt text="No posts" domain="module_dizkus"}</p>
{/if}
<p class="z-center">
    <a style="font-size: 0.8em;" href="{modurl modname=Dizkus func=main}" title="{gt text="Go to forum" domain="module_dizkus"}">{gt text="Go to forum" domain="module_dizkus"}</a>
</p>
