<span>
{if $last_post}
    {gt text="Last post by %s" tag1=$last_post.poster.user_id|profilelinkbyuid}<br />
    {$last_post.post_time|dateformat:'datetimebrief'}
    <a class="tooltips" title="{gt text="View latest post"}" href="{lastTopicUrl topic=$last_post.topic.topic_id replies=$replies}">{img modname='Dizkus' src="icon_topic_latest.gif" __alt="View latest posts"} {$last_post.topic.topic_title}</a>
{/if}
</span>