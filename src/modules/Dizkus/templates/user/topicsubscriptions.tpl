{gt text="Manage topic subscriptions" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
<form id="dzk_topicsubscriptions" action="{modurl modname=Dizkus type=user func=topicsubscriptions}" method="post">
    <div>
        <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />

        {if $subscriptions}
        <div class="forumbg dzk_rounded">
            <div class="inner">

                <ul class="topiclist">
                    <li class="dzk_header">
                        <dl>
                            <dt><span>{gt text="Topic"}</span></dt>
                            <dd class="favorites"><span>{gt text="Posted"}</span></dd>
                            <dd class="lastpost"><span>{gt text="Unsubscribe from topic"}</span></dd>
                        </dl>
                    </li>
                </ul>
                <ul class="topiclist forums">
                    <li class="row categorytitle">
                        <label for="alltopic">{gt text="Remove all topic subscriptions"}</label>&nbsp;<input name="alltopic" id="alltopic" type="checkbox" value="1" />
                    </li>
                    {foreach item=subscription from=$subscriptions}
                    <li class="row">
                        <dl class="icon">
                            <dt class='ctheme-topic-title'>
                                <a href="{$subscription.last_post_url_anchor}" title="{$subscription.forum_name|safetext} :: {$subscription.topic_title|safetext}">{$subscription.topic_title|safetext}</a>
                                <span>{gt text="Forum"}: {$subscription.forum_name|truncate:70}</span>
                            </dt>
                            <dd class="favorites">
                                <span>
                                    {gt text="Posted by %s" tag1=$subscription.poster_name|profilelinkbyuname}<br />
                                    {$subscription.topic_time|dateformat:'datetimebrief'}
                                </span>
                            </dd>
                            <dd class="lastpost">
                                <input class="topic_checkbox" type="checkbox" name="topic_id[]" value="{$subscription.topic_id}" />
                            </dd>
                        </dl>
                    </li>
                    {/foreach}
                </ul>

            </div>
        </div>

        {else}

        <div class="forumbg dzk_message dzk_rounded">
            <div class="inner">
                <strong>{gt text="No topic subscriptions found."}</strong>
            </div>
        </div>

        {/if}

        <div class="z-buttons z-gap">
            {button src="button_ok.png" set="icons/extrasmall" __alt="Submit" __title="Submit" __text="Submit"}
        </div>
    </div>
</form>

{include file='user/footer.tpl'}