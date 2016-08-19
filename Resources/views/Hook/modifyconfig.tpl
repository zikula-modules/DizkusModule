{admincategorymenu}
<div class="z-admin-content clearfix">
    {modgetinfo modname=$activeModule info='displayname' assign='displayName'}
    {modgetimage modname=$activeModule assign='image'}
    {moduleheader modname=$activeModule type='admin' title=$displayName putimage=true image=$image}

<h3><span class="fa fa-comments"></span>&nbsp;{gt text="Dizkus settings for %s" tag1=$activeModule}</h3>

<form class="form-horizontal" action="{route name="zikuladizkusmodule_admin_hookconfigprocess"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="activeModule" value="{$activeModule}" />

        {foreach from=$areas item='area'}
            {assign var='areaid' value=$area.sareaid}
            <fieldset>
                <legend>{gt text='Dizkus hook option settings for area "%s"' tag1=$area.areatitle domain="module_dizkus"}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="dizkus_forum">
                        {gt text="Forum to place hooked topics within:" domain="module_dizkus"}
                    </label>
                    <div class="col-lg-9">
                        <select class="form-control" id='dizkus_forum' name='dizkus[{$areaid}][forum]'>
                        {foreach from=$forums item='forum'}
                            <option label="{$forum.text}" value="{$forum.value}" {if isset($dizkushookconfig.$areaid.forum) and ($dizkushookconfig.$areaid.forum eq $forum.value)}selected="selected"{/if}>{$forum.text}</option>
                        {/foreach}
                        </select>
                    </div>
                </div>
            </fieldset>
        {/foreach}
        <div class="col-lg-offset-3 col-lg-9">
            <input class="btn btn-success" type="submit" name="save" value="{gt text="Save"}" />
            <a class="btn btn-danger" href="{modurl modname=$activeModule type="admin" func='index'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
        </div>
    </div>
</form>

{adminfooter}