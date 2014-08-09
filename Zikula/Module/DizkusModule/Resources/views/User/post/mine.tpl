{gt text="Your forum posts" assign='templatetitle'}
{pagesetvar name=title value="`$templatetitle` - `$text`"}

{include file='User/header.tpl'}

<div id="latestposts">

    <nav class="navbar navbar-default" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-ex1-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li{if $action neq "topics"} class="active"{/if}><a href="{modurl modname=$module type='user' func='mine'}">{gt text="View your posts"}</a></li>
                <li{if $action eq "topics"} class="active"{/if}><a href="{modurl modname=$module type='user' func='mine' action='topics'}">{gt text="View your topics"}</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{$text}</h2>
        </div>
        {pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}
        {include file='User/forum/forumtopicstable.tpl'}
        {pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}
        {include file='User/forum/panelfooter.tpl'}
    </div>
</div>

{include file='User/footer.tpl'}