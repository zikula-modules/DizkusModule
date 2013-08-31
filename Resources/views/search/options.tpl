<div id="dzk_search">
    <input type="hidden" name="Dizkus_startnum" value="0" />
    <div class="z-formnote z-informationmsg">{gt text='Notice: For forum searches, your search query must be between %1$s and %2$s characters long.' tag1=$modvars.Dizkus.minsearchlength tag2=$modvars.Dizkus.maxsearchlength domain="module_dizkus"}</div>
    <input type="checkbox" name="active[Dizkus]" id="active_Dizkus" value="1" {if $active}checked="checked"{/if} />
    <label for="active_Dizkus">{gt text='Search forum(s):' domain="module_dizkus"}</label>
    <select name="Dizkus_forum[]" id="Dizkus_forum" size="5" multiple="multiple">
        <option value="-1" selected="selected">{gt text='All forums' domain="module_dizkus"}</option>
        {foreach item='forum' from=$forums}
            <option value="{$forum.value}">{$forum.text|safetext}</option>
        {/foreach}
    </select>
    <label for="Dizkus_searchwhere">{gt text='Search in' domain="module_dizkus"}:</label>
    <select name="Dizkus_searchwhere" id="Dizkus_searchwhere" size="1">
        <option value="post" selected="selected">{gt text='Postings' domain="module_dizkus"}</option>
        <option value="author">{gt text='Author' domain="module_dizkus"}</option>
    </select>

</div>