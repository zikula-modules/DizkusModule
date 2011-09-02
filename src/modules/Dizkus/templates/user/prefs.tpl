{gt text="Personal settings" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<div id="dzk_userprefs">

    <h2>{gt text="Settings"}</h2>

    <div class="roundedbar dzk_rounded">
        <div class="inner">

            <ul class="linklist z-clearfix">
                <li>{gt text="Recent posts order in topic view"}
                    <br />
                    <span><a id="sortorder" class="dzk_arrow sortorderasclink hidden" href="javascript:void(0);" title="{gt text="Change post order"}">{gt text="Change post order"}</a>&nbsp;({gt text="now showing"}
                    {if $post_order eq 'asc'}
                    <span id="sortorder_asc">{gt text="oldest submissions at top"})</span>
                    <span id="sortorder_desc" class="hidden">{gt text="newest submissions at top"})</span>
                    {elseif $post_order eq 'desc'}
                    <span id="sortorder_asc" class="hidden">{gt text="oldest submissions at top"})</span>
                    <span id="sortorder_desc">{gt text="newest submissions at top"})</span>
                    {/if}
                    </span>
                    <form action="javascript:void(0);" method="post">
                        <div>
                            <input type="hidden" id="authid" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
                        </div>
                    </form>
                    <noscript>
                        <div>
                            {if $post_order == 'asc'}
                            <a class="dzk_arrow sortorderasclink" href="{modurl modname="Dizkus" type="user" func="prefs" act="change_post_order" return_to="prefs"}" title="{gt text="Change post order"}">{gt text="Change post order"}</a>&nbsp;({gt text="Recent posts sort order"}: {gt text="Oldest submissions at top"})
                            {else}
                            <a class="dzk_arrow sortorderdesclink" href="{modurl modname="Dizkus" type="user" func="prefs" act="change_post_order" return_to="prefs"}" title="{gt text="Change post order"}">{gt text="Change post order"}</a>&nbsp;({gt text="Recent posts sort order"}: {gt text="Newest submissions at top"})
                            {/if}
                        </div>
                    </noscript>
                </li>
            </ul>

            <ul class="linklist z-clearfix">
                <li>
                    {gt text="Manage topic subscriptions"}<br />
                    <a class="dzk_arrow subscribetopiclink" href="{modurl modname='Dizkus' type=user func=topicsubscriptions}" title="{gt text="Manage topic subscriptions"}">{gt text="Manage topic subscriptions"}</a>
                </li>
            </ul>

            {if $favorites_enabled eq 'yes'}
            <ul class="linklist z-clearfix">
                <li>
                    {gt text="Favourites"}<br />
                    <span><a id="forumdisplaymode" class="dzk_arrow favouriteslink hidden" href="javascript:void(0);" title="{gt text="Toggle forum display"}">{gt text="Toggle forum display"}</a>&nbsp;({gt text="now showing"}
                    {if $favorites eq true}
                    <span id="favorites_true">{gt text="favourite forums only"})</span>
                    <span id="favorites_false" class="hidden">{gt text="all forums"})</span>
                    {else}
                    <span id="favorites_true" class="hidden">{gt text="favourite forums only"})</span>
                    <span id="favorites_false">{gt text="all forums"})</span>
                    {/if}
                    </span>

                    <noscript>
                        <div>
                            {if $favorites eq true}
                            <a class="dzk_arrow showallforumslink" href="{modurl modname=Dizkus type=user func=prefs act=showallforums}" title="{gt text="Show all forums"}">{gt text="Show all forums"}</a>
                            {else}
                            <a class="dzk_arrow showfavoriteslink" href="{modurl modname=Dizkus type=user func=prefs act=showfavorites}" title="{gt text="Show favourites only"}">{gt text="Show favourites only"}</a>
                            {/if}
                        </div>
                    </noscript>
                </li>
            </ul>
            {/if}

            {if $signaturemanagement eq "yes"}
            <ul class="linklist z-clearfix">
                <li>
                    {gt text="Posting display settings"}<br />
                    <a class="dzk_arrow subscribelink" href="{modurl modname=Dizkus type=user func=signaturemanagement}" title="{gt text="Manage your signature"}">{gt text="Manage your signature"}</a>
                </li>
            </ul>
            {/if}

            {if $ignorelist_handling ne "none" and $contactlist_available}
            <ul class="linklist z-clearfix">
                <li>
                    {gt text="'Ignore list' settings"}<br />
                    <a class="dzk_arrow subscribelink" href="{modurl modname=Dizkus type=user func=ignorelistmanagement}" title="{gt text="Manage settings for the list of users that you are ignoring"}">{gt text="Manage settings for your ignore list"}</a>
                </li>
            </ul>
            {/if}

            <ul class="linklist z-clearfix">
                <li>
                    {gt text="Autosubscribe to new topics"}<br />
                    <span id="javascriptautosubscription" class="dzk_arrow autosubscribelink hidden">
                    {if $autosubscribe eq true}
                    <input id="newtopicautosubscribe" name="newtopicautosubscribe" type="checkbox" value="1" checked="checked" style="margin-right: 1em;" />
                    <span id="autosubscription">{gt text="Yes, autosubscribe to new topics"}</span>
                    <span id="noautosubscription" class="hidden">{gt text="No, do not autosubscribe to new topics"}</span>
                    {else}
                    <input id="newtopicautosubscribe" name="newtopicautosubscribe" type="checkbox" value="1" style="margin-right: 1em;" />
                    <span id="autosubscription" class="hidden">{gt text="Yes, autosubscribe to new topics"}</span>
                    <span id="noautosubscription">{gt text="No, do not autosubscribe to new topics"}</span>
                    {/if}
                    <img id="progressautosubscribe" style="visibility: hidden;" src="images/ajax/indicator.white.gif" width="16" height="16" alt="{gt text="Working. Please wait..."}" />
                    </span>

                    <noscript>
                        <div>
                            {if $autosubscribe eq false}
                            {gt text="(You are not subscribing new topics by default now)"}<br />
                            <a class="dzk_arrow autosubscribelink" href="{modurl modname=Dizkus type=user func=prefs act=autosubscribe}" title="{gt text="Autosubscribe to new topics"}">{gt text="Yes, autosubscribe to new topics"}</a>
                            {else}
                            {gt text="(You are subscribing new topics by default now)"}< br />
                            <a class="dzk_arrow autosubscribelink" href="{modurl modname=Dizkus type=user func=prefs act=noautosubscribe}" title="{gt text="Do not autosubscribeto new topics"}">{gt text="No, do not autosubscribeto new topics"}</a>
                            {/if}
                        </div>
                    </noscript>
                </li>
            </ul>

        </div>
    </div>

    <h2>{gt text="Personal settings for each forum"}</h2>
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
</div>
