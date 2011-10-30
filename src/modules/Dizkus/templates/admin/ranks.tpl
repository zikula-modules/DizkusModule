{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin_ranks.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit user ranks"}</h3>
</div>

<div id="dizkus_admin">

    <p class="z-informationmsg">{gt text="In this page, you can create, delete and edit user ranks for that users acquire automatically after a certain number of posts in the forums. To create a new rank, simply type entries in the fields of the 'Create new user rank' section and click on the 'Create' button. To edit a rank, edit the fields of a rank in the ranks list, and then click on 'Save rank changes'. To remove a rank, put a checkmark in the 'Delete rank' checkbox beside the desired rank, and then click on the 'Save rank changes' button."}</p>

    <form class="z-form" action="{modurl modname='Dizkus' type='admin' func='ranks' ranktype='0'}" method="post">
        <div>
            <input type="hidden" name="ranks[-1][rank_special]" value="0" />
            <fieldset>
                <legend>{gt text="Create new rank"}</legend>
                <div class="z-formrow">
                    <label for="rank_title">{gt text="User rank name"}</label>
                    <input id="rank_title" type="text" name="ranks[-1][rank_title]" value="" maxlength="50" size="20" />
                </div>
                <div class="z-formrow">
                    <label for="rank_min">{gt text="Minimum number of posts"}</label>
                    <input id="rank_min" type="text" name="ranks[-1][rank_min]" value="" maxlength="5" size="4" />
                </div>
                <div class="z-formrow">
                    <label for="rank_max">{gt text="Maximum number of posts"}</label>
                    <input id="rank_max" type="text" name="ranks[-1][rank_max]" value="" maxlength="5" size="4" />
                </div>
                <div class="z-formrow">
                    <label for="newrank_image">{gt text="Internal Dizkus image"}</label>
                    <div>
                        <select name="ranks[-1][rank_image]" id="newrank_image" onchange="Zikula.Dizkus.ShowNewRankImage('{$coredata.Dizkus.url_ranks_images}')">
                            {foreach name='availableranks' item='rankimage' from=$rankimages}
                            <option value="{$rankimage}" {if $smarty.foreach.availableranks.first}selected="selected"{capture assign=selectedimage"}{$rankimage}{/capture}{/if}>{$rankimage}</option>
                            {/foreach}
                        </select>
                        <img id="newimage" src="{$coredata.Dizkus.url_ranks_images}/{$selectedimage}" alt="rankimage" />
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="rank_desc">{gt text="Description"}</label>
                    <input id="rank_desc" type="text" name="ranks[-1][rank_desc]" value="" maxlength="255" size="60" />
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

    <form class="z-form" action="{modurl modname='Dizkus' type='admin' func='ranks' ranktype='0'}" method="post">
        <table class="z-admintable">
            <thead>
                <tr>
                    <th>{gt text="User rank"}</th>
                    <th>{gt text="Minimum number of posts"}</th>
                    <th>{gt text="Maximum number of posts"}</th>
                    <th>{gt text="Image"}</th>
                    <th>{gt text="Description"}</th>
                    <th>{gt text="Delete rank"}</th>
                </tr>
            </thead>
            <tbody>
                {foreach key='num' item='rank' from=$ranks}
                <tr class="{cycle values=z-odd,z-even}">
                    <td><input type="text" name="ranks[{$rank.rank_id}][rank_title]" value="{$rank.rank_title|safetext}" maxlength="50" size="20" /></td>
                    <td><input type="text" name="ranks[{$rank.rank_id}][rank_min]" value="{$rank.rank_min}" maxlength="5" size="4" /></td>
                    <td><input type="text" name="ranks[{$rank.rank_id}][rank_max]" value="{$rank.rank_max}" maxlength="5" size="4" /></td>
                    <td>
                        <select name="ranks[{$rank.rank_id}][rank_image]" id="rank_image{$num}" onchange="Zikula.Dizkus.ShowRankImage({$num}, '{$coredata.Dizkus.url_ranks_images}')">
                            {foreach item=rankimage from=$rankimages}
                            <option value="{$rankimage}" {if $rankimage eq $rank.rank_image}selected="selected"{/if}>{$rankimage}</option>
                            {/foreach}
                        </select>
                        <img id="image{$num}" src="{$coredata.Dizkus.url_ranks_images}/{$rank.rank_image}" alt="rankimage" />
                    </td>
                    <td><input type="text" name="ranks[{$rank.rank_id}][rank_desc]" value="{$rank.rank_desc}" maxlength="255" size="40" /></td>
                    <td><input type="checkbox" value="1" name="ranks[{$rank.rank_id}][rank_delete]" /></td>
                </tr>
                {foreachelse}
                <tr class="z-admintableempty"><td colspan="6">{gt text='No items found.'}</td></tr>
                {/foreach}
            </tbody>
        </table>
        <div class="z-formbuttons z-buttons">
            {button src="button_ok.png" set="icons/extrasmall" __alt="Submit" __title="Submit" __text="Submit"}
        </div>
    </form>

</div>

{adminfooter}