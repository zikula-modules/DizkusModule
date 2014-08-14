{if count($lastposts) > 0}
    <ul class="fa-ul" style="margin-left:0;padding-left:40px;">
    {foreach item='lastpost' from=$lastposts}
        <li><i class="fa-li fa fa-comment text-muted"></i><a title="{gt text='in'} {$lastpost.name}" class="tooltips" href="{$lastpost.last_post_url_anchor}">{$lastpost.title}</a><br />
        <small>&commat;{$lastpost.posted_time} {gt text='by'} {$lastpost.poster_name|profilelinkbyuname}</small></li>
    {/foreach}
    </ul>
{else}
    <p class="text-center">{gt text="No posts" domain="module_dizkus"}</p>
{/if}
<p class="text-center">
    <a style="font-size: 0.8em;" href="{modurl modname=$module type='user' func='index'}" title="{gt text="Go to forum"}">{gt text="Go to forum"}</a>
</p>