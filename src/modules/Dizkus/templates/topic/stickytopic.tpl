{if $mode eq "sticky"}
{gt text="Give this topic 'sticky' status" assign="templatetitle"}
{gt text="When you click the 'Sticky' button at the bottom of this page, the topic you have selected will be assigned <strong>'sticky'</strong> status. You can change its status again at a later time if you so choose." assign="description"}
{gt text="Give this topic 'sticky' status" assign="buttontitle"}
{else}
{gt text="Remove 'sticky' status" assign="templatetitle"}
{gt text="When you click the 'Unsticky' button at the bottom of this page, the topic you have selected will be set to <strong>'unsticky'</strong> status. You can change its status again at a later time if you so choose." assign="description"}
{gt text="Remove 'sticky' status" assign="buttontitle"}
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
        <fieldset id="dzk_stickytopic"  >
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="z-formbuttons z-buttons">
                {button src="button_ok.png" set="icons/extrasmall" alt=$buttontitle title=$buttontitle text=$buttontitle}
            </div>
        </fieldset>
    </div>
</form>

{include file='user/footer.tpl'}