{ajaxheader modname='Dizkus' ui=true}

{pageaddvar name='javascript' value='jQuery'}

{if isset($modvars.Dizkus.ajax) && $modvars.Dizkus.ajax}
{pageaddvar name='javascript' value='javascript/helpers/Zikula.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_user.js'}
{/if}

{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        Zikula.UI.Tooltips($$('.tooltips'));
    });
</script>
{/pageaddvarblock}

{browserhack condition="if gt IE 6"}
<script type="text/javascript" src="{$baseurl}modules/Dizkus/javascript/niftycube.js"></script>
<script type="text/javascript">
    // <![CDATA[
    document.observe('dom:loaded', function() { Nifty("div.dzk_rounded","transparent") });
    // ]]>
</script>
{/browserhack}

{formutil_getpassedvalue name='func' default='main' assign='func'}
{* set the page title *}
{if !isset($maintitle)}
{assign var='maintitle' value=''}
{/if}
{if $func eq 'main'}
{gt text='Forum' assign='maintitle'}
{elseif $func eq 'viewforum' AND isset($forum)}
{*assign var='maintitle' value=$forum.cat_title|cat:' - '|cat:$forum.forum_name*}
{elseif $func eq 'viewtopic' AND isset($topic)}
{assign var='maintitle' value=$topic.topic_title}
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

