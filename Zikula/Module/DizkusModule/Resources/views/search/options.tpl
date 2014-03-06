<div id="dzk_search">
    <input type="checkbox" name="active[{$module}]" id="active_{$module}" value="1" {if $active}checked="checked"{/if} />
    <label for="active_{$module}">{gt text='Search forum(s):'}</label>
    <input type="hidden" name="Dizkus_startnum" value="0" />
    <div class="form-group">
        <div class="col-md-11 col-md-offset-1">
            <p class="alert alert-info">{gt text='Notice: For forum searches, your search query must be between %1$s and %2$s characters long.' tag1=$modvars.ZikulaDizkusModule.minsearchlength tag2=$modvars.ZikulaDizkusModule.maxsearchlength domain="module_dizkus"}</p>
            <label for="Dizkus_forum">{gt text='Select forums'}:</label>
            <select name="modvar[{$module}][forum][]" id="Dizkus_forum" class='form-control' size="5" multiple="multiple">
                <option value="-1" selected="selected">{gt text='All forums'}</option>
                {foreach item='forum' from=$forums}
                    <option value="{$forum.value}">{$forum.text|safetext}</option>
                {/foreach}
            </select>
            <label for="Dizkus_searchwhere">{gt text='Search in'}:</label>
            <select name="modvar[{$module}][location]" id="Dizkus_searchwhere" class='form-control' size="1">
                <option value="post" selected="selected">{gt text='Postings'}</option>
                <option value="author">{gt text='Author'}</option>
            </select>
        </div>
    </div>
</div>