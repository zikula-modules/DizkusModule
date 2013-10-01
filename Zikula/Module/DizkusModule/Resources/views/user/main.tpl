{include file='user/header.tpl'}

<div class="panel panel-default">
    <div class="panel-heading"><h2 class='icon-home'>&nbsp;{gt text="Forums index page"}</h2>
        {if $numposts}
        <div style='position:absolute; top:0; right:0; padding: 2em 1em;'>
            <a title="{gt text="RSS Feed"}" href="{modurl modname=$module type='user' func='feed'}"><i class='icon-rss-sign icon-150x icon-orange'></i></a>
            {gt text="Total posts: %s" tag1=$numposts}
        </div>
        {/if}
    </div>
    {foreach item='parent' from=$forums}
    <table class='table'>
        <thead>
            <tr class='active'>
                <th colspan='2'>
                    <a id="forumlink_{$parent.name}" class='tooltips' title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$parent.forum_id}">
                        <i class='icon-comments'></i>&nbsp;{$parent.name|safetext|upper}</a>
                </th>
                <th class='data'>{gt text="Subforums"|upper}</th>
                <th class='data'>{gt text="Topics"|upper}</th>
                <th class='data'>{gt text="Posts"|upper}</th>
                <th class='lastpost'>{gt text="Last post"|upper}</th>
            </tr>
        </thead>
        <tbody>
            {foreach item='forum' from=$parent.children}
            <tr>
                <td class='data'>
                    {datecompare date1=$forum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                    <a class='tooltips' title="{gt text="Go to forum"} '{$forum.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$forum.forum_id}">
                        <span class="icon-stack icon-2x">
                            <i class="icon-comments icon-stack-base"></i>
                        {if $comp}
                            <i class="icon-star icon-overlay-upper-left icon-blue"></i>
                        {else}
                            <i class="icon-ok icon-overlay-lower-right icon-green"></i>
                        {/if}
                        </span>
                    </a>
                </td>
                <td class='description'>
                    <h3><a class='tooltips' title="{gt text="Go to forum"} '{$forum.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$forum.forum_id}">{$forum.name|safetext}</a></h3>
                    {if $forum.description neq ''}<p>{$forum.description|safehtml}</p>{/if}
                    {include file='user/moderatedBy.tpl' forum=$forum}
                </td>
                <td class='data'>{$forum.children|count}</td>
                <td class='data'>{$forum.topicCount|safetext}</td>
                <td class='data'>{$forum.postCount|safetext}</td>
                <td class='lastpost'>
                {if isset($forum.last_post)}
                    {include file='user/lastPostBy.tpl' last_post=$forum.last_post}
                {else}
                    <span></span>
                {/if}
                </td>
            </tr>
            {foreachelse}
            <tr>
                <td colspan='6' class='text-center warning'>
                    {gt text="No child forums available."}
                </td>
            </tr>
            {/foreach}

            {assign var='freeTopicsInForum' value=$parent.topics|count}
            {if $freeTopicsInForum > 0}
            <tr>
                <td colspan='6' class='text-center success'>{gt text="There is %s topic not in a child forum." plural="There are %s topics not in a subforum." tag1=$freeTopicsInForum count=$freeTopicsInForum}
                    <a id="forumlink_{$parent.name}" title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$parent.forum_id}">{gt text="Go to forum"} '{$parent.name|safetext}'</a>
                </td>
            </tr>
            {/if}
        </tbody>
    </table>
    {/foreach}

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