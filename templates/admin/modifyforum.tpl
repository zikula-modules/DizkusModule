{ajaxheader ui=true modname='Dizkus' filename='chosen/chosen.proto.min.js'}
{pageaddvar name='stylesheet' value='modules/Dizkus/javascript/chosen/chosen.css'}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>
        {if $forum_name}
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
            {formlabel for="forum_name" __text='Name'}
            {formtextinput id="forum_name" size="70" maxLength="150" mandatory=true}
        </div>


        <div class="z-formrow">
            {formlabel for="forum_desc" __text="Description"}
            {formtextinput id="forum_desc" textMode="multiline" rows="3" cols="40"}
        </div>

        <div class="z-formrow">
            {formlabel for="parent" __text="Parent"}
            {formdropdownlist id="parent" items=$parents}
        </div>

        {if $forum_name}
        <div class="z-formrow">
            {formlabel for="forum_info" __text="Forum Information"}
            <span id="forum_info">
                {boardstats type='forumtopics' id=$forum_id assign=topiccount}
                {boardstats type='forumposts' id=$forum_id assign=postcount}
                <a title="{gt text='Visit this forum'}" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$forum_id}">
                    {$topiccount}&nbsp;{if $topiccount eq 1}{gt text="Topic"}{else}{gt text="Topics"}{/if}&nbsp;/&nbsp;
                    {$postcount}&nbsp;{if $postcount eq 1}{gt text="Post"}{else}{gt text="Posts"}{/if}
                </a>
            </span>
        </div>
        {/if}

        {*<div class="z-formrow">
            {formlabel for="moduleref" __text="Hooked module"}
            <select id="moduleref" name="moduleref" size="1">
                {foreach item='hookmod' from=$hooked_modules}
                    <option value="{$hookmod.id}" {if $hookmod.sel == true}selected="selected"{/if}>{$hookmod.name}</option>
                    {foreachelse}
                    <option value="0" selected="selected">{gt text="No hooked module found."}</option>
                {/foreach}
            </select>
            <p class="z-formnote z-sub">{gt text="Notice: This is used for the comments feature. All submissions within this module will be posted within this forum. The list only includes modules for which Dizkus has been activated as a hook."}</p>
        </div>*}


        <div id="chosenCss" class="z-formrow">
            {formlabel for="moderatorUsers" __text="Moderation (Users)"}
            {formdropdownlist id="moderatorUsers" items=$allUsers cssClass="chzn-select" selectionMode='multiple'}
        </div>

        <div id="chosenCss" class="z-formrow">
            {formlabel for="moderatorGroups" __text="Moderation (Groups)"}
            {formdropdownlist id="moderatorGroups" items=$allGroups cssClass="chzn-select" selectionMode='multiple'}
        </div>


        <div id="extsource" class="z-formrow">
            {formlabel for="pop3_active" __text="External source"}
                <div class="z-formlist">
                    {formradiobutton id="noexternal" dataField="extsource" onclick="$('mail2forumField').hide();$('rss2forumField').hide()"}
                    {formlabel for="noexternal" __text='No external source'}
                </div>
                <div class="z-formlist">
                    {formradiobutton id="mail2forum" dataField="extsource" onclick="$('mail2forumField').show()"}
                    {formlabel for="mail2forum" __text='Mail2Forum'}
                </div>
                <div class="z-formlist">
                    {modavailable modname="Feeds" assign="feeds"}
                    {if $feeds}
                    {formradiobutton id="rss2forum" dataField='extsource' onclick="$('rss2forumField').show()"}
                    {formlabel for="rss2forum" __text='RSS2Forum'}
                    {else}
                    {formradiobutton id="rss2forum" dataField='extsource' disabled=true}
                    {formlabel for="rss2forum" __text='RSS2Forum'}
                    &nbsp;<span style="color: red;">{gt text="'Feeds' module is not available."}</span>
                    {/if}
                </div>
        </div>
    </fieldset>

    {* TODO do all the control of these fieldsets with javascript onload *}
    {*<fieldset id="pnlogindata" {*if $forum.externalsource == 0*}{*style="display:none;"{*/if*}{*>
        <div class="z-formrow">
            {formlabel for="pnuser" __text="User name"}
            {formtextinput id="pnuser" maxLength="60" size="30"}
        </div>
        <div class="z-formrow">
            {formlabel for="pnpassword" __text="Password"}
            {formtextinput textMode="password" id="pnpassword" maxLength="60" size="30"}
        </div>
        <div class="z-formrow">
            {formlabel for="pnpasswordconfirm" __text="Password (repeat for verification)"}
            {formtextinput textMode="password" id="pnpasswordconfirm" maxLength="60" size="30"}
        </div>
    </fieldset>

    <fieldset id="mail2forumField" {if $extsource != 'mail2forum'}style="display:none;"{/if}>
        <legend>{gt text="Mail2Forum"}</legend>
        <div class="z-formrow">
            {formlabel for="pop3_test" __text="Perform POP3 test after saving"}
            {formcheckbox id="pop3_test"}
        </div>
        <div class="z-formrow">
            {formlabel for="pop3_server" __text="POP3 server"}
            {formtextinput id="pop3_server" maxLength="60" size="30"}
        </div>
        <div class="z-formrow">
            {formlabel for="pop3_port" __text="POP3 port"}
            {formtextinput id="pop3_port" maxLength="5" size="5"}
        </div>
        <div class="z-formrow">
            {formlabel for="pop3_login" __text="POP3 log-in name"}
            {formtextinput id="pop3_login" maxLength="60" size="30"}
        </div>
        <div class="z-formrow">
            {formlabel for="pop3_password" __text="POP3 password"}
            {formtextinput textMode="password" id="pop3_password" maxLength="60" size="30"}
        </div>
        <div class="z-formrow">
            {formlabel for="pop3_passwordconfirm" __text="POP3 password (repeat for verification)"}
            {formtextinput textMode="password" id="pop3_passwordconfirm" maxLength="60" size="30"}
        </div>
        <div class="z-formrow">
            {formlabel for="pop3_matchstring" __text="Rule"}
            {formtextinput id="pop3_matchstring" maxLength="255" size="30" }
            <em class="z-formnote z-sub">
                {gt text="Notice: This rule is a regular expression applied to posts incoming via e-mail, in order to prevent spam postings. If there is no rule here then no checks will be performed."}
            </em>
        </div>
    </fieldset> *}

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