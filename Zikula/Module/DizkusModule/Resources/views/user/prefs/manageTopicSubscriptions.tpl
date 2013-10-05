{gt text="Manage topic subscriptions" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

{modulelinks modname=$module type='prefs'}<br />

{form id="dzk_topicsubscriptions"}
{formvalidationsummary}
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{$templatetitle}</h2>
    </div>
    {*pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'*}

    <table class='table table-striped'>
        <thead>
        <tr class='active'>
            <th class='col-lg-3'>{gt text="Topic"}</th>
            <th class='col-lg-6'>{gt text="Unsubscribe from topic"}</th>
            <th>{gt text="Last post"}</th>
        </tr>
        </thead>
        <tbody>
        {if count($subscriptions) > 0}
            <tr class='warning'>
                <td></td>
                <td><label for="alltopic"><input name="all" id="all" type="checkbox" value="1" onclick="jQuery('.z-form-checkbox').attr('checked', this.checked);"/>&nbsp;{gt text="Remove all topic subscriptions"}</label></td>
                <td></td>
            </tr>
        {/if}
        {foreach item='subscription' from=$subscriptions}
            <tr>
                <td>
                    <a href="{modurl modname=$module type='user' func='viewtopic' topic=$subscription.topic.topic_id}" title="{$subscription.topic.topic_id|safetext} :: {$subscription.topic.title|safetext}">{$subscription.topic.title|safetext}</a>
                </td>
                <td>{formcheckbox class="topic_checkbox" id=$subscription.topic.topic_id group="topicIds"}</td>
                <td>{include file='user/lastPostBy.tpl' last_post=$subscription.topic.last_post}</td>
            </tr>
            {foreachelse}
            <tr>
                <td colspan='3' class='text-center danger'>
                    {gt text="No topic subscriptions found."}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{if count($subscriptions) > 0}
    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            {formbutton class="btn btn-success" commandName="save" __text="Submit"}
        </div>
    </div>
{/if}
{/form}

{include file='user/footer.tpl'}