{gt text="Manage your signature" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>

{form cssClass="z-form z-linear"}
{formvalidationsummary}
<fieldset>
    <legend>{gt text="Posting display settings"}</legend>
    <div class="z-formrow">
        {formlabel for="signature" __text="Signature"}
        {formtextinput id="signature" textMode="multiLine" rows="5" cols="40"}
    </div>
</fieldset>
<div class="z-formbuttons z-buttons">
    {formbutton commandName="update" __text="Submit" class="z-bt-ok"}
</div>
{/form}

{include file='user/footer.tpl'}
