{* CREATE ICON VARIABLES *}
{capture assign="unsubscribe_icon"}<span class="icon-stack"><i class="icon-ban-circle icon-stack-base"></i><i class="icon-envelope-alt"></i></span>{/capture}
{capture assign="subscribe_icon"}<span class="icon-stack"><i class="icon-envelope-alt"></i></span>{/capture}
{capture assign="unsticky_icon"}<span class="icon-stack"><i class="icon-ban-circle icon-stack-base"></i><i class="icon-bullhorn"></i></span>{/capture}
{capture assign="sticky_icon"}<span class="icon-stack"><i class="icon-bullhorn"></i></span>{/capture}
{capture assign="unsolve_icon"}<span class="icon-stack"><i class="icon-ban-circle icon-stack-base"></i><i class="icon-ok"></i></span>{/capture}
{capture assign="solve_icon"}<span class="icon-stack"><i class="icon-ok"></i></span>{/capture}
{* ------- *}

{assign var='templatetitle' value=$topic.title}
{include file='user/header.tpl' parent=$topic.forum.forum_id}
<input id="topic_id" name="topic" type="hidden" value="{$topic.topic_id}">
{if $modvars.ZikulaDizkusModule.ajax}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.User.ViewTopic.js'}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.Tools.js'}
{/if}

<h2>
    <span class='text-success' id="topic_solved" {if !$topic.solved or !$modvars.ZikulaDizkusModule.solved_enabled}style='display:none'{/if}>
        [<span class='icon-ok'>&nbsp;{gt text="Solved"}</span>]
    </span>
    <span id="edittopicsubjectbutton" title="">
        <span id="topic_title">{$topic.title|safehtml|notifyfilters:'dizkus.filter_hooks.post.filter'}<span id='edittopicicon' style='display:none;'>&nbsp;<i class='icon-pencil icon-red'></i></span></span>
    </span>
</h2>

{* add inline edit *}
{if ($modvars.ZikulaDizkusModule.ajax && ($isModerator || $topic->userAllowedToEdit()))}
    {include file='ajax/edittopicsubject.tpl'}
{/if}

