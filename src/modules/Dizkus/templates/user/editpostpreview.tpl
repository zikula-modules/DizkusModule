{assign var="onlinestyle" value="style='background-image: url(`$baseurl`modules/Dizkus/images/`$coredata.language`/icon_user_online.gif); background-position: top right; background-repeat: no-repeat;\"'"} 

<h2>{$post.topic_subject|safetext}</h2>

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
                </ul>
            </div>

            <div class="postbody dzk_colpost_right">

                <div class="postinfo">
                    {if isset($topic)}<a class="linktopostlink tooltips" style="margin-right: 5px;" href="{modurl modname='Dizkus' type=user func=viewtopic topic=$post.topic_id start=$topic.start}#pid{$post.post_id}" title="{gt text="Link to this post"}">{img modname='Dizkus' src='target.gif' __alt='Link to this post'}</a>{/if}
                    <strong>{gt text="Posted"}: </strong>{$post.posted_unixtime|dateformat:'datetimebrief'}
                </div>

                <div class="content" id="postingtext_{$post.post_id}">
                    {$post.post_textdisplay}
                </div>

            </div>

            <div class="postlink">
                {if !isset($preview) OR $preview neq true}
                <div class="dzk_colpost_right">
                    <ul class="nonjavascriptpostingoptions linklist z-clearfix" style="float:right;">
                        {if $post.poster_data.moderate eq true}
                        {if (isset($num) AND $num neq 0) OR (isset($topic) AND $topic.start neq 0)}
                        <li><a class="dzk_arrow movepostlink tooltips" title="{gt text="Move post"}" href="{modurl modname='Dizkus' type=user func=movepost post=$post.post_id}">{gt text="Move post"}</a></li>
                        <li><a class="dzk_arrow splittopiclink tooltips" title="{gt text="Split topic"}" href="{modurl modname='Dizkus' type=user func=splittopic post=$post.post_id}">{gt text="Split topic"}</a></li>
                        {/if}
                        {/if}

                        {if isset($topic) AND $topic.topic_status neq 1}
                        {if $post.poster_data.reply eq true}
                        <li><a class="dzk_arrow quotepostlink tooltips" title="{gt text="Quote post or selection"}" href="{modurl modname='Dizkus' type=user func=reply post=$post.post_id}">{gt text="Quote"}</a></li>
                        {/if}
                        {if $post.poster_data.edit eq 1}
                        <li><a class="dzk_arrow editpostlink tooltips" title="{gt text="Edit post"}" href="{modurl modname='Dizkus' type=user func=editpost post=$post.post_id}">{gt text="Edit"}</a></li>
                        {/if}
                        {elseif isset($topic)}
                        {img modname='Dizkus' src=icon_post_close.gif class="tooltips" __alt="Topic locked"}
                        {/if}
                        <li><a class="dzk_arrow notifymoderatorlink tooltips" href="{modurl modname='Dizkus' type=user func=report post=$post.post_id}" title="{gt text="Notify moderator about this posting"}">{gt text="Notify moderator"}</a></li>
                    </ul>
                </div>
                {/if}
            </div>
        </div>

    </div>
</div>
