{if $mode eq "lock"}
{gt text="Lock topic" assign="templatetitle"}
{gt text="When you press the 'Lock' button at the bottom of this page, the topic you have selected will be <strong>locked</strong>. You can unlock it at a later time if you so choose." assign="description"}
{gt text="Lock topic" assign="buttontitle"}
{else}
{gt text="Unlock topic" assign="templatetitle"}
{gt text="When you press the 'Unlock' button at the bottom of this page, the topic you have selected will be <strong>unlocked</strong>. You can lock it again at a later time if you so choose." assign="description"}
{gt text="Unlock topic" assign="buttontitle"}
{/if}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
<p class="z-informationmsg">{$description}</p>
<form class="z-form" action="{modurl modname=Dizkus type=user func=topicadmin}" method="post">
    <div>
        <input type="hidden" name="mode" value="{$mode}" />
        <input type="hidden" name="topic" value="{$topic_id}" />
        <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
        <fieldset id="dzk_locktopic">
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="z-formbuttons z-buttons">
                {button src="button_ok.png" set="icons/extrasmall" alt=$buttontitle title=$buttontitle text=$buttontitle}
            </div>
        </fieldset>
    </div>
</form>

{include file='user/footer.tpl'}
