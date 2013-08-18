{if (count($forum.moderatorUsers) > 0) OR (count($forum.moderatorGroups) > 0)}
<div id='dzk_moderatedby'>
<em>{gt text="Moderated by"}:</em>
{/if}
{if count($forum.moderatorUsers) > 0}
<span>
{foreach name='moderators' item='mod' key='modid' from=$forum.moderatorUsers}
{$mod.forumUser.user.uid|profilelinkbyuid}{if !$smarty.foreach.moderators.last}, {/if}
{/foreach}
{if count($forum.moderatorGroups) > 0}, {/if}
</span>
{/if}
{if count($forum.moderatorGroups) > 0}
<span>
{foreach name='modgroups' item='group' key='id' from=$forum.moderatorGroups}
{$group.group.name} ({gt text='Group'}){if !$smarty.foreach.modgroups.last}, {/if}
{/foreach}
</span>
{/if}
{if (count($forum.moderatorUsers) > 0) OR (count($forum.moderatorGroups) > 0)}
</div>
{/if}