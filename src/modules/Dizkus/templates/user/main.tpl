{include file='user/header.tpl'}

{if $view_category == -1}

<div id="dzk_maincategorylist">
    {foreach item='category' from=$tree}
    <div class="forabg dzk_rounded">
        <div class="inner">
            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt>
                            <span><a id="categorylink_{$category.cat_id}" class="{if $category.new_posts == true}newpostscategorylink{else}categorylink{/if}" title="{gt text="Go to category"} '{$category.cat_title|safetext}'" href="{modurl modname='Dizkus' type=user func=main viewcat=$category.cat_id}">{$category.cat_title|safetext}</a></span>
                        </dt>
                        <dd class="topics"><span>{gt text="Topics"}</span></dd>
                        <dd class="posts"><span>{gt text="Posts"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Last post"}</span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item='forum' from=$category.forums}
                <li class="row">
                    <dl class="icon">
                        <dt {if $forum.new_posts == true}class='new-posts'{else}class='no-new-posts'{/if} >
                            <a title="{gt text="Go to forum"} '{$forum.forum_name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$forum.forum_id}">{$forum.forum_name|safetext}</a><br />
                            {if $forum.forum_desc neq ''}{$forum.forum_desc|safehtml}<br />{/if}
                            {if !empty($forum.forum_mods)}
                            <em>{gt text="Moderated by"}:</em>
                            {foreach name='moderators' item='mod' key='modid' from=$forum.forum_mods}
                            {if $modid lt 1000000}{$mod|profilelinkbyuname}{else}{$mod|safetext}{/if}{if !$smarty.foreach.moderators.last}, {/if}
                            {/foreach}
                            {/if}
                        </dt>

                        <dd class="topics">{$forum.forum_topics|safetext}</dd>
                        <dd class="posts">{$forum.forum_posts|safetext}</dd>
                        <dd class="lastpost">
                            {if isset($forum.last_post_data)}
                            <span>
                                {gt text="Last post by %s" tag1=$forum.last_post_data.name|profilelinkbyuname}<br />
                                {gt text="Written on %s:" tag1=$forum.last_post_data.unixtime|dateformat:'datetimebrief'}
                                {if $forum.last_post_data.url_anchor neq ''}
                                <a class="latesttopicimage tooltips" href="{$forum.last_post_data.url_anchor|safetext}" title="{gt text='View latest post: %s' tag1=$forum.last_post_data.subject|safehtml|truncate:70}">{$forum.last_post_data.subject|safetext|truncate:70}</a>
                                {/if}
                            </span>
                            {else}
                            <span>{$forum.last_post}</span>
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

        </div>
    </div>
    {/foreach}
</div>

{else}

<div id="dzk_categorylist">
    <div class="forabg dzk_rounded">
        <div class="inner">
            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt><span>{$view_category_data.cat_title|safetext}</span></dt>
                        <dd class="topics"><span>{gt text="Topics"}</span></dd>
                        <dd class="posts"><span>{gt text="Posts"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Last post"}</span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item='forum' from=$view_category_data.forums}
                <li class="row">
                    <dl class="icon">
                        <dt {if $forum.new_posts == true}class='new-posts'{else}class='no-new-posts'{/if} >
                            <a title="{gt text="Go to forum"} '{$forum.forum_name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$forum.forum_id}">{$forum.forum_name|safetext}</a><br />
                            {if $forum.forum_desc neq ''}{$forum.forum_desc|safehtml}<br />{/if}
                            {if !empty($forum.forum_mods)}
                            <em>{gt text="Moderated by"}:</em>
                            {foreach name='moderators' item='mod' key='modid' from=$forum.forum_mods}
                            {if $modid lt 1000000}{$mod|profilelinkbyuname}{else}{$mod|safetext}{/if}{if !$smarty.foreach.moderators.last}, {/if}
                            {/foreach}
                            {/if}
                        </dt>

                        <dd class="topics">{$forum.forum_topics|safetext}</dd>
                        <dd class="posts">{$forum.forum_posts|safetext}</dd>
                        <dd class="lastpost">
                            {if isset($forum.last_post_data)}
                            <span>
                                {gt text="Last post by %s" tag1=$forum.last_post_data.name|profilelinkbyuname}<br />
                                {gt text="Written on %s:" tag1=$forum.last_post_data.unixtime|dateformat:'datetimebrief'}
                                {if $forum.last_post_data.url_anchor neq ''}
                                <a class="latesttopicimage tooltips" href="{$forum.last_post_data.url_anchor|safetext}" title="{gt text='View latest post: %s' tag1=$forum.last_post_data.subject|safehtml}">{$forum.last_post_data.subject|safetext}</a>
                                {/if}
                            </span>
                            {else}
                            <span>{$forum.last_post|default:'&nbsp;'}</span>
                            {/if}
                        </dd>
                    </dl>
                </li>
                {foreachelse}
                <li class="row dzk_empty">{gt text="No forums created."}</li>
                {/foreach}
            </ul>

        </div>
    </div>
</div>

{/if}

{include file='user/footer.tpl'}
