{foreach from=$tree item="item" name='foo'}
    {if $item.lvl > 0}
        {assign var='lvl' value=$item.lvl*4-4}
        <tr>
            <td>{'&nbsp;'|str_repeat:$lvl}{$item.name}</td>
            <td nowrap="nowrap">
                <a class="tooltips" href="{modurl modname=$module type='admin' func='modifyForum' id=$item.forum_id}" title="{gt text="edit forum"} '{$item.name}'"><i class='icon-pencil icon-150x'></i></a>
                <a class="tooltips" href="{modurl modname=$module type='user' func='viewforum' forum=$item.forum_id}" title="{gt text="view forum"} '{$item.name}'"><i class='icon-eye-open icon-blue icon-150x'></i></a>
                <a class="tooltips" href="{modurl modname=$module type='admin' func='deleteforum' id=$item.forum_id}" title="{gt text="delete forum"} '{$item.name}'"><i class='icon-trash icon-red icon-150x'></i></a>
                {if !$smarty.foreach.foo.first}
                    <a class="tooltips" href="{modurl modname=$module type='admin' func='changeForumOrder' forum=$item.forum_id action='moveUp'}" title='{gt text="move up"}'>
                        <i class='icon-arrow-up icon-150x'></i>
                    </a>
                {/if}
                {if !$smarty.foreach.foo.last}
                    <a class="tooltips" href="{modurl modname=$module type='admin' func='changeForumOrder' forum=$item.forum_id action='moveDown'}"  title='{gt text="move down"}'
                    {if $smarty.foreach.foo.first}style="margin-left:20px{/if}">
                        <i class='icon-arrow-down icon-150x'></i>
                </a>
            {/if}
        </td>
    </tr>
{/if}
{if count($item.__children) > 0}
    {include file='admin/subtree.tpl' tree=$item.__children}
{/if}
{/foreach}