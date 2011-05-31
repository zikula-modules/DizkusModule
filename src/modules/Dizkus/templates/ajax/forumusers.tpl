{dizkusonline assign='online'}
{assign var='anonsessions' value=$modvars.ZConfig.anonymoussessions}
{assign var='mins' value=$modvars.ZConfig.secinactivemins}

<p class="onlinetop">
    {gt text="Users on-line"}:
</p>

<ul id="dzk_onlinelist">
    {if $online.numusers > 0}
    {foreach name=onlineusers item=user from=$online.unames}
    <li>
        {if $user.admin == '1'}{$user.uname|profilelinkbyuname:"dzkadminuser"}{else}{$user.uname|profilelinkbyuname:"dzknoadminuser"}{/if}{if !$smarty.foreach.onlineusers.last}, {/if}
    </li>
    {/foreach}
    {if $online.numguests > 0 or $anonsessions == 1}
    <li>{gt text="and"}</li>
    {/if}
    {/if}

    {if $anonsessions == 1}
    <li>
        {$online.numguests} {if $online.numguests == 0}{gt text="anonymous guests"}{else}{if $online.numguests == 1}{gt text="anonymous guest"}{else}{gt text="anonymous guests"}{/if}{/if}
    </li>
    {/if}
</ul>

<p class="onlinehint">
    {gt text='This list is based on users active over the last %1$s minutes.' tag1=$mins}
</p>
