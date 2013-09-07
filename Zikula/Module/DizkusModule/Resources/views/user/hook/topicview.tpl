{browserhack condition="if gt IE 6"}
<script type="text/javascript" src="{$baseurl}modules/Dizkus/javascript/niftycube.js"></script>
<script type="text/javascript">
    // <![CDATA[
    document.observe('dom:loaded', function() {
        Nifty("div.dzk_rounded", "transparent");
    });
    // ]]>
</script>
{/browserhack}

<div id="dizkus">

    <input id="topic_id" name="topic" type="hidden" value="{$topic.topic_id}">
    {if $modvars.ZikulaDizkusModule.ajax}
        {* JS files not loaded via header like other templates*}
        {pageaddvar name='javascript' value='jQuery'}
        {pageaddvar name='javascript' value='modules/zikula-dizkus/Zikula/Module/DizkusModule/Resources/public/js/Zikula.Dizkus.User.ViewTopic.js'}
        {pageaddvar name='javascript' value='modules/zikula-dizkus/Zikula/Module/DizkusModule/Resources/public/js/Zikula.Dizkus.Tools.js'}
    {/if}
    {pageaddvar name="jsgettext" value="module_dizkus_js:Dizkus"}
    {pageaddvar name='javascript' value='zikula'}

    {userloggedin assign='userloggedin'}

    <h2>{gt text="%s Comment" plural="%s Comments" tag1=$pager.numitems-1 count=$pager.numitems-1}</h2>

    {pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}

    <div id="dzk_postinglist">
        <ul>
            {counter start=0 print=false assign='post_counter'}
            {foreach key='num' item='post' from=$posts}
                {if !$post.isFirstPost}
                    {counter}
                    <li class="post_{$post.post_id}">
                        {include file='user/post/single.tpl'}
                    </li>
                {/if}
            {/foreach}
            <li id="quickreplyposting" class="hidden">&nbsp;</li>
            <li id="quickreplypreview" class="hidden">&nbsp;</li>
        </ul>
    </div>

    {pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}

    {if ($permissions.comment eq true)}
        <a id="reply">
            <div id="dzk_quickreply" class="forum_post {cycle values='post_bg1,post_bg2'} dzk_rounded"{if $topic.status eq 1} style='display:none'{/if}>
                <div class="inner">
                    <div class="dzk_subcols z-clearfix">
                        <form id="quickreplyform" class="dzk_form" action="{modurl modname=$module type='user' func='reply'}" method="post" enctype="multipart/form-data">
                            <div>
                                <input type="hidden" id="forum" name="forum" value="{$topic.forum.forum_id}" />
                                <input type="hidden" id="topic" name="topic" value="{$topic.topic_id}" />
                                <input type="hidden" id="quote" name="quote" value="" />
                                <input type="hidden" id="returnurl" name="returnurl" value="{$returnurl}" />
                                <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
                                <div class="post_header">
                                    <label for="message" class="quickreply_title" style="display:block;">{gt text="Quick reply"}</label>
                                </div>
                                <div class="post_text_wrap">
                                    <div class="post_text">
                                        <div id="dizkusinformation"></div>
                                        <textarea id="message" name="message" cols="10" rows="60"></textarea>

                                        {if $modvars.ZikulaDizkusModule.striptags == 'yes'}
                                            <p>{gt text="No HTML tags allowed (except inside [code][/code] tags)"}</p>
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
                                                    <li id="quickreplybuttons" class="z-buttons">
                                                        <input id="btnSubmitQuickReply" class="z-bt-ok z-bt-small" type="submit" name="submit" value="{gt text="Submit"}" />
                                                        <input id="btnPreviewQuickReply" class="z-bt-preview z-bt-small" type="submit" name="preview" value="{gt text="Preview"}" />
                                                        {button type="button" id="btnCancelQuickReply" class="dzk_detachable z-bt-small hidden" src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel" __text="Cancel"}
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
            var subscribeTopic = "{{gt text='Subscribe to topic'}}";
            var unsubscribeTopic = "{{gt text='Unsubscribe from topic'}}";
            var lockTopic = "{{gt text='Lock topic'}}";
            var unlockTopic = "{{gt text='Unlock topic'}}";
            var stickyTopic = "{{gt text="Give this topic 'sticky' status"}}";
            var unstickyTopic = "{{gt text="Remove 'sticky' status"}}";
            var solveTopic = "{{gt text="Mark as solved"}}";
            var unsolveTopic = "{{gt text="Mark as unsolved"}}";
            // ]]>
        </script>

</div>