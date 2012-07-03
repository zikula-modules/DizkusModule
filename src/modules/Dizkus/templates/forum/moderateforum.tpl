{gt text="Moderate" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>

{if $forum.forum_desc <> ''}
<p class='ctheme-description'>{$forum.forum_desc|safehtml}</p>
{/if}

<form class="z-form" method="post" action="{modurl modname='Dizkus' type='user' func='moderateforum' forum=$forum.forum_id}">

    {if $forum.topics}
    <p><a href="{modurl modname="Dizkus" type="user" func="viewforum" forum=$forum.forum_id}">{gt text="Go back to normal forum view"}</a></p>
    {dzkpager total=$forum.forum_topics}

    <div class="forumbg dzk_rounded">
        <div class="inner">

            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt><span>{gt text="Topic"}</span></dt>
                        <dd class="posts"><span>{gt text="Replies"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Last post"}</span></dd>
                        <dd class="mark"><span>{gt text="Selection"}<input type="checkbox" id="alltopic"  value="" /></span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {assign var='stickystarted' value='0'}
                {assign var='topicstarted'  value='0'}
                {foreach item=topic from=$forum.topics}
                <li class="row">
                    <dl class="icon {if $topic.sticky eq 1}dzk_sticky{/if}">
                        <dt class='ctheme-topic-title'>
                            {if $topic.sticky eq 1}
                            {img modname='Dizkus' src='icon_post_sticky.gif' __alt='Sticky topic'  __title='Topic is sticky (it will always stay at the top of the topics list)' }
                            {/if}
                            {if $topic.topic_status eq 1}
                            {img modname='Dizkus' src='icon_post_close.gif' __alt='This topic is locked. No more posts accepted'  __title='Topic locked' }
                            {/if}
                            {if $topic.new_posts eq 1}
                            {img modname='Dizkus' src='icon_redfolder.gif' __alt='New posts since your last visit'  __title='New posts since your last visit' }
                            {else}
                            {img modname='Dizkus' src='icon_folder.gif' __alt='Normal topic'  __title='Normal topic' }
                            {/if}
                            {if $topic.hot_topic eq 1}
                            {img modname='Dizkus' src='icon_hottopic.gif' __alt='Hot topic'  __title='Hot topic' }
                            {/if}
                            {$topic.topic_id|viewtopiclink:$topic.topic_title:$forum.forum_name}
                            <em class="z-sub">({$topic.topic_views} {gt text="Views"})</em>
                            <span>{gt text="Poster: %s" tag1=$topic.uname|profilelinkbyuname}</span>
                            {dzkpager objectid=$topic.topic_id total=$topic.total_posts add_prevnext=false separator=", " linkall=true force="viewtopic" tag="span"}
                        </dt>
                        <dd class="posts">{$forum.forum_topics}</dd>
                        <dd class="lastpost">
                            <span>
                                {gt text="Last post by %s" tag1=$topic.last_poster|profilelinkbyuname}<br />
                                {$topic.post_time_unix|dateformat:'datetimebrief'}
                                <a class="tooltips" title="{gt text="View latest post"}" href="{$topic.last_post_url_anchor|safetext}">{img modname='Dizkus' src="icon_topic_latest.gif" __alt="View latest post" }</a>
                            </span>
                        </dd>
                        <dd class="mark">
                            <input type="checkbox" class="topic_checkbox" name="topic_id[]" value="{$topic.topic_id}" />
                        </dd>
                    </dl>
                </li>
                {/foreach}
            </ul>

        </div>
    </div>
    <p><a href="{modurl modname="Dizkus" type="user" func="viewforum" forum=$forum.forum_id}">{gt text="Go back to normal forum view"}</a></p>
    {dzkpager total=$forum.forum_topics}

    {else}

    <div class="forumbg dzk_message dzk_rounded">
        <div class="inner">
            <strong>{gt text="There are no topics in this forum."}</strong>
        </div>
    </div>

    {/if}

    <div class="z-warningmsg">{gt text="Warning! You will not be prompted for confirmation. Clicking on 'Submit' will immediately execute the chosen action."}</div>

    <fieldset>
        <legend>{gt text="Moderators options"}</legend>
        <div class="z-formrow">
            <label for="mode">{gt text="Actions"}</label>
            <select name="mode" id="mode" size="1">
                <option value="">&lt;&lt; {gt text="Choose action"} &gt;&gt;</option>
                <option value="sticky">{gt text="Give selected topics 'sticky' status"}</option>
                <option value="unsticky">{gt text="Remove 'sticky' status from selected topics"}</option>
                <option value="lock">{gt text="Lock selected topics"}</option>
                <option value="unlock">{gt text="Open selected topics"}</option>
                <option value="delete">{gt text="Delete selected topics"}</option>
                <option value="move">{gt text="Move selected topics"}</option>
                <option value="join">{gt text="Join topics"}</option>
            </select>
        </div>
        <div class="z-formrow">
            <label for="moveto">{gt text="Choose target forum to move topic(s) to"}</label>
            <select name="moveto" id="moveto">
                <option value=''>&lt;&lt; {gt text="Select target forum"} &gt;&gt;</option>
                {foreach item=singleforum from=$forums}
                <option value="{$singleforum.forum_id}">{$singleforum.cat_title|safetext}{gt text="&nbsp;::&nbsp;"}{$singleforum.forum_name|safetext}</option>
                {/foreach}
            </select>
        </div>
        <div class="z-formrow">
            <label for="createshadowtopic">{gt text="Create shadow topic"}</label>
            <input type="checkbox" name="createshadowtopic" id="createshadowtopic" value="1" />
        </div>
        <div class="z-formrow">
            <label for="jointotopic">{gt text="To join topics, select the target topic here"}</label>
            <span>
                <select id="jointotopic" name="jointo_select" onchange="$('jointo').value=this.options[this.selectedIndex].value">
                    <option value=''>&lt;&lt; {gt text="Choose target topic"} &gt;&gt;</option>
                    {foreach item=topic from=$forum.topics}
                    <option value="{$topic.topic_id}">{$topic.topic_title|safetext}</option>
                    {/foreach}
                </select>
                <label for="jointo">{gt text="or target topic #"}</label>
                <input type="text" name="jointo" id="jointo" value="" size="5" maxlength="10" />
            </span>
        </div>
    </fieldset>
    <div class="z-formbuttons z-buttons">
        <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
        {button src="button_ok.png" set="icons/extrasmall" __alt="Submit" __title="Submit" __text="Submit"}
        {button src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel" __text="Cancel"}
    </div>
</form>

{include file='user/footer.tpl'}