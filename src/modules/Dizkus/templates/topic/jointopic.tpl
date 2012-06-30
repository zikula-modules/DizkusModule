{gt text="Join topics" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>

<form class="z-form" action="{modurl modname=Dizkus type=topic func=jointopic}" method="post">
    <div>
        <input type="hidden" name="mode" value="join" />
        <input type="hidden" name="topic" value="{$topic_id}" />
        <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
        <fieldset id="dzk_jointopic">
            <legend>{gt text="Join topic with another topic"}</legend>
            <div class="z-formrow">
                <label for="to_topic_id">{gt text="ID of target topic"}</label>
                <span>
                    <input type="text" id="to_topic_id" name="to_topic_id" value="" size="10" maxlength="20" />
                </span>
            </div>
            <div class="z-formbuttons z-buttons">
                {button src="button_ok.png" set="icons/extrasmall" __alt="Join topics" __title="Join topics" __text="Join topics"}
            </div>
        </fieldset>
    </div>
</form>

{include file='user/footer.tpl'}