{gt text="Move topic" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

{form cssClass="z-form z-linear"}
{formvalidationsummary}

        <fieldset id="dzk_movetopic">
            <legend>{gt text="Move topic to another forum"}</legend>
            <p class="z-informationmsg">{gt text="When you press the 'Move' button at the bottom of this form, the topic you have selected (and all its related posts) will be <strong>moved</strong> to the forum you have selected. Note: You will only be able to move the topic to a forum for which you are moderator. An administrator is allowed to move any topic to any forum."}</p>
            <div class="z-formrow">
                <label>{gt text="Target forum"}</label>
           {formdropdownlist id="forum" items=$forums size="1" maxLength="255"}
            </div>
            <div class="z-formrow">
            	{formcheckbox id="createshadowtopic" checked=1}
                {formlabel for="createshadowtopic" __text="Create shadow topic"}
            </div>                                           
        <div class="z-formbuttons z-buttons">
            {formbutton class="z-bt-ok"      commandName="movetopic"   __text="Move"}
            {formbutton class="z-bt-cancel"      commandName="cancel"   __text="Back"}
        </div><br />                           
   
    </fieldset>
{/form}

{include file='user/footer.tpl'}