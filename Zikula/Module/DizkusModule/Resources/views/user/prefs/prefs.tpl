{gt text="Personal settings" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<div id="dzk_userprefs">

    <h2>{gt text="Personal Settings"}</h2>

    {modulelinks modname=$module type='prefs'}<br />

    {form cssClass="z-form"}
    {formvalidationsummary}

    <fieldset>
        <div class="z-formrow">
            {formlabel for="postOrder" __text="Post order"}
            {formdropdownlist id="postOrder" items=$orders}
        </div>
        {if $modvars.ZikulaDizkusModule.favorites_enabled eq 'yes'}
            <div class="z-formrow">
                {formlabel for="displayOnlyFavorites" __text="Display only favorite forums"}
                {formcheckbox id="displayOnlyFavorites"}
            </div>
        {/if}
        <div class="z-formrow">
            {formlabel for="autosubscribe" __text="Autosubscribe to new topics"}
            {formcheckbox id="autosubscribe"}
        </div>

        <div class="z-formbuttons z-buttons">
            {formbutton commandName="save" __text="Save" class="z-bt-ok"}
            {formbutton commandName="cancel" __text="Cancel" class="z-bt-cancel"}
        </div>
    </fieldset>
    {/form}
</div>