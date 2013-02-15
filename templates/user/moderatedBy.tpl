{if count($forum.moderatorUsers) > 0}
<em>{gt text="Moderated by"}:</em>
{foreach name='moderators' item='mod' key='modid' from=$forum.moderatorUsers}
{$mod.forumUser.user_id|profilelinkbyuid}{* demo code *}-{$mod.forumUser.user.uname}{* /demo code *}{if !$smarty.foreach.moderators.last}, {/if}
{/foreach}
{/if}
{if count($forum.moderatorGroups) > 0},
{foreach name='moderators' item='mod' key='modid' from=$forum.moderatorGroups}
{$mod.group.name} ({gt text='Group'}){if !$smarty.foreach.moderators.last}, {/if}
{/foreach}
{/if}