{ajaxheader modname=$modinfo.name filename='Zikula.Dizkus.Admin.ManageSubscriptions.js' ui=true}
{pageaddvar name="stylesheet" value="modules/Dizkus/style/liveusersearch.css"}
{adminheader}
{strip}
{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        liveusersearch();
    });
</script>
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
                <div id="username_choices" class="autocomplete_user"></div>
                {img id="ajax_indicator" style="display: none;" modname='core' set="ajax" src="indicator_circle.gif" alt=""}
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
                            <input name="alltopic" type="checkbox" value="1" onClick="Zikula.toggleInput('.topicsubscriptions');" />
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
                            <a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$subscription.forum_id}" title="{$subscription.forum.name}">{$subscription.forum.name|safetext}</a>
                        </td>
                    </tr>
                    {/foreach}
                    <tr class="{cycle values='z-odd,z-even'}">
                        <td>
                            <input name="allforums" type="checkbox" value="1" onClick="Zikula.toggleInput('.forumsubscriptions');" />
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