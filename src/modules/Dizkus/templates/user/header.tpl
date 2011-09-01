{ajaxheader modname='Dizkus' ui=true}
{pageaddvar name='javascript' value='javascript/helpers/Zikula.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_user.js'}

{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        Zikula.UI.Tooltips($$('.tooltips'));
    });
</script>
{/pageaddvarblock}

{browserhack condition="if gt IE 6"}
<script type="text/javascript" src="{$baseurl}modules/Dizkus/javascript/niftycube.js"></script>
<script type="text/javascript">
    // <![CDATA[
    document.observe('dom:loaded', function() { Nifty("div.dzk_rounded","transparent") });
    // ]]>
</script>
{/browserhack}

{formutil_getpassedvalue name='func' default='main' assign='func'}
{* set the page title *}
{if !isset($maintitle)}
{assign var='maintitle' value=''}
{/if}
{if $func eq 'main'}
{gt text='Forum' assign='maintitle'}
{elseif $func eq 'viewforum' AND isset($forum)}
{assign var='maintitle' value=$forum.cat_title|cat:' - '|cat:$forum.forum_name}
{elseif $func eq 'viewtopic' AND isset($topic)}
{assign var='maintitle' value=$topic.topic_title}
{elseif $func eq 'newtopic'}
{gt text='New topic in forum' assign='maintitle'}
{/if}
{if $maintitle neq ''}
{pagesetvar name='title' value=$maintitle}
{/if}

{insert name='getstatusmsg'}

