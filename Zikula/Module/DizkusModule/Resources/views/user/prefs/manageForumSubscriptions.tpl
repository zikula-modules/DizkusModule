{gt text="Manage forum subscriptions" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
{modulelinks modname='Dizkus' type='prefs'}<br />

{if $subscriptions}
    {form id="dzk_topicsubscriptions"}
    {formvalidationsummary}
    <div>
        <div class="forumbg dzk_rounded">
            <div class="inner">

                <ul class="topiclist">
                    <li class="dzk_header">
                        <dl>
                            <dt><span>{gt text="Forum"}</span></dt>
                            <dd class="lastpost"><span>{gt text="Unsubscribe from forum"}</span></dd>
                        </dl>
                    </li>
                </ul>
                <ul class="topiclist forums">
                    <li class="row categorytitle">
                        <label for="alltopic">{gt text="Remove all forum subscriptions"}</label>&nbsp;<input name="all" id="all" type="checkbox" value="1" onclick="jQuery('.z-form-checkbox').attr('checked', this.checked);"/>
                    </li>
                    {foreach item='subscription' from=$subscriptions}
                        <li class="row">
                            <dl class="icon">
                                <dt class='ctheme-topic-title'>
                                <a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$subscription.forum.forum_id}" title="{$subscription.forum.name|safetext}">{$subscription.forum.name|safetext}</a>
                                </dt>
                                <dd class="lastpost">
                                    {formcheckbox class="topic_checkbox" id=$subscription.forum.forum_id group="forumIds"}
                                </dd>
                            </dl>
                        </li>
                    {/foreach}
                </ul>

            </div>
        </div>

        <div class="z-buttons z-formbuttons">
            {formbutton class="z-bt-ok" commandName="save" __text="Submit"}
        </div>
    </div>
    {/form}

{else}

    <div class="forumbg dzk_message dzk_rounded">
        <div class="inner">
            <strong>{gt text="No forum subscriptions found."}</strong>
        </div>
    </div>

{/if}

{include file='user/footer.tpl'}