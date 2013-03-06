{gt text="Split topic" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
<p class="z-informationmsg">{gt text="Notice: This will split the topic before the selected posting."}</p>
{form cssClass="z-form"}
{formvalidationsummary}
    <div>
        <fieldset id="dzk_splittopic">
            <legend>{gt text="Subject for new topic"}</legend>
            <div class="z-formrow">
                {formlabel for="newsubject" __text="Subject"}
                {formtextinput id="newsubject" size="40" maxLength="100"}
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {formbutton class="z-bt-ok" commandName="split" __text="Split"}
            {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
        </div>
    </div>
{/form}

{include file='user/footer.tpl'}