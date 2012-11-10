<span>
    {gt text="Last post by %s" tag1=$last_post.poster_id|profilelinkbyuid}<br />
    {$last_post.post_time|dateformat:'datetimebrief'}
        <a class="tooltips" title="{gt text="View latest post"}" href="{modurl modname='Dizkus' type='user' func='viewtopic' topic=$last_post.post_id}">{img modname='Dizkus' src="icon_topic_latest.gif" __alt="View latest posts" }</a>
</span>