{assign var="onlinestyle" value="style='background-image: url(`$baseurl`modules/Dizkus/images/`$coredata.language`/icon_user_online.gif); background-position: top right; background-repeat: no-repeat;\"'"} 

<div id="dizkus">

    <div id="posting_{$post.post_id}" class="forum_post dzk_rounded {cycle values='post_bg1,post_bg2'}" {if $post.poster_data.online}{$onlinestyle}{/if}>
        <div class="inner">

            <div class="dzk_subcols z-clearfix">
                <div id="posting_{$post.post_id}_userinfo" class="post_author dzk_colpost_left">
                    <div class="dzk_avatar">
                        <strong>{$post.poster_data.uname|profilelinkbyuname}</strong>
                        <br />
                        {useravatar uid=$post.poster_data.uid}
                        {if isset($post.poster_data.rank_image) && isset($post.poster_data.rank)}
                        <br />
                        <img class="userinforankimage" src="{$baseurl}{$post.poster_data.rank_image}" alt="{$post.poster_data.rank}" />
                        {/if}
                    </div>

                    <ul>
                        {if isset($post.poster_data.rank)}
                        <li><strong>{gt text="Rank"}: </strong>{$post.poster_data.rank|safetext}</li>
                        {/if}
                        <li><strong>{gt text="Registered"}: </strong>{$post.poster_data.user_regdate|dateformat:'datebrief'}</li>
                        {if !$post.poster_data.online}
                        <li><strong>{gt text="Last visit"}: </strong>{$post.poster_data.user_lastvisit|dateformat:'datebrief'}</li>
                        {/if}
                        <li><strong>{gt text="Posts"}: </strong>{$post.poster_data.user_posts}</li>
                        {if $coredata.logged_in eq true}
                        {/if}
                    </ul>
                </div>

                <div class="postbody dzk_colpost_right">
                    <div class="postinfo">
                        {if isset($topic)}<a class="linktopostlink tooltips" href="{modurl modname='Dizkus' type='user' func='viewtopic' topic=$post.topic_id start=$topic.start}#pid{$post.post_id}" title="{gt text="Link to this post"}">{img modname='Dizkus' src='target.gif' __alt='Link to this post'}</a>{/if}
                        <strong>{gt text="Posted"}:</strong>&nbsp;{$post.posted_unixtime|dateformat:'datetimebrief'}
                    </div>
                    <div class="content" id="postingtext_{$post.post_id}">
                        {$post.post_text}
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>
