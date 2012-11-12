{assign var='templatetitle' value=$topic.topic_title}
{include file='user/header.tpl' parent=$topic.forum_id}

<h2>
{usergetvar name='uid' assign='currentUser'}
{if $permissions.moderate eq 1 || $topic.topic_poster eq $currentUser}
<span class="editabletopicheader tooltips" id="edittopicsubjectbutton_{$topic.topic_id}" title="{gt text="Click to edit"}">
        {if $topic.solved eq 1 and $modvars.Dizkus.solved_enabled}
            {gt text="[Solved]"}
        {/if}
        {$topic.topic_title|safetext}
    </span>
{else}
<span class="noneditabletopicheader">
    {if $topic.solved eq 1 and $modvars.Dizkus.solved_enabled}
        {gt text="[Solved]"}
    {/if}
    {$topic.topic_title|safetext}
</span>
<a class="dzk_notextdecoration" title="{gt text="Bottom"}" href="#bottom">&nbsp;{img modname='Dizkus' src="icon_bottom.gif" __alt="Bottom"}</a>
{/if}
</h2>

{userloggedin assign='userloggedin'}

<div class="dzk_topicoptions roundedbar dzk_rounded" id="topic_{$topic.topic_id}">
    <div class="inner">
        <div id="dzk_javascriptareatopic">
            <ul class="dzk_topicoptions linklist z-clearfix">
                {*if $topic.prev_topic_id and $topic.topic_id neq $topic.prev_topic_id }
                <li><a class="dzk_arrow previoustopiclink tooltips" title="{gt text="Previous topic"}" href="{modurl modname='Dizkus' type=user func=viewtopic topic=$topic.prev_topic_id}">&nbsp;</a></li>
                {/if*}

                {if $permissions.comment}
                <li><a class="dzk_arrow newtopiclink tooltips" title="{gt text="Create a new topic"}" href="{modurl modname='Dizkus' type=user func=newtopic forum=$topic.forum_id}">{gt text="New topic"}</a></li>
                {/if}

                {if $userloggedin}
                <li><a class="dzk_arrow mailtolink tooltips" title="{gt text="Send the posts within this topic as an e-mail message to someone"}" href="{modurl modname='Dizkus' type=user func=emailtopic topic=$topic.topic_id}">{gt text="Send as e-mail"}</a></li>
                {/if}

                {*<li>{printtopic_button topic_id=$topic.topic_id cat_id=$topic.cat_id forum_id=$topic.forum_id}*}</li>

                {if $userloggedin}
                <li>
                    {if $isSubscribed}
                    <a id="toggletopicsubscriptionbutton_{$topic.topic_id}_subscribed" class="dzk_arrow tooltips" href="{modurl modname='Dizkus' type='user' func='changeTopicStatus' action='unsubscribe' topic=$topic.topic_id}" title="{gt text="Unsubscribe from topic"}">{gt text="Unsubscribe from topic"}</a>
                    {else}
                    <a id="toggletopicsubscriptionbutton_{$topic.topic_id}_unsubscribed" class="dzk_arrow tooltips" href="{modurl modname='Dizkus' type='user' func='changeTopicStatus' action='subscribe' topic=$topic.topic_id}">{gt text="Subscribe to topic"}</a>
                    {/if}
                </li>
                {if $modvars.Dizkus.solved_enabled|default:0}
                <li>
                    {if $topic.solved eq 0}
                    <a class="dzk_arrow tooltips" href="{modurl modname='Dizkus' type='user' func='changeTopicStatus' action='solved' topic=$topic.topic_id}" title="{gt text="Mark as solved"}">
                        {gt text="Mark as solved"}
                    </a>
                    {else}
                    <a class="dzk_arrow tooltips" href="{modurl modname='Dizkus' type='user' func='changeTopicStatus' action='unsolved' topic=$topic.topic_id}" title="{gt text="Mark as unsolved"}">
                        {gt text="Mark as unsolved"}
                    </a>
                    {/if}
                </li>
                {/if}
                {/if}

                {*if $topic.next_topic_id and $topic.topic_id neq $topic.next_topic_id}
                <li>
                    <a class="dzk_arrow nexttopiclink tooltips" title="{gt text="Next topic"}" href="{modurl modname='Dizkus' type=user func=viewtopic topic=$topic.next_topic_id}">
                        &nbsp;
                    </a>
                </li>
                {/if*}
            </ul>

            {if $permissions.moderate eq 1}
            <ul class="dzk_topicoptions linklist z-clearfix">
                <li>
                    {if $topic.topic_status eq 0}
                    <a id="toggletopiclockbutton_{$topic.topic_id}_unlocked" class="dzk_arrow tooltips" title="{gt text="Lock topic"}" href="{modurl modname='Dizkus' type='user' func='changeTopicStatus' action='lock' topic=$topic.topic_id}">{gt text="Lock topic"}</a>
                    {else}
                    <a id="toggletopiclockbutton_{$topic.topic_id}_locked" class="dzk_arrow tooltips" title="{gt text="Unlock topic"}" href="{modurl modname='Dizkus' type='user' func='changeTopicStatus' action='unlock' topic=$topic.topic_id}">{gt text="Unlock topic"}</a>
                    {/if}
                </li>

                <li>
                    {if $topic.sticky eq 0}
                    <a id="toggletopicstickybutton_{$topic.topic_id}_unsticky" class="dzk_arrow tooltips" title="{gt text="Give this topic 'sticky' status"}" href="{modurl modname='Dizkus' type='user' func='changeTopicStatus' action='sticky' topic=$topic.topic_id}">{gt text="Give this topic 'sticky' status"}</a>
                    {else}
                    <a id="toggletopicstickybutton_{$topic.topic_id}_sticky" class="dzk_arrow tooltips" title="{gt text="Remove 'sticky' status"}" href="{modurl modname='Dizkus' type='user' func='changeTopicStatus' action='unsticky' topic=$topic.topic_id}">{gt text="Remove 'sticky' status"}</a>
                    {/if}
                </li>

                <li><a class="dzk_arrow movetopiclink tooltips" title="{gt text="Move topic"}" href="{modurl modname='Dizkus' type=user func=movetopic topic=$topic.topic_id}">{gt text="Move topic"}</a></li>
                <li><a class="dzk_arrow deletetopiclink tooltips" title="{gt text="Delete topic"}" href="{modurl modname='Dizkus' type=user func=deletetopic topic=$topic.topic_id}">{gt text="Delete topic"}</a></li>
            </ul>
            {/if}
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

