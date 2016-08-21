{pageaddvar name="javascript" value="jquery"}
{pageaddvar name='javascript' value='@ZikulaDizkusModule/Resources/public/js/chosen_v1.0.0/chosen.jquery.min.js'}
{pageaddvar name='stylesheet' value='@ZikulaDizkusModule/Resources/public/js/chosen_v1.0.0/chosen.css'}
{pageaddvar name='stylesheet' value='@ZikulaDizkusModule/Resources/public/css/chosen-bootstrap.css'}
{pageaddvar name='javascript' value='@ZikulaDizkusModule/Resources/public/js/Zikula.Dizkus.Admin.ModifyForum.js'}

{adminheader}
<h3>
    <span class="fa fa-pencil-square-o"></span>&nbsp;
    {if $name}
        {gt text="Edit forum"}
    {else}
        {gt text="New forum"}
    {/if}
</h3>

<div id="dizkus_admin">

    {form cssClass="form-horizontal" role="form"}
    {formvalidationsummary}

    <fieldset>

        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="name" __text='Name'}
            <div class="col-lg-9">
                {formtextinput id="name" size="70" maxLength="150" mandatory=true cssClass='form-control'}
            </div>
        </div>

        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="description" __text="Description"}
            <div class="col-lg-9">
                {formtextinput id="description" textMode="multiline" rows="3" cols="40" cssClass='form-control'}
            </div>
        </div>

        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="status" __text="Lock Forum"}
            <div class="col-lg-9">
                <div class="checkbox">
                    {formcheckbox id="status"}
                </div>
                <div class="help-block alert alert-info">
                    <em>{gt text="Locking a forum prevents new <strong>topics</strong> from being created in the forum."}</em>
                </div>
            </div>
        </div>

        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="parent" __text="Parent"}
            <div class="col-lg-9">
                {formdropdownlist id="parent" items=$parents cssClass='form-control'}
            </div>
        </div>

        {if $name}
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="forum_info" __text="Forum Information"}
            <div class="col-lg-9">
                <span id="forum_info">
                    {boardstats type='forumtopics' id=$forum_id assign='topiccount'}
                    {boardstats type='forumposts' id=$forum_id assign='postcount'}
                    <a title="{gt text='Visit this forum'}" href="{route name='zikuladizkusmodule_user_viewforum' forum=$forum_id}">
                        {$topiccount}&nbsp;{if $topiccount eq 1}{gt text="Topic"}{else}{gt text="Topics"}{/if}&nbsp;/&nbsp;
                        {$postcount}&nbsp;{if $postcount eq 1}{gt text="Post"}{else}{gt text="Posts"}{/if}
                    </a>
                </span>
            </div>
        </div>
        {/if}

        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="moderatorUsers" __text="Moderation (Users)"}
            <div class="col-lg-9">
                {formdropdownlist id="moderatorUsers" items=$allUsers cssClass="chzn-select form-control" selectionMode='multiple'}
            </div>
        </div>

        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="moderatorGroups" __text="Moderation (Groups)"}
            <div class="col-lg-9">
                {formdropdownlist id="moderatorGroups" items=$allGroups cssClass="chzn-select form-control" selectionMode='multiple'}
            </div>
        </div>


        <div id="extsource" class="form-group">
            <strong class="col-lg-3 control-label">{gt text="External source"}</strong>
            <div class="col-lg-9">
                <div class="radio">
                    {formlabel for="noexternal" __text='No external source'}
                    {formradiobutton id="noexternal" dataField="extsource"}
                </div>
                <div class="radio">
                    {formlabel for="mail2forum" __text='Mail2Forum'}
                    {formradiobutton id="mail2forum" dataField="extsource"}
                </div>
                <div class="radio">
                    {modavailable modname="Feeds" assign="feeds"}
                    {formlabel for="rss2forum" __text='RSS2Forum'}
                    {if $feeds}
                    {formradiobutton id="rss2forum" dataField='extsource'}
                    {else}
                    {formradiobutton id="rss2forum" dataField='extsource' disabled='disabled'}
                        &nbsp;<span class="text-danger">{gt text="'Feeds' module is not available."}</span>
                    {/if}
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset id="logindata" {if (($extsource != 'mail2forum') && ($extsource != 'rss2forum'))}style="display:none;"{/if}>
        <legend>{gt text="Connect as User..."}</legend>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="coreUser" __text="Core user"}
            <div class="col-lg-9">
                {formdropdownlist id="coreUser" items=$allUsers cssClass='form-control'}
            </div>
        </div>
    </fieldset>

    <fieldset id="mail2forumField" {if $extsource != 'mail2forum'}style="display:none;"{/if}>
        <legend>{gt text="Mail2Forum"}</legend>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="pop3_test" __text="Perform POP3 test after saving"}
            <div class="col-lg-9">
                <div class="checkbox">
                    {formcheckbox id="pop3_test"}
                </div>
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="server" __text="POP3 server"}
            <div class="col-lg-9">
                {formtextinput id="server" maxLength="60" size="30" cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="port" __text="POP3 port"}
            <div class="col-lg-9">
                {formtextinput id="port" maxLength="5" size="5" cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="login" __text="POP3 log-in name"}
            <div class="col-lg-9">
                {formtextinput id="login" maxLength="60" size="30" cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="password" __text="POP3 password"}
            <div class="col-lg-9">
                {formtextinput textMode="password" id="password" maxLength="60" size="30" cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="passwordconfirm" __text="POP3 password (repeat for verification)"}
            <div class="col-lg-9">
                {formtextinput textMode="password" id="passwordconfirm" maxLength="60" size="30" cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="matchstring" __text="Rule"}
            <div class="col-lg-9">
                {formtextinput id="matchstring" maxLength="255" size="30" cssClass='form-control'}
                <em class="help-block">
                    {gt text="Notice: This rule is a regular expression applied to posts incoming via e-mail, in order to prevent spam postings. If there is no rule here then no checks will be performed."}
                </em>
            </div>
        </div>
    </fieldset>

    {if $feeds}
    <fieldset id="rss2forumField" {if $extsource != 'rss2forum'}style="display:none;"{/if}>
        <legend>{gt text="RSS2Forum"}</legend>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="rssfeed" __text="Select RSS feed"}
            <div class="col-lg-9">
                <select name="rssfeed" size="1" class="form-control">
                    <option value="0">{gt text="None"}</option>
                    {if $rssfeeds}
                        {foreach item='feed' from=$rssfeeds}
                            <option value="{$feed.fid}" {if $feed.fid == $externalsourceurl}selected="selected"{/if}>{$feed.name|safetext} ({$feed.url})</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
    </fieldset>
    {/if}

    {notifydisplayhooks eventname='dizkus.ui_hooks.forum.ui_edit' id=$forum_id}

    <div class="col-lg-offset-3 col-lg-9">
        {formbutton id="submit" commandName="submit" __text="Save" class="btn btn-success"}
        {formbutton id="cancel" commandName="cancel" __text="Cancel" class="btn btn-danger"}
    </div>

    {/form}

</div>

{adminfooter}