<div id="dizkus">

    <a id="top" accesskey="t"></a>

    {if $coredata.Dizkus.forum_enabled neq 'no'}
    <div class="dzk_navbar dzk_rounded">
        <div class="inner z-clearfix">
            {* start of breadcrumbs *}
            {strip}
            <ul class="linklist navlinks z-clearfix">
                <li class="icon-home">
                    <a class="dzk_arrow homelink tooltips" title="{gt text='Go to forums index page'}" href="{modurl modname='Dizkus' type='user' func='main'}">{gt text="Forums index page"}</a>
                </li>
                {if $func eq 'main' AND $view_category neq -1}
                <li>
                    <span class="tooltips" title="{gt text='Category'}">{gt text="&nbsp;::&nbsp;"}{$view_category_data.cat_title|safetext}</span>
                </li>
                {elseif ($func eq 'viewforum' OR $func eq 'moderateforum') AND isset($forum)}
                <li>
                    {gt text="&nbsp;::&nbsp;"}<a class="tooltips" title="{gt text="Go to category"}: {$forum.cat_title|safetext}" href="{modurl modname='Dizkus' type=user func=main viewcat=$forum.cat_id}">{$forum.cat_title|safetext}</a>
                </li>
                {elseif $func eq 'viewtopic' AND isset($topic)}
                <li>
                    {gt text="&nbsp;::&nbsp;"}<a class="tooltips" title="{gt text="Go to category"}: {$topic.cat_title|safetext}" href="{modurl modname='Dizkus' type=user func=main viewcat=$topic.cat_id}">{$topic.cat_title|safetext}</a>
                </li>
                {elseif $func eq 'newtopic'}
                <li>
                    {gt text="&nbsp;::&nbsp;"}<a class="tooltips" title="{gt text="Go to category"}: {$newtopic.cat_title|safetext}" href="{modurl modname='Dizkus' type=user func=main viewcat=$newtopic.cat_id}">{$newtopic.cat_title|safetext}</a>
                </li>
                {/if}
                {if ($func eq 'viewforum' OR $func eq 'moderateforum') AND isset($forum)}
                {if $forum.is_subforum == 1}
                <li>
                    {gt text="&nbsp;::&nbsp;"}
                    <span class="tooltips" title="{gt text='Forum name'}">{getForumName id=$forum.cat_id}</span>
                </li>
                {/if}
                <li>
                    <span class="tooltips" title="{gt text='Forum name'}">{gt text="&nbsp;::&nbsp;"}{$forum.forum_name|safetext}</span>
                </li>
                {/if}
                {if $func eq 'viewtopic' AND isset($topic)}
                <li>
                    {gt text="&nbsp;::&nbsp;"}<a class="tooltips" title="{$topic.forum_name|safetext}" href="{modurl modname='Dizkus' type=user func=viewforum forum=$topic.forum_id}">{$topic.forum_name|safetext}</a>
                </li>
                {/if}
                {if $favorites|default:false}
                <li>&nbsp;<em>({gt text="Favourites"})</em></li>
                {/if}
            </ul>
            {/strip}
            {* end of breadcrumbs *}

            <ul class="linklist z-clearfix" style="float:right;">
                {if $coredata.logged_in eq 1 AND $func eq 'main' AND $coredata.Dizkus.favorites_enabled eq 'yes'}
                {if $favorites}
                <li><a class="dzk_arrow showallforumslink tooltips" href="{modurl modname=Dizkus type=user func=prefs act=showallforums}" title="{gt text="Show all forums"}">{gt text="Show all forums"}</a></li>
                {else}
                <li><a class="dzk_arrow showfavoriteslink tooltips" href="{modurl modname=Dizkus type=user func=prefs act=showfavorites}" title="{gt text="Show favourite forums only"}">{gt text="Show favourite forums only"}</a></li>
                {/if}
                {/if}
                <li><a class="dzk_arrow latestpostslink tooltips" title="{gt text="View latest posts"}" href="{modurl modname='Dizkus' type=user func=viewlatest}">{gt text="View latest posts"}</a></li>
                <li><a class="dzk_arrow searchlink tooltips" title="{gt text="Search forums"}" href="{modurl modname='Search' type=user func=main}">{gt text="Search forums"}</a></li>
                {if $coredata.logged_in neq 1}
                <li><a class="dzk_arrow loginlink tooltips" title="{gt text="Log-in"}" href="{modurl modname="Users" type="user" func="loginscreen"}">{gt text="Log-in"}</a></li>
                <li><a class="dzk_arrow registerlink tooltips" title="{gt text="Register"}" href="{modurl modname="Users" type="user" func="register"}">{gt text="Register"}</a></li>
                {else}
                <li><a class="dzk_arrow searchpostslink tooltips" title="{gt text="View your posts"}" href="{$coredata.user.uname|searchlink}">{gt text="View your posts"}</a></li>
                <li><a class="dzk_arrow configurelink tooltips" title="{gt text="Personal settings"}" href="{modurl type="user" modname="Dizkus" func="prefs"}">{gt text="Personal settings"}</a></li>
                {/if}
            </ul>
        </div>
    </div>
    {/if}

    {* print the subtitle *}
    {if $func eq 'main' AND $view_category eq -1}
    <h2>{gt text="Forums index page"}</h2>
    {elseif $func eq 'main' AND $view_category neq -1}
    <h2>{$view_category_data.cat_title|safetext}</h2>
    {elseif $func eq 'viewforum' AND isset($forum)}
    <h2>{$forum.forum_name|safetext}</h2>
    {elseif $func eq 'viewtopic' AND isset($topic)}
    <h2>
        {if $topic.access_topicsubjectedit eq 1}
        <span class="editabletopicheader tooltips" id="edittopicsubjectbutton_{$topic.topic_id}" title="{gt text="Click to edit"}">
            {$topic.topic_title|safetext}
        </span>
        {else}
        <span class="noneditabletopicheader">
            {$topic.topic_title|safetext}
        </span>       
        {/if}
        <a class="dzk_notextdecoration" title="{gt text="Bottom"}" href="#bottom">&nbsp;{img modname='Dizkus' src="icon_bottom.gif" __alt="Bottom"}</a>
    </h2>
    {elseif $maintitle neq ''}
    <h2>{$maintitle|safetext}</h2>
    {/if}
