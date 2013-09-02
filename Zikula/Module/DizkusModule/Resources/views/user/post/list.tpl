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

                        {if $topic.status eq 1}
                            {img modname='Dizkus' src="icon_post_close.gif" __alt="Topic locked"  __title="This topic is locked. No more posts accepted."}
                        {/if}
                        {if $topic.replyCount >= $modvars.ZikulaDizkusModule.hot_threshold}
                            {img modname='Dizkus' src="icon_hottopic.gif" __alt="Hot topic"  __title="Hot topic"}
                        {/if}
                        <a href="{modurl modname=$module type='user' func='viewtopic' topic=$topic.topic_id}" title="{$topic.title|truncate:70}">{$topic.title|truncate:70}</a>
                        <span>{gt text="Forum"}: <a href="{modurl modname=$module type='user' func='viewforum' forum=$topic.forum.forum_id}" title="{$topic.forum.name|truncate:70}">{$topic.forum.name|truncate:70}</a></span>
                        </dt>
                        <dd class="posts">{$topic.replyCount|safetext}</dd>
                        <dd class="lastpost">
                            {include file='user/lastPostBy.tpl' last_post=$topic.last_post}
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