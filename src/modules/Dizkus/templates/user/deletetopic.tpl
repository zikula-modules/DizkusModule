{gt text="Delete this topic" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
<div class="z-warningmsg">{gt text="Do you really want to permanently delete this topic and all posts under this topic?"}</div>
<form class="z-form" action="{modurl modname=Dizkus type=user func=topicadmin}" method="post">
    <div>
        <input type="hidden" name="mode" value="{$mode}" />
        <input type="hidden" name="topic" value="{$topic_id}" />
        <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
        <fieldset id="dzk_deletetopic">
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="z-formbuttons z-buttons">
                {button src="button_ok.gif" set="icons/extrasmall" type="submit" name="submit" value="delete" __alt="Delete this topic" __title="Delete this topic"}
                <a href="{modurl modname=Dizkus type=user func=view}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'}</a>
            </div>
        </fieldset>
    </div>
</form>
{include file='user/footer.tpl'}
