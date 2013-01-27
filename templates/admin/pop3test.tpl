{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="mail" size="small"}
    <h3>{gt text="POP3 test results"}</h3>
</div>

<div id="dizkus_admin">

    <form class="z-form" action="" method="post">
        <input type="hidden" name="forum_id" value="{$forum_id}" />
        <fieldset>
            <legend>{gt text="POP3 test results"}}</legend>
            {foreach item=message from=$messages}
            <p>{$message}</p>
            {/foreach}
        </fieldset>
        <div class="z-formbuttons z-buttons">
            <input type="submit" name="backtoforum" value="{gt text="Back to forum administration"}" />
        </div>
    </form>

</div>

{adminfooter}