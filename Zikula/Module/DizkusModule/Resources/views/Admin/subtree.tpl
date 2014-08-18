{foreach from=$tree item="item" name='foo'}
    {if $item.lvl > 0}
        {assign var='lvl' value=$item.lvl*4-4}
        <tr>
            <td>{'&nbsp;'|str_repeat:$lvl}{$item.name}</td>
            <td nowrap="nowrap">
                <a class="tooltips" href="{route name='zikuladizkusmodule_admin_modifyforum' id=$item.forum_id}" title="{gt text="edit forum"} '{$item.name}'"><i class='fa fa-pencil fa-150x'></i></a>
                <a class="tooltips" href="{route name='zikuladizkusmodule_user_viewforum' forum=$item.forum_id}" title="{gt text="view forum"} '{$item.name}'"><i class='fa fa-eye fa-blue fa-150x'></i></a>
                <a class="tooltips" href="{route name='zikuladizkusmodule_admin_deleteforum' id=$item.forum_id}" title="{gt text="delete forum"} '{$item.name}'"><i class='fa fa-trash-o fa-red fa-150x'></i></a>
                {if !$smarty.foreach.foo.first}
                    <a class="tooltips" href="{route name='zikuladizkusmodule_admin_changeforumorder' forum=$item.forum_id action='moveUp'}" title='{gt text="move up"}'>
                        <i class='fa fa-arrow-up fa fa-150x'></i>
                    </a>
                {/if}
                {if !$smarty.foreach.foo.last}
                    <a class="tooltips" href="{route name='zikuladizkusmodule_admin_changeforumorder' forum=$item.forum_id action='moveDown'}"  title='{gt text="move down"}'
                    {if $smarty.foreach.foo.first}style="margin-left:20px{/if}">
                        <i class='fa fa-arrow-down fa fa-150x'></i>
                </a>
            {/if}
        </td>
    </tr>
{/if}
{if count($item.__children) > 0}
    {include file='Admin/subtree.tpl' tree=$item.__children}
{/if}
{/foreach}