{* ******************************************************
* TOPIC NAVBAR
******************************************************* *}
{userloggedin assign='userloggedin'}
<nav class="navbar navbar-topic navbar-default" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-topic-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand icon-comments" style='font-size:3em;color:mediumseagreen' href="#"></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div id="navbar-topic-collapse" class="collapse navbar-collapse">
        <ul class="nav navbar-nav navbar-left">
            {if !empty($previousTopic) and $topic.topic_id neq $previousTopic}{assign var='disabled' value=''}{else}{assign var='disabled' value=' disabled'}{/if}
            <li><a class="tooltips icon-chevron-left{$disabled}" title="{gt text="Previous topic"}" href="{modurl modname=$module type='user' func='viewtopic' topic=$previousTopic}"></a></li>
            {if !empty($nextTopic) and $topic.topic_id neq $nextTopic}{assign var='disabled' value=''}{else}{assign var='disabled' value=' disabled'}{/if}
            <li><a class="tooltips icon-chevron-right{$disabled}" title="{gt text="Next topic"}" href="{modurl modname=$module type='user' func='viewtopic' topic=$nextTopic}"></a></li>

            {if $permissions.comment}
            <li><a class="icon-comment-alt tooltips" title="{gt text="Create a new topic"}" href="{modurl modname=$module type='user' func='newtopic' forum=$topic.forum.forum_id}">&nbsp;{gt text="New topic"}</a></li>
            {/if}

            {if $userloggedin}
            <li><a class="tooltips" title="{gt text="Send the posts within this topic as an e-mail message to someone"}" href="{modurl modname=$module type='user' func='emailtopic' topic=$topic.topic_id}">
               <span class="icon-stack">
                   <i class="icon-envelope-alt"></i>
                   <i class="icon-share-alt icon-overlay-upper-left" style='color:lightblue;'></i>
               </span>
            </a></li>
            {/if}

            <li>{printtopic_button topic_id=$topic.topic_id forum=$topic.forum}</li>

            {if $userloggedin}
            <li>
                {if $isSubscribed}
                    {modurl modname=$module type='user' func='changeTopicStatus' action='unsubscribe' topic=$topic.topic_id assign='url'}
                    {gt text="Unsubscribe from topic" assign='msg'}
                {else}
                    {modurl modname=$module type='user' func='changeTopicStatus' action='subscribe' topic=$topic.topic_id assign='url'}
                    {gt text="Subscribe to topic" assign='msg'}
                {/if}
                <a id="toggletopicsubscription" class="tooltips" data-status="{if $isSubscribed}1{else}0{/if}" href="{$url}" title="{$msg}">
                    {if $isSubscribed}{$unsubscribe_icon}{else}{$subscribe_icon}{/if}
                </a>
            </li>
                {usergetvar name='uid' assign='currentUser'}
                {if ($modvars.ZikulaDizkusModule.solved_enabled|default:0) && ($isModerator || ($currentUser == $topic.poster.user.uid))}
                <li>
                    {if $topic.solved}
                        {modurl modname=$module type='user' func='changeTopicStatus' action='unsolve' topic=$topic.topic_id assign='url'}
                        {gt text="Mark as unsolved" assign='msg'}
                    {else}
                        {modurl modname=$module type='user' func='changeTopicStatus' action='solve' topic=$topic.topic_id assign='url'}
                        {gt text="Mark as solved" assign='msg'}
                    {/if}
                    <a id="toggletopicsolve" class="tooltips" data-status="{if $topic.solved}1{else}0{/if}" href="{$url}" title="{$msg}">
                        {if $topic.solved}{$unsolve_icon}{else}{$solve_icon}{/if}
                    </a>
                </li>
                {/if}
            {/if}

        </ul>
        <ul class="nav navbar-nav navbar-right">
            {if $isModerator}
            <li>
                {if $topic.status eq 0}
                    {modurl modname=$module type='user' func='changeTopicStatus' action='lock' topic=$topic.topic_id assign='url'}
                    {gt text="Lock topic" assign='msg'}
                    {assign var="iconclass" value="icon-lock"}
                {else}
                    {modurl modname=$module type='user' func='changeTopicStatus' action='unlock' topic=$topic.topic_id assign='url'}
                    {gt text="Unlock topic" assign='msg'}
                    {assign var="iconclass" value="icon-unlock"}
                {/if}
                <a id="toggletopiclock" class="{$iconclass} tooltips" title="{$msg}" data-status="{if $topic.status}1{else}0{/if}" href="{$url}"></a>
            </li>

            <li>
                {if $topic.sticky eq 0}
                    {modurl modname=$module type='user' func='changeTopicStatus' action='sticky' topic=$topic.topic_id assign='url'}
                    {gt text="Give this topic 'sticky' status" assign='msg'}
                {else}
                    {modurl modname=$module type='user' func='changeTopicStatus' action='unsticky' topic=$topic.topic_id assign='url'}
                    {gt text="Remove 'sticky' status" assign='msg'}
                {/if}
                <a id="toggletopicsticky" class="tooltips" title="{$msg}" data-status="{if $topic.sticky}1{else}0{/if}" href="{$url}">
                    {if $topic.sticky eq 0}{$sticky_icon}{else}{$unsticky_icon}{/if}
                </a>
            </li>

            <li><a class="icon-arrow-right tooltips" title="{gt text="Move topic"}" href="{modurl modname=$module type='user' func='movetopic' topic=$topic.topic_id}"></a></li>
            <li><a class="icon-remove tooltips" title="{gt text="Delete topic"}" href="{modurl modname=$module type='user' func='deletetopic' topic=$topic.topic_id}"></a></li>
            {/if}
            <li><a class="dzk_notextdecoration tooltips" title="{gt text="To bottom of page"}" href="#bottom"><i class=' icon-chevron-sign-down'></i></a></li>
        </ul>
    </div><!-- /.navbar-collapse -->
</nav>

{* ******************************************************
* TOPIC LIST
******************************************************* *}
{pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}

<div id="dzk_postinglist">
    <ul>
        {counter start=0 print=false assign='post_counter'}
        {foreach key='num' item='post' from=$posts}
            {counter}
            <li class="post_{$post.post_id}">
                {include file='user/post/single.tpl'}
            </li>
        {/foreach}
        <li id="quickreplyposting" class="hidden">&nbsp;</li>
        <li id="quickreplypreview" class="hidden">&nbsp;</li>
    </ul>
</div>

{pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}

