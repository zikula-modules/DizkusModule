{foreach from=$tree item="item"}
    {assign var='lvl' value=$item.lvl*4}
    <tr class="{cycle values="z-odd,z-even"}">
        <td>{'&nbsp;'|str_repeat:$lvl}{$item.forum_name}</td>
        <td>
            {if $item.lvl == 0}
            <a href="{modurl modname='Dizkus' func='modifyCategory' type='admin' id=$item.forum_id}">{img modname=core set=icons/extrasmall src=xedit.png alt="Edit"}</a>
            <a href="{modurl modname='Dizkus' func=modifyCategory type=admin}">{img modname=core set=icons/extrasmall src=demo.png alt="View"}</a>
            {else}
            <a href="{modurl modname='Dizkus' func='modifyForum' type='admin' id=$item.forum_id}">{img modname=core set=icons/extrasmall src=xedit.png alt="Edit"}</a>
            <a href="{modurl modname=Dizkus func=viewCategory type=user}">{img modname=core set=icons/extrasmall src=demo.png alt="View"}</a>
            {/if}
        </td>
    </tr>
    {if count($item.__children) > 0}
    {include file='admin/subtree.tpl' tree=$item.__children}
    {/if}
{/foreach}