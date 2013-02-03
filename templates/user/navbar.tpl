<div class="dzk_navbar dzk_rounded">
    <div class="inner z-clearfix">

        {* bread crumbs menu *}
        <ul class="linklist navlinks z-clearfix">
            <li class="icon-home">
                <a class="dzk_arrow homelink tooltips" title="{gt text='Go to forums index page'}" href="{modurl modname='Dizkus' type='user' func='main'}">{gt text="Forums index page"}</a>
            </li>

            {if isset($breadcrumbs)}
            {foreach from=$breadcrumbs item='breadcrumb'}
            <li>
                {gt text="&nbsp;&raquo;&nbsp;"}
                <a class="tooltips" href="{$breadcrumb.url}" title="{$breadcrumb.title|safetext}">
                    {$breadcrumb.title|safetext}
                </a>
            </li>
            {/foreach}
            {/if}

            {if isset($templatetitle)}
            <li>
                <span class="tooltips">
                    {gt text="&nbsp;&raquo;&nbsp;"}{$templatetitle|safetext}
                </span>
            </li>
            {/if}

            {if isset($favorites) and $favorites}
                <li>&nbsp;<em>({gt text="Favourites"})</em></li>
            {/if}
        </ul>
        {* /bread crumbs menu *}


        <ul class="linklist z-clearfix" style="float:right;">
        {if $coredata.logged_in eq 1 AND $func eq 'main' AND $modvars.Dizkus.favorites_enabled eq 'yes'}
            {modapifunc modname='Dizkus' type='Favorites' func='getStatus' assign="favorites"}
            {if $favorites}
                <li><a class="dzk_arrow showallforumslink tooltips" href="{modurl modname=Dizkus type=user func=showallforums}" title="{gt text="Show all forums"}">{gt text="Show all forums"}</a></li>
                {else}
                <li><a class="dzk_arrow showfavoriteslink tooltips" href="{modurl modname=Dizkus type=user func=showfavorites}" title="{gt text="Show favourite forums only"}">{gt text="Show favourite forums only"}</a></li>
            {/if}
        {/if}
            <li><a class="dzk_arrow latestpostslink tooltips" title="{gt text="View latest posts"}" href="{modurl modname='Dizkus' type=user func=viewlatest}">{gt text="View latest posts"}</a></li>
            <li><a class="dzk_arrow searchlink tooltips" title="{gt text="Search forums"}" href="{modurl modname='Search' type=user func=main}">{gt text="Search forums"}</a></li>
        {if $coredata.logged_in neq 1}
            <li><a class="dzk_arrow loginlink tooltips" title="{gt text="Log-in"}" href="{modurl modname="Users" type="user" func="loginscreen"}">{gt text="Log-in"}</a></li>
            <li><a class="dzk_arrow registerlink tooltips" title="{gt text="Register"}" href="{modurl modname="Users" type="user" func="register"}">{gt text="Register"}</a></li>
            {else}
            <li><a class="dzk_arrow searchpostslink tooltips" title="{gt text="View your posts"}" href="{modurl modname="Dizkus" type="user" func="myposts"}">{gt text="View your posts"}</a></li>
            <li><a class="dzk_arrow configurelink tooltips" title="{gt text="Personal settings"}" href="{modurl modname="Dizkus" type="user" func="prefs"}">{gt text="Personal settings"}</a></li>
        {/if}
        {checkpermissionblock component="Dizkus::" instance=".*" level="ACCESS_ADMIN"}
            <li><a class="dzk_arrow adminlink tooltips" title="{gt text="Administrate Dizkus"}" href="{modurl modname="Dizkus" type="admin" func="main"}">{gt text="Administration"}</a></li>
        {/checkpermissionblock}
        </ul>
    </div>
</div>