{if ($topic.topic_status neq 1) and ($permissions.comment eq true)}
<a id="reply">
<div id="dzk_quickreply" class="forum_post {cycle values='post_bg1,post_bg2'} dzk_rounded">
    <div class="inner">
        <div class="dzk_subcols z-clearfix">
            <form id="quickreplyform" class="dzk_form" action="{modurl modname='Dizkus' type='user' func='reply'}" method="post" enctype="multipart/form-data">
                <div>
                    <input type="hidden" id="forum" name="forum" value="{$topic.forum_id}" />
                    <input type="hidden" id="topic" name="topic" value="{$topic.topic_id}" />
                    <input type="hidden" id="quote" name="quote" value="" />
                    <input type="hidden" id="authid" name="authid" value="" />
                    <div class="post_header">
                        <label for="message" class="quickreply_title" style="display:block;">{gt text="Quick reply"}</label>
                    </div>
                    <div class="post_text_wrap">
                        <div class="post_text">
                            <div id="dizkusinformation"></div>
                            {notifydisplayhooks eventname='dizkus.ui_hooks.editor.display_view' id='message'}
                            <textarea id="message" name="message" cols="10" rows="60"></textarea>

                            {if isset($hooks.MediaAttach)}{$hooks.MediaAttach}{/if}
                            {if $modvars.Dizkus.striptags == 'yes'}
                            <p>{gt text="No HTML tags allowed (except inside [code][/code] tags)"}</p>
                            {/if}

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
                                        <li id="quickreplybuttons" class="z-buttons hidden">
                                            {button id="btnCreateQuickReply" class="dzk_detachable z-bt-small" src=button_ok.png set=icons/extrasmall __alt="Submit" __title="Submit" __text="Submit"}
                                            {button id="btnPreviewQuickReply" class="dzk_detachable z-bt-small" src=xeyes.png set=icons/extrasmall __alt="Preview" __title="Preview" __text="Preview"}
                                            {button id="btnCancelQuickReply" class="dzk_detachable z-bt-small" src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel" __text="Cancel"}
                                        </li>
                                        <li id="nonajaxquickreplybuttons" class="z-buttons">
                                            <input class="z-bt-ok z-bt-small" type="submit" name="submit" value="{gt text="Submit"}" />
                                            <input class="z-bt-preview z-bt-small" type="submit" name="preview" value="{gt text="Preview"}" />
                                            <input class="z-bt-cancel z-bt-small" type="submit" name="reset" value="{gt text="Cancel"}" />
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

{mediaattach_fileuploads objectid=$topic.topic_id}
<div id="dzk_displayhooks">
    {if isset($hooks.Ratings)}{$hooks.Ratings}{/if}
</div>

{/if}


{include file='user/moderatedBy.tpl' mods=$topic.forum_mods}

<script type="text/javascript">
    // <![CDATA[
    var storingReply = "{{gt text='Storing reply...'}}";
    var preparingPreview = "{{gt text='Preparing preview...'}}";
    var storingPost = "{{gt text='Storing post...'}}";
    var deletingPost = "{{gt text='Deleting post...'}}";
    var updatingPost = "{{gt text='Updating post...'}}";
    var statusNotChanged = "{{gt text='Unchanged'}}";
    var statusChanged = "{{gt text='Changed'}}";
    var subscribeTopic = "{{gt text='Subscribe to topic'}}";
    var unsubscribeTopic = "{{gt text='Unsubscribe from topic'}}";
    var lockTopic = "{{gt text='Lock topic'}}";
    var unlockTopic = "{{gt text='Unlock topic'}}";
    var stickyTopic = "{{gt text="Give this topic 'sticky' status"}}";
    var unstickyTopic = "{{gt text="Remove 'sticky' status"}}";
    // ]]>
</script>

{include file='user/footer.tpl'}
