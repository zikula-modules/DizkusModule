{if $modvars.ZikulaDizkusModule.ajax}
    {pageaddvar name='javascript' value='jQuery'}
{/if}

{formutil_getpassedvalue name='func' default='index' assign='func'}
{* set the page title *}
{if !isset($maintitle)}
    {assign var='maintitle' value=''}
{/if}
{if $func eq 'index'}
    {gt text='Forum' assign='maintitle'}
{elseif $func eq 'viewforum' AND isset($forum)}
    {*assign var='maintitle' value=$forum.cat_title|cat:' - '|cat:$forum.name*}
{elseif $func eq 'viewtopic' AND isset($topic)}
    {assign var='maintitle' value=$topic.title}
{elseif $func eq 'newtopic'}
    {gt text='New topic in forum' assign='maintitle'}
{/if}

{if isset($templatetitle)}
    {assign var='maintitle' value=$templatetitle}
{/if}


{if $maintitle neq ''}
    {pagesetvar name='title' value=$maintitle}
{/if}



{insert name='getstatusmsg'}

<div id="dizkus">

    <a id="top" accesskey="t"></a>

    {include file='user/navbar.tpl'}

