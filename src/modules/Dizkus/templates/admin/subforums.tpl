{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="options" size="small"}
    <h3>{gt text="Sub forums"}</h3>
</div>

<div id="dizkus_admin">

    <p>
        <a style="margin: 0.5em 0;" class="z-icon-es-new" href="{modurl modname='Dizkus' type='admin' func='modifysubforum'}">{gt text='Add sub forum'}</a>
    </p>

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
                    <a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$subforum.forum_id}">{$subforum.forum_name|safetext}</a>
                </td>
                <td>{$subforum.forum_desc|safetext}</td>
                <td>
                    {getForumName id=$subforum.cat_id}
                </td>
                <td>
                    <a href="{modurl modname='Dizkus' type='admin' func='modifysubforum' id=$subforum.forum_id}">{img modname=core set=icons/extrasmall src=xedit.png alt="Edit"}</a>
                </td>
            </tr>
            {foreachelse}
            <tr class="z-admintableempty"><td colspan="4">{gt text="No sub forums available"}</td></tr>
            {/foreach}
        </tbody>
    </table>

</div>

{adminfooter}