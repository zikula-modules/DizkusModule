{gt text="Send as e-mail" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='User/header.tpl'}

{form role='form' cssClass='form-horizontal'}
{formvalidationsummary}
<div>
    <fieldset id="dzk_emailtopic">
        <legend>{$templatetitle}</legend>
        <div class="form-group">
            {formlabel for="sendto_email" text="Send to" class="col-lg-3 control-label"}
            <div class="col-lg-9">
                {formemailinput id="sendto_email" size="50" maxLength="50" mandatory=true cssClass="form-control" placeholder="Enter email"}
            </div>
        </div>
        <div class="form-group">
            {formlabel for="emailsubject" text="Subject line" class="col-lg-3 control-label"}
            <div class="col-lg-9">
                {formtextinput id="emailsubject" size="50" maxLength="100" mandatory=true cssClass="form-control"}
            </div>
        </div>
        <div class="form-group">
            {formlabel for="message" text="Message body" class="col-lg-3 control-label"}
            <div class="col-lg-9">
                {formtextinput id="message" textMode="multiline" rows="10" cols="80" mandatory=true cssClass="form-control"}
            </div>
        </div>
    </fieldset>
    <div class="col-lg-offset-3 col-lg-9">
        {formbutton class="btn btn-success" commandName="send"   __text="Send"}
        {formbutton class="btn btn-danger" commandName="cancel" __text="Cancel"}
    </div>
</div>
{/form}

{include file='User/footer.tpl'}