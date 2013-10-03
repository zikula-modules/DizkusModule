<ol class="breadcrumb clearfix">
    <li>
    {if ($smarty.get.func neq "index")}
        <a class="tooltips icon-home" title="{gt text='Go to forums index page'}" href="{modurl modname=$module type='user' func='index'}">&nbsp;{gt text="Forums index page"}</a>
    {else}
        <span class='icon-home'>&nbsp;{gt text="Forums index page"}</span>
    {/if}
    </li>

    {if isset($breadcrumbs)}
    {foreach from=$breadcrumbs item='breadcrumb'}
    <li><a href="{$breadcrumb.url}"">{$breadcrumb.title|safehtml|notifyfilters:'dizkus.filter_hooks.post.filter'}</a></li>
    {/foreach}
    {/if}

    {if isset($templatetitle)}
    <li><span>{$templatetitle|safehtml|notifyfilters:'dizkus.filter_hooks.post.filter'}</span></li>
    {/if}

    {modapifunc modname=$module type='Favorites' func='getStatus' assign="favorites"}
    {if isset($favorites) and $favorites}
        <li><em>{gt text="Favorites"}</em></li>
    {/if}
    <em class='pull-right'>{gt text='Current forum time:'} {$smarty.now|dateformat:'datetimebrief'}</em>
</ol>
{* ******************************************************
* MAIN NAVBAR
******************************************************* *}
<nav class="navbar navbar-inverse dizkus-main" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-main-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="{modurl modname=$module type='user' func='index'}">{gt text='Dizkus Forum'}</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div id="navbar-main-collapse" class="collapse navbar-collapse">
        <ul class="nav navbar-nav navbar-right">
            {if $coredata.logged_in eq 1 AND $func eq 'index' AND $modvars.ZikulaDizkusModule.favorites_enabled eq 'yes'}
                {if $favorites}
                    <li><a class='icon-comments' href="{modurl modname=$module type='user' func='showallforums'}" title="{gt text="Show all forums"}">&nbsp;{gt text="Show all forums"}</a></li>
                {else}
                    <li><a class='icon-heart' href="{modurl modname=$module type='user' func='showfavorites'}" title="{gt text="Show favourite forums only"}">&nbsp;{gt text="Show favourite forums only"}</a></li>
                {/if}
            {/if}
            <li><a class='icon-comments' title="{gt text="View latest posts"}" href="{modurl modname=$module type='user' func='viewlatest'}">&nbsp;{gt text="View latest posts"}</a></li>
            {if $coredata.logged_in neq 1}
                {assign value="index.php?"|cat:$smarty.server.QUERY_STRING|urlencode var='redirect'}
                <li><a title="{gt text="Log-in"}" href="{modurl modname="Users" type="user" func="login" returnpage=$redirect}">{gt text="Log-in"}</a></li>
                <li><a title="{gt text="Register"}" href="{modurl modname="Users" type="user" func="register"}">{gt text="Register"}</a></li>
                <li><a class='tooltips' title="{gt text="Search forums"}" href="{modurl modname='Search' type='user' func='index'}"><i class='icon-search'></i></a></li>
            {else}
                <li><a class='icon-comment' title="{gt text="View your posts"}" href="{modurl modname=$module type='user' func='mine'}">&nbsp;{gt text="View your posts"}</a></li>
                <li><a class='tooltips' title="{gt text="Search forums"}" href="{modurl modname='Search' type='user' func='index'}"><i class='icon-search'></i></a></li>
                <li><a class='tooltips' title="{gt text="Personal settings"}" href="{modurl modname=$module type='user' func='prefs'}"><i class='icon-user'></i><i class='icon-wrench'></i></a></li>
            {/if}
            {checkpermissionblock component="Dizkus::" instance=".*" level="ACCESS_ADMIN"}
                <li><a class='tooltips' title="{gt text="Administrate Dizkus"}" href="{modurl modname=$module type='admin' func='index'}"><i class='icon-cogs'></i></a></li>
            {/checkpermissionblock}
        </ul>
    </div><!-- /.navbar-collapse -->
</nav>