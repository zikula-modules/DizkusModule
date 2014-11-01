{* CREATE ICON VARIABLES *}
{capture assign="unsubscribe_icon"}<span class="fa-stack"><i class="fa fa-ban fa-stack-2x"></i><i class="fa fa-envelope-o fa-stack-1x"></i></span>{/capture}
{capture assign="subscribe_icon"}<span class="fa-stack"><i class="fa fa-envelope-o fa-stack-1x"></i></span>{/capture}
{capture assign="unsticky_icon"}<span class="fa-stack"><i class="fa fa-ban fa-stack-2x"></i><i class="fa fa-bullhorn fa-stack-1x"></i></span>{/capture}
{capture assign="sticky_icon"}<span class="fa-stack"><i class="fa fa-bullhorn fa-stack-1x"></i></span>{/capture}
{* ------- *}

{assign var='templatetitle' value=$topic.title|safehtml}
{include file='User/header.tpl' parent=$topic.forum.forum_id}
<input id="topic_id" name="topic" type="hidden" value="{$topic.topic_id}">
{if $modvars.ZikulaDizkusModule.ajax}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.User.ViewTopic.js'}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.Tools.js'}
{/if}

{if $modvars.ZikulaDizkusModule.solved_enabled && $topic.solved eq -1}
    {assign var='topic_unsolved_style' value=' style="display:inline;"'}
    {assign var='topic_solved_style' value=' style="display:none;"'}
{elseif $modvars.ZikulaDizkusModule.solved_enabled && $topic.solved gt 0}
    {assign var='topic_unsolved_style' value=' style="display:none;"'}
    {assign var='topic_solved_style' value=' style="display:inline;"'}
{else}
    {assign var='topic_unsolved_style' value=' style="display:none;"'}
    {assign var='topic_solved_style' value=' style="display:none;"'}
{/if}
<h2>
    <span class="text-danger" id="topic_unsolved"{$topic_unsolved_style}>
        [<span class="fa fa-question">&nbsp;{gt text="Support request"}</span>]
    </span>
    <span class="text-success" id="topic_solved"{$topic_solved_style}>
        [<span class="fa fa-check">&nbsp;{gt text="Solved"}</span>]
    </span>
    <span id="edittopicsubjectbutton" title="">
        <span id="topic_title">{$topic.title|safehtml|notifyfilters:'dizkus.filter_hooks.post.filter'}</span><span id='edittopicicon' style='display:none;'>&nbsp;<i class="fa fa-pencil fa fa-red"></i></span>
    </span>
</h2>

