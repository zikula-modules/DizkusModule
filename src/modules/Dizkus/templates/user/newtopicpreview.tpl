<h2>{$newtopic.subject|safetext}</h2>

<div id="posting" class="forum_post post_bg1 dzk_rounded">
    <div class="inner">

        <div class="dzk_subcols z-clearfix">
            <div class="post_author dzk_colpost_left">
                <div class="dzk_avatar">
                    <strong>{$newtopic.poster_data.uname|profilelinkbyuname}</strong>
                    <br />
                    {useravatar uid=$newtopic.poster_data.uid}
                    {if isset($newtopic.poster_data.rank_image) && isset($newtopic.poster_data.rank)}
                    <br />
                    <img class="userinforankimage" src="{$baseurl}{$newtopic.poster_data.rank_image}" alt="{$newtopic.poster_data.rank}" />
                    {/if}
                </div>

                <ul>
                    {if isset($newtopic.poster_data.rank)}
                    <li><strong>{gt text="Rank"}: </strong>{$newtopic.poster_data.rank|safetext}</li>
                    {/if}
                    <li><strong>{gt text="Registered"}: </strong>{$newtopic.poster_data.user_regdate|dateformat:'datebrief'}</li>
                    <li><strong>{gt text="Posts"}: </strong>{$newtopic.poster_data.user_posts}</li>
                </ul>
            </div>

            <div class="postbody dzk_colpost_right">

                <div class="postinfo">
                    <strong>{gt text="Posted"}:</strong>&nbsp;{$newtopic.topic_unixtime|dateformat:'datetimebrief'}
                </div>

                <div class="content"{if isset($newtopic.post_id)} id="postingtext_{$newtopic.post_id}"{/if}>
                    {$newtopic.message_display|notifyfilters:'dizkus.filter_hooks.message.filter'}
                </div>

            </div>
        </div>

    </div>
</div>