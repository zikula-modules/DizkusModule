
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{gt text="Delete category?"}</h3>
</div>

<div id="dizkus_admin">
    {form cssClass="z-form"}
    {formvalidationsummary}

    <div class="z-warningmsg">
        {gt text="Are you sure want to remove the category '%s'." tag1=$cat_title}
    </div>

    {if count($forums) > 0}
    <div class="z-formrow">
        {formlabel for="action" __text='What should happen with the forums of this category:'}
        {formdropdownlist id="action" items=$actions}
    </div>
    {else}
        {formtextinput textMode="hidden" id='action' value=''}
    {/if}

    <div class="z-formbuttons z-buttons">
        {formbutton id="submit" commandName="submit" __text="Yes" class="z-bt-ok"}
        {formbutton id="restore" commandName="cancel" __text="Cancel" class="z-bt-cancel"}
    </div>

    {/form}
</div>

{adminfooter}
