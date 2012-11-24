{if count($forum.moderatorUsers) > 0}
<em>{gt text="Moderated by"}:</em>
{foreach name='moderators' item='mod' key='modid' from=$forum.moderatorUsers}
{$mod.user_id|profilelinkbyuid}{if !$smarty.foreach.moderators.last}, {/if}
{/foreach}
{/if}
{if count($forum.moderatorGroups) > 0},
{foreach name='moderators' item='mod' key='modid' from=$forum.moderatorGroups}
{$mod.group.name} (Group){if !$smarty.foreach.moderators.last}, {/if}
{/foreach}
{/if}