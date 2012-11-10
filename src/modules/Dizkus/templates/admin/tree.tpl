{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="options" size="small"}
    <h3>{gt text="Forum tree"}</h3>
</div>

<div id="dizkus_admin">


    <ul class="z-menulinks">
        <li>
            <a href="{modurl modname='Dizkus' type='admin' func='modifycategory'}" title="Create a new category" class="z-iconlink z-icon-es-new">
                {gt text='Create a new category'}
            </a>
        </li>
        <li>
            <a href="{modurl modname='Dizkus' type='admin' func='modifyforum'}" title="Create a new forum" class="z-iconlink z-icon-es-new">
                {gt text='Create a new forum'}
            </a>
        </li>
        <li>
            <a href="{modurl modname='Dizkus' type='admin' func='syncforums'}" title="Recalculate cached post and topics totals" class="z-iconlink z-icon-es-gears">
                {gt text='Recalculate cached post and topics totals'}
            </a>
        </li>
    </ul><br />

    <table class="z-admintable">
        <thead>
            <tr>
                <th width="100%">{gt text="Name"}</th>
                <th>{gt text="Actions"}</th>
            </tr>
        </thead>
        <tbody>
        {include file='admin/subtree.tpl'}
            {*foreach item='item' from=$tree name='fooo'}

                <tr class="{cycle values='z-odd,z-even'}">
                    <td>
                        <a href="{modurl modname='Dizkus' type='user' func='viewforum'}">{$category.name|safetext}</a>
                    </td>
                    <td nowrap>
                        <a href="{modurl modname='Dizkus' type='admin' func='modifycategory' id=$category.id}">
                            {img modname=core set=icons/extrasmall src=xedit.png __alt="Edit"}
                        </a>
                        <a href="{modurl modname='Dizkus' type='user' func='main' viewcat=$category.id}">
                            {img modname=core set=icons/extrasmall src=demo.png __alt="Show"}
                        </a>
                        <a href="{modurl modname='Dizkus' type='admin' func='deletecategory' id=$category.id}">
                            {img modname=core set=icons/extrasmall src=14_layer_deletelayer.png __alt="Delete"}
                        </a>
                        {if !$smarty.foreach.fooo.first}
                            <a href="{modurl modname='Dizkus' type='admin' func='changeCatagoryOrder' id=$category.id action='increase'}">
                                {img modname=core set=icons/extrasmall src=up.png __alt="Up"}
                            </a>
                        {/if}

                        {if !$smarty.foreach.fooo.last}
                            <a href="{modurl modname='Dizkus' type='admin' func='changeCatagoryOrder' id=$category.id action='decrease'}"{if $smarty.foreach.fooo.first} style="margin-left:20px"{/if}>
                                {img modname=core set=icons/extrasmall src=down.png __alt="Down"}
                            </a>
                        {/if}

                    </td>
                </tr>
            {foreachelse}
                <tr class="z-admintableempty">
                    <td colspan="4">
                        {gt text="No categories and forums available"}
                    </td>
                </tr>
            {/foreach*}
        </tbody>

    </table>

</div>

{adminfooter}