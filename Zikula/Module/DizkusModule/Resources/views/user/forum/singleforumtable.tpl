<table class='table'>
    <thead>
        <tr class='active'>
            <th colspan='2'>
                {if !isset($forum)}
                <a id="forumlink_{$parent.name}" class='tooltips' title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$parent.forum_id}">
                    <i class='icon-comments'></i>&nbsp;{$parent.name|safetext|upper}</a>
                {/if}
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
                {gt text="No subforums available."}
            </td>
        </tr>
        {/foreach}

        {assign var='freeTopicsInForum' value=$parent.topics|count}
        {if $freeTopicsInForum > 0}
        <tr>
            <td colspan='6' class='text-center success'>{gt text="There is %s topic not in a subforum." plural="There are %s topics not in a subforum." tag1=$freeTopicsInForum count=$freeTopicsInForum}
                <a id="forumlink_{$parent.name}" title="{gt text="Go to forum"} '{$parent.name|safetext}'" href="{modurl modname=$module type='user' func='viewforum' forum=$parent.forum_id}">{gt text="Go to forum"} '{$parent.name|safetext}'</a>
            </td>
        </tr>
        {/if}
    </tbody>
</table>