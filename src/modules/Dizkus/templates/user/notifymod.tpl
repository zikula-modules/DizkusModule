{gt text="Notify moderator about this posting" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
<p class="z-informationmsg">
    {gt text="A moderator will be notified about the selected posting.<br />Valid reasons are: Copyright violations, personal insults and so on.<br />The following are not valid reasons for moderation: Typos, difference of opinion on the topic et cetera."}
</p>
{form cssClass="z-form z-linear"}
{formvalidationsummary}
    <div>
        <fieldset id="dzk_notifymoderator">
            <div class="z-formrow">
                {formlabel for="modcomment" __text="Your comment:"}
                {formtextinput id="modcomment" textMode="multiline" rows="6" cols="60"}
            </div>
                
        <div class="z-formbuttons z-buttons">
            {formbutton class="z-bt-ok"     commandName="send"   __text="Send"}
            {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
        </div>
        </fieldset>
    </div>
{/form}

{include file='user/footer.tpl'}
