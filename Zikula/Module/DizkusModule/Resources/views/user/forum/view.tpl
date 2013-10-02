{assign var='templatetitle' value=$forum.name}
{include file='user/header.tpl' parent=$forum}

{if isset($modvars.ZikulaDizkusModule.ajax) && $modvars.ZikulaDizkusModule.ajax}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.Tools.js'}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.User.ViewForum.js'}
    <input id="forum_id" type="hidden" value={$forum.forum_id}>
{/if}

{if count($forum.children) > 0}
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>
            <span class="icon-stack">
              <i class="icon-comments icon-stack-base"></i>
              {if $forum->isLocked()}<i class="icon-lock icon-overlay-lower-right"></i>{/if}
            </span>
            &nbsp;{$forum.name|safetext}
        </h2>
    </div>
    {if $forum.description neq ''}
    <div class="panel-body">{$forum.description|safehtml}</div>
    {/if}
    {include file='user/forum/singleforumtable.tpl' parent=$forum}
    <div class='panel-footer'>
        {dizkusonline assign='online'}
        {assign var='anonsessions' value=$modvars.ZConfig.anonymoussessions}
        {assign var='mins' value=$modvars.ZConfig.secinactivemins}
        <h3>{gt text="Users on-line"}</h3>
        <ul id="ctheme_onlinelist" class="z-clearfix">
            {if $online.numusers > 0}
                {foreach name='onlineusers' item='user' from=$online.unames}
                    <li>{if $user.admin == '1'}{$user.uname|profilelinkbyuname}{else}{$user.uname|profilelinkbyuname}{/if}{if !$smarty.foreach.onlineusers.last}, {/if}</li>
                {/foreach}
            {else}
                <li>{gt text="0 users"}</li>
            {/if}
            {if $online.numguests > 0 and $anonsessions == 1}
                <li>{gt text='and %1$s anonymous guest' plural='and %1$s anonymous guests' count=$online.numguests tag1=$online.numguests}</li>
            {/if}
        </ul>
        <p class="z-sub">{gt text='This list is based on users active over the last %s minutes.' tag1=$mins}</p>
    </div>
</div>
{/if}

{* ******************************************************
* FORUM NAVBAR
******************************************************* *}
{if $permissions.comment eq true}
<nav class="navbar navbar-default" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-forum-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">{$forum.name|safetext}</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div id="navbar-forum-collapse" class="collapse navbar-collapse">
        <ul class="nav navbar-nav navbar-right">
            {if $permissions.comment && !$forum->isLocked()}
            <li><a class='icon-comment-alt' title="{gt text="Start a new topic"}" href="{modurl modname=$module type='user' func='newtopic' forum=$forum.forum_id}">&nbsp;{gt text="New topic"}</a></li>
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
                    <a class='icon-envelope-alt' id="forum-subscription" data-status="{if $isSubscribed}1{else}0{/if}" href="{$url}" title="{$msg}">&nbsp;{$msg}</a>
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
                        <a class='icon-heart-empty' id="forum-favourite" data-status="{if $isFavorite}1{else}0{/if}" href="{$url}" title="{$msg}">&nbsp;{$msg}</a>
                    </li>
                {/if}
            {/if}

            {if $isModerator OR $permissions.moderate}
                <li><a class='icon-wrench' title="{gt text="Moderate"}" href="{modurl modname=$module type='user' func='moderateforum' forum=$forum.forum_id}">&nbsp;{gt text="Moderate"}</a></li>
            {/if}
        </ul>
    </div><!-- /.navbar-collapse -->
</nav>
{/if}

{if $topics}
    {if (count($topics) > 0)}

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
    {elseif (!$forum->isLocked())}
        <div class="alert alert-info text-center">
            {gt text="There are no topics in this forum yet."}&nbsp;<a class='icon-comment-alt btn btn-info btn-sm' title="{gt text="Start a new topic"}" href="{modurl modname=$module type='user' func='newtopic' forum=$forum.forum_id}">&nbsp;{gt text="Start a new topic"}</a>
        </div>
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