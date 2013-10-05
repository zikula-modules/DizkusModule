<table class='table table-condensed'>
    <thead>
    <tr class='active'>
        <th colspan='2'>{gt text="Topic"|upper}</th>
        <th class='data'>{gt text="Replies"|upper}</th>
        <th class='data'>{gt text="Views"|upper}</th>
        <th class='lastpost'>{gt text="Last post"|upper}</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=topic from=$topics}
        <tr>
            <td class='data'>
                {datecompare date1=$forum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                <span class="icon-stack icon-2x">
                    {if $topic.sticky eq 1}
                    <i title='{gt text="Topic is pinned to top of list."}' class="icon-bullhorn tooltips icon-stack-base"></i>
                    {else}
                    <i class="icon-comment-alt icon-stack-base"></i>
                    {/if}
                    {if $topic.status eq 1}<i title='{gt text="This topic is locked."}' class="icon-lock icon-black icon-overlay-lower-right tooltips"></i>{/if}
                    {if $comp}<i class="icon-star icon-overlay-upper-left icon-blue"></i>{/if}
                </span>
            </td>
            <td class='description'>
                <h4>
                    {if $topic.solved}
                    <i title='{gt text="This topic is solved."}' class="icon-ok icon-green tooltips"></i>
                    {/if}
                    {$topic.topic_id|viewtopiclink:$topic.title}
                </h4>
                <div class='text-muted'>
                    <small>{gt text="by %s" tag1=$topic.poster.user.uid|profilelinkbyuid} {gt text='on'} {$topic.topic_time|dateformat:'datetimebrief'}</small>
                    {assign var='total_posts' value=$topic.replyCount+1}
                    {dzkpager objectid=$topic.topic_id total=$total_posts add_prevnext=false separator=", " linkall=true force="viewtopic" tag="div"}
                </div>
            </td>
            <td class='data'>
                {if $topic.replyCount >= $modvars.ZikulaDizkusModule.hot_threshold}
                    <span title='{gt text="Hot topic"}' class='icon-red tooltips'><i class='icon-fire'></i>&nbsp;{$topic.replyCount|safetext}</span>
                {else}
                    {$topic.replyCount|safetext}
                {/if}
            </td>
            <td class='data'>{$topic.viewCount|safetext}</td>
            <td class='lastpost'>
                {if isset($topic.last_post)}
                    {include file='user/lastPostBy.tpl' last_post=$topic.last_post}
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
    </tbody>
</table>