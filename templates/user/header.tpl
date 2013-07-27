{pageaddvar name='javascript' value='jQuery'}
{*pageaddvar name='javascript' value='jQuery-ui'}
{pageaddvar name="stylesheet" value="javascript/jquery-ui/themes/base/jquery-ui.css"}

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        // By suppling no content attribute, the library uses each elements title attribute by default
        jQuery('.tooltips').tooltip({
                position: {
                    my: "left bottom+45",
                    at: "left bottom"
                }
        });
    });
</script>*}

{browserhack condition="if gt IE 6"}
<script type="text/javascript" src="{$baseurl}modules/Dizkus/javascript/niftycube.js"></script>
<script type="text/javascript">
    // <![CDATA[
    document.observe('dom:loaded', function() { Nifty("div.dzk_rounded","transparent") });
    // ]]>
</script>
{/browserhack}

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

