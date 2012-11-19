{include file='user/header.tpl' __templatetitle='Manage topic subscriptions'}

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
                            <dt><span>{gt text="Topic"}</span></dt>
                            <dd class="favorites"><span>{gt text="Posted"}</span></dd>
                            <dd class="lastpost"><span>{gt text="Unsubscribe from topic"}</span></dd>
                        </dl>
                    </li>
                </ul>
                <ul class="topiclist forums">
                    <li class="row categorytitle">
                        <label for="alltopic">{gt text="Remove all topic subscriptions"}</label>&nbsp;<input name="alltopic" id="alltopic" type="checkbox" value="1" onclick="jQuery('.z-form-checkbox').attr('checked', this.checked);"/>
                    </li>
                    {foreach item=subscription from=$subscriptions}
                        <li class="row">
                            <dl class="icon">
                                <dt class='ctheme-topic-title'>
                                    <a href="{modurl modname='Dizkus' type='user' func='viewtopic' topic=$subscription.topic.topic_id}" title="{$subscription.topic.topic_id|safetext} :: {$subscription.topic.topic_title|safetext}">{$subscription.topic.topic_title|safetext}</a>
                                    <span>{gt text="Forum"}: {$subscription.topic.topic_id|truncate:70}</span>
                                </dt>
                                <dd class="lastpost">
                                    {include file='user/lastPostBy.tpl' last_post=$subscription.topic.last_post replies=$subscription.topic.topic_replies}
                                </dd>
                                <dd class="favorites">
                                    {formcheckbox class="topic_checkbox" id=$subscription.topic.topic_id group="topicIds"}
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
        <strong>{gt text="No topic subscriptions found."}</strong>
    </div>
</div>

{/if}

{include file='user/footer.tpl'}