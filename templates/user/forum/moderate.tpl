
{include file='user/header.tpl' __templatetitle='Moderate'}

{*if $forum.description <> ''}
<p class='ctheme-description'>{$forum.description|safehtml}</p>
{/if*}

{if $forum.topics}

{form cssClass="z-form"}
{formvalidationsummary}

    <p><a href="{modurl modname="Dizkus" type="user" func="viewforum" forum=$forum.forum_id}">{gt text="Go back to normal forum view"}</a></p>
    {dzkpager total=$forum.topicCount}

    <div class="forumbg dzk_rounded">
        <div class="inner">

            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt><span>{$forum.name} {gt text="Topics"}</span></dt>
                        <dd class="posts"><span>{gt text="Replies"}</span></dd>
                        <dd class="lastpost"><span>{gt text="Last post"}</span></dd>
                        <dd class="mark"><span>{gt text="Selection"}<input type="checkbox" id="alltopic"  value="" /></span></dd>
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {assign var='stickystarted' value='0'}
                {assign var='topicstarted'  value='0'}
                {foreach item='topic' from=$forum.topics}
                    <li class="row">
                        <dl class="icon {if $topic.sticky eq 1}dzk_sticky{/if}">
                            <dt class='ctheme-topic-title'>
                                {if $topic.sticky eq 1}
                                    {img modname='Dizkus' src='icon_post_sticky.gif' __alt='Sticky topic'  __title='Topic is sticky (it will always stay at the top of the topics list)' }
                                {/if}
                                {if $topic.status eq 1}
                                    {img modname='Dizkus' src='icon_post_close.gif' __alt='This topic is locked. No more posts accepted'  __title='Topic locked' }
                                {/if}
                                {datecompare date1=$topic.last_post.post_time date2=$last_visit_unix comp=">" assign='comp'}
                                {if $comp}
                                    {img modname='Dizkus' src='icon_redfolder.gif' __alt='New posts since your last visit'  __title='New posts since your last visit' }
                                    {else}
                                    {img modname='Dizkus' src='icon_folder.gif' __alt='Normal topic'  __title='Normal topic' }
                                {/if}
                                {if $topic.hot_topic eq 1}
                                    {img modname='Dizkus' src='icon_hottopic.gif' __alt='Hot topic'  __title='Hot topic' }
                                {/if}
                                {$topic.topic_id|viewtopiclink:$topic.title:$forum.name}
                                <em class="z-sub">({$topic.topic_views} {gt text="Views"})</em>
                                <span>{gt text="Poster: %s" tag1=$topic.poster.user.uid|profilelinkbyuid}</span>
                                {dzkpager objectid=$topic.topic_id total=$topic.total_posts add_prevnext=false separator=", " linkall=true force="viewtopic" tag="span"}
                            </dt>
                            <dd class="posts">{$forum.topicCount}</dd>
                            <dd class="lastpost">
                                {include file='user/lastPostBy.tpl' last_post=$topic.last_post}
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
    {dzkpager total=$forum.topicCount}

    <div class="z-warningmsg">{gt text="Warning! You will not be prompted for confirmation. Clicking on 'Submit' will immediately execute the chosen action."}</div>

    <fieldset>
        <legend>{gt text="Moderator options"}</legend>
        <div class="z-formrow">
            {formlabel for="mode" __text="Actions"}
            {formdropdownlist id="mode" items=$actions}
        </div>
        <div class="z-formrow">
            {formlabel for="moveto" __text="Choose target forum to move topic(s) to"}
            {formdropdownlist id="moveto" items=$forums}
        </div>
        <div class="z-formrow">
            {formlabel for="createshadowtopic" __text="Create shadow topic"}
            {formcheckbox id="createshadowtopic"}
        </div>
        <div class="z-formrow">
            {formlabel for="jointotopic" __text="To join topics, select the target topic here"}
            <span>
                {formdropdownlist id="jointotopic" items=$topicSelect}
                {formlabel for="jointo" __text="or target topic #"}
                {formintinput id="jointo" size="5" maxLength="10"}
            </span>
        </div>
    </fieldset>
    <div class="z-formbuttons z-buttons">
        {formbutton class="z-bt-ok" commandName="submit"  __text="Submit"}
        {formbutton class="z-bt-cancel" commandName="cancel"   __text="Cancel"}
    </div>
{/form}

    {else}

<div class="forumbg dzk_message dzk_rounded">
    <div class="inner">
        <strong>{gt text="There are no topics in the forum '%s' to moderate." tag1=$forum.name|safetext}</strong>
    </div>
</div>

{/if}

{include file='user/footer.tpl'}