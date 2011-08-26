{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Sub forum"}</h3>
</div>

<div id="dizkus_admin">

    {form cssClass="z-form"}
    {formvalidationsummary}

    <fieldset>
        <legend>{gt text="Sub forum settings"}</legend>

        <div class="z-formrow">
            {formlabel for="forum_name" __text='Name'}
            {formtextinput id="forum_name" size="30" maxLength="150"}
        </div>
        <div class="z-formrow">
            {formlabel for="forum_desc" __text="Description"}
            {formtextinput id="forum_desc" textMode="multiline" rows="3" cols="40"}
        </div>
        <div class="z-formrow">
            {formlabel for="is_subforum" __text="Main forum"}
            {formdropdownlist id="is_subforum" items=$mainforums}
        </div>
    </fieldset>

    <div class="z-formbuttons z-buttons">
        {formbutton id="submit" commandName="submit" __text="Save" class="z-bt-ok"}
        {formbutton id="restore" commandName="cancel" __text="Cancel" class="z-bt-cancel"}
    </div>

    {/form}

</div>

{adminfooter}