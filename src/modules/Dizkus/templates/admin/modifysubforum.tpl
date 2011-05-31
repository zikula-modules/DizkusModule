{gt text="Settings" assign=templatetitle}
{include file='admin/header.tpl'}

{form cssClass="z-form"}
{formvalidationsummary}

<fieldset>
    <legend>{gt text="General settings"}</legend>

    <div class="z-formrow">
        {formlabel for="forum_name" __text='Name'}
        {formtextinput id="forum_name" size="30" maxLength="150"}
    </div>
    <div class="z-formrow">
        {formlabel for="forum_desc" __text="Description"}
        {formtextinput id="forum_desc" textMode="multiline" rows="3" cols="40"}
    </div>
    <div class="z-formrow">
        {formlabel for="cat_id" __text="Main forum"}
        {formdropdownlist id="is_subforum" items=$mainforums}
    </div>
</fieldset>

<div class="z-formbuttons">
    {formbutton id="submit" commandName="submit" __text="Save" class="dzk_img ok"}
    {formbutton id="restore" commandName="cancel" __text="Cancel" class="dzk_img cancel"}
</div>

{/form}


{include file='admin/footer.tpl'}
