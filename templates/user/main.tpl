{include file='user/header.tpl'}

{if $viewcat > 0}
<h2>{$tree.0.forum_name|safetext}</h2>
{else}
<h2>{gt text="Forums index page"}</h2>
{/if}

<div id="dzk_maincategorylist">
    {foreach item='category' from=$tree}
    <div class="forabg dzk_rounded">
        <div class="inner">
            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt>
                            <span><a id="categorylink_{$category.forum_name}" title="{gt text="Go to category"} '{$category.forum_name|safetext}'" href="{modurl modname='Dizkus' type=user func=viewforum forum=$category.forum_id}">{$category.forum_name|safetext}</a></span>
                        </dt>
                        <dd class="subforums"><span>{gt text="Subforums"}</span></dd>
                        <dd class="topics"><span>{gt text="Topics"}</span></dd>
                        <dd class="posts"><span>{gt text="Posts"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Last post"}</span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item='forum' from=$category.children}
                    <li class="row">
                        <dl class="icon">
                            {datecompare date1=$forum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                            <dt class='{if $comp}new-posts{else}no-new-posts{/if}'>
                                <a title="{gt text="Go to forum"} '{$forum.forum_name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$forum.forum_id}">{$forum.forum_name|safetext}</a><br />
                                {if $forum.description neq ''}{$forum.description|safehtml}<br />{/if}
                                {include file='user/moderatedBy.tpl' forum=$forum}
                            </dt>

                            <dd class="subforums">{$forum.children|count}</dd>
                            <dd class="topics">{$forum.topicCount|safetext}</dd>
                            <dd class="posts">{$forum.forum_posts|safetext}</dd>
                            <dd class="lastpost">
                                {if isset($forum.last_post)}
                                {include file='user/lastPostBy.tpl' last_post=$forum.last_post}
                                {else}
                                <span></span>
                                {/if}
                            </dd>
                        </dl>
                    </li>
                    {foreachelse}
                    <li class="row dzk_empty">
                        {gt text="No forums created."}
                    </li>
                {/foreach}
            </ul>
            {if $viewcat > 0}
            <div id="dzk_displayhooks">
                {notifydisplayhooks eventname='dizkus.ui_hooks.forum.ui_view' id=$category.forum_id}
            </div>
            {/if}

        </div>
    </div>
    {/foreach}
</div>

{include file='user/footer.tpl'}
