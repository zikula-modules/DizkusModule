{gt text="Latest forum posts" assign=templatetitle}
{pagesetvar name=title value="`$templatetitle` - `$text`"}

{include file='user/header.tpl'}

<div id="latestposts">

    <h2>{gt text="Latest forum posts"} ({$text})</h2>

    <div class="roundedbar dzk_rounded">
        <div class="inner">
            <form class="dzk_form" method="post" action="{modurl modname='Dizkus' type=user func=viewlatest}">
                <ul class="linklist z-clearfix">
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=3}">{gt text="Yesterday"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=2}">{gt text="Today"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=1}">{gt text="Last 24 hours"}</a></li>
                    <li><a class="dzk_arrow"></a><button type="submit">{gt text="Last"}</button> <input type="text" name="nohours" id="Dizkus_hours" size="3" value="{$nohours}" maxlength="3" tabindex="0" /> <button type="submit">{gt text="hours"}</button></li>
                </ul>
                <ul class="linklist z-clearfix">
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=4}">{gt text="Last week"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=4 unanswered=1}">{gt text="Last week, unanswered"}</a></li>
                    {if $last_visit_unix <> 0}
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=6}">{gt text="Last visit"}</a></li>
                    {/if}
                </ul>
            </form>
        </div>
    </div>

    <a id="postings"></a>
    <h2>{gt text="Forums"}</h2>

    <div class="forumbg dzk_rounded">
        <div class="inner">

            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt><span>{gt text="Topic"}</span></dt>
                        <dd class="posts"><span>{gt text="Replies"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Posted"}</span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item='post' from=$posts}
                <li class="row">
                    <dl class="icon">
                        <dt class='ctheme-topic-title'>
                            {if $post.sticky eq 1}
                            {img modname='Dizkus' src="icon_post_sticky.gif" __alt="Sticky topic"  __title="Topic is sticky (it will always stay at the top of the topics list)"}
                            {/if}
                            {if $post.topic_status eq 1}
                            {img modname='Dizkus' src="icon_post_close.gif" __alt="Topic locked"  __title="This topic is locked. No more posts accepted."}
                            {/if}
                            {if $post.hot_topic eq 1}
                            {img modname='Dizkus' src="icon_hottopic.gif" __alt="Hot topic"  __title="Hot topic"}
                            {/if}
                            <a href="{$post.last_post_url_anchor}" title="{$post.topic_title|truncate:70}">{$post.topic_title|truncate:70}</a>
                            <span>{gt text="Forum"}: <a href="{modurl modname='Dizkus' func='viewforum' forum=$post.forum_id}" title="{$post.forum_name|truncate:70}">{$post.forum_name|truncate:70}</a></span>
                        </dt>
                        <dd class="posts">{$post.topic_replies|safetext}</dd>
                        <dd class="lastpost">
                            <span>
                                {gt text="Posted by %s" tag1=$post.poster_name|profilelinkbyuname}<br />
                                {$post.posted_unixtime|dateformat:'datetimebrief'}
                            </span>
                        </dd>
                    </dl>
                </li>
                {foreachelse}
                <li class="row nonewtopics">{gt text="No posts"}</li>
                {/foreach}

            </ul>
        </div>
    </div>

    <a id="rsspostings"></a>
    {if $rssposts}
    <h2>{gt text="RSS feeds"}</h2>

    <div class="forumbg dzk_rounded">

        <div class="inner">

            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt><span>{gt text="Topic"}</span></dt>
                        <dd class="posts"><span>{gt text="Replies"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Posted"}</span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item='post' from=$rssposts}
                <li class="row">
                    <dl class="icon">
                        <dt class='ctheme-topic-title'>
                            {if $post.sticky eq 1}
                            {img modname='Dizkus' src="icon_post_sticky.gif" __alt="Sticky topic"  __title="Topic is sticky (it will always stay at the top of the topics list)"}
                            {/if}
                            {if $post.topic_status eq 1}
                            {img modname='Dizkus' src="icon_post_close.gif" __alt="Topic locked"  __title="This topic is locked. No more posts accepted."}
                            {/if}
                            {if $post.hot_topic eq 1}
                            {img modname='Dizkus' src="icon_hottopic.gif" __alt="Hot topic"  __title="Hot topic"}
                            {/if}
                            <a href="{$post.last_post_url_anchor}" title="{$post.cat_title} :: {$post.forum_name}">{$post.topic_title}</a>
                            <span>{gt text="Forum"}: <a href="{modurl modname='Dizkus' func='viewforum' forum=$post.forum_id}" title="{$post.forum_name}">{$post.forum_name|truncate:"50"}</a></span>
                        </dt>
                        <dd class="posts">{$post.topic_replies|safetext}</dd>
                        <dd class="lastpost">
                            <span>
                                {gt text="Posted by %s" tag1=$post.poster_name|profilelinkbyuname}<br />
                                {$post.posted_unixtime|dateformat:'datetimebrief'}
                            </span>
                        </dd>
                    </dl>
                </li>
                {foreachelse}
                <li class="row nonewtopics">
                    {gt text="No posts"}
                </li>
                {/foreach}

            </ul>
        </div>
    </div>
    {/if}

    <a id="m2fpostings"></a>
    {if $m2fposts}
    <h2>{gt text="Mailing lists"}</h2>

    <div class="forumbg dzk_rounded">

        <div class="inner">

            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt><span>{gt text="Topic"}</span></dt>
                        <dd class="posts"><span>{gt text="Replies"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Posted"}</span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item='post' from=$m2fposts}
                <li class="row">
                    <dl class="icon">
                        <dt class='ctheme-topic-title'>
                            {if $post.sticky eq 1}
                            {img modname='Dizkus' src="icon_post_sticky.gif" __alt="Sticky topic"  __title="Topic is sticky (it will always stay at the top of the topics list)"}
                            {/if}
                            {if $post.topic_status eq 1}
                            {img modname='Dizkus' src="icon_post_close.gif" __alt="Topic locked"  __title="This topic is locked. No more posts accepted."}
                            {/if}
                            {if $post.hot_topic eq 1}
                            {img modname='Dizkus' src="icon_hottopic.gif" __alt="Hot topic"  __title="Hot topic"}
                            {/if}
                            <a href="{$post.last_post_url_anchor}" title="{$post.cat_title} :: {$post.forum_name}">{$post.topic_title}</a>
                            <span>{gt text="Forum"}: <a href="{modurl modname='Dizkus' func='viewforum' forum=$post.forum_id}" title="{$post.forum_name}">{$post.forum_name|truncate:"50"}</a></span>
                        </dt>
                        <dd class="posts">{$post.topic_replies|safetext}</dd>
                        <dd class="lastpost">
                            <span>
                                {gt text="Posted by %s" tag1=$post.poster_name|profilelinkbyuname}<br />
                                {$post.posted_unixtime|dateformat:'datetimebrief'}
                            </span>
                        </dd>
                    </dl>
                </li>
                {foreachelse}
                <li class="row nonewtopics">
                    {gt text="No posts"}
                </li>
                {/foreach}

            </ul>
        </div>
    </div>
    {/if}

</div>

{include file='user/footer.tpl'}
