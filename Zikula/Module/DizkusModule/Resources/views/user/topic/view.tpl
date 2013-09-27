{assign var='templatetitle' value=$topic.title}
{include file='user/header.tpl' parent=$topic.forum.forum_id}
<input id="topic_id" name="topic" type="hidden" value="{$topic.topic_id}">
{if $modvars.ZikulaDizkusModule.ajax}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.User.ViewTopic.js'}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.Tools.js'}
{/if}

<h2>
    <span id="edittopicsubjectbutton" title="">
        <span id="topic_solved" {if !$topic.solved or !$modvars.ZikulaDizkusModule.solved_enabled}class="z-hide"{/if}>
            [{gt text="Solved"}]
        </span>
        <span id="topic_title">{$topic.title|safehtml|notifyfilters:'dizkus.filter_hooks.post.filter'}</span>
        {icon id="edittopicicon" type="xedit" size="extrasmall" class="z-hide"}
    </span>
</h2>

{* add inline edit *}
{usergetvar name='uid' assign='currentUser'}
{if ($modvars.ZikulaDizkusModule.ajax && ($permissions.moderate eq 1 || $topic->userAllowedToEdit()))}
    {include file='ajax/edittopicsubject.tpl'}
    <script type="text/javascript">
        jQuery(document).ready(function() {
            // toggle visibility of edit icon for topic title
            jQuery('#edittopicsubjectbutton').hover(
                    function() {jQuery('#edittopicicon').removeClass('z-hide');},
                    function() {jQuery('#edittopicicon').addClass('z-hide');}
            );
            jQuery('#edittopicsubjectbutton').addClass('editabletopicheader tooltips').attr('title', '{{gt text="Click to edit"}}');
            jQuery('#edittopicsubjectbutton').click(function() {jQuery('#topicsubjectedit_editor').removeClass('z-hide')}
            );
            jQuery('#topicsubjectedit_cancel').click(function() {jQuery('#topicsubjectedit_editor').addClass('z-hide')}
            );
            jQuery("#topicsubjectedit_save").click(changeTopicTitle);
        });
    </script>
{/if}


{userloggedin assign='userloggedin'}

<div class="dzk_topicoptions roundedbar dzk_rounded" id="topic_{$topic.topic_id}">
    <div class="inner">
        <div id="dzk_javascriptareatopic">
            <ul class="dzk_topicoptions linklist z-clearfix">
                {if !empty($previousTopic) and $topic.topic_id neq $previousTopic}
                    <li><a class="tooltips icon-chevron-left" title="{gt text="Previous topic"}" href="{modurl modname=$module type='user' func='viewtopic' topic=$previousTopic}">&nbsp;</a></li>
                    {/if}

                {if $permissions.comment}
                    <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Create a new topic"}" href="{modurl modname=$module type='user' func='newtopic' forum=$topic.forum.forum_id}">&nbsp;{gt text="New topic"}</a></li>
                    {/if}

                {if $userloggedin}
                    <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Send the posts within this topic as an e-mail message to someone"}" href="{modurl modname=$module type='user' func='emailtopic' topic=$topic.topic_id}">&nbsp;{gt text="Send as e-mail"}</a></li>
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
                        <a id="toggletopicsubscription" class="tooltips icon-chevron-sign-right" data-status="{if $isSubscribed}1{else}0{/if}" href="{$url}" title="{$msg}">&nbsp;{$msg}</a>
                    </li>
                    {if ($modvars.ZikulaDizkusModule.solved_enabled|default:0) && (($permissions.moderate eq 1) || ($currentUser == $topic.poster.user.uid))}
                        <li>
                            {if $topic.solved}
                                {modurl modname=$module type='user' func='changeTopicStatus' action='unsolve' topic=$topic.topic_id assign='url'}
                                {gt text="Mark as unsolved" assign='msg'}
                            {else}
                                {modurl modname=$module type='user' func='changeTopicStatus' action='solve' topic=$topic.topic_id assign='url'}
                                {gt text="Mark as solved" assign='msg'}
                            {/if}
                            <a id="toggletopicsolve" class="tooltips icon-chevron-sign-right" data-status="{if $topic.solved}1{else}0{/if}" href="{$url}" title="{$msg}">&nbsp;{$msg}</a>
                        </li>
                    {/if}
                {/if}

                {if !empty($nextTopic) and $topic.topic_id neq $nextTopic}
                    <li>
                        <a class="tooltips icon-chevron-right" title="{gt text="Next topic"}" href="{modurl modname=$module type='user' func='viewtopic' topic=$nextTopic}">
                            &nbsp;
                        </a>
                    </li>
                {/if}
            </ul>

            {if $permissions.moderate eq 1}
                <ul class="dzk_topicoptions linklist z-clearfix">
                    <li>
                        {if $topic.status eq 0}
                            {modurl modname=$module type='user' func='changeTopicStatus' action='lock' topic=$topic.topic_id assign='url'}
                            {gt text="Lock topic" assign='msg'}
                        {else}
                            {modurl modname=$module type='user' func='changeTopicStatus' action='unlock' topic=$topic.topic_id assign='url'}
                            {gt text="Unlock topic" assign='msg'}
                        {/if}
                        <a id="toggletopiclock" class="tooltips icon-chevron-sign-right" title="{$msg}" data-status="{if $topic.status}1{else}0{/if}" href="{$url}">&nbsp;{$msg}</a>
                    </li>

                    <li>
                        {if $topic.sticky eq 0}
                            {modurl modname=$module type='user' func='changeTopicStatus' action='sticky' topic=$topic.topic_id assign='url'}
                            {gt text="Give this topic 'sticky' status" assign='msg'}
                        {else}
                            {modurl modname=$module type='user' func='changeTopicStatus' action='unsticky' topic=$topic.topic_id assign='url'}
                            {gt text="Remove 'sticky' status" assign='msg'}
                        {/if}
                        <a id="toggletopicsticky" class="tooltips icon-chevron-sign-right" title="{$msg}" data-status="{if $topic.sticky}1{else}0{/if}" href="{$url}">&nbsp;{$msg}</a>
                    </li>

                    <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Move topic"}" href="{modurl modname=$module type='user' func='movetopic' topic=$topic.topic_id}">&nbsp;{gt text="Move topic"}</a></li>
                    <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Delete topic"}" href="{modurl modname=$module type='user' func='deletetopic' topic=$topic.topic_id}">&nbsp;{gt text="Delete topic"}</a></li>
                </ul>
            {/if}
            <span class="z-clearfix dzk_bottomlink"><a class="dzk_notextdecoration" title="{gt text="Bottom"}" href="#bottom"><i class=' icon-chevron-sign-down'></i></a></span>
        </div>

    </div>
