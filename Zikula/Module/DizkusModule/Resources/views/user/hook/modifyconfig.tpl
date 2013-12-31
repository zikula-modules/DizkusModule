{adminheader}
<h3><span class="fa fa-comments"></span>&nbsp;{gt text="Diskus settings for %s" tag1=$ActiveModule}</h3>

<form class="form-horizontal" action="{modurl modname=$ActiveModule type="admin" func="dizkushookconfigprocess"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="dizkus[dizkus_csrftoken]" value="{insert name="csrftoken"}" />
        <input type="hidden" name="ActiveModule" value="{$ActiveModule}" />

        {foreach from=$areas item='area'}
            {assign var='areaid' value=$area.sareaid}
            <fieldset>
                <legend>{gt text='Dizkus hook option settings for area "%s"' tag1=$area.areatitle domain="module_dizkus"}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="dizkus_forum">
                        {gt text="Forum to place hooked topics within:" domain="module_dizkus"}
                    </label>
                    <div class="col-lg-9">
                        <select class='form-control' id='dizkus_forum' name='dizkus[{$areaid}][forum]'>
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
            <a class='btn btn-danger' href="{modurl modname=$ActiveModule type="admin" func='main'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
        </div>
    </div>
</form>

{adminfooter}