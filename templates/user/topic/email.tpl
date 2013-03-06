{gt text="Send as e-mail" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

{form cssClass="z-form"}
{formvalidationsummary}
    <div>
        <fieldset id="dzk_emailtopic">
            <legend>{$templatetitle}</legend>
            <div class="z-formrow">
                {formlabel for="sendto_email" text="Sent to"}
                {formemailinput id="sendto_email" size="50" maxLength="50" mandatory=true}
            </div>
            <div class="z-formrow">
                {formlabel for="emailsubject" text="Subject line"}
                {formtextinput id="emailsubject" size="50" maxLength="100" mandatory=true}
            </div>
            <div class="z-formrow">
                {formlabel for="message" text="Message body"}
                {formtextinput id="message" textMode="multiline" rows="10" cols="80" mandatory=true}
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            {formbutton class="z-bt-ok"     commandName="send"   __text="Send"}
            {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
        </div>
    </div>
{/form}

{include file='user/footer.tpl'}