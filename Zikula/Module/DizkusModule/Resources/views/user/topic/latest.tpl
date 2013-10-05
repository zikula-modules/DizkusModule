{gt text="Latest forum posts" assign='templatetitle'}
{pagesetvar name=title value="`$templatetitle` - `$text`"}

{include file='user/header.tpl'}

<div id="latestposts">

    <h2>{gt text="Latest forum posts"} ({$text})</h2>

    <div class="roundedbar dzk_rounded">
        <div class="inner">
            <form class="dzk_form" method="post" action="{modurl modname=$module type='user' func='viewlatest'}">
                <ul class="linklist z-clearfix">
                    <li><a class="dzk_arrow" href="{modurl modname=$module type='user' func='viewlatest' selorder=3}">{gt text="Since Yesterday"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname=$module type='user' func='viewlatest' selorder=2}">{gt text="Today"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname=$module type='user' func='viewlatest' selorder=1}">{gt text="Last 24 hours"}</a></li>
                    <li><a class="dzk_arrow"></a><input type='hidden' name='selorder' value='5'><button type="submit">{gt text="Last"}</button> <input type="text" name="nohours" id="Dizkus_hours" size="3" value="{$nohours}" maxlength="3" tabindex="0" /> <button type="submit">{gt text="hours"}</button></li>
                </ul>
                <ul class="linklist z-clearfix">
                    <li><a class="dzk_arrow" href="{modurl modname=$module type='user' func='viewlatest' selorder=4}">{gt text="Last week"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname=$module type='user' func='viewlatest' selorder=unanswered}">{gt text="Unanswered"}</a></li>
                    <li><a class="dzk_arrow" href="{modurl modname=$module type='user' func='viewlatest' selorder=unsolved}">{gt text="Unsolved"}</a></li>
                        {if $last_visit_unix <> 0}
                        <li><a class="dzk_arrow" href="{modurl modname=$module type='user' func='viewlatest' selorder=6 last_visit_unix=$last_visit_unix}">{gt text="Last visit"}</a></li>
                        {/if}
                </ul>
            </form>
        </div>
    </div>

    {include file='user/forum/forumtopicstable.tpl'}

</div>

{include file='user/footer.tpl'}