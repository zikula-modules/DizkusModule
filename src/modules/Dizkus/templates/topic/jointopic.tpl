{gt text="Join topic" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

{form cssClass="z-form z-linear"}
{formvalidationsummary}

        <fieldset id="dzk_movetopic">
            <legend>{gt text="Join topic with another topic"}</legend>
             <p class="z-informationmsg">{gt text="Target topic Id must be different than"} {$from_topic_id}</p>
            <div class="z-formrow">
            	{formlabel for="subject" text="ID of target topic" mandatory=true}
            	{formtextinput id="to_topic_id" size="10" maxLength="200"}
            </div>                                           
        <div class="z-formbuttons z-buttons">
            {formbutton class="z-bt-ok"      commandName="jointopic"   __text="Join"}
            {formbutton class="z-bt-cancel"      commandName="cancel"   __text="Back"}
        </div><br />                           
   
    </fieldset>
{/form}

{include file='user/footer.tpl'}
