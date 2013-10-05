{gt text="Personal settings" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<div id="dzk_userprefs">

    {modulelinks modname=$module type='prefs'}<br />

    <h2>{gt text="Personal Settings"}</h2>

    {form cssClass="form-horizontal" role="form"}
    {formvalidationsummary}

    <fieldset>
        <div class="form-group">
            {formlabel for="postOrder" __text="Post order" class="col-lg-3 control-label"}
            <div class="col-lg-9">
            {formdropdownlist id="postOrder" items=$orders cssClass='form-control'}
            </div>
        </div>
        {if $modvars.ZikulaDizkusModule.favorites_enabled eq 'yes'}
            <div class="form-group">
                {formlabel for="displayOnlyFavorites" __text="Display only favorite forums" class="col-lg-3 control-label"}
                <div class="col-lg-9">
                {formcheckbox id="displayOnlyFavorites"}
                </div>
            </div>
        {/if}
        <div class="form-group">
            {formlabel for="autosubscribe" __text="Autosubscribe to new topics" class="col-lg-3 control-label"}
            <div class="col-lg-9">
            {formcheckbox id="autosubscribe"}
            </div>
        </div>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {formbutton commandName="save" __text="Save" class="btn btn-success"}
                {formbutton commandName="cancel" __text="Cancel" class="btn btn-danger"}
            </div>
        </div>
    </fieldset>
    {/form}
</div>