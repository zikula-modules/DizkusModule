<div class="dzk_navbar dzk_rounded">
    <div class="inner z-clearfix">

        {* bread crumbs menu *}
        <ul class="linklist navlinks z-clearfix">
            <li>
                {if ($smarty.get.func neq "index")}
                    <a class="tooltips icon-home" title="{gt text='Go to forums index page'}" href="{modurl modname=$module type='user' func='index'}">&nbsp;{gt text="Forums index page"}</a>
                {else}
                    <span class='icon-home'>&nbsp;{gt text="Forums index page"}</span>
                {/if}
            </li>

            {if isset($breadcrumbs)}
                {foreach from=$breadcrumbs item='breadcrumb'}
                    <li>
                        {gt text="&nbsp;&raquo;&nbsp;"}
                        <a class="tooltips" href="{$breadcrumb.url}" title="{$breadcrumb.title|safehtml|notifyfilters:'dizkus.filter_hooks.post.filter'}">
                            {$breadcrumb.title|safehtml|notifyfilters:'dizkus.filter_hooks.post.filter'}
                        </a>
                    </li>
                {/foreach}
            {/if}

            {if isset($templatetitle)}
                <li>
                    <span class="tooltips">
                        {gt text="&nbsp;&raquo;&nbsp;"}{$templatetitle|safehtml|notifyfilters:'dizkus.filter_hooks.post.filter'}
                    </span>
                </li>
            {/if}

            {modapifunc modname=$module type='Favorites' func='getStatus' assign="favorites"}
            {if isset($favorites) and $favorites}
                <li>&nbsp;<em>({gt text="Favorites"})</em></li>
                {/if}
        </ul>
        {* /bread crumbs menu *}


        <ul class="linklist z-clearfix" style="float:right;">
            {if $coredata.logged_in eq 1 AND $func eq 'index' AND $modvars.ZikulaDizkusModule.favorites_enabled eq 'yes'}
                {if $favorites}
                    <li><a class="tooltips icon-chevron-sign-right" href="{modurl modname=$module type='user' func='showallforums'}" title="{gt text="Show all forums"}">&nbsp;{gt text="Show all forums"}</a></li>
                    {else}
                    <li><a class="tooltips icon-chevron-sign-right" href="{modurl modname=$module type='user' func='showfavorites'}" title="{gt text="Show favourite forums only"}">&nbsp;{gt text="Show favourite forums only"}</a></li>
                    {/if}
                {/if}
            <li><a class="tooltips icon-chevron-sign-right" title="{gt text="View latest posts"}" href="{modurl modname=$module type='user' func='viewlatest'}">&nbsp;{gt text="View latest posts"}</a></li>
            <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Search forums"}" href="{modurl modname='Search' type='user' func='index'}">&nbsp;{gt text="Search forums"}</a></li>
                {if $coredata.logged_in neq 1}
                    {assign value="index.php?"|cat:$smarty.server.QUERY_STRING|urlencode var='redirect'}
                <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Log-in"}" href="{modurl modname="Users" type="user" func="login" returnpage=$redirect}">&nbsp;{gt text="Log-in"}</a></li>
                <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Register"}" href="{modurl modname="Users" type="user" func="register"}">&nbsp;{gt text="Register"}</a></li>
                {else}
                <li><a class="tooltips icon-chevron-sign-right" title="{gt text="View your posts"}" href="{modurl modname=$module type='user' func='mine'}">&nbsp;{gt text="View your posts"}</a></li>
                <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Personal settings"}" href="{modurl modname=$module type='user' func='prefs'}">&nbsp;{gt text="Personal settings"}</a></li>
                {/if}
                {checkpermissionblock component="Dizkus::" instance=".*" level="ACCESS_ADMIN"}
            <li><a class="tooltips icon-chevron-sign-right" title="{gt text="Administrate Dizkus"}" href="{modurl modname=$module type='admin' func='index'}">&nbsp;{gt text="Administration"}</a></li>
                {/checkpermissionblock}
        </ul>
    </div>
</div>