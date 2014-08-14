{if $topforumscount > 0}
    <h5>{gt text="%s Most-active forum" plural="%s Most-active forums" tag1=$topforumscount count=$topforumscount} <small>(topics/posts)</small>:</h5>
    <ul class="fa-ul" style="margin-left:0;padding-left:40px;">
        {foreach item='topforum' from=$topforums}
        <li><i class="fa-li fa fa-comments text-muted"></i>
            <a href="{modurl modname=$module type='user' func='viewforum' forum=$topforum.forum_id}" title="{$topforum.cat_title} :: {$topforum.name}">{$topforum.name}</a>
            <small>({$topforum.topicCount}/{$topforum.postCount})</small>
        </li>
        {/foreach}
    </ul>
{/if}

{if $lastpostcount > 0}
    <h5>{gt text="%s Recent topic" plural="%s Recent topics" tag1=$lastpostcount count=$lastpostcount}:</h5>
    {include file="Block/recentposts.tpl" lastposts=$lastposts showfooter=false}
{/if}

{if $toppostercount > 0}
    <h5>{gt text="%s Most-active poster in last %s months" plural="%s Most-active posters in last %n months" tag1=$toppostercount tag2=$months count=$toppostercount}:</h5>
    <ul class="fa-ul" style="margin-left:0;padding-left:40px;">
        {foreach item='topposter' from=$topposters}
        <li><i class="fa-li fa fa-user text-muted"></i>{$topposter.user_name|profilelinkbyuname}&nbsp;<small>{$topposter.postCount} {gt text="Posts"}</small></li>
        {/foreach}
    </ul>
{/if}

<h5>{gt text="Total"}:</h5>
<ul class="fa-ul" style="margin-left:0;padding-left:40px;">
    <li><i class="fa-li fa fa-check text-muted"></i>{gt text="Forums"}: {$total_forums}</li>
    <li><i class="fa-li fa fa-check text-muted"></i>{gt text="Topics"}: {$total_topics}</li>
    <li><i class="fa-li fa fa-check text-muted"></i>{gt text="Posts"}: {$total_posts}</li>
    <li><i class="fa-li fa fa-check text-muted"></i>{gt text="Last User"}: {$last_user}</li>
</ul>
<p class="text-center">
    <a style="font-size: 0.8em;" href="{modurl modname=$module type='user' func='index'}" title="{gt text="Go to forum"}">{gt text="Go to forum"}</a>
</p>