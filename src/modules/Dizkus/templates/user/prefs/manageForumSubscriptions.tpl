{gt text="Personal settings" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<div id="dzk_userprefs">

    <h2>{gt text="Settings"}</h2>

    {modulelinks modname='Dizkus' type='prefs'}<br />


    <div class="forumbg dzk_rounded">

        <div class="inner">

            <ul class="topiclist">
                <li class="dzk_header">
                    <dl>
                        <dt><span>{gt text="Category"} / {gt text="Forums"}</span></dt>
                        <dd class="favorites"><span>{gt text="Subscription"}</span></dd>
                        {if $favorites_enabled eq 'yes'}
                        <dd class="favorites"><span>{gt text="Favourites"}</span></dd>
                        {/if}
                    </dl>
                </li>
            </ul>

            <ul class="topiclist forums">
                {foreach item=category from=$tree}
                <li class="row categorytitle">
                    <a href="{modurl modname='Dizkus' type=user func=main viewcat=$category.cat_id}"><strong>{$category.cat_title|safetext}</strong></a>
                </li>
                {foreach item=forum from=$category.forums}
                <li class="row">
                    <dl class="icon">
                        <dt class='ctheme-topic-title'>
                            <a href="{modurl modname='Dizkus' type=user func=viewforum forum=$forum.forum_id}"><strong>{$forum.forum_name|safetext}</strong></a>
                            <span>{$forum.forum_desc|safehtml}</span>
                        </dt>
                        <dd class="favorites">
                            {if $forum.is_subscribed eq 0}
                            <a id="toggleforumsubscriptionbutton_{$forum.forum_id}" class="dzk_arrow" href="javascript:void(0);" title="{gt text="Subscribe to forum"}">{gt text="Subscribe to forum"}</a>
                            {else}
                            <a id="toggleforumsubscriptionbutton_{$forum.forum_id}" class="dzk_arrow" href="javascript:void(0);" title="{gt text="Unsubscribe from forum"}">{gt text="Unsubscribe from forum"}</a>
                            {/if}

                            <noscript>
                                <div>
                                    {if $forum.is_subscribed == 0}
                                    <a class="subscribelink" href="{modurl modname="Dizkus" type="user" func="prefs" act="subscribe_forum" forum=$forum.forum_id return_to=prefs}" title="{gt text="Subscribe to forum"}">{gt text="Subscribe to forum"}</a>
                                    {else}
                                    <a class="unsubscribelink" href="{modurl modname="Dizkus" type="user" func="prefs" act="unsubscribe_forum" forum=$forum.forum_id return_to=prefs}" title="{gt text="Unsubscribe from forum"}">{gt text="Unsubscribe from forum"}</a>
                                    {/if}
                                </div>
                            </noscript>
                        </dd>
                        {if $favorites_enabled eq 'yes'}
                        <dd class="favorites">
                            {if $forum.is_favorite eq 0}
                            <a id="toggleforumfavouritebutton_{$forum.forum_id}" class="dzk_arrow" href="javascript:void(0);" title="{gt text="Add forum to favourites"}">{gt text="Add forum to favourites"}</a>
                            {else}
                            <a id="toggleforumfavouritebutton_{$forum.forum_id}" class="dzk_arrow" href="javascript:void(0);" title="{gt text="Remove forum from favourites"}">{gt text="Remove forum from favourites"}</a>
                            {/if}
                            <noscript>
                                <div>
                                    {if $forum.is_favorite eq 0}
                                    <a class="addfavoritelink" href="{modurl modname="Dizkus" type="user" func="prefs" act="add_favorite_forum" forum=$forum.forum_id return_to=prefs}" title="{gt text="Add forum to favourites"}">{gt text="Add forum to favourites"}</a>
                                    {else}
                                    <a class="removefavoritelink" href="{modurl modname="Dizkus" type="user" func="prefs" act="remove_favorite_forum" forum=$forum.forum_id return_to=prefs}" title="{gt text="Remove forum from favourites"}">{gt text="Remove forum from favourites"}</a>
                                    {/if}
                                </div>
                            </noscript>
                        </dd>
                        {/if}
                    </dl>
                </li>
                {/foreach}
                {/foreach}
            </ul>

        </div>
    </div>

</div>

<script type="text/javascript">
    // <![CDATA[
    var subscribeForum = "{{gt text='Subscribe to forum'}}";
    var unsubscribeForum = "{{gt text='Unsubscribe from forum'}}";
    var favouriteForum = "{{gt text='Add forum to favourites'}}";
    var unfavouriteForum = "{{gt text='Remove forum from favourites'}}";
    // ]]>
</script>

{* end of the Dizkus container *}

