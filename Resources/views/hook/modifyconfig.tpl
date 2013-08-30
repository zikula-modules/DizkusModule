{admincategorymenu}
<div class="z-adminbox">
    <h1>{gt text=$ActiveModule}</h1>
    {modulelinks modname=$ActiveModule type='admin'}
</div>

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='Dizkus' src='admin.png'}</div>
    <h2>{gt text="Diskus settings for %s" tag1=$ActiveModule}</h2>
    <form class="z-form" action="{modurl modname=$ActiveModule type="admin" func="dizkushookconfigprocess"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="dizkus[dizkus_csrftoken]" value="{insert name="csrftoken"}" />
            <input type="hidden" name="ActiveModule" value="{$ActiveModule}" />

            {foreach from=$areas item='area'}
                {assign var='areaid' value=$area.sareaid}
                <fieldset>
                    <legend>{gt text='Dizkus hook option settings for area "%s"' tag1=$area.areatitle domain="module_dizkus"}</legend>
                    <div class="z-formrow">
                        <label for="dizkus_forum">{gt text="Forum to place hooked topics within:" domain="module_dizkus"}</label>
                        <select id='dizkus_forum' name='dizkus[{$areaid}][forum]' />
                        {foreach from=$forums item='forum'}
                            <option label="{$forum.text}" value="{$forum.value}" {if $dizkushookconfig.$areaid.forum eq $forum.value}selected="selectde"{/if}>{$forum.text}</option>
                        {/foreach}
                        </select>
                    </div>
                </fieldset>
            {/foreach}
            <div class="z-buttons z-formbuttons">
                {button src="button_ok.png" set="icons/extrasmall" class='z-btgreen' __alt="Save" __title="Save" __text="Save"}
                <a class='z-btred' href="{modurl modname=$ActiveModule type="admin" func='main'}" title="{gt text="Cancel"}">{img modname='core' src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </form>
</div>