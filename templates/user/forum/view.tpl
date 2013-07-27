{assign var='templatetitle' value=$forum.name}
{include file='user/header.tpl' parent=$forum}

{if isset($modvars.Dizkus.ajax) && $modvars.Dizkus.ajax}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_user_viewforum.js'}
<input id="forum_id" type="hidden" value={$forum.forum_id}>
{/if}

<h2>{$forum.name|safetext}</h2>

{if $forum.description neq ''}
<p class='ctheme-description'>{$forum.description|safehtml}</p>
{/if}

{if $permissions.moderate eq true or $permissions.comment eq true}

<div id="dzk_maincategorylist">

    {if count($forum.children) > 0}
    <div class="forabg dzk_rounded dzk_marginbottom">
        <div class="inner">
            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt><span>{gt text='Sub Forums'}</span></dt>
                        <dd class="subforums"><span>{gt text="Subforums"}</span></dd>
                        <dd class="topics"><span>{gt text="Topics"}</span></dd>
                        <dd class="posts"><span>{gt text="Posts"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Last post"}</span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item='subforum' from=$forum.children}
                <li class="row">
                    <dl class="icon">
                        {datecompare date1=$subforum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                        <dt class='{if $comp}new-posts{else}no-new-posts{/if}'>
                            <a title="{gt text="Go to subforum"} '{$subforum.name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$subforum.forum_id}">{$subforum.name|safetext}</a><br />
                            {if $subforum.description neq ''}{$subforum.description|safehtml}<br />{/if}
                        </dt>
                        <dd class="subforums">{$subforum.children|count}</dd>
                        <dd class="topics">{$subforum.topicCount|safetext}</dd>
                        <dd class="posts">{$subforum.postCount|safetext}</dd>

                        <dd class="lastpost">
                            {if isset($subforum.last_post)}
                            {include file='user/lastPostBy.tpl' last_post=$subforum.last_post}
                            {/if}
                        </dd>
                    </dl>
                </li>
                {/foreach}
            </ul>

        </div>
    </div>
    {/if}

    {if $forum.lvl > 1}
    <div class="roundedbar dzk_rounded">
        <div class="inner">
            <ul id="dzk_javascriptareaforum" class="linklist z-clearfix">
                {* Moderate *}
                {if $permissions.comment}
                <li><a class="dzk_arrow newtopiclink tooltips" title="{gt text="Start a new topic"}" href="{modurl modname='Dizkus' type='user' func='newtopic' forum=$forum.forum_id}">{gt text="New topic"}</a></li>
                {/if}

                {if $coredata.logged_in}
                <li>
                {modapifunc modname='Dizkus' type='Forum' func='isSubscribed' forum=$forum assign='isSubscribed'}
                {if !$isSubscribed}
                    {modurl modname='Dizkus' type='user' func='modifyForum' action='subscribe' forum=$forum.forum_id assign='url'}
                    {gt text="Subscribe to forum" assign='msg'}
                {else}
                    {modurl modname='Dizkus' type='user' func='modifyForum' action='unsubscribe' forum=$forum.forum_id assign='url'}
                    {gt text="Unsubscribe from forum" assign='msg'}
                {/if}
                <a id="forum-subscription" class="dzk_arrow tooltips" href="{$url}" title="{$msg}">{$msg}</a>
                </li>
                {if $modvars.Dizkus.favorites_enabled eq "yes"}
                <li>
                    {modapifunc modname='Dizkus' type='Favorites' func='isFavorite' forum=$forum assign='isFavorite'}
                    {if $isFavorite}
                        {modurl modname='Dizkus' type='user' func='modifyForum' action='removeFromFavorites' forum=$forum.forum_id assign='url'}
                        {gt text="Remove forum from favourites" assign='msg'}
                    {else}
                        {modurl modname='Dizkus' type='user' func='modifyForum' action='addToFavorites' forum=$forum.forum_id assign='url'}
                        {gt text="Add forum to favourites" assign='msg'}
                    {/if}
                    <a id="forum-favourite" class="dzk_arrow tooltips" href="{$url}" title="{$msg}">{$msg}</a>
                </li>
                {/if}
                {/if}

                {if $isModerator OR $permissions.moderate}
                <li><a class="dzk_arrow moderatelink tooltips" title="{gt text="Moderate"}" href="{modurl modname='Dizkus' type='user' func='moderateforum' forum=$forum.forum_id}">{gt text="Moderate"}</a></li>
                {/if}
            </ul>
        </div>
    </div>
    {/if}
</div>
{/if}

{if $forum.lvl > 1}
{if $topics}

{pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}
{mediaattach_attachicon topics=$topics assign='uploadtopicids'}

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

            {foreach item=topic from=$topics}

            {assign var='topic' value=$topic->toArray()}
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

                        {datecompare date1=$forum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                        {if $comp}
                        {img modname='Dizkus' src='icon_redfolder.gif' __alt='New posts since your last visit'  __title='New posts since your last visit' }
                        {else}
                        {img modname='Dizkus' src='icon_folder.gif' __alt='Normal topic'  __title='Normal topic' }
                        {/if}
                        {if $topic.topic_replies >= $modvars.Dizkus.hot_threshold}
                        {img modname='Dizkus' src='icon_hottopic.gif' __alt='Hot topic'  __title='Hot topic' }
                        {/if}
                        {if $showattachment eq 1}
                        {img modname='core' set='icons/extrasmall' src='attach.gif' __alt='Attachments'  __title='Attachments' }
                        {/if}
                        {$topic.topic_id|viewtopiclink:$topic.title}
                        <span>{gt text="Poster: %s" tag1=$topic.poster.user.uid|profilelinkbyuid}</span>
                        {assign var='total_posts' value=$topic.topic_replies+1}
                        {dzkpager objectid=$topic.topic_id total=$total_posts add_prevnext=false separator=", " linkall=true force="viewtopic" tag="span"}
                    </dt>
                    <dd class="posts">{$topic.topic_replies}</dd>
                    <dd class="views">{$topic.topic_views}</dd>
                    <dd class="lastpost">
                        {if isset($topic.last_post)}
                        {include file='user/lastPostBy.tpl' last_post=$topic.last_post}
                        {/if}
                    </dd>
                </dl>
            </li>
            {/foreach}

        </ul>
    </div>

</div>
{pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}


{else}

<div class="forumbg dzk_message dzk_rounded">
    <div class="inner"><strong>{gt text="There are no topics in this forum."}</strong></div>
</div>
{/if}
{/if}

<div id="dzk_displayhooks">
    {notifydisplayhooks eventname='dizkus.ui_hooks.forum.ui_view' id=$forum.forum_id}
</div>

{include file='user/moderatedBy.tpl' forum=$forum}

<script type="text/javascript">
    // <![CDATA[
    var subscribeForum = "{{gt text='Subscribe to forum'}}";
    var unsubscribeForum = "{{gt text='Unsubscribe from forum'}}";
    var favouriteForum = "{{gt text='Add forum to favourites'}}";
    var unfavouriteForum = "{{gt text='Remove forum from favourites'}}";
    // ]]>
</script>

{include file='user/footer.tpl'}
