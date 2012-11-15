{gt text="Latest forum posts" assign=templatetitle}


{pagesetvar name=title value="`$templatetitle` - `$text`"}

{include file='user/header.tpl'}

<div id="latestposts">

    <h2>{gt text="Latest forum posts"} ({$text})</h2>

    <div class="roundedbar dzk_rounded">
        <div class="inner">
            <form class="dzk_form" method="post" action="{modurl modname='Dizkus' type=user func=viewlatest}">
                <ul class="linklist z-clearfix">
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=3}">{gt text="Since Yesterday"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=2}">{gt text="Today"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=1}">{gt text="Last 24 hours"}</a></li>
                    <li><a class="dzk_arrow"></a><button type="submit">{gt text="Last"}</button> <input type="text" name="nohours" id="Dizkus_hours" size="3" value="{$nohours|default:2}" maxlength="3" tabindex="0" /> <button type="submit">{gt text="hours"}</button></li>
                </ul>
                <ul class="linklist z-clearfix">
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=4}">{gt text="Last week"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=unanswered}">{gt text="Unanswered"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type=user func=viewlatest selorder=unsolved}">{gt text="Unsolved"}</a></li>
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
                {foreach item='topic' from=$posts}
                <li class="row">
                    <dl class="icon">
                        <dt class='ctheme-topic-title'>
                            {if $topic.sticky eq 1}
                            {img modname='Dizkus' src="icon_post_sticky.gif" __alt="Sticky topic"  __title="Topic is sticky (it will always stay at the top of the topics list)"}
                            {/if}

                            {if $topic.topic_status eq 1}
                            {img modname='Dizkus' src="icon_post_close.gif" __alt="Topic locked"  __title="This topic is locked. No more posts accepted."}
                            {/if}
                            {if $topic.topic_replies >= $modvars.Dizkus.hot_threshold}
                            {img modname='Dizkus' src="icon_hottopic.gif" __alt="Hot topic"  __title="Hot topic"}
                            {/if}
                            <a href="{modurl modname='Dizkus' type='user' func='viewtopic' topic=$topic.topic_id viewlast=1}" title="{$topic.topic_title|truncate:70}">{$topic.topic_title|truncate:70}</a>
                            <span>{gt text="Forum"}: <a href="{*modurl modname='Dizkus' func='viewforum' forum=$topic.forum_id*}" title="{*$topic.forum_name|truncate:70*}">{*$topic.forum_name|truncate:70*}</a></span>
                        </dt>
                        <dd class="posts">{$topic.topic_replies|safetext}</dd>
                        <dd class="lastpost">
                            <span>
                                {gt text="Posted by %s" tag1=$topic.last_post.poster_id|profilelinkbyuid}<br />
                                {$topic.last_post.post_time|dateformat:'datetimebrief'}
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

    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start' maxpages="10"}

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
                            {if $topic.sticky eq 1}
                            {img modname='Dizkus' src="icon_post_sticky.gif" __alt="Sticky topic"  __title="Topic is sticky (it will always stay at the top of the topics list)"}
                            {/if}
                            {if $topic.topic_status eq 1}
                            {img modname='Dizkus' src="icon_post_close.gif" __alt="Topic locked"  __title="This topic is locked. No more posts accepted."}
                            {/if}
                            {if $topic.hot_topic eq 1}
                            {img modname='Dizkus' src="icon_hottopic.gif" __alt="Hot topic"  __title="Hot topic"}
                            {/if}
                            <a href="{$topic.last_post_url_anchor}" title="{$topic.cat_title} :: {$topic.forum_name}">{$topic.topic_title}</a>
                            <span>{gt text="Forum"}: <a href="{modurl modname='Dizkus' func='viewforum' forum=$topic.forum_id}" title="{$topic.forum_name}">{$topic.forum_name|truncate:"50"}</a></span>
                        </dt>
                        <dd class="posts">{$topic.topic_replies|safetext}</dd>
                        <dd class="lastpost">
                            <span>
                                {gt text="Posted by %s" tag1=$topic.poster_name|profilelinkbyuname}<br />
                                {$topic.posted_unixtime|dateformat:'datetimebrief'}
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
                            {if $topic.sticky eq 1}
                            {img modname='Dizkus' src="icon_post_sticky.gif" __alt="Sticky topic"  __title="Topic is sticky (it will always stay at the top of the topics list)"}
                            {/if}
                            {if $topic.topic_status eq 1}
                            {img modname='Dizkus' src="icon_post_close.gif" __alt="Topic locked"  __title="This topic is locked. No more posts accepted."}
                            {/if}
                            {if $topic.hot_topic eq 1}
                            {img modname='Dizkus' src="icon_hottopic.gif" __alt="Hot topic"  __title="Hot topic"}
                            {/if}
                            <a href="{$topic.last_post_url_anchor}" title="{$topic.cat_title} :: {$topic.forum_name}">{$topic.topic_title}</a>
                            <span>{gt text="Forum"}: <a href="{modurl modname='Dizkus' func='viewforum' forum=$topic.forum_id}" title="{$topic.forum_name}">{$topic.forum_name|truncate:"50"}</a></span>
                        </dt>
                        <dd class="posts">{$topic.topic_replies|safetext}</dd>
                        <dd class="lastpost">
                            <span>
                                {gt text="Posted by %s" tag1=$topic.poster_name|profilelinkbyuname}<br />
                                {$topic.posted_unixtime|dateformat:'datetimebrief'}
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
