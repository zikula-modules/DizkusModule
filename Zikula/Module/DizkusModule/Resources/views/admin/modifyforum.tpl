{pageaddvar name="javascript" value="jquery"}
{pageaddvar name='javascript' value='modules/zikula-dizkus/Zikula/Module/DizkusModule/Resources/public/js/chosen_v0.14.0/chosen.jquery.min.js'}
{pageaddvar name='stylesheet' value='modules/Dizkus/javascript/chosen_v0.14.0/chosen.css'}
{pageaddvar name='javascript' value='modules/zikula-dizkus/Zikula/Module/DizkusModule/Resources/public/js/Zikula.Dizkus.Admin.ModifyForum.js'}
<style>
    /***************
    * Modify chosen css to compesate for z-form styling
    ***************/
    .chzn-container {
        margin-left: 29%;
    }
</style>
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>
        {if $name}
            {gt text="Edit forum"}
        {else}
            {gt text="New forum"}
        {/if}
    </h3>
</div>

<div id="dizkus_admin">

    {form cssClass="z-form"}
    {formvalidationsummary}

    <fieldset>

        <div class="z-formrow">
            {formlabel for="name" __text='Name'}
            {formtextinput id="name" size="70" maxLength="150" mandatory=true}
        </div>

        <div class="z-formrow">
            {formlabel for="description" __text="Description"}
            {formtextinput id="description" textMode="multiline" rows="3" cols="40"}
        </div>

        <div class="z-formrow">
            {formlabel for="status" __text="Lock Forum"}
            {formcheckbox id="status"}
        </div>
        <p class="z-formnote z-sub">
            <em>{gt text="Locking a forum prevents new <strong>topics</strong> from being created in the forum."}</em>
        </p>

        <div class="z-formrow">
            {formlabel for="parent" __text="Parent"}
            {formdropdownlist id="parent" items=$parents}
        </div>

        {if $name}
            <div class="z-formrow">
                {formlabel for="forum_info" __text="Forum Information"}
                <span id="forum_info">
                    {boardstats type='forumtopics' id=$forum_id assign='topiccount'}
                    {boardstats type='forumposts' id=$forum_id assign='postcount'}
                    <a title="{gt text='Visit this forum'}" href="{modurl modname=$module type='user' func='viewforum' forum=$forum_id}">
                {$topiccount}&nbsp;{if $topiccount eq 1}{gt text="Topic"}{else}{gt text="Topics"}{/if}&nbsp;/&nbsp;
        {$postcount}&nbsp;{if $postcount eq 1}{gt text="Post"}{else}{gt text="Posts"}{/if}
    </a>
</span>
</div>
{/if}

<div class="z-formrow">
    {formlabel for="moderatorUsers" __text="Moderation (Users)"}
    {formdropdownlist id="moderatorUsers" items=$allUsers cssClass="chzn-select" selectionMode='multiple'}
</div>

<div class="z-formrow">
    {formlabel for="moderatorGroups" __text="Moderation (Groups)"}
    {formdropdownlist id="moderatorGroups" items=$allGroups cssClass="chzn-select" selectionMode='multiple'}
</div>


<div id="extsource" class="z-formrow">
    {formlabel for="pop3_active" __text="External source"}
    <div class="z-formlist">
        {formradiobutton id="noexternal" dataField="extsource"}
        {formlabel for="noexternal" __text='No external source'}
    </div>
    <div class="z-formlist">
        {formradiobutton id="mail2forum" dataField="extsource"}
        {formlabel for="mail2forum" __text='Mail2Forum'}
    </div>
    <div class="z-formlist">
        {modavailable modname="Feeds" assign="feeds"}
        {if $feeds}
            {formradiobutton id="rss2forum" dataField='extsource'}
            {formlabel for="rss2forum" __text='RSS2Forum'}
        {else}
            {formradiobutton id="rss2forum" dataField='extsource' disabled=true}
            {formlabel for="rss2forum" __text='RSS2Forum'}
            &nbsp;<span style="color: red;">{gt text="'Feeds' module is not available."}</span>
        {/if}
    </div>
</div>
</fieldset>

<fieldset id="logindata" {if (($extsource != 'mail2forum') && ($extsource != 'rss2forum'))}style="display:none;"{/if}>
    <legend>{gt text="Connect as User..."}</legend>
    <div class="z-formrow">
        {formlabel for="coreUser" __text="Core user"}
        {formdropdownlist id="coreUser" items=$allUsers}
    </div>
</fieldset>

<fieldset id="mail2forumField" {if $extsource != 'mail2forum'}style="display:none;"{/if}>
    <legend>{gt text="Mail2Forum"}</legend>
    <div class="z-formrow">
        {formlabel for="pop3_test" __text="Perform POP3 test after saving"}
        {formcheckbox id="pop3_test"}
    </div>
    <div class="z-formrow">
        {formlabel for="server" __text="POP3 server"}
        {formtextinput id="server" maxLength="60" size="30"}
    </div>
    <div class="z-formrow">
        {formlabel for="port" __text="POP3 port"}
        {formtextinput id="port" maxLength="5" size="5"}
    </div>
    <div class="z-formrow">
        {formlabel for="login" __text="POP3 log-in name"}
        {formtextinput id="login" maxLength="60" size="30"}
    </div>
    <div class="z-formrow">
        {formlabel for="password" __text="POP3 password"}
        {formtextinput textMode="password" id="password" maxLength="60" size="30"}
    </div>
    <div class="z-formrow">
        {formlabel for="passwordconfirm" __text="POP3 password (repeat for verification)"}
        {formtextinput textMode="password" id="passwordconfirm" maxLength="60" size="30"}
    </div>
    <div class="z-formrow">
        {formlabel for="matchstring" __text="Rule"}
        {formtextinput id="matchstring" maxLength="255" size="30"}
        <em class="z-formnote z-sub">
            {gt text="Notice: This rule is a regular expression applied to posts incoming via e-mail, in order to prevent spam postings. If there is no rule here then no checks will be performed."}
        </em>
    </div>
</fieldset>

{if $feeds}
    <fieldset id="rss2forumField" {if $extsource != 'rss2forum'}style="display:none;"{/if}>
        <legend>{gt text="RSS2Forum"}</legend>
        <div class="z-formrow">
            {formlabel for="rssfeed" __text="Select RSS feed"}
            <select name="rssfeed" size="1">
                <option value="0">{gt text="None"}</option>
                {if $rssfeeds}
                    {foreach item='feed' from=$rssfeeds}
                        <option value="{$feed.fid}" {if $feed.fid == $externalsourceurl}selected="selected"{/if}>{$feed.name|safetext} ({$feed.url})</option>
                    {/foreach}
                {/if}
            </select>
        </div>
    </fieldset>
{/if}

{notifydisplayhooks eventname='dizkus.ui_hooks.forum.ui_edit' id=$forum_id}

<div class="z-formbuttons z-buttons">
    {formbutton id="submit" commandName="submit" __text="Save" class="z-bt-ok"}
    {formbutton id="restore" commandName="cancel" __text="Cancel" class="z-bt-cancel"}
</div>

{/form}

</div>

{adminfooter}