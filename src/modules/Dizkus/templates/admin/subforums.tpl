{gt text="Sub forums" assign=templatetitle}
{include file='admin/header.tpl'}

<a href="{modurl modname='Dizkus' type='admin' func='modifysubforum'}">{gt text='Add subforum'}</a>

<table class="z-admintable">
    <thead>
        <tr>
            <th>{gt text="Name"}</th>
            <th>{gt text="Description"}</th>
            <th>{gt text="Main forum"}</th>
            <th>{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach item=subforum from=$subforums}
        <tr class="{cycle values=z-odd,z-even}">
            <td>
                <a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$subforum.forum_id}">
                    {$subforum.forum_name}
                </a>
            </td>
            <td>{$subforum.forum_desc}</td>
            <td>
                {getForumName id=$subforum.cat_id}
            </td>
            <td>
                <a href="{modurl modname='Dizkus' type='admin' func='modifysubforum' id=$subforum.forum_id}">
                    {img modname=core set=icons/extrasmall src=xedit.png alt="Edit"}
                </a>
            </td>
        </tr>
        {foreachelse}
        <tr class="z-admintableempty"><td colspan="2">{gt text="No sub forums available!"}</td></tr>
        {/foreach}
    </tbody>
</table>

{include file='admin/footer.tpl'}
