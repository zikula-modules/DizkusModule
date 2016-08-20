{gt text="Manage your signature" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='User/header.tpl'}

<h2>{$templatetitle}</h2>
{modulelinks modname=$module type='prefs'}<br />

{form cssClass='form-horizontal' role="form"}
{formvalidationsummary}
<fieldset>
    <legend>{gt text="Posting display settings"}</legend>
    <div class="form-group">
        {formlabel for="signature" __text="Signature" class="col-lg-3 control-label"}
        <div class="col-lg-9">
            {formtextinput id="signature" textMode="multiLine" rows="5" cols="40" cssClass='form-control'}
        </div>
    </div>
</fieldset>
    <div class="col-lg-offset-3 col-lg-9">
    {formbutton commandName="update" __text="Submit" class="btn btn-success"}
</div>
{/form}

{include file='User/footer.tpl'}
