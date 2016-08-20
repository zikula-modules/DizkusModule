{pageaddvar name="javascript" value="jquery"}
{pageaddvar name='javascript' value='@ZikulaDizkusModule/Resources/public/js/Zikula.Dizkus.Admin.DeleteForum.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Delete forum"}</h3>
</div>

<div id="dizkus_admin">

    {form cssClass="z-form"}
    {formvalidationsummary}


    <div class="z-warningmsg">
        {gt text="Are you sure want to remove the forum '%s'." tag1=$name}
    </div>

    <div class="z-formrow">
        {formlabel for="action" __text='Move or remove subforums and topics'}
        {formdropdownlist id="action" items=$actions}
    </div>

    <div id="destinationSelector" class="z-formrow" style="display: none;">
        {formlabel for="destination" __text='destination:'}
        {formdropdownlist id="destination" items=$destinations}
    </div>

    {notifydisplayhooks eventname='dizkus.ui_hooks.forum.ui_delete' id=$forum_id}

    <div class="z-formbuttons z-buttons">
        {formbutton id="submit" commandName="submit" __text="Yes" class="z-bt-ok"}
        {formbutton id="restore" commandName="cancel" __text="Cancel" class="z-bt-cancel"}
    </div>

    {/form}

</div>

{adminfooter}
