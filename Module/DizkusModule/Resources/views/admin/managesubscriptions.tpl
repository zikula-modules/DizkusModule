{pageaddvar name='javascript' value='zikula'}
{pageaddvar name='javascript' value='jQuery'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/jQuery-Autocomplete-1.2.7/dist/jquery.autocomplete.min.js'}
{pageaddvar name="javascript" value="modules/Dizkus/javascript/Zikula.Dizkus.Admin.ManageSubscriptions.js"}
{pageaddvar name="javascript" value="modules/Dizkus/javascript/Zikula.Dizkus.Tools.js"}
{adminheader}
{strip}
    {pageaddvarblock}
    <style>
        .autocomplete-suggestions { border: 1px solid #999; background: #FFF; overflow: auto; }
        .autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
        .autocomplete-selected { background: #F0F0F0; }
        .autocomplete-suggestions strong { font-weight: normal; color: #3399FF; }
    </style>
    {/pageaddvarblock}
{/strip}
<div class="z-admin-content-pagetitle">
    {icon type="mail" size="small"}
    <h3>{gt text="Manage subscriptions"}</h3>
</div>

<div id="dizkus_admin">

    {form cssClass="z-form"}
    {formvalidationsummary}
    <div id="liveusersearch" class="">
        <fieldset>
            <label for="username">{gt text="Search for a user"}:</label>&nbsp;<input size="25" maxlength="25" type="text" id="username" value="{$username}" />
        </fieldset>
    </div>


    <div>
        {if count($topicsubscriptions) > 0}
            <h2>{gt text="Manage topic subscriptions"}</h2>
            <table class="z-admintable">
                <thead>
                    <tr>
                        <th width=15px></th>
                        <th>{gt text="Topic"}</th>
                        <th>{gt text="Poster"}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item='subscription' from=$topicsubscriptions}
                        <tr class="{cycle values='z-odd,z-even'}">
                            <td>
                                {formcheckbox id=$subscription.id group='topicsubscriptions' cssClass="topicsubscriptions"}
                            </td>
                            <td>
                                {$subscription.topic.title}
                                {*<a href="{$subscription.last_post_url_anchor|safetext}" title="{$subscription.name|safetext} :: {$subscription.title|safetext}">{$subscription.title|safetext}</a>*}
                            </td>
                            <td>
                                {$subscription.topic.poster.user.uid|profilelinkbyuid}
                            </td>
                        </tr>
                    {/foreach}
                    <tr class="{cycle values='z-odd,z-even'}">
                        <td>
                            <input id="alltopic" type="checkbox" value="1" />
                        </td>
                        <td colspan=2>
                            <em>{gt text="All"}</em>
                        </td>
                    </tr>
                </tbody>
            </table>
        {else}
            <h3>{gt text="No topic subscriptions found."}</h3>
        {/if}

        {if count($forumsubscriptions) > 0}
            <h2>{gt text="Manage forum subscriptions"}</h2>
            <table class="z-admintable">
                <thead>
                    <tr>
                        <th style='width:15px;'></th>
                        <th>{gt text="Forum"}</th>
                    </tr>
                </thead>
                <tbody >
                    {foreach item='subscription' from=$forumsubscriptions}
                        <tr class="{cycle values='z-odd,z-even'}">
                            <td >
                                {formcheckbox id=$subscription.msg_id group='forumsubscriptions' cssClass="forumsubscriptions"}
                            </td>
                            <td>
                                <a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$subscription.forum.forum_id}" title="{$subscription.forum.name}">{$subscription.forum.name|safetext}</a>
                            </td>
                        </tr>
                    {/foreach}
                    <tr class="{cycle values='z-odd,z-even'}">
                        <td>
                            <input id="allforums" type="checkbox" value="1" />
                        </td>
                        <td>
                            <em>{gt text="All"}</em>
                        </td>
                    </tr>
                </tbody>
            </table>



        {else}
            <h3>{gt text="No forum subscriptions found."}</h3>
        {/if}

        <div class="z-formbuttons z-buttons">
            {formbutton id="submit" commandName="submit" __text="Unsubscribe selected" class="z-bt-ok"}
        </div>
    </div>
    {/form}

</div>
{adminfooter}