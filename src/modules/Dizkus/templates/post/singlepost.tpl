{assign var='msgmodule' value=$modvars.ZConfig.messagemodule}
{assign var="onlinestyle" value="style='background-image: url(`$baseurl`modules/Dizkus/images/`$coredata.language`/icon_user_online.gif); background-position: top right; background-repeat: no-repeat;\"'"} 

{if isset($post_counter) AND isset($post_count) AND $post_counter == $post_count}<a id="bottom"></a>{/if}
<a id="pid{$post.post_id}" ></a>

{if $post.contactlist_ignored|default:0 == 1}
<div id="hidelink_posting_{$post.post_id}" class="roundedbar dzk_rounded">
    {gt text="Show hidden postings of ignored user"} <em>{$post.poster_data.uname}</em>
    <a href="javascript:void(0);" title="{gt text="Click here"}">({gt text="Click here"})</a>
</div>
{/if}

<div id="posting_{$post.post_id}" class="forum_post dzk_rounded {cycle values='post_bg1,post_bg2'}" {if $post.poster_data.online}{$onlinestyle}{/if}>
    <div class="inner">

        <div class="dzk_subcols z-clearfix">
            <div id="posting_{$post.post_id}_userinfo" class="post_author dzk_colpost_left">
                <div class="dzk_avatar">
                    <strong>{$post.poster_data.uname|profilelinkbyuname}</strong>
                    <br />
                    {useravatar uid=$post.poster_data.uid}
                    {if isset($post.poster_data.rank_image) && isset($post.poster_data.rank)}
                    <br />
                    {if $post.poster_data.rank_link neq ''}<a href="{$post.poster_data.rank_link}" title="{$post.poster_data.rank_link}">{/if}
                        <img class="userinforankimage" src="{$baseurl}{$post.poster_data.rank_image}" alt="{$post.poster_data.rank}" title="{$post.poster_data.rank_desc}" />
                    {if $post.poster_data.rank_link neq ''}</a>{/if}
                    {/if}
                </div>

                <ul>
                    {if isset($post.poster_data.rank)}
                    <li><strong>{gt text="Rank"}: </strong>{$post.poster_data.rank|safetext}</li>
                    {/if}
                    <li><strong>{gt text="Registered"}: </strong>{$post.poster_data.user_regdate|dateformat:'datebrief'}</li>
                    {if !$post.poster_data.online}
                    <li><strong>{gt text="Last visit"}: </strong>{$post.poster_data.user_lastvisit|dateformat:'datebrief'}</li>
                    {/if}
                    <li><strong>{gt text="Posts"}: </strong>{$post.poster_data.user_posts}</li>
                    {if $coredata.logged_in eq true}
                    <li>
                        {* image link to profile deactivated because of a bug in the core *}
                        {* $post.poster_data.uname|profilelinkbyuname:'':"`$baseurl`modules/Dizkus/images/icon_post_profile.gif" *}
                        {if $msgmodule}
                        <a href="{modurl modname=$msgmodule func="user" func="newpm" uid=$post.poster_data.uid}">{img modname='Dizkus' src='icon_post_pn.gif' __alt='Send a private message'}</a>
                        {/if}
                        {if isset($topic) AND $post.poster_data.moderate eq true AND $post.poster_data.seeip eq true}
                        <a title="{gt text="View IP address"}" href="{modurl modname='Dizkus' type=user func=topicadmin mode=viewip post=$post.post_id topic=$topic.topic_id}">{img modname='Dizkus' src='icon_post_ip.gif' __alt='View IP address'}</a>
                        {/if}
                        &nbsp;
                    </li>
                    {/if}
                </ul>
            </div>

            <div class="postbody dzk_colpost_right">
                <div class="postinfo">
                    {if isset($topic)}<a class="linktopostlink tooltips" href="{modurl modname='Dizkus' type='user' func='viewtopic' topic=$post.topic_id start=$topic.start}#pid{$post.post_id}" title="{gt text="Link to this post"}">{img modname='Dizkus' src='target.gif' __alt='Link to this post'}</a>{/if}
                    <strong>{gt text="Posted"}: </strong>{$post.posted_unixtime|dateformat:'datetimebrief'}
                </div>
                <div class="content" id="postingtext_{$post.post_id}">
                    {$post.post_text|safehtml|notifyfilters:'dizkus.filter_hooks.message.filter'}
                </div>
            </div>

            <div class="postlink">
                {if !isset($preview) OR $preview neq true}
                <div class="dzk_colpost_right">
                    <ul id="postingoptions_{$post.post_id}" class="javascriptpostingoptions hidden linklist z-clearfix" style="float:right;">
                        {if $post.poster_data.moderate eq true}
                        {if (isset($num) AND $num neq 0) OR (isset($topic) AND $topic.start neq 0)}
                        <li><a class="movepostlink tooltips" title="{gt text="Move post"}" href="{modurl modname='Dizkus' type=user func=movepost post=$post.post_id}">{img modname='Dizkus' src='icon_post_move.gif' __alt='Move post' }</a></li>
                        <li><a class="splittopiclink tooltips" title="{gt text="Split topic"}" href="{modurl modname='Dizkus' type=user func=splittopic post=$post.post_id}">{img modname='Dizkus' src='icon_post_split.gif' __alt='Split topic' }</a></li>
                        {/if}
                        {/if}

                        {if isset($topic) AND $topic.topic_status neq 1}
                        {if $post.poster_data.reply eq true}
                        <li><a class="quotepostlink tooltips" id="quotebutton_{$post.post_id}" title="{gt text="Quote post or selection"}" href="javascript:void(0);">{img modname='Dizkus' src='icon_post_quote.gif' __alt='Quote'}</a></li>
                        {/if}
                        {if $post.poster_data.edit eq 1}
                        <li><a class="editpostlink tooltips" id="editbutton_{$post.post_id}" title="{gt text="Edit post"}" href="javascript:void(0);">{img modname='Dizkus' src='icon_post_edit.gif' __alt='Edit'}</a></li>
                        {/if}
                        {elseif isset($topic)}
                        <li>{img modname='Dizkus' src="icon_post_close.gif" class="tooltips" __alt="Topic locked" }</li>
                        {/if}
                        <li><a class="notifymoderatorlink tooltips" href="{modurl modname='Dizkus' type=user func=report post=$post.post_id}" title="{gt text="Notify moderator about this posting"}">{img modname='Dizkus' src='icon_topic_mod.gif' __alt='Notify moderator' }</a></li>
                        <li><a class="dzk_notextdecoration tooltips" title="{gt text="Top"}" href="#top">&nbsp;{img modname='Dizkus' src="icon_up.gif" __alt="Top" }</a></li>
                    </ul>
                    <noscript>
                        <ul class="nonjavascriptpostingoptions linklist z-clearfix" style="float:right;">
                            {if $post.poster_data.moderate eq true}
                            {if (isset($num) AND $num neq 0) OR (isset($topic) AND $topic.start neq 0)}
                            <li><a class="dzk_arrow movepostlink" title="{gt text="Move post"}" href="{modurl modname='Dizkus' type=user func=movepost post=$post.post_id}">{gt text="Move post"}</a></li>
                            <li><a class="dzk_arrow splittopiclink" title="{gt text="Split topic"}" href="{modurl modname='Dizkus' type=user func=splittopic post=$post.post_id}">{gt text="Split topic"}</a></li>
                            {/if}
                            {/if}

                            {if isset($topic) AND $topic.topic_status neq 1}
                            {if $post.poster_data.reply eq true}
                            <li><a class="dzk_arrow quotepostlink" title="{gt text="Quote post or selection"}" href="{modurl modname='Dizkus' type=user func=reply post=$post.post_id}">{gt text="Quote"}</a></li>
                            {/if}
                            {if $post.poster_data.edit eq 1}
                            <li><a class="dzk_arrow editpostlink" title="{gt text="Edit post"}" href="{modurl modname='Dizkus' type=user func=editpost post=$post.post_id}">{gt text="Edit"}</a></li>
                            {/if}
                            {elseif isset($topic)}
                            <li>{img modname='Dizkus' src=icon_post_close.gif __alt="Topic locked" }</li>
                            {/if}
                            <li><a class="dzk_arrow notifymoderatorlink" href="{modurl modname='Dizkus' type=user func=report post=$post.post_id}" title="{gt text="Notify moderator about this posting"}">{gt text="Notify moderator"}</a></li>
                        </ul>
                    </noscript>
                </div>
                {/if}
            </div>
        </div>

    </div>
</div>

{if $post.contactlist_ignored|default:0 == 1}
<script type="text/javascript">
    // <![CDATA[
    $('posting_{{$post.post_id}}').toggle();
    // ]]>
</script>
{/if}
