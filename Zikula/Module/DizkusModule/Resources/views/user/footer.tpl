    {if $modvars.ZikulaDizkusModule.forum_enabled neq 'no'}
    <div class="dzk_marginbottom">
        {formutil_getpassedvalue name='func' default='index' assign='func'}
        {if ($func eq 'index' OR $func eq 'viewforum') AND isset($forum)}
            <h3 class="footer_title">{gt text="Posts"}</h3>
            <ul id="ctheme-legenda">
                <li>
                    <span class="icon-stack">
                        <i class="icon-comments icon-stack-base"></i>
                        <i class="icon-star icon-overlay-upper-left icon-blue"></i>
                    </span>
                    {gt text="New posts since your last visit on %s" tag1=$last_visit_unix|dateformat:'datetimebrief'}
                </li>
                <li>
                    <span class="icon-stack">
                        <i class="icon-comments icon-stack-base"></i>
                        <i class="icon-ok icon-overlay-lower-right icon-green"></i>
                    </span>
                    {gt text="No new posts since your last visit on %s" tag1=$last_visit_unix|dateformat:'datetimebrief'}
                </li>
                <li><strong>{gt text='The time is now'} {$smarty.now|dateformat:'datetimebrief'}</strong></li>
            </ul>
        {/if}
<!--
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

        {if $online.numguests > 0 and $anonsessions == 1}
            <li>
                {gt text='and %1$s anonymous guest' plural='and %1$s anonymous guests' count=$online.numguests tag1=$online.numguests}
            </li>
        {/if}
        </ul>
        <p class="z-sub">{gt text='This list is based on users active over the last %s minutes.' tag1=$mins}</p>
    </div>
    {/if}
-->
</div><!-- div opened in user/header.tpl -->