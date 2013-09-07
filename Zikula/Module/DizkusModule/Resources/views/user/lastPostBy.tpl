<span>
    {if $last_post}
        {gt text="Last post by %s" tag1=$last_post.poster.user.uid|profilelinkbyuid}<br />
        {$last_post.post_time|dateformat:'datetimebrief'}
        <a class="tooltips" title="{gt text="View latest post"}" href="{lastTopicUrl topic=$last_post.topic}">{img modname=$module src="icon_topic_latest.gif" __alt="View latest posts"} {$last_post.topic.title|strip_tags|truncate:25:'...'}</a>
    {/if}
</span>