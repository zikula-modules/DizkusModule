{gt text="Move topic" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>

<form class="z-form" action="{modurl modname='Dizkus' type='topic' func='movetopic'}" method="get">
    <div>
    	<input type="hidden" name="module" value="Dizkus" />
        <input type="hidden" name="type" value="topic" />
        <input type="hidden" name="func" value="movetopic" />
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
                {button src="button_ok.png" set="icons/extrasmall" __alt="Move topic" __title="Move topic" value='true' __text="Move topic"}
            </div>
        </fieldset>
    </div>
</form>



{include file='user/footer.tpl'}