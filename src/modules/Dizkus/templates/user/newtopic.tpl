{include file='user/header.tpl'}

<div id="newtopicpreview" style="display: none;">&nbsp;</div>

{if $preview}
<div id="nonajaxnewtopicpreview">
    {include file='user/newtopicpreview.tpl'}
</div>
{/if}


{form cssClass="z-form z-linear"}
{formvalidationsummary}
    <fieldset>

        <div class="z-formrow">
            {formlabel for="subject" text="Subject line"}
            {formtextinput id="subject" size="80" maxLength="100" mandatory=true}
        </div>

        <br />
        {notifydisplayhooks eventname='dizkus.ui_hooks.editor.display_view' id='message'}
        {formtextinput id="message" textMode="multiline" rows="10" cols="60"}
        {if isset($hooks.MediaAttach)}
            {$hooks.MediaAttach}
        {/if}
        {if $modvars.Dizkus.striptags == 'yes'}
            <p>
                {gt text="No HTML tags allowed (except inside [code][/code] tags)"}
            </p>
        {/if}

        <div class="dzk_subcols z-clearfix">
            <div id="newtopicoptions" class="dzk_col_left">
                <ul>
                    <li><strong>{gt text="Options"}</strong></li>
                    {if $coredata.logged_in}
                    <li>
                        {formcheckbox id="attach_signature" checked=1}
                        {formlabel for="attach_signature" __text="Attach my signature"}
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