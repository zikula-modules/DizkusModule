{gt text="Delete this topic" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
<div class="z-warningmsg">
    {gt text="Do you really want to permanently delete this topic and all posts under this topic?"}
</div>

{form cssClass="z-form"}
{formvalidationsummary}
    <div>
        <fieldset id="dzk_deletetopic">
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="z-formbuttons z-buttons">
                {formbutton class="z-bt-ok"     commandName="save"   __text="Yes"}
                {formbutton class="z-bt-cancel" commandName="cancel" __text="No"}
            </div>
        </fieldset>
    </div>      
{/form}

{include file='user/footer.tpl'}

