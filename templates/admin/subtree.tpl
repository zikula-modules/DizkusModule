{foreach from=$tree item="item" name='foo'}
    {assign var='lvl' value=$item.lvl*4}
    <tr class="{cycle values="z-odd,z-even"}">
        <td>{'&nbsp;'|str_repeat:$lvl}{$item.forum_name}</td>
        <td nowrap="nowrap">
            {if $item.lvl == 0}
            <a href="{modurl modname='Dizkus' type='admin' func='modifyCategory' id=$item.forum_id}">{img modname='core' set='icons/extrasmall' src='xedit.png' alt="Edit"}</a>
            <a href="{modurl modname='Dizkus' type='user' func='main' viewcat=$item.forum_id}">{img modname='core' set='icons/extrasmall' src='demo.png' alt="View"}</a>
            {else}
            <a href="{modurl modname='Dizkus' type='admin' func='modifyForum' id=$item.forum_id}">{img modname='core' set='icons/extrasmall' src='xedit.png' alt="Edit"}</a>
            <a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$item.forum_id}">{img modname='core' set='icons/extrasmall' src='demo.png' alt="View"}</a>
            {/if}
            {if !$smarty.foreach.foo.first}
                <a href="{modurl modname='Dizkus' type='admin' func='changeForumOrder' forum=$item.forum_id action='moveUp'}">
                    {img modname='core' set='icons/extrasmall' src='up.png' __alt="Up"}
                </a>
            {/if}
            {if !$smarty.foreach.foo.last}
                <a href="{modurl modname='Dizkus' type='admin' func='changeForumOrder' forum=$item.forum_id action='moveDown'}"
                   {if $smarty.foreach.foo.first}style="margin-left:20px{/if}">
                    {img modname='core' set='icons/extrasmall' src='down.png' __alt="Down"}
                </a>
            {/if}
        </td>
    </tr>
    {if count($item.__children) > 0}
    {include file='admin/subtree.tpl' tree=$item.__children}
    {/if}
{/foreach}