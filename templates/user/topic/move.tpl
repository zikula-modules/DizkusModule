{gt text="Move or join topics" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl' parent=$topic}

<h2>{$templatetitle}</h2>

{form cssClass="z-form"}
{formvalidationsummary}

    <fieldset id="dzk_movetopic">
        <legend>{gt text="Move topic to another forum"}</legend>
        <p class="z-informationmsg">{gt text="When you press the 'Move' button at the bottom of this form, the topic you have selected (and all its related posts) will be <strong>moved</strong> to the forum you have selected. Note: You will only be able to move the topic to a forum for which you are moderator. An administrator is allowed to move any topic to any forum."}</p>
        <div class="z-formrow">
            {formlabel for="forum_id" __text="Target forum"}
            {formdropdownlist id="forum_id" items=$forums}
        </div>
        <div class="z-formrow">
            {formlabel for="createshadowtopic" __text="Create shadow topic"}
            {formcheckbox id="createshadowtopic"}
        </div>
        <div class="z-formbuttons z-buttons">
            {formbutton class="z-bt-ok"  commandName="move" __text="Move topics"}
        </div>
    </fieldset>

    <fieldset id="dzk_jointopic">
        <legend>{gt text="Join topic with another topic"}</legend>
        <div class="z-formrow">
            {formlabel for="to_topic_id" __text="ID of target topic"}
            {formintinput id="to_topic_id" size="10" maxLength="20"}
        </div>

        <div class="z-formbuttons z-buttons">
            {formbutton class="z-bt-ok" commandName="join"   __text="Join topics"}
        </div>

    </fieldset>

    <div class="z-buttons">
        {formbutton class="z-bt-cancel" commandName="cancel"   __text="Cancel"}
    </div><br />

{/form}

{include file='user/footer.tpl'}