{gt text="POP3 test results" assign=templatetitle}
{include file='admin/header.tpl'}

<form class="z-form" action="" method="post">
    <input type="hidden" name="forum_id" value="{$forum_id}" />
    <fieldset>
        <legend>{$templatetitle}</legend>
        {foreach item=message from=$messages}
        <p>{$message}</p>
        {/foreach}
    </fieldset>
    <div class="z-formbuttons">
        <input type="submit" name="backtoforum" value="{gt text="Back to forum administration"}" />
    </div>
</form>

