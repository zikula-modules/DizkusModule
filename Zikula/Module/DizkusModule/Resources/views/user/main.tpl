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
                        <span><a id="categorylink_{$parent.name}" title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$parent.forum_id}">{$parent.name|safetext}</a></span>
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
                    <dl>
                        {datecompare date1=$forum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
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
                                <h3 class='pull-left; width:100%'><a title="{gt text="Go to forum"} '{$forum.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$forum.forum_id}">{$forum.name|safetext}</a></h3>
                                {if $forum.description neq ''}<p>{$forum.description|safehtml}</p>{/if}
                                {include file='user/moderatedBy.tpl' forum=$forum}
                            </div>
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
                        <a id="forumlink_{$parent.name}" title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$parent.forum_id}">{gt text="Go to forum"} '{$parent.name|safetext}'</a>
                    </p>
                </li>
                {/if}
            {foreachelse}
                <li class="row dzk_empty">
                    {gt text="No subforums available."}
                    {if $parent.topicCount > 0}
                        <p>{gt text="There is %s topic." plural="There are %s topics." tag1=$parent.topicCount count=$parent.topicCount}
                            <a id="forumlink_{$parent.name}" title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$parent.forum_id}">{gt text="Go to forum"} '{$parent.name|safetext}'</a>
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

<hr />
<h1>Some ideas --remove this later</h1>
<p class='z-clearfix'><i class="icon-quote-left icon-3x pull-left icon-muted"></i>
    A pull quote or possibly internal styling of [quote].</p>
<p class='z-clearfix'><i class="icon-flag icon-2x pull-left icon-border"></i>
    A flag in a box.</p>
<span title='Topic is sticky (it will always stay at the top of the topics list)' class="icon-stack icon-2x tooltips">
  <i class="icon-comments-alt icon-stack-base icon-muted"></i>
  <i class="icon-lock icon-overlay-lower-right"></i>
  <i class="icon-certificate icon-overlay-upper-left icon-orange"></i>
</span>
multiple layers of icons with tooltips too<br>
<span class="icon-stack icon-2x">
  <i class="icon-comment icon-stack-base icon-muted"></i>
  <i class="icon-plus icon-overlay-lower-right text-success"></i>
</span>
create a topic<br>
<span class="icon-stack icon-2x">
  <i class="icon-comments icon-stack-base icon-muted"></i>
  <i class="icon-star icon-overlay-lower-right text-info"></i>
</span>
star (or new) topic (or maybe unread)<br>
<span class="icon-stack icon-2x">
  <i class="icon-comments icon-stack-base icon-muted"></i>
  <i class="icon-ok icon-overlay-lower-right text-success"></i>
</span>
a topic that has been fully read<br>
<span class="icon-stack icon-2x">
  <i class="icon-circle icon-stack-base icon-muted"></i>
  <i class="icon-pushpin text-danger"></i>
</span>
a pinned/sticky topic<br>