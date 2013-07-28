{include file='user/header.tpl' __templatetitle='New topic'}

<div id="newtopicpreview" style="display: none;">&nbsp;</div>

{if $preview}
<div id="nonajaxnewtopicpreview">
    {*include file='user/topic/newpreview.tpl'*}
    {include file='user/post/single.tpl'}
</div>
{/if}


{form cssClass="z-form z-linear"}
{formvalidationsummary}
    <fieldset>

        <div class="z-formrow">
            {formlabel for="title" __text="Subject line"}
            {formtextinput id="title" size="80" maxLength="100" mandatory=true}
        </div>

        <br />
        {formlabel for="message" __text="Message" class='z-hide'}
        {formtextinput id="message" textMode="multiline" rows="10" cols="60" maxLength="65527" mandatory=true}
        {if $modvars.Dizkus.striptags == 'yes'}
            <p>
                {gt text="No HTML tags allowed (except inside [code][/code] tags)"}
            </p>
        {/if}
        {notifydisplayhooks eventname='dizkus.ui_hooks.post.ui_edit' id=null}
        {notifydisplayhooks eventname='dizkus.ui_hooks.topic.ui_edit' id=null}

        <div class="dzk_subcols z-clearfix">
            <div id="newtopicoptions" class="dzk_col_left">
                <ul>
                    <li><strong>{gt text="Options"}</strong></li>
                    {if $coredata.logged_in}
                    <li>
                        {formcheckbox id="attachSignature" checked=1}
                        {formlabel for="attachSignature" __text="Attach my signature"}
                    </li>
                    <li>
                        {formcheckbox id="subscribe_topic" checked=1}
                        {formlabel for="subscribe_topic" __text="Notify me when a reply is posted"}
                    </li>
                    {/if}
                </ul>
            </div>
        </div><br />


        <div class="z-formbuttons z-buttons">
            {formbutton class="z-bt-ok"      commandName="save"   __text="Submit"}
            {formbutton class="z-bt-preview" commandName="preview" __text="Preview"}
            {formbutton class="z-bt-cancel"  commandName="cancel" __text="Cancel"}
        </div><br />

    </fieldset>
{/form}

<div id="newtopicconfirmation" style="display: none;">&nbsp;</div>

{include file='user/footer.tpl'}