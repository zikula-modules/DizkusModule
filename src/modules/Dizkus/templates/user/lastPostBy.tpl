<span>
    {if $last_post}
    {getForeignKey entity=$last_post->getposter() key='user_id' assign='poster_id'}
    {if isset($poster_id)}
        {gt text="Last post by %s" tag1=$poster_id|profilelinkbyuid}<br />
        {$last_post.post_time|dateformat:'datetimebrief'}
        <a class="tooltips" title="{gt text="View latest post"}" href="{lastTopicUrl topic=$last_post.topic_id replies=$replies}">{img modname='Dizkus' src="icon_topic_latest.gif" __alt="View latest posts" }</a>
    {/if}
    {/if}
</span>