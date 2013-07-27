{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin_ranks.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit honorary ranks"}</h3>
</div>

<div id="dizkus_admin">

    <p class="z-informationmsg">{gt text="In this page, you can create, delete and edit special honorary ranks awarded to particular users (site staff or project team members, for example). To create a new rank, simply type entries in the fields of the 'Create new rank' section and click on the 'Create honorary rank' button. To edit a rank, edit the fields of a rank in the ranks list, and then click on 'Save rank changes'. To remove a rank, put a checkmark in the 'Delete rank' checkbox beside the desired rank, and then click on the 'Save rank changes' button."}</p>
    <form class="z-form" action="{modurl modname='Dizkus' type='admin' func='ranks' ranktype='1'}" method="post">
        <div>
            <input type="hidden" name="ranks[-1][type]" value="1" />
            <fieldset>
                <legend>{gt text="Create new rank"}</legend>
                <div class="z-formrow">
                    <label for="title">{gt text="Honorary rank name"}</label>
                    <input id="title" type="text" name="ranks[-1][title]" value="" maxlength="50" size="20" />
                </div>
                <div class="z-formrow">
                    <label for="newrank_image">{gt text="Internal Dizkus image"}</label>
                    <div>
                        <select name="ranks[-1][image]" id="newrank_image" onchange="Zikula.Dizkus.ShowNewRankImage('{$modvars.Dizkus.url_ranks_images}')">
                            {foreach name='availableranks' item='rankimage' from=$rankimages}
                            <option value="{$rankimage}" {if $smarty.foreach.availableranks.first}selected="selected"{capture assign='selectedimage'}{$rankimage}{/capture}{/if}>{$rankimage}</option>
                            {/foreach}
                        </select>
                        <img id="newimage" src="{$modvars.Dizkus.url_ranks_images}/{$selectedimage}" alt="rankimage" />
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="description">{gt text="Description"}</label>
                    <input id="description" type="text" name="ranks[-1][description]" value="" maxlength="255" size="60" />
                </div>
                <p class="z-formnote z-informationmsg">
                    {gt text="Notice: For the rank image, you can either choose one of the standardly-supplied Dizkus rank images, or you can use an external image of your choice. To use an internal Dizkus image, select the desired image from the 'Internal Dizkus image' dropdown list. If you want to use an external image, enter the path to the image within the file system in the 'External image' box. Alternatively, you can use an image that can be found on the Internet: if your entry in the 'External image' box starts with 'http://' then the graphic will fetched from the link entered."}
                </p>
                <div class="z-formbuttons z-buttons">
                    {button class="z-bt-small" src="edit_add.png" set="icons/extrasmall" __alt="Create" __title="Create" __text="Create"}
                </div>
            </fieldset>
        </div>
    </form>

    <form class="z-form" action="{modurl modname='Dizkus' type='admin' func='ranks' ranktype='1'}" method="post">
        <table class="z-admintable">
            <thead>
                <tr>
                    <th>{gt text="Honorary rank name"}</th>
                    <th>{gt text="Image"}</th>
                    <th>{gt text="Description"}</th>
                    <th>{gt text="Delete rank"}</th>
                </tr>
            </thead>
            <tbody>
                {foreach key='num' item='rank' from=$ranks}
                <tr class="{cycle values='z-odd,z-even'}">
                    <td>
                        <input type="text" name="ranks[{$rank.rank_id}][title]" value="{$rank.title|safetext}" maxlength="50" size="20" />
                    </td>
                    <td>
                        <select name="ranks[{$rank.rank_id}][image]" id="rank_image{$num}" onchange="Zikula.Dizkus.ShowRankImage({$num}, '{$modvars.Dizkus.url_ranks_images}')">
                            {foreach item='rankimage' from=$rankimages}
                            <option value="{$rankimage}" {if $rankimage eq $rank.image}selected="selected"{/if}>{$rankimage}</option>
                            {/foreach}
                        </select><img id="image{$num}" src="{$modvars.Dizkus.url_ranks_images}/{$rank.image}" alt="rankimage" />
                    </td>
                    <td><input type="text" name="ranks[{$rank.rank_id}][description]" value="{$rank.description}" maxlength="255" size="40" /></td>
                    <td>
                        <input type="checkbox" value="1" name="ranks[{$rank.rank_id}][rank_delete]" />
                    </td>
                </tr>
                {foreachelse}
                <tr class="z-admintableempty"><td colspan="4">{gt text='No items found.'}</td></tr>
                {/foreach}
            </tbody>
        </table>
        <div class="z-formbuttons z-buttons">
            {button src="button_ok.png" set="icons/extrasmall" __alt="Submit" __title="Submit" __text="Submit"}
        </div>
    </form>

</div>
{adminfooter}