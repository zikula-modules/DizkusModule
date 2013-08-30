{include file='user/header.tpl'}

<h2>{gt text="Forums index page"}</h2>

<div id="dzk_maincategorylist">
    {foreach item='parent' from=$forums}
    <div class="forabg dzk_rounded">
        <div class="inner">
            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt class="forumlist">
                            <span><a id="categorylink_{$parent.name}" title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$parent.forum_id}">{$parent.name|safetext}</a></span>
                        </dt>
                        <dd class="subforums"><span>{gt text="Subforums"}</span></dd>
                        <dd class="topics"><span>{gt text="Topics"}</span></dd>
                        <dd class="posts"><span>{gt text="Posts"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Last post"}</span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item='forum' from=$parent.children}
                <li class="row">
                    <dl class="icon">
                        {datecompare date1=$forum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                        <dt class='{if $comp}new-posts{else}no-new-posts{/if}'>
                            <a title="{gt text="Go to forum"} '{$forum.name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$forum.forum_id}">{$forum.name|safetext}</a><br />
                            {if $forum.description neq ''}{$forum.description|safehtml}<br />{/if}
                            {include file='user/moderatedBy.tpl' forum=$forum}
                        </dt>
                        <dd class="subforums">{$forum.children|count}</dd>
                        <dd class="topics">{$forum.topicCount|safetext}</dd>
                        <dd class="posts">{$forum.postCount|safetext}</dd>
                        <dd class="lastpost">
                            {if isset($forum.last_post)}
                            {include file='user/lastPostBy.tpl' last_post=$forum.last_post}
                            {else}
                            <span></span>
                            {/if}
                        </dd>
                    </dl>
                </li>
                {assign var='freeTopicsInForum' value=$parent.topics|count}
                {if $freeTopicsInForum > 0}
                <li class="row z-center">
                        <p>{gt text="There is %s topic not in a subforum." plural="There are %s topics not in a subforum." tag1=$freeTopicsInForum count=$freeTopicsInForum}
                        <a id="forumlink_{$parent.name}" title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$parent.forum_id}">{gt text="Go to forum"} '{$parent.name|safetext}'</a>
                        </p>
                </li>
                {/if}
                {foreachelse}
                <li class="row dzk_empty">
                    {gt text="No subforums available."}
                    {if $parent.topicCount > 0}
                        <p>{gt text="There is %s topic." plural="There are %s topics." tag1=$parent.topicCount count=$parent.topicCount}
                        <a id="forumlink_{$parent.name}" title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$parent.forum_id}">{gt text="Go to forum"} '{$parent.name|safetext}'</a>
                        </p>
                    {/if}
                </li>
                {/foreach}
            </ul>
        </div>
    </div>
    {/foreach}
</div>

{include file='user/footer.tpl'}