</div>

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

{if ($permissions.comment eq true)}
    <a id="reply">
        <div id="dzk_quickreply" class="panel panel-info"{if $topic.status eq 1} style='display:none'{/if}>
            <div class="panel-heading">
                <h3>{gt text="Quick reply"}</h3>
            </div>
            <div class="panel-body">
                <div class="dzk_subcols z-clearfix">
                    <form id="quickreplyform" class="dzk_form" action="{modurl modname=$module type='user' func='reply'}" method="post" enctype="multipart/form-data">
                        <div>
                            <input type="hidden" id="forum" name="forum" value="{$topic.forum.forum_id}" />
                            <input type="hidden" id="topic" name="topic" value="{$topic.topic_id}" />
                            <input type="hidden" id="quote" name="quote" value="" />
                            <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
                            <div class="post_text_wrap">
                                <div class="post_text">
                                    <div id="dizkusinformation"></div>
                                    <textarea id="message" name="message" cols="10" rows="60"></textarea>

                                    {if $modvars.ZikulaDizkusModule.striptags == 'yes'}
                                        <p class='text-info'>{gt text="No HTML tags allowed (except inside [code][/code] tags)"}</p>
                                    {/if}

                                    {notifydisplayhooks eventname='dizkus.ui_hooks.post.ui_edit' id=null}
                                    <div class="dzk_subcols z-clearfix">
                                        <div id="quickreplyoptions" class="dzk_col_left">
                                            <ul>
                                                <li><strong>{gt text="Options"}</strong></li>
                                                        {if $userloggedin}
                                                    <li>
                                                        <input type="checkbox" id="attach_signature" name="attach_signature" checked="checked" value="1" />
                                                        <label for="attach_signature">{gt text="Attach my signature"}</label>
                                                    </li>
                                                    <li>
                                                        <input type="checkbox" id="subscribe_topic" name="subscribe_topic" checked="checked" value="1" />
                                                        <label for="subscribe_topic">{gt text="Notify me when a reply is posted"}</label>
                                                    </li>
                                                {/if}
                                                <li id="quickreplybuttons">
                                                    <input id="btnSubmitQuickReply" class="btn btn-success" type="submit" name="submit" value="{gt text="Submit"}" />
                                                    <input id="btnPreviewQuickReply" class="btn btn-primary" type="submit" name="preview" value="{gt text="Preview"}" />
                                                    <button id="btnCancelQuickReply" class="btn btn-danger" type="submit" name="cancel">{gt text="Cancel"}</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="post_footer"></div>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <div id="dzk_displayhooks">
            {notifydisplayhooks eventname='dizkus.ui_hooks.topic.ui_view' id=$topic.topic_id}
        </div>

    {/if}


    {include file='user/moderatedBy.tpl' forum=$topic.forum}

    <script type="text/javascript">
        // @TODO Replace by Zikula.__() and remove this vars.
        // <![CDATA[
        var subscribeTopic = " {{gt text='Subscribe to topic'}}";
        var unsubscribeTopic = " {{gt text='Unsubscribe from topic'}}";
        var lockTopic = " {{gt text='Lock topic'}}";
        var unlockTopic = " {{gt text='Unlock topic'}}";
        var stickyTopic = " {{gt text="Give this topic 'sticky' status"}}";
        var unstickyTopic = " {{gt text="Remove 'sticky' status"}}";
        var solveTopic = " {{gt text="Mark as solved"}}";
        var unsolveTopic = " {{gt text="Mark as unsolved"}}";
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
