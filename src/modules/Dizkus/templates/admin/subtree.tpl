{foreach from=$forums item='forum' name='foo'}
<tr class="{cycle values='z-odd,z-even'}">
    <td>
        <a href="{modurl modname='Dizkus' type='user' func='viewforum'}" style="margin-left:{$margin}px">{$forum.name|safetext}</a>
    </td>
    <td nowrap>
        <a href="{modurl modname='Dizkus' type='admin' func='modifyforum' id=$forum.id}">
            {img modname=core set=icons/extrasmall src=xedit.png __alt="Edit"}
        </a>
        <a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$forum.id}">
            {img modname=core set=icons/extrasmall src=demo.png __alt="Show"}
        </a>


        {if !$smarty.foreach.foo.first}
        <a href="{modurl modname='Dizkus' type='admin' func='changeForumOrder' id=$forum.id action='increase'}">
            {img modname=core set=icons/extrasmall src=up.png __alt="Up"}
        </a>
        {/if}


        {if !$smarty.foreach.foo.last}
        <a href="{modurl modname='Dizkus' type='admin' func='changeForumOrder' id=$forum.id action='decrease'}"
        {if $smarty.foreach.foo.first}style="margin-left:20px{/if}">
            {img modname=core set=icons/extrasmall src=down.png __alt="Down"}
        </a>
        {/if}

    </td>
    {if count($forum.subforums) > 0}
        {assign var='margin2' value=$margin+20}
        {include file="admin/subtree.tpl" forums=$forum.subforums margin=$margin2}
    {/if}
</tr>
{/foreach}