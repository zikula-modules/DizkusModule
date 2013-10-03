{include file='user/header.tpl'}

<div class="panel panel-default">
    <div class="panel-heading"><h2 class='icon-home'>&nbsp;{gt text="Forums index page"}</h2>
        {if isset($numposts)}
        <div style='position:absolute; top:0; right:0; padding: 1.25em;'>
            <a class='btn btn-default btn-sm tooltips' title="{gt text="RSS Feed"}" href="{modurl modname=$module type='user' func='feed'}"><i class='icon-rss-sign icon-150x icon-orange'></i>
            {gt text="Total posts: %s" tag1=$numposts}</a>
        </div>
        {/if}
    </div>
    {foreach item='parent' from=$forums}
        {include file='user/forum/singleforumtable.tpl'}
    {/foreach}
    {include file='user/forum/panelfooter.tpl'}
</div>

{include file='user/footer.tpl'}