{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{$templatetitle}</h3>
</div>

<div id="dizkus_admin">

    {form cssClass="z-form"}
    {formvalidationsummary}

    <fieldset>

        <div class="z-formrow">
            {formlabel for="name" __text='Title'}
            {formtextinput id="name" size="30" maxLength="100" mandatory=true}
        </div>
    </fieldset>

    {notifydisplayhooks eventname='dizkus.ui_hooks.forum.ui_edit' id=$forum_id}

    <div class="z-formbuttons z-buttons">
        {formbutton id="submit" commandName="submit" __text="Save" class="z-bt-ok"}
        {formbutton id="restore" commandName="cancel" __text="Cancel" class="z-bt-cancel"}
    </div>

    {/form}

</div>

{adminfooter}