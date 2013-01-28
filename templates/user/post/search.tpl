{gt text="Latest forum posts" assign=templatetitle}
{pagesetvar name=title value="`$templatetitle` - `$text`"}

{include file='user/header.tpl'}

<div id="latestposts">

    <h2>{$text}</h2>

    {* search menu *}
    <div class="roundedbar dzk_rounded">
        <div class="inner">
            <form class="dzk_form" method="post" action="{modurl modname='Dizkus' type=user func=viewlatest}">
                <ul class="linklist z-clearfix">
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type='user' func='myposts'}">{gt text="View your posts"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname='Dizkus' type='user' func='myposts' action='topics'}">{gt text="View your topics"}</a></li>
                </ul>
            </form>
        </div>
    </div>
    {* /search menu *}

    {include file='user/post/list.tpl'}

</div>

{include file='user/footer.tpl'}
