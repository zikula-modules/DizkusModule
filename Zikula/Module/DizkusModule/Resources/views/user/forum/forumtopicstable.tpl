{if !isset($moderate)}{assign var='moderate' value=false}{/if}
<table class='table table-condensed'>
    <thead>
    <tr class='active'>
        <th colspan='2'>{gt text="Topic"|upper}</th>
        <th class='data'>{gt text="Replies"|upper}</th>
        <th class='data'>{gt text="Views"|upper}</th>
        <th class='lastpost'>{gt text="Last post"|upper}</th>
        {if $moderate}<th class='data'>{gt text='All'|upper}&nbsp;<input type="checkbox" id="alltopic" value="" onclick="jQuery('.topic_checkbox').attr('checked', this.checked);" /></th>{/if}
    </tr>
    </thead>
    <tbody>
    {foreach item=topic from=$topics}
        <tr>
            <td class='data'>
                {if isset($forum)}
                    {datecompare date1=$forum.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                {else}
                    {assign value=false var="comp"}
                {/if}
                <span class="fa-stack fa-2x">
                    {if $topic.sticky eq 1}
                    <i title='{gt text="Topic is pinned to top of list."}' class="fa fa-bullhorn fa-stack-2x tooltips"></i>
                    {else}
                    <i class="fa fa-comment-o fa-stack-2x"></i>
                    {/if}
                    {if $topic.status eq 1}<i title='{gt text="This topic is locked."}' class="fa fa-lock fa-stack-1x fa-black fa-overlay-lower-right tooltips"></i>{/if}
                    {if $comp}<i class="fa fa-star fa-stack-1x fa-overlay-upper-left fa-blue"></i>{/if}
                </span>
            </td>
            <td class='description'>
                <h4>
                    {if $topic.solved gt 0}
                    <i title='{gt text="This topic is solved."}' class="fa fa-check fa-green tooltips"></i>
                    {elseif $topic.solved eq -1}
                    <i title='{gt text="This topic is a support request."}' class="fa fa-question fa-red tooltips"></i>
                    {/if}
                    {$topic.topic_id|viewtopiclink:$topic.title}
                </h4>
                <div class='text-muted'>
                    <small>{gt text="by %s" tag1=$topic.poster.user.uname|profilelinkbyuname} {gt text='on'} {$topic.firstPostTime|dateformat:'datetimebrief'}</small>
                    {assign var='total_posts' value=$topic.replyCount+1}
                    {dzkpager objectid=$topic.topic_id total=$total_posts add_prevnext=false separator=", " linkall=true force="viewtopic" tag="div"}
                </div>
            </td>
            <td class='data'>
                {if $topic.replyCount >= $modvars.ZikulaDizkusModule.hot_threshold}
                    <span title='{gt text="Hot topic"}' class='fa-red tooltips'><i class='fa fa-fire'></i>&nbsp;{$topic.replyCount|safetext}</span>
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
            {if $moderate}<td class='data'><input type="checkbox" class="topic_checkbox" name="topic_id[]" value="{$topic.topic_id}"/></td>{/if}
        </tr>
        {foreachelse}
        <tr>
            {if $moderate}{assign var='cols' value='7'}{else}{assign var='cols' value='6'}{/if}
            <td colspan='{$cols}' class='text-center warning'>
                {gt text="No subforums available."}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>