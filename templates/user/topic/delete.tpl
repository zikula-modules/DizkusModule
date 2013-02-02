{gt text="Delete this topic" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl' parent=$topic_id}

<h2>{$templatetitle}</h2>
<div class="z-warningmsg">
    {gt text="Do you really want to permanently delete this topic and all posts under this topic?"}
</div>

{form cssClass="z-form"}
{formvalidationsummary}
    <div>
        <fieldset id="dzk_deletetopic">
            <legend>{gt text="Confirmation prompt"}</legend>
            <h3>{gt text='Delete Topic'}: {$topic_title}</h3>
            <div class="z-formrow">
                {formlabel for="sendReason" __text='Send a reason to the poster'}
                {formcheckbox id="sendReason" value=false onclick="Zikula.checkboxswitchdisplaystate(this, 'diskus_reason_container', true);"}
            </div>
            
             <div class="z-formrow" id="diskus_reason_container" style="display:none">
                {formlabel for="reason" text='Write a reason'}
                {gt text='Your post "%s" was deleted, because ' tag1=$topic_title assign='reason'}
                {formtextinput id="reason" textMode="multiline" rows="3" cols="40"}
            </div>
            
            <div class="z-formbuttons z-buttons">
                {formbutton class="z-bt-ok"     commandName="save"   __text="Yes"}
                {formbutton class="z-bt-cancel" commandName="cancel" __text="No"}
            </div>
        </fieldset>
    </div>      
{/form}

{include file='user/footer.tpl'}

