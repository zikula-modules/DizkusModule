{gt text="Manage forum subscriptions" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

{modulelinks modname=$module type='prefs'}<br />

{form id="dzk_topicsubscriptions"}
{formvalidationsummary}
<div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{$templatetitle}</h2>
        </div>
        {*pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'*}

        <table class='table table-striped'>
            <thead>
            <tr class='active'>
                <th class='col-lg-3'>{gt text="Forum"}</th>
                <th>{gt text="Unsubscribe from forum"}</th>
            </tr>
            </thead>
            <tbody>
            {if count($subscriptions) > 0}
                <tr class='warning'>
                    <td></td>
                    <td><label for="alltopic"><input name="all" id="all" type="checkbox" value="1" onclick="jQuery('.forum_checkbox').attr('checked', this.checked);" />&nbsp;{gt text="Remove all forum subscriptions"}</label></td>
                </tr>
            {/if}
            {foreach item='subscription' from=$subscriptions}
                <tr>
                    <td><a href="{modurl modname=$module type='user' func='viewforum' forum=$subscription.forum.forum_id}" title="{$subscription.forum.name|safetext}">{$subscription.forum.name|safetext}</a></td>
                    <td>{formcheckbox cssClass="forum_checkbox" id=$subscription.forum.forum_id group="forumIds"}</td>
                </tr>
                {foreachelse}
                <tr>
                    <td colspan='2' class='text-center danger'>
                        {gt text="No forum subscriptions found."}
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
</div>
{/form}



{include file='user/footer.tpl'}