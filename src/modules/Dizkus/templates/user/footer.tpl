{if $coredata.Dizkus.forum_enabled neq 'no'}

<div class="dzk_marginbottom">
    {formutil_getpassedvalue name='func' default='main' assign='func'}
    {if ($func eq 'main' OR $func eq 'viewforum') AND isset($forum)}
    <h3 class="footer_title">{gt text="Posts"}</h3>
    <ul id="ctheme-legenda">
        <li>
            {img modname='Dizkus' src='icon_redfolder.gif' __alt='New posts since your last visit'  __title="New posts since your last visit"}
            {gt text="New posts since your last visit on %s" tag1=$last_visit_unix|dateformat:'datetimebrief'}
        </li>
        <li>
            {img modname='Dizkus' src='icon_folder.gif' __alt='No new posts since your last visit'  __title="New posts since your last visit"}
            {gt text="No new posts since your last visit on %s" tag1=$last_visit_unix|dateformat:'datetimebrief'}
        </li>
    </ul>
    {/if}

    {if $func eq 'main' && $numposts}
    <h3 class="footer_title">{gt text="Total"}</h3>
    <ul>
        <li>
            <a title="{gt text="RSS"}" href="backforum.php">{img modname='Dizkus' src='icon_rss.gif' __alt='RSS'  __title="RSS"}</a>
            {gt text="Total posts: %s" tag1=$numposts}
        </li>
    </ul>
    {/if}

    {dizkusonline assign='online'}
    {assign var='anonsessions' value=$modvars.ZConfig.anonymoussessions}
    {assign var='mins' value=$modvars.ZConfig.secinactivemins}

    <h3 class="footer_title">{gt text="Users on-line"}</h3>
    <ul id="ctheme_onlinelist" class="z-clearfix">
        {if $online.numusers > 0}
        {foreach name='onlineusers' item='user' from=$online.unames}
        <li>
            {if $user.admin == '1'}{$user.uname|profilelinkbyuname}{else}{$user.uname|profilelinkbyuname}{/if}{if !$smarty.foreach.onlineusers.last}, {/if}
        </li>
        {/foreach}
        {else}
        <li>
            {gt text="0 users"}
        </li>
        {/if}

        {if $online.numguests > 0 or $anonsessions == 1}
        <li>
            {gt text='and %1$s anonymous guest' plural='and %1$s anonymous guests' count=$online.numguests tag1=$online.numguests}
        </li>
        {/if}
    </ul>
    <p class="z-sub">{gt text='This list is based on users active over the last %s minutes.' tag1=$mins}</p>
</div>

{/if}
</div>