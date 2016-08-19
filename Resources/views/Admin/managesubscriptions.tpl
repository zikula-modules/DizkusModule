{*pageaddvar name='javascript' value='zikula'}{* @todo I think this is unneeded *}
{pageaddvar name='javascript' value='jQuery'}
{pageaddvar name='javascript' value='@ZikulaDizkusModule/Resources/public/js/jQuery-Autocomplete-1.2.7/dist/jquery.autocomplete.min.js'}
{pageaddvar name="javascript" value='@ZikulaDizkusModule/Resources/public/js/Zikula.Dizkus.Admin.ManageSubscriptions.js'}
{pageaddvar name='javascript' value='@ZikulaDizkusModule/Resources/public/js/Zikula.Dizkus.Tools.js'}
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
<h3>
    <span class="fa fa-envelope"></span>&nbsp;{gt text="Manage subscriptions"}
</h3>

<div id="dizkus_admin">

    {form cssClass="form-inline"}
    {formvalidationsummary}
    <div id="liveusersearch" class="">
        <fieldset>
            <label for="username" class="control-label col-lg-2">{gt text="Search for a user"}:</label>&nbsp;<div class="col-lg-3"><input class="form-control" maxlength="25" type="text" id="username" name='username' value="{$username}" placeholder="type a username" /></div>
        </fieldset>
    </div>


    <div>
        {if isset($topicsubscriptions) && count($topicsubscriptions) > 0}
        <h2>{gt text="Manage topic subscriptions"}</h2>
        <div class="panel panel-default">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width='15px'></th>
                        <th>{gt text="Topic"}</th>
                        <th>{gt text="Poster"}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item='subscription' from=$topicsubscriptions}
                    <tr>
                        <td>
                            {formcheckbox id=$subscription.id group='topicsubscriptions' cssClass="topicsubscriptions"}
                        </td>
                        <td>
                            {$subscription.topic.title}
                            {*<a href="{$subscription.last_post_url_anchor|safetext}" title="{$subscription.name|safetext} :: {$subscription.title|safetext}">{$subscription.title|safetext}</a>*}
                        </td>
                        <td>
                            {$subscription.topic.poster.user.uname|profilelinkbyuname}
                        </td>
                    </tr>
                    {/foreach}
                    <tr class="danger">
                        <td>
                            <input id="alltopic" type="checkbox" value="1" />
                        </td>
                        <td colspan=2>
                            <em>{gt text="All topic subscriptions"}</em>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {else}
            <h3>{gt text="No topic subscriptions found."}</h3>
        {/if}

        {if isset($forumsubscriptions) && count($forumsubscriptions) > 0}
        <h2>{gt text="Manage forum subscriptions"}</h2>
        <div class="panel panel-default">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style='width:15px;'></th>
                        <th>{gt text="Forum"}</th>
                    </tr>
                </thead>
                <tbody >
                    {foreach item='subscription' from=$forumsubscriptions}
                        <tr>
                            <td >
                                {formcheckbox id=$subscription.msg_id group='forumsubscriptions' cssClass="forumsubscriptions"}
                            </td>
                            <td>
                                <a href="{route name='zikuladizkusmodule_user_viewforum' forum=$subscription.forum.forum_id}" title="{$subscription.forum.name}">{$subscription.forum.name|safetext}</a>
                            </td>
                        </tr>
                    {/foreach}
                    <tr class="danger">
                        <td>
                            <input id="allforums" type="checkbox" value="1" />
                        </td>
                        <td>
                            <em>{gt text="All forum subscriptions"}</em>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {else}
            <h3>{gt text="No forum subscriptions found."}</h3>
        {/if}

        {formbutton id="submit" commandName="submit" __text="Unsubscribe selected" class="btn btn-success"}
    </div>
    {/form}

</div>
{adminfooter}
