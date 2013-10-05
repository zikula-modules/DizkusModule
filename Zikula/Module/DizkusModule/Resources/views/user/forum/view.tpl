{assign var='templatetitle' value=$forum.name}
{include file='user/header.tpl' parent=$forum}

{if isset($modvars.ZikulaDizkusModule.ajax) && $modvars.ZikulaDizkusModule.ajax}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.Tools.js'}
    {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.User.ViewForum.js'}
    <input id="forum_id" type="hidden" value={$forum.forum_id}>
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
            <a class="navbar-brand" href="#">{$forum.name|safetext}&nbsp;{gt text="forum"}</a>
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

{* ******************************************************
* SUBFORUM DISPLAY
******************************************************* *}
{if count($forum.children) > 0}
<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <h2 class='dizkus-clean pull-left'>
            <span class="icon-stack">
              <i class="icon-comments icon-stack-base"></i>
              {if $forum->isLocked()}<i class="icon-lock icon-overlay-lower-right"></i>{/if}
            </span>
            &nbsp;{$forum.name|safetext}&nbsp;{gt text='subforums'}
        </h2>
    </div>
    {if $forum.description neq ''}
    <div class="panel-body">{$forum.description|safehtml}</div>
    {/if}
    {include file='user/forum/singleforumtable.tpl' parent=$forum}
    {if !isset($topics) || (count($topics) eq 0)}
        {include file='user/forum/panelfooter.tpl'}
    {/if}
</div>
{/if}

{* ******************************************************
* TOPICS TABLE
******************************************************* *}
{if isset($topics)}
{if (count($topics) > 0)}
<div class="panel panel-info">
    <div class="panel-heading">
        <h2>{$forum.name|safetext}&nbsp;{gt text='topics'}</h2>
    </div>
    {pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}
    {include file='user/forum/forumtopicstable.tpl'}
    {pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}
    {include file='user/forum/panelfooter.tpl'}
</div>
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