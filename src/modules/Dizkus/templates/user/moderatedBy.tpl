{if $mods|@count > 0}
<ul id="dzk_moderatorlist" class="linklist z-clearfix">
    <li><em>{gt text="Moderated by"}:</em></li>
    {foreach name=moderators item=mod key=modid from=$mods}
    <li>
        {if $modid lt 1000000}{$mod.user_id|profilelinkbyuid}{else}{$mod|safetext}{/if}{if !$smarty.foreach.moderators.last}, {/if}
    </li>
    {/foreach}
</ul>
{/if}