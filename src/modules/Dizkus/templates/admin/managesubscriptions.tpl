{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="mail" size="small"}
    <h3>{gt text="Manage subscriptions"}</h3>
</div>

<div id="dizkus_admin">

    <form class="z-form" id="subscriptions" action="{modurl modname='Dizkus' type='admin' func='managesubscriptions'}" method="post">
        <fieldset>
            <label for="username">{gt text="User name"}</label>&nbsp;<input type="text" name="username" id="username" value="{$username}" />
            {button class="z-button z-bt-small" src="search.png" set="icons/extrasmall" __alt="Show users' subscriptions" __title="Show users' subscriptions" __text="Show users' subscriptions"}
        </fieldset>
    </form>

    <form class="z-form" id="managesubscriptions" action="{modurl modname='Dizkus' type='admin' func='managesubscriptions'}" method="post">
        <div>
            <input type="hidden" name="uid" value="{$uid}" />
            {if $topicsubscriptions|@count <> 0}
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
            <h2>{gt text="Manage topic subscriptions"}</h2>
            <table class="z-admintable">
                <caption>(<label for="alltopic">{gt text="Remove all topic subscriptions"}</label>&nbsp;<input name="alltopic" id="alltopic" type="checkbox" value="1" />&nbsp;)</caption>
                <thead>
                    <tr>
                        <th>{gt text="Topic"}</th>
                        <th>{gt text="Poster"}</th>
                        <th>{gt text="Unsubscribe from topic"}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item='subscription' from=$topicsubscriptions}
                    <tr class="{cycle values='z-odd,z-even'}">
                        <td>
                            <a href="{$subscription.last_post_url_anchor|safetext}" title="{$subscription.forum_name|safetext} :: {$subscription.topic_title|safetext}">{$subscription.topic_title|safetext}</a>
                        </td>
                        <td>
                            {$subscription.poster_name|profilelinkbyuname}
                        </td>
                        <td>
                            <input class="topic_checkbox" type="checkbox" name="topic_id[]" value="{$subscription.topic_id}" />
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
            {else}
            <h3>{gt text="No topic subscriptions found."}</h3>
            {/if}

            {if $forumsubscriptions|@count <> 0}
            <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
            <h2>{gt text="Manage forum subscriptions"}</h2>
            <table class="z-admintable">
                <caption>(<label for="allforum">{gt text="Remove all forum subscriptions"}</label>&nbsp;<input name="allforum" id="allforum" type="checkbox" value="1" />&nbsp;)</caption>
                <thead>
                    <tr>
                        <th>{gt text="Forum"}</th>
                        <th>{gt text="Unsubscribe from forum"}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item='subscription' from=$forumsubscriptions}
                    <tr class="{cycle values='z-odd,z-even'}">
                        <td>
                            <a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$subscription.forum_id}" title="{$subscription.cat_title} :: {$subscription.forum_name}">{$subscription.forum_name|safetext}</a>
                        </td>
                        <td>
                            <input class="forum_checkbox" type="checkbox" name="forum_id[]" value="{$subscription.forum_id}" />
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>

            {else}
            <h3>{gt text="No forum subscriptions found."}</h3>
            {/if}

            <div class="z-formbuttons z-buttons">
                {button src="button_ok.png" set="icons/extrasmall" __alt="Submit" __title="Submit" __text="Submit"}
            </div>
        </div>
    </form>

</div>
{adminfooter}