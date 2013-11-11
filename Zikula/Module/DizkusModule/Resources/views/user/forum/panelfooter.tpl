<div class='panel-footer clearfix'>
    <div class='pull-left'>
        {dizkusonline assign='online'}
        {assign var='anonsessions' value=$modvars.ZConfig.anonymoussessions}
        {assign var='mins' value=$modvars.ZConfig.secinactivemins}
        <h3>{gt text="Users on-line"}</h3>
        <ul id="ctheme_onlinelist" class="z-clearfix">
            {if $online.numusers > 0}
                {foreach name='onlineusers' item='user' from=$online.unames}
                    <li>{if $user.admin == '1'}{$user.uname|profilelinkbyuname}{else}{$user.uname|profilelinkbyuname}{/if}{if !$smarty.foreach.onlineusers.last}, {/if}</li>
                {/foreach}
            {else}
                <li>{gt text="0 users"}</li>
            {/if}
            {if $online.numguests > 0 and $anonsessions == 1}
                <li>{gt text='and %1$s anonymous guest' plural='and %1$s anonymous guests' count=$online.numguests tag1=$online.numguests}</li>
            {/if}
        </ul>
        <p class="z-sub">{gt text='This list is based on users active over the last %s minutes.' tag1=$mins}</p>
    </div>
    <div id='forum-legend' class='pull-right'>
            <span class="fa-stack">
                <i class="fa fa-comments fa-stack-2x"></i>
                <i class="fa fa-star fa-stack-1x fa-overlay-upper-left fa-blue"></i>
            </span>
        {gt text="New posts since %s" tag1=$last_visit_unix|dateformat:'datetimebrief'}
    </div>
</div>