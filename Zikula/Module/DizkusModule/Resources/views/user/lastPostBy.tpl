<span>
    {if $last_post}
        {gt text="Last post by %s" tag1=$last_post.poster.user.uname|profilelinkbyuname}<br />
        {$last_post.post_time|dateformat:'datetimebrief'}
        <a class="tooltips" title="{gt text="View latest post"}" href="{lastTopicUrl topic=$last_post.topic}"><i class='fa fa-hand-o-right'></i> {gt text='view'}</a>
    {/if}
</span>