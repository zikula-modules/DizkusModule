<div id="dzk_search">
    <div>
        <input type="checkbox" name="active[Dizkus]" id="active_Dizkus" value="1" {if $active}checked="checked"{/if} />
        <label for="active_Dizkus">{gt text='Search forums' domain="module_dizkus"}</label>
        <div class="z-formnote z-informationmsg">{gt text='Notice: For forum searches, your search query must be between %1$s and %2$s characters long.' tag1=$coredata.Dizkus.minsearchlength tag2=$coredata.Dizkus.maxsearchlength domain="module_dizkus"}</div>
    </div>

    <input type="hidden" name="Dizkus_startnum" value="0" />

    <dl>
        <dt>
            <label for="Dizkus_forum">{gt text='Category and forum:' domain="module_dizkus"}</label>
        </dt>
        <dd>
            <select name="Dizkus_forum[]" id="Dizkus_forum" size="5" multiple="multiple">
                <option value="-1" selected="selected">{gt text='All forums' domain="module_dizkus"}</option>
                {foreach item='forum' from=$forums}
                <option value="{$forum.forum_id}">{$forum.cat_title|safetext} {gt text='&nbsp;::&nbsp;'} {$forum.forum_name|safetext}</option>
                {/foreach}
            </select>
        </dd>

        <dt>
            <label for="Dizkus_searchwhere">{gt text='Search in' domain="module_dizkus"}:</label>
        </dt>
        <dd>
            <select name="Dizkus_searchwhere" id="Dizkus_searchwhere" size="1">
                <option value="post" selected="selected">{gt text='Postings' domain="module_dizkus"}</option>
                <option value="author">{gt text='Author' domain="module_dizkus"}</option>
            </select>
        </dd>
    </dl>

</div>