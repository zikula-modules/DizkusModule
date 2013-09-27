{assign var='templatetitle' value=$forum.name}
{include file='user/header.tpl' parent=$forum}

{if isset($modvars.ZikulaDizkusModule.ajax) && $modvars.ZikulaDizkusModule.ajax}
    {pageaddvar name='javascript' value=$moduleInstance->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.Tools.js'}
    {pageaddvar name='javascript' value=$moduleInstance->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.User.ViewForum.js'}
    <input id="forum_id" type="hidden" value={$forum.forum_id}>
{/if}

<h2>{$forum.name|safetext}</h2>

{if $forum.description neq ''}
    <p class='ctheme-description'>{$forum.description|safehtml}</p>
{/if}

<div id="dzk_maincategorylist">

    {if count($forum.children) > 0}
    <div class="forabg dzk_rounded dzk_marginbottom">
        <div class="inner">
            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt class="forumlist"><span>{gt text='Forums'}</span></dt>
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
                    <dl>
                        {datecompare date1=$subforum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                        <dt class='forumlist'>
                            <div>
                                <span class="icon-stack icon-2x pull-left">
                                    <i class="icon-comments icon-stack-base"></i>
                                    {if $comp}
                                        <i class="icon-star icon-overlay-upper-left icon-blue"></i>
                                    {else}
                                        <i class="icon-ok icon-overlay-lower-right icon-green"></i>
                                    {/if}
                                </span>
                                    <h3 class='pull-left; width:100%'><a title="{gt text="Go to subforum"} '{$subforum.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$subforum.forum_id}">{$subforum.name|safetext}</a></h3>
                                    {if $subforum.description neq ''}<p>{$subforum.description|safehtml}</p>{/if}
                            </div>
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

{if $permissions.comment eq true}
    {if $forum.lvl > 0}
        <div class="roundedbar dzk_rounded">
            <div class="inner">
                <ul id="dzk_javascriptareaforum" class="linklist z-clearfix">
                    {if $permissions.comment && !$forum->isLocked()}
                    <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Start a new topic"}" href="{modurl modname=$module type='user' func='newtopic' forum=$forum.forum_id}">&nbsp;{gt text="New topic"}</a></li>
                    {/if}

                    {if $coredata.logged_in}
                    <li>
                        {modapifunc modname=$module type='Forum' func='isSubscribed' forum=$forum assign='isSubscribed'}
                        {if !$isSubscribed}
                            {modurl modname=$module type='user' func='modifyForum' action='subscribe' forum=$forum.forum_id assign='url'}
                            {gt text="Subscribe to forum" assign='msg'}
                        {else}
                            {modurl modname=$module type='user' func='modifyForum' action='unsubscribe' forum=$forum.forum_id assign='url'}
                            {gt text="Unsubscribe from forum" assign='msg'}
                        {/if}
                        <a id="forum-subscription" class="tooltips icon-chevron-sign-right" data-status="{if $isSubscribed}1{else}0{/if}" href="{$url}" title="{$msg}">&nbsp;{$msg}</a>
                    </li>
                    {if $modvars.ZikulaDizkusModule.favorites_enabled eq "yes"}
                    <li>
                        {modapifunc modname=$module type='Favorites' func='isFavorite' forum=$forum assign='isFavorite'}
                        {if $isFavorite}
                            {modurl modname=$module type='user' func='modifyForum' action='removeFromFavorites' forum=$forum.forum_id assign='url'}
                            {gt text="Remove forum from favourites" assign='msg'}
                        {else}
                            {modurl modname=$module type='user' func='modifyForum' action='addToFavorites' forum=$forum.forum_id assign='url'}
                            {gt text="Add forum to favourites" assign='msg'}
                        {/if}
                        <a id="forum-favourite" class="tooltips icon-chevron-sign-right" data-status="{if $isFavorite}1{else}0{/if}" href="{$url}" title="{$msg}">&nbsp;{$msg}</a>
                    </li>
                    {/if}
                    {/if}

                    {if $isModerator OR $permissions.moderate}
                    <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Moderate"}" href="{modurl modname=$module type='user' func='moderateforum' forum=$forum.forum_id}">&nbsp;{gt text="Moderate"}</a></li>
                    {/if}
                </ul>
            </div>
        </div>
    {/if}
{/if}
</div>

{if $forum.lvl > 0}
    {if $topics}
        {if ((count($topics) > 0) || (!$forum->isLocked()))}

            {pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}

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

                            <li class="row">
                                <dl class="icon {if $topic.sticky eq 1}dzk_sticky{/if}">
                                    <dt class='ctheme-topic-title'>
                                    {if $topic.sticky eq 1}
                                        <i title='{gt text="Topic is sticky (it will always stay at the top of the topics list)"}' class="icon-bullhorn icon-150x icon-red tooltips"></i>
                                    {/if}
                                    {if $topic.status eq 1}
                                        <i title='{gt text="This topic is locked. No more posts accepted."}' class="icon-lock icon-150x icon-black tooltips"></i>
                                    {/if}
                                    {if $topic.solved}
                                        <i title='{gt text="This topic is solved."}' class="icon-ok icon-150x icon-green tooltips"></i>
                                    {/if}
                                    {if $topic.replyCount >= $modvars.ZikulaDizkusModule.hot_threshold}
                                        <i title='{gt text="Hot topic"}' class="icon-fire icon-150x icon-orange tooltips"></i>
                                    {/if}

                                    <i class="icon-comment-alt icon-150x icon-black"></i>
                                    {datecompare date1=$forum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                                    {if $comp}
                                        {* @todo the styling on the span class prevents proper stacking of icons *}
                                        {*img modname=$module src='icon_redfolder.gif' __alt='New posts since your last visit'  __title='New posts since your last visit' class='tooltips'*}
                                    {else}
                                        {*img modname=$module src='icon_folder.gif' __alt='Normal topic'  __title='Normal topic' class='tooltips'*}
                                    {/if}
                                    {$topic.topic_id|viewtopiclink:$topic.title}
                                    <span>{gt text="Poster: %s" tag1=$topic.poster.user.uid|profilelinkbyuid}</span>
                                    {assign var='total_posts' value=$topic.replyCount+1}
                                    {dzkpager objectid=$topic.topic_id total=$total_posts add_prevnext=false separator=", " linkall=true force="viewtopic" tag="span"}
                                    </dt>
                                    <dd class="posts">{$topic.replyCount}</dd>
                                    <dd class="views">{$topic.viewCount}</dd>
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
{/if}

<div id="dzk_displayhooks">
    {notifydisplayhooks eventname='dizkus.ui_hooks.forum.ui_view' id=$forum.forum_id}
</div>

{include file='user/moderatedBy.tpl' forum=$forum}

<script type="text/javascript">
    // <![CDATA[
    var subscribeForum = " {{gt text='Subscribe to forum'}}";
    var unsubscribeForum = " {{gt text='Unsubscribe from forum'}}";
    var favouriteForum = " {{gt text='Add forum to favourites'}}";
    var unfavouriteForum = " {{gt text='Remove forum from favourites'}}";
    // ]]>
</script>

{include file='user/footer.tpl'}