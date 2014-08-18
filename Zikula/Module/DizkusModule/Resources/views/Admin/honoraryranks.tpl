{pageaddvar name="javascript" value="jquery"}
{pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.Admin.Ranks.js'}
{adminheader}
<h3>
    <span class="fa fa-trophy"></span>&nbsp;{gt text="Edit honorary ranks"}
</h3>
<div id="dizkus_admin">

    <p class="alert alert-info">{gt text="In this page, you can create, delete and edit special honorary ranks awarded to particular users (site staff or project team members, for example). To create a new rank, simply type entries in the fields of the 'Create new rank' section and click on the 'Create honorary rank' button. To edit a rank, edit the fields of a rank in the ranks list, and then click on 'Save rank changes'. To remove a rank, put a checkmark in the 'Delete rank' checkbox beside the desired rank, and then click on the 'Save rank changes' button."}</p>
    <form class="form-horizontal" action="{route name='zikuladizkusmodule_admin_ranks' ranktype='1'}" method="post">
        <div>
            <input type="hidden" name="ranks[-1][type]" value="1" />
            <input type="hidden" id="rankImagesPath" value="{$modvars.ZikulaDizkusModule.url_ranks_images}" />
            <fieldset>
                <legend>{gt text="Create new rank"}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="title">{gt text="Honorary rank name"}</label>
                    <div class="col-lg-9">
                        <input id="title" class='form-control' type="text" name="ranks[-1][title]" value="" maxlength="50" size="20" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="newrank_image">{gt text="Internal Dizkus image"}</label>
                    <div class="col-lg-9">
                        <div class='col-lg-3'>
                            <select name="ranks[-1][image]" id="newrank_image" class='form-control input-sm'>
                            {foreach name='availableranks' item='rankimage' from=$rankimages}
                                <option value="{$rankimage}" {if $smarty.foreach.availableranks.first}selected="selected"{capture assign='selectedimage'}{$rankimage}{/capture}{/if}>{$rankimage}</option>
                            {/foreach}
                            </select>
                        </div>
                        <img id="newimage" src="{$modvars.ZikulaDizkusModule.url_ranks_images}/{$selectedimage}" alt="rankimage" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="description">{gt text="Description"}</label>
                    <div class="col-lg-9">
                        <input id="description" class='form-control' type="text" name="ranks[-1][description]" value="" maxlength="255" size="60" />
                    </div>
                </div>
                <div class="col-lg-offset-3 col-lg-9">
                    {button class="btn btn-success" __alt="Create" __title="Create" __text="Create"}
                </div>
            </fieldset>
        </div>
    </form>

    <form action="{route name='zikuladizkusmodule_admin_ranks' ranktype='1'}" method="post">
        <table class="table table-striped table-bordered">
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
                            <input type="text" class='form-control' name="ranks[{$rank.rank_id}][title]" value="{$rank.title|safetext}" maxlength="50" size="20" />
                        </td>
                        <td>
                            <div class='col-lg-7'>
                                <select name="ranks[{$rank.rank_id}][image]" id="rank_image{$num}" data-id='{$num}' class="rankimageselect form-control input-sm">
                                {foreach item='rankimage' from=$rankimages}
                                    <option value="{$rankimage}" {if $rankimage eq $rank.image}selected="selected"{/if}>{$rankimage}</option>
                                {/foreach}
                                </select>
                            </div>
                            <img id="image{$num}" src="{$modvars.ZikulaDizkusModule.url_ranks_images}/{$rank.image}" alt="rankimage" />
                        </td>
                        <td><input type="text" class='form-control' name="ranks[{$rank.rank_id}][description]" value="{$rank.description}" maxlength="255" size="40" /></td>
                        <td>
                            <input type="checkbox" value="1" name="ranks[{$rank.rank_id}][rank_delete]" />
                        </td>
                    </tr>
                {foreachelse}
                    <tr class="danger"><td colspan="4">{gt text='No items found.'}</td></tr>
                    {/foreach}
            </tbody>
        </table>
        <div class="col-lg-offset-3 col-lg-9">
            {button class='btn btn-success' __alt="Submit" __title="Submit" __text="Submit"}
        </div>
    </form>

</div>
{adminfooter}