{* add inline edit *}
{if ($modvars.ZikulaDizkusModule.ajax && ($isModerator || $topic->userAllowedToEdit()))}
    {include file='Ajax/edittopicsubject.tpl'}
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
        <a class="navbar-brand fa fa-comments" style='font-size:3em;color:mediumseagreen' href="#"></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div id="navbar-topic-collapse" class="collapse navbar-collapse">
        <ul class="nav navbar-nav navbar-left">
            {if !empty($previousTopic) and $topic.topic_id neq $previousTopic}{assign var='disabled' value=''}{else}{assign var='disabled' value=' disabled'}{/if}
            <li><a class="tooltips fa fa-chevron-left{$disabled}" title="{gt text="Previous topic"}" href="{route name='zikuladizkusmodule_user_viewtopic' topic=$previousTopic}"></a></li>
            {if !empty($nextTopic) and $topic.topic_id neq $nextTopic}{assign var='disabled' value=''}{else}{assign var='disabled' value=' disabled'}{/if}
            <li><a class="tooltips fa fa-chevron-right{$disabled}" title="{gt text="Next topic"}" href="{route name='zikuladizkusmodule_user_viewtopic' topic=$nextTopic}"></a></li>

            {if $permissions.comment}
            <li><a class="fa fa-comment-o tooltips" title="{gt text="Create a new topic"}" href="{route name='zikuladizkusmodule_user_newtopic' forum=$topic.forum.forum_id}">&nbsp;{gt text="New topic"}</a></li>
            {/if}

            {if $userloggedin}
            <li><a class="tooltips" title="{gt text="Send the posts within this topic as an e-mail message to someone"}" href="{route name='zikuladizkusmodule_user_emailtopic' topic=$topic.topic_id}">
               <span class="fa-stack">
                   <i class="fa fa-envelope-o fa-stack-1x"></i>
                   <i class="fa fa-share fa-stack-1x fa-overlay-upper-left" style='color:lightblue;'></i>
               </span>
            </a></li>
            {/if}

            <li>{printtopic_button topic_id=$topic.topic_id forum=$topic.forum}</li>

            {if $userloggedin}
            <li>
                {if $isSubscribed}
                    {route name='zikuladizkusmodule_user_changetopicstatus' action='unsubscribe' topic=$topic.topic_id assign='url'}
                    {gt text="Unsubscribe from topic" assign='msg'}
                {else}
                    {route name='zikuladizkusmodule_user_changetopicstatus' action='subscribe' topic=$topic.topic_id assign='url'}
                    {gt text="Subscribe to topic" assign='msg'}
                {/if}
                <a id="toggletopicsubscription" class="tooltips" data-action="{if $isSubscribed}unsubscribe{else}subscribe{/if}" href="{$url}" title="{$msg}">
                    {if $isSubscribed}{$unsubscribe_icon}{else}{$subscribe_icon}{/if}
                </a>
            </li>
            {/if}

        </ul>
        <ul class="nav navbar-nav navbar-right">
            {if $isModerator}
            <li>
                {if $topic.status eq 0}
                    {route name='zikuladizkusmodule_user_changetopicstatus' action='lock' topic=$topic.topic_id assign='url'}
                    {gt text="Lock topic" assign='msg'}
                    {assign var="iconclass" value="fa fa-lock"}
                {else}
                    {route name='zikuladizkusmodule_user_changetopicstatus' action='unlock' topic=$topic.topic_id assign='url'}
                    {gt text="Unlock topic" assign='msg'}
                    {assign var="iconclass" value="fa fa-unlock"}
                {/if}
                <a id="toggletopiclock" class="{$iconclass} tooltips" title="{$msg}" data-action="{if $topic.status}unlock{else}lock{/if}" href="{$url}"></a>
            </li>

            <li>
                {if $topic.sticky eq 0}
                    {route name='zikuladizkusmodule_user_changetopicstatus' action='sticky' topic=$topic.topic_id assign='url'}
                    {gt text="Give this topic 'sticky' status" assign='msg'}
                {else}
                    {route name='zikuladizkusmodule_user_changetopicstatus' action='unsticky' topic=$topic.topic_id assign='url'}
                    {gt text="Remove 'sticky' status" assign='msg'}
                {/if}
                <a id="toggletopicsticky" class="tooltips" title="{$msg}" data-action="{if $topic.sticky}unsticky{else}sticky{/if}" href="{$url}">
                    {if $topic.sticky eq 0}{$sticky_icon}{else}{$unsticky_icon}{/if}
                </a>
            </li>

            <li><a class="fa fa-arrow-right tooltips" title="{gt text="Move or join topic"}" href="{route name='zikuladizkusmodule_user_movetopic' topic=$topic.topic_id}"></a></li>
            <li><a class="fa fa-times tooltips" title="{gt text="Delete topic"}" href="{route name='zikuladizkusmodule_user_deletetopic' topic=$topic.topic_id}"></a></li>
            {/if}
            <li><a class="tooltips" title="{gt text="To bottom of page"}" href="#bottom"><i class="fa fa-chevron-circle-down"></i></a></li>
        </ul>
    </div><!-- /.navbar-collapse -->
</nav>

{* ******************************************************
* TOPIC LIST
******************************************************* *}
{pager rowcount=$pager.numitems limit=$pager.itemsperpage|default:15 posvar='start' route='zikuladizkusmodule_user_viewtopic'}

<div id="dzk_postinglist">
    <ul class="post_list">
        {counter start=0 print=false assign='post_counter'}
        {foreach key='num' item='post' from=$posts}
            {counter}
            <li class="post post_{$post.post_id}">
                {include file='User/post/single.tpl'}
            </li>
        {/foreach}
        <li id="quickreplyposting" class="hidden">&nbsp;</li>
        <li id="quickreplypreview" class="hidden">&nbsp;</li>
    </ul>
</div>

{pager rowcount=$pager.numitems limit=$pager.itemsperpage|default:15 posvar='start' route='zikuladizkusmodule_user_viewtopic'}

{* ******************************************************
* QUICK REPLY FORM
******************************************************* *}
{if ($permissions.comment eq true)}
    {include file='User/topic/quickreply.tpl'}
{/if}

{include file='User/moderatedBy.tpl' forum=$topic.forum well=true}

{include file='User/topic/translations.tpl'}

<a id="bottom" accesskey="b"></a>
{include file='User/footer.tpl'}
