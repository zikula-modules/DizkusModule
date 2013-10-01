{include file='user/header.tpl'}

<div class="panel panel-default">
    <div class="panel-heading"><h2 class='icon-home'>&nbsp;{gt text="Forums index page"}</h2>
        {if isset($numposts)}
        <div style='position:absolute; top:0; right:0; padding: 2em 1em;'>
            <a title="{gt text="RSS Feed"}" href="{modurl modname=$module type='user' func='feed'}"><i class='icon-rss-sign icon-150x icon-orange'></i></a>
            {gt text="Total posts: %s" tag1=$numposts}
        </div>
        {/if}
    </div>
    {foreach item='parent' from=$forums}
        {include file='user/forum/singleforumtable.tpl'}
    {/foreach}
    <div class='panel-footer'>
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
</div>

{include file='user/footer.tpl'}

<hr />
<h1>Some ideas --remove this later</h1>
<p class='z-clearfix'><i class="icon-quote-left icon-3x pull-left icon-muted"></i>
    A pull quote or possibly internal styling of [quote].</p>
<p class='z-clearfix'><i class="icon-flag icon-2x pull-left icon-border"></i>
    A flag in a box.</p>
<span title='Topic is sticky (it will always stay at the top of the topics list)' class="icon-stack icon-2x tooltips">
  <i class="icon-comments-alt icon-stack-base icon-muted"></i>
  <i class="icon-lock icon-overlay-lower-right"></i>
  <i class="icon-certificate icon-overlay-upper-left icon-orange"></i>
</span>
multiple layers of icons with tooltips too<br>
<span class="icon-stack icon-2x">
  <i class="icon-comment icon-stack-base icon-muted"></i>
  <i class="icon-plus icon-overlay-lower-right text-success"></i>
</span>
create a topic<br>
<span class="icon-stack icon-2x">
  <i class="icon-comments icon-stack-base icon-muted"></i>
  <i class="icon-star icon-overlay-lower-right text-info"></i>
</span>
star (or new) topic (or maybe unread)<br>
<span class="icon-stack icon-2x">
  <i class="icon-comments icon-stack-base icon-muted"></i>
  <i class="icon-ok icon-overlay-lower-right text-success"></i>
</span>
a topic that has been fully read<br>
<span class="icon-stack icon-2x">
  <i class="icon-circle icon-stack-base icon-muted"></i>
  <i class="icon-pushpin text-danger"></i>
</span>
a pinned/sticky topic<br>