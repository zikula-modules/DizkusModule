{assign var='msgmodule' value=$modvars.ZConfig.messagemodule}
{modapifunc modname=$module type='UserData' func='getUserOnlineStatus' uid=$post.poster.user.uid assign='isPosterOnline'}

{if isset($post_counter) AND isset($post_count) AND $post_counter == $post_count}<a id="bottom"></a>{/if}
<a id="pid{$post.post_id}"></a>

<div id="posting_{$post.post_id}" class="panel panel-default {*cycle values='post_bg1,post_bg2'*}">
    {if $isPosterOnline}<div class="ribbon-wrapper-right"><div class="ribbon-right ribbon-blue">{gt text="ONLINE"}</div></div>{/if}
    {if isset($preview) AND ($preview eq 1)}<div class="ribbon-wrapper-left"><div class="ribbon-left ribbon-red">{gt text="PREVIEW"}</div></div>{/if}
    <div class='panel-heading'>
        <div class="postdate{if $isPosterOnline} padright{/if}">
            {if isset($topic)}<a class="tooltips" href="{modurl modname=$module type='user' func='viewtopic' topic=$topic.topic_id start=$start}#pid{$post.post_id}" title="{gt text="Link to this post"}"><i class='icon-file-alt'></i></a>{/if}
            <strong>{gt text="Posted"}: </strong>{$post.post_time|dateformat:'datetimebrief'}
        </div>
    </div>
    <div class="panel-body">
        <div class="dzk_subcols z-clearfix">
            <div id="posting_{$post.post_id}_userinfo" class="post_author dzk_colpost_left">
                <div class="dzk_avatar">
                    <strong>{$post.poster.user.uname|profilelinkbyuid|profilelinkbyuname}</strong>
                    <br />
                    {* TODO: this is temp to show the data is here w/o another DB call
                    <p>{$post.poster.user.uname}</p>
                    <p>{$post.poster.user.email}</p>
                    *}
                    <div>{useravatar uid=$post.poster.user.uid class='gravatar'}</div>

                    {if !empty($post.poster.rank.image)}
                        {if $post.poster.rank.rank_link neq ''}
                            <a href="{$post.poster.rank.rank_link}" title="{$post.poster.rank.rank_link}">
                            {/if}
                            <img class="userinforankimage" src="{$baseurl}{$post.poster.rank.imageLink}" alt="{$post.poster.rank.title}" title="{$post.poster.rank.description}" />
                        {if $post.poster.rank.rank_link neq ''}</a>{/if}
                    {else}
                        {getRankByPostCount posts=$post.poster.postCount ranks=$ranks assign='posterRank'}
                        {if $posterRank.rank_link neq ''}
                        <a href="{$posterRank.rank_link}" title="{$posterRank.rank_link}">
                        {/if}
                        {if $posterRank.image neq ''}
                            <img class="userinforankimage" src="{$baseurl}{$posterRank.imageLink}" alt="{$posterRank.title}" title="{$posterRank.description}" />
                        {/if}
                    {if $posterRank.rank_link neq ''}</a>{/if}
                {/if}
                </div>

                <ul>
                {if !empty($post.poster.rank.title)}
                    <li><strong>{gt text="Rank"}: </strong>{$post.poster.rank.title|safetext}</li>
                {else}
                    <li><strong>{gt text="Rank"}: </strong>{$posterRank.title|safetext}</li>
                {/if}
                    {usergetvar name='user_regdate' assign="user_regdate"}
                    <li><strong>{gt text="Registered"}: </strong>{$user_regdate|dateformat:'datebrief'}</li>
                {if !$isPosterOnline}
                    <li><strong>{gt text="Last visit"}: </strong>{$post.poster.lastvisit|dateformat:'datebrief'}</li>
                {/if}


                    <li><strong>{gt text="Posts"}: </strong>{$post.poster.postCount}</li>
                {if $coredata.logged_in eq true}
                    <li>
                        {capture assign="profileIcon"}<i class='icon-user icon-150x'></i>{/capture}
                        {$post.poster.user.uname|profilelinkbyuname:'tooltips':$profileIcon}
                        {if $msgmodule}
                            <a class='tooltips' title="{gt text="Send private message"}" href="{modurl modname=$msgmodule func="user" func="newpm" uid=$post.poster.user_ui}"><i class='icon-envelope-alt icon-150x'></i></a>
                        {/if}
                        {if isset($topic) AND isset($post.poster_data) AND $post.poster_data.moderate eq true AND $post.poster_data.seeip eq true}
                        <a class='tooltips' title="{gt text="View IP address"}" href="{modurl modname=$module type='user' func='viewIpData' post=$post.post_id}"><i class='icon-info-sign icon-150x'></i></a>
                        {/if}
                        &nbsp;
                    </li>
                {/if}
                </ul>
            </div>

            <div class="postbody dzk_colpost_right">
                <div class="dizkusinformation_post" id="dizkusinformation_{$post.post_id}" style="display: none;">{img modname='core' set='ajax' src='indicator.white.gif'}</div>
                <div class="content" id="postingtext_{$post.post_id}">
                    {$post.post_text|dzkVarPrepHTMLDisplay|notifyfilters:'dizkus.filter_hooks.post.filter'|transformtags}
                    {if $post.attachSignature AND ($modvars.ZikulaDizkusModule.removesignature == 'no')}
                        {usergetvar name='signature' assign="signature" uid=$post.poster.user.uid}
                        {if !empty($signature)}
                            <div class='dzk_postSignature'>
                                {$modvars.ZikulaDizkusModule.signature_start}
                                <br />{$signature|dzkVarPrepHTMLDisplay|notifyfilters:'dizkus.filter_hooks.post.filter'}
                                <br />{$modvars.ZikulaDizkusModule.signature_end}
                            </div>
                        {/if}
                    {/if}
                </div>
                {notifydisplayhooks eventname='dizkus.ui_hooks.post.ui_view' id=$post.post_id}
            </div>
        </div>
    </div>
    <div class="panel-footer">
        {if !isset($preview) OR $preview neq true}
        <div class="pull-right">
            <ul id="postingoptions_{$post.post_id}" class="javascriptpostingoptions linklist">
                {if isset($permissions.moderate) AND $permissions.moderate eq true}
                    {if ((isset($num) AND $num neq 0) OR (isset($topic) AND $start neq 0)) AND !$post.isFirstPost}
                        <li><a class="icon-arrow-right icon-150x tooltips" title="{gt text="Move post"}" href="{modurl modname=$module type='user' func='movepost' post=$post.post_id}"></a></li>
                        <li><a class="icon-cut icon-150x tooltips" title="{gt text="Split topic"}" href="{modurl modname=$module type='user' func='splittopic' post=$post.post_id}"></a></li>
                    {/if}
                {/if}
                {if isset($topic) AND $topic.status neq 1}
                    {if isset($permissions.comment) AND $permissions.comment eq true AND $modvars.ZikulaDizkusModule.ajax}
                        <li><a class="icon-quote-left icon-150x tooltips" id="quotebutton_{$post.post_id}" title="{gt text="Quote post"}" onclick="quote('{dzkquote text=$post.post_text|htmlentities uid=$post.poster.user.uid}');"></a></li>
                    {/if}
                    {if isset($permissions.edit) AND $permissions.edit eq 1 OR $post.userAllowedToEdit}
                        <li><a class="editpostlink icon-edit icon-150x tooltips" data-post="{$post.post_id}" id="editbutton_{$post.post_id}" title="{gt text="Edit post"}" href="{modurl modname=$module type='user' func='editpost' post=$post.post_id}"></a></li>
                    {/if}
                {elseif isset($topic)}
                    <li><i class='icon-lock icon-150x tooltips' title='{gt text='This topic is locked'}'></i></li>
                {/if}
                {if !isset($notify) OR $notify eq false}
                    {if isset($permissions.comment) AND $permissions.comment eq true}
                        <li><a class="icon-bell icon-150x tooltips" href="{modurl modname=$module type='user' func='report' post=$post.post_id}" title="{gt text="Notify moderator about this posting"}"></a></li>
                    {/if}
                    <li><a class="icon-chevron-sign-up icon-150x dzk_notextdecoration tooltips" title="{gt text="Top"}" href="#top">&nbsp;</a></li>
                {/if}
            </ul>
        </div>
        <div class='clearfix'></div>
        {/if}
    </div>
</div>