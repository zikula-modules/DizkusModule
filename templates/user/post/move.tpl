{gt text="Move post from one topic to another" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
{form cssClass="z-form"}
{formvalidationsummary}
    <div>
        <fieldset>
            <div class="z-formrow">
                {formlabel for="to_topic" text="ID of target topic"}
                {formintinput id="to_topic_id" size="10" maxLength="20" mandatory=true}
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {formbutton class="z-bt-ok"     commandName="move"   __text="Move post"}
            {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
        </div>
    </div>
{/form}

{include file='user/footer.tpl'}