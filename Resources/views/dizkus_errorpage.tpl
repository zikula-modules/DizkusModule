{include file='user/header.tpl'}

<h2>{gt text='Error! The following problem occurred'}:</h2>
<br />
<div class="z-errormsg">
    {$error_text}<br /><br />
    {if $file neq ""}File: {$file}<br />{/if}
    {if $line neq ""}Line: {$line}<br />{/if}
    <br />
    <a title="{gt text='Send bug report to site administrator'}" href="mailto:{$adminmail|safetext}">{gt text='Send bug report to site administrator'}</a>
</div>

{include file='user/footer.tpl'}