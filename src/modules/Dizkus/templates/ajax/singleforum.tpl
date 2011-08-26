<li id="forum_{$forum.forum_id}" class="forumline existing">
    <div class="dzk_handle z-clearfix">
        <div id="forumtitle_{$forum.forum_id}" style="float: left; width: 60%;">
            {if $newforum <> true}
            <a href="{modurl modname=Dizkus type=user func=viewforum forum=$forum.forum_id}" title="{$forum.forum_name|safetext}">{$forum.forum_name|safetext}</a> (ID:{$forum.forum_id})
            {else}
            {$forum.forum_name} ({gt text="new forum"})
            {/if}
        </div>
        <div class="z-buttons" style="float: right; width: 30%; text-align: right; padding-right: 1em;">
            <button id="editforum_{$forum.forum_id}" class="z-bt-small" title="{gt text="Load forum data"}">{img modname='Dizkus' src="icon_show.gif" __alt="Load forum data" }</button>
            <button id="hideforum_{$forum.forum_id}" class="z-bt-small" style="display: none;" title="{gt text="Hide forum"}">{img modname='Dizkus' src="icon_hide.gif"}</button>
            <button id="showforum_{$forum.forum_id}" class="z-bt-small" style="display: none;" title="{gt text="Show forum"}">{img modname='Dizkus' src="icon_show.gif"}</button>
            {if $newforum eq true}
            <button id="canceladdforum_{$forum.forum_id}" class="z-bt-small" title="{gt text="Cancel"}">{img modname='Dizkus' src="icon_canceladdforum.gif" __alt="Cancel" }</button>
            {/if}
            <img id="progressforumimage_{$forum.forum_id}" style="visibility: hidden;" src="images/ajax/indicator.white.gif" width="16" height="16" alt="{gt text="Working. Please wait..."}" />
        </div>
    </div>
    <div id="editforumcontent_{$forum.forum_id}" style="{if $newforum <> true}display: none; {/if}margin: 0 1em;">
        {if $newforum eq true}
        {include file='ajax/editforum.tpl'}
        {else}
        &nbsp;
        {/if}
    </div>
</li>
