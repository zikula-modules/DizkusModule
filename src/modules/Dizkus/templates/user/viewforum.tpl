{include file='user/header.tpl'}

{if $forum.forum_desc <> ''}
<p class='ctheme-description'>{$forum.forum_desc|safehtml}</p>
{/if}

{if $forum.access_moderate eq true or $forum.access_comment or $coredata.logged_in}

<div id="dzk_maincategorylist">

    {if count($subforums) > 0}
    <div class="forabg dzk_rounded dzk_marginbottom">
        <div class="inner">
            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt><span>{gt text='Sub Forums'}</span></dt>
                        <dd class="topics"><span>{gt text="Topics"}</span></dd>
                        <dd class="posts"><span>{gt text="Posts"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Last post"}</span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item='subforum' from=$subforums}
                <li class="row">
                    <dl class="icon">
                        <dt {if $subforum.new_posts == true}class='new-posts'{else}class='no-new-posts'{/if} >
                            <a title="{gt text="Go to subforum"} '{$subforum.forum_name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$subforum.forum_id}">{$subforum.forum_name|safetext}</a><br />
                            {if $subforum.forum_desc neq ''}{$subforum.forum_desc|safehtml}<br />{/if}
                        </dt>
                        <dd class="topics">{$subforum.forum_topics|safetext}</dd>
                        <dd class="posts">{$subforum.forum_posts|safetext}</dd>
                        <dd class="lastpost">
                            {if isset($subforum.last_post_data)}
                            <span>
                                {gt text="Last post by %s" tag1=$subforum.last_post_data.name|profilelinkbyuname}<br />
                                {gt text="Written on %s:" tag1=$subforum.last_post_data.unixtime|dateformat:'datetimebrief'}
                                {if $subforum.last_post_data.url_anchor neq ''}
                                <a class="latesttopicimage tooltips" href="{$subforum.last_post_data.url_anchor|safetext}" title="{gt text='View latest post: %s' tag1=$subforum.last_post_data.subject|safehtml|truncate:70}">{$subforum.last_post_data.subject|safetext|truncate:70}</a>
                                {/if}
                            </span>
                            {else}
                            <span>{$subforum.last_post|default:'&nbsp;'}</span>
                            {/if}
                        </dd>
                    </dl>
                </li>
                {/foreach}
            </ul>

        </div>
    </div>
    {/if}

    <div class="roundedbar dzk_rounded">
        <div class="inner">
            <ul id="dzk_javascriptareaforum" class="hidden linklist z-clearfix">
                {* Moderate *}
                {if $forum.access_comment}
                <li><a class="dzk_arrow newtopiclink tooltips" title="{gt text="Start a new topic"}" href="{modurl modname='Dizkus' type=user func=newtopic forum=$forum.forum_id}">{gt text="New topic"}</a></li>
                {/if}

                {if $coredata.logged_in}
                <li>
                    {if $forum.is_subscribed eq 0}
                    <a id="toggleforumsubscriptionbutton_{$forum.forum_id}" class="dzk_arrow tooltips" href="javascript:void(0);" title="{gt text="Subscribe to forum"}">{gt text="Subscribe to forum"}</a>
                    {else}
                    <a id="toggleforumsubscriptionbutton_{$forum.forum_id}" class="dzk_arrow tooltips" href="javascript:void(0);" title="{gt text="Unsubscribe from forum"}">{gt text="Unsubscribe from forum"}</a>
                    {/if}
                </li>
                {if $coredata.Dizkus.favorites_enabled eq "yes"}
                <li>
                    {if $forum.is_favorite eq 0}
                    <a id="toggleforumfavouritebutton_{$forum.forum_id}" class="dzk_arrow tooltips" href="javascript:void(0);" title="{gt text="Add forum to favourites"}">{gt text="Add forum to favourites"}</a>
                    {else}
                    <a id="toggleforumfavouritebutton_{$forum.forum_id}" class="dzk_arrow tooltips" href="javascript:void(0);" title="{gt text="Remove forum from favourites"}">{gt text="Remove forum from favourites"}</a>
                    {/if}
                </li>
                {/if}
                {/if}

                {if $forum.access_moderate eq true}
                <li><a class="dzk_arrow moderatelink tooltips" title="{gt text="Moderate"}" href="{modurl modname='Dizkus' type=user func=moderateforum forum=$forum.forum_id}">{gt text="Moderate"}</a></li>
                {/if}
            </ul>

            <noscript>
                <ul id="dzk_nonjavascriptareaforum" class="linklist z-clearfix">
                    {* Moderate *}
                    {if $forum.access_comment}
                    <li><a class="dzk_arrow newtopiclink" title="{gt text="Start a new topic"}" href="{modurl modname='Dizkus' type=user func=newtopic forum=$forum.forum_id}">{gt text="New topic"}</a></li>
                    {/if}

                    {if $forum.access_moderate eq true}
                    <li><a class="dzk_arrow moderatelink" title="{gt text="Moderate"}" href="{modurl modname='Dizkus' type=user func=moderateforum forum=$forum.forum_id}">{gt text="Moderate"}</a></li>
                    {/if}

                    {if $coredata.logged_in}
                    {if $forum.is_subscribed eq 0}
                    <li><a class="dzk_arrow subscribelink" href="{modurl modname="Dizkus" type="user" func="prefs" act="subscribe_forum" forum=$forum.forum_id}" title="{gt text="Subscribe to forum"}">{gt text="Subscribe to forum"}</a></li>
                    {else}
                    <li><a class="dzk_arrow unsubscribelink" href="{modurl modname="Dizkus" type="user" func="prefs" act="unsubscribe_forum" forum=$forum.forum_id}" title="{gt text="Unsubscribe from forum"}">{gt text="Unsubscribe from forum"}</a></li>
                    {/if}
                    {if $coredata.Dizkus.favorites_enabled eq "yes"}
                    {if $forum.is_favorite eq 0}
                    <li><a class="dzk_arrow addfavoritelink" href="{modurl modname="Dizkus" type="user" func="prefs" act="add_favorite_forum" forum=$forum.forum_id}" title="{gt text="Add forum to favourites"}">{gt text="Add forum to favourites"}</a></li>
                    {else}
                    <li><a class="dzk_arrow removefavoritelink" href="{modurl modname="Dizkus" type="user" func="prefs" act="remove_favorite_forum" forum=$forum.forum_id}" title="{gt text="Remove forum from favourites"}">{gt text="Remove forum from favourites"}</a></li>
                    {/if}
                    {/if}
                    {/if}
                </ul>
            </noscript>
        </div>
    </div>
    {/if}
</div>

{if $forum.topics}

{dzkpager total=$forum.forum_topics}
{mediaattach_attachicon topics=$forum.topics assign='uploadtopicids'}

<div class="forumbg dzk_rounded">
    <div class="inner">

        <ul class="topiclist">
            <li class="dzk_header">
                <dl>
                    <dt><span>{gt text="Topic"}</span></dt>
                    <dd class="posts"><span>{gt text="Replies"}</span></dd>
                    <dd class="views"><span>{gt text="Views"}</span></dd>
                    <dd class="lastpost"><span>{gt text="Last post"}</span></dd>
                </dl>
            </li>
        </ul>

        <ul class="topiclist forums">

            {assign var='fstarted' value='0'}
            {assign var='topicstarted'  value='0'}

            {foreach item=topic from=$forum.topics}

            {assign var='showattachment' value='0'}
            {foreach item='hasuploads' key='topicid' from=$uploadtopicids}
            {if $topicid eq $topic.topic_id && $hasuploads eq 1}
            {assign var='showattachment' value='1'}
            {/if}
            {/foreach}

            <li class="row">
                <dl class="icon {if $topic.sticky eq 1}dzk_sticky{/if}">
                    <dt class='ctheme-topic-title'>
                        {if $topic.sticky eq 1}
                        {img modname='Dizkus' src='icon_post_sticky.gif' __alt='Sticky topic'  __title='Topic is sticky (it will always stay at the top of the topics list)' }
                        {/if}
                        {if $topic.topic_status eq 1}
                        {img modname='Dizkus' src='icon_post_close.gif' __alt='Locked topic'  __title='This topic is locked. No more posts accepted.' }
                        {/if}
                        {if $topic.new_posts eq 1}
                        {img modname='Dizkus' src='icon_redfolder.gif' __alt='New posts since your last visit'  __title='New posts since your last visit' }
                        {else}
                        {img modname='Dizkus' src='icon_folder.gif' __alt='Normal topic'  __title='Normal topic' }
                        {/if}
                        {if $topic.hot_topic eq 1}
                        {img modname='Dizkus' src='icon_hottopic.gif' __alt='Hot topic'  __title='Hot topic' }
                        {/if}
                        {if $showattachment eq 1}
                        {img modname='core' set='icons/extrasmall' src='attach.gif' __alt='Attachments'  __title='Attachments' }
                        {/if}
                        {$topic.topic_id|viewtopiclink:$topic.topic_title}
                        <span>{gt text="Poster: %s" tag1=$topic.uname|profilelinkbyuname}</span>
                        {dzkpager objectid=$topic.topic_id total=$topic.total_posts add_prevnext=false separator=", " linkall=true force="viewtopic" tag="span"}
                    </dt>
                    <dd class="posts">{$topic.topic_replies}</dd>
                    <dd class="views">{$topic.topic_views}</dd>
                    <dd class="lastpost">
                        <span>
                            {gt text="Last post by %s" tag1=$topic.last_poster|profilelinkbyuname}<br />
                            {$topic.post_time_unix|dateformat:'datetimebrief'}
                            <a class="tooltips" title="{gt text="View latest post"}" href="{$topic.last_post_url_anchor|safetext}">{img modname='Dizkus' src="icon_topic_latest.gif" __alt="View latest posts" }</a>
                        </span>
                    </dd>
                </dl>
            </li>
            {/foreach}

        </ul>
    </div>

</div>
{dzkpager total=$forum.forum_topics}

{else}

<div class="forumbg dzk_message dzk_rounded">
    <div class="inner"><strong>{gt text="There are no topics in this forum."}</strong></div>
</div>
{/if}

{if $forum.forum_mods|@count > 0}
<ul id="dzk_moderatorlist" class="linklist z-clearfix">
    <li><em>{gt text="Moderated by"}:</em></li>
    {foreach name=moderators item=mod key=modid from=$forum.forum_mods}
    <li>
        {if $modid lt 1000000}{$mod|profilelinkbyuname}{else}{$mod|safetext}{/if}
        {if !$smarty.foreach.moderators.last}, {/if}
    </li>
    {/foreach}
</ul>
{/if}

<script type="text/javascript">
    // <![CDATA[
    var subscribeForum = "{{gt text='Subscribe to forum'}}";
    var unsubscribeForum = "{{gt text='Unsubscribe from forum'}}";
    var favouriteForum = "{{gt text='Add forum to favourites'}}";
    var unfavouriteForum = "{{gt text='Remove forum from favourites'}}";
    // ]]>
</script>

{include file='user/footer.tpl'}
