 <h2>{$newtopic.topic_title|safetext}</h2>

<div id="posting" class="forum_post post_bg1 dzk_rounded">
    <div class="inner">

        <div class="dzk_subcols z-clearfix">
            <div class="post_author dzk_colpost_left">
                <div class="dzk_avatar">
                    <strong>{$newtopic.poster.poster_id|profilelinkbyuid}</strong>
                    <br />
                    {useravatar uid=$newtopic.poster.poster_i}
                    {*if isset($newtopic.poster_data.image) && isset($newtopic.poster_data.rank)}
                    <br />
                    <img class="userinforankimage" src="{$baseurl}{$newtopic.poster_data.image}" alt="{$newtopic.poster_data.rank}" />
                    {/if*}
                </div>

                <ul>
                    {if isset($newtopic.poster.rank)}
                    <li><strong>{gt text="Rank"}: </strong>{*$newtopic.poster_data.rank|safetext*}</li>
                    {/if}
                    <li><strong>{gt text="Registered"}: </strong>{*$newtopic.poster_data.user_regdate|dateformat:'datebrief'*}</li>
                    <li><strong>{gt text="Posts"}: </strong>{*$newtopic.poster_data.postCount*}</li>
                </ul>
            </div>

            <div class="postbody dzk_colpost_right">

                <div class="postinfo">
                    <strong>{gt text="Posted"}:</strong>&nbsp;{$newtopic.topic_time|dateformat:'datetimebrief'}
                </div>

                <div class="content"{if isset($newtopic.post_id)} id="postingtext_{$newtopic.post_id}"{/if}>
                    {$data.message|notifyfilters:'dizkus.filter_hooks.message.filter'}
                </div>

            </div>
        </div>

    </div>
</div>