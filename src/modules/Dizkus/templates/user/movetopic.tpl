{gt text="Move or join topics" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>

<form class="z-form" action="{modurl modname='Dizkus' type='user' func='topicadmin'}" method="post">
    <div>
        <input type="hidden" name="mode" value="{$mode}" />
        <input type="hidden" name="topic" value="{$topic_id}" />
        <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
        <fieldset id="dzk_movetopic">
            <legend>{gt text="Move topic to another forum"}</legend>
            <p class="z-informationmsg">{gt text="When you press the 'Move' button at the bottom of this form, the topic you have selected (and all its related posts) will be <strong>moved</strong> to the forum you have selected. Note: You will only be able to move the topic to a forum for which you are moderator. An administrator is allowed to move any topic to any forum."}</p>
            <div class="z-formrow">
                <label>{gt text="Target forum"}</label>
                <select name="forum">
                    {foreach item='forum' from=$forums key='forum_id'}
                    <option value="{$forum_id}">{$forum|safetext}</option>
                    {/foreach}
                </select>
            </div>
            <div class="z-formrow">
                <label for="createshadowtopic">{gt text="Create shadow topic"}</label>
                <input type="checkbox" id="createshadowtopic" name="createshadowtopic" value="1" />
            </div>
            <div class="z-formbuttons z-buttons">
                {button src="button_ok.png" set="icons/extrasmall" __alt="Move topic" __title="Move topic" __text="Move topic"}
            </div>
        </fieldset>
    </div>
</form>

<form class="z-form" action="{modurl modname=Dizkus type=user func=topicadmin}" method="post">
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