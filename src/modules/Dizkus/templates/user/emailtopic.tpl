{gt text="Send as e-mail" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

{if $error_msg neq ''}
<div class="z-errormsg">{$error_msg}</div>
{/if}

<form id="emailtopic" class="z-form" action="{modurl modname='Dizkus' type='user' func='emailtopic'}" method="post">
    <div>
        <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
        <input type="hidden" name="topic" value="{$topic.topic_id}" />
        <fieldset id="dzk_emailtopic">
            <legend>{$templatetitle}</legend>
            <div class="z-formrow">
                <label for="sendto_email">{gt text="Sent to"}</label>
                <input type="text" id="sendto_email" name="sendto_email" size="50" maxlength="50" value="{$sendto_email}" />
            </div>
            <div class="z-formrow">
                <label for="emailsubject">{gt text="Subject line"}&nbsp;</label>
                <input type="text" id="emailsubject" name="emailsubject" size="50" maxlength="100" value="{$emailsubject}" />
            </div>
            <div class="z-formrow">
                <label for="message">{gt text="Message body"}</label>
                <textarea id="message" name="message" rows="10" cols="80">{$message|safetext}</textarea>
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {button class="dzk_detachable z-bt-small" src="button_ok.png" set="icons/extrasmall" __alt="Submit" __title="Submit" __text="Submit"}
        </div>
    </div>
</form>

{include file='user/footer.tpl'}