{* ******************************************************
* QUICK REPLY FORM
******************************************************* *}
{if ($permissions.comment eq true)}
    <a id="reply"></a>
    <div id="dzk_quickreply" class="panel panel-info"{if $topic.status eq 1} style='display:none'{/if}>
        <div class="panel-heading">
            <h3>{gt text="Quick reply"}</h3>
        </div>
        <div class="panel-body">
            <form id="quickreplyform" role='form' action="{modurl modname=$module type='user' func='reply'}" method="post" enctype="multipart/form-data">
                <div id="dizkusinformation_-1" style='display:none;'>{img modname='core' set='ajax' src='indicator.white.gif'}</div>
                <div class="form-group">
                    <input type="hidden" id="forum" name="forum" value="{$topic.forum.forum_id}" />
                    <input type="hidden" id="topic" name="topic" value="{$topic.topic_id}" />
                    <input type="hidden" id="quote" name="quote" value="" />
                    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
                    <label for="message" class="sr-only">{gt text="Message"}</label>
                    <textarea id="message" class="form-control" name="message" rows="10"></textarea>

                    {if $modvars.ZikulaDizkusModule.striptags == 'yes'}
                        <p class='help-block'>{gt text="No HTML tags allowed (except inside [code][/code] tags)"}</p>
                    {/if}
                </div>
                <div class="form-group">
                    {notifydisplayhooks eventname='dizkus.ui_hooks.post.ui_edit' id=null}
                </div>
                {if $userloggedin}
                <div><strong>{gt text="Options"}</strong></div>
                <div class="checkbox">
                    <label for="attach_signature">
                    <input type="checkbox" id="attach_signature" name="attach_signature" checked="checked" value="1" />
                    {gt text="Attach my signature"}</label>
                </div>
                <div class="checkbox">
                    <label for="subscribe_topic">
                    <input type="checkbox" id="subscribe_topic" name="subscribe_topic" checked="checked" value="1" />
                    {gt text="Notify me when a reply is posted"}</label>
                </div>
                {/if}
                <input id="btnSubmitQuickReply" class="btn btn-success" type="submit" name="submit" value="{gt text="Submit"}" />
                <input id="btnPreviewQuickReply" class="btn btn-primary" type="submit" name="preview" value="{gt text="Preview"}" />
                <button id="btnCancelQuickReply" class="btn btn-danger" style='display:hidden' type="submit" name="cancel">{gt text="Cancel"}</button>

                <div class="post_footer"></div>
            </form>
        </div>
    </div>

    <div id="dzk_displayhooks">
        {notifydisplayhooks eventname='dizkus.ui_hooks.topic.ui_view' id=$topic.topic_id}
    </div>

{/if}

{include file='user/moderatedBy.tpl' forum=$topic.forum well=true}

<script type="text/javascript">
    // @TODO Replace by Zikula.__() and remove this vars.
    // <![CDATA[
    var clickToEdit = "{{gt text="Click to edit"}}";
    var subscribeTopic = " {{gt text='Subscribe to topic'}}";
    var subscribeTopicIcon = "{{$subscribe_icon|strip|addslashes}}";
    var unsubscribeTopic = " {{gt text='Unsubscribe from topic'}}";
    var unsubscribeTopicIcon = "{{$unsubscribe_icon|strip|addslashes}}";
    var lockTopic = " {{gt text='Lock topic'}}";
    var unlockTopic = " {{gt text='Unlock topic'}}";
    var stickyTopic = " {{gt text="Give this topic 'sticky' status"}}";
    var stickyTopicIcon = "{{$sticky_icon|strip|addslashes}}";
    var unstickyTopic = " {{gt text="Remove 'sticky' status"}}";
    var unstickyTopicIcon = "{{$unsticky_icon|strip|addslashes}}";
    var solveTopic = " {{gt text="Mark as solved"}}";
    var solveTopicIcon = "{{$solve_icon|strip|addslashes}}";
    var unsolveTopic = " {{gt text="Mark as unsolved"}}";
    var unsolveTopicIcon = "{{$unsolve_icon|strip|addslashes}}";
    var zChanged = "{{gt text="Changed"}}";
    var zLoadingPost = "{{gt text="Loading post"}}";
    var zDeletingPost = "{{gt text="Deleting post"}}";
    var zUpdatingPost = "{{gt text="Updating post"}}";
    var zStoringReply = "{{gt text="Storing reply"}}";
    var zPreparingPreview = "{{gt text="Preparing preview"}}";
    // ]]>
</script>
<a id="bottom" accesskey="b"></a>
{include file='user/footer.tpl'}
