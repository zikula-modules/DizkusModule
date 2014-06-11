<fieldset>
    <legend><i class='fa fa-comment fa-lg'></i> {gt text="Dizkus"}</legend>
        {if !isset($topic)}
            <div class="form-group">
                <div class="col-lg-3 control-label">
                    <label for="dizkus_checkbox">{gt text="Enable commenting on this item"}</label>
                </div>
                <div class="col-lg-9">
                    <div class="checkbox">
                        <input id="dizkus_checkbox" name="dizkus[createTopic]" type="checkbox" value="1" {if $newTopic}checked="checked"{/if} />
                    </div>
                </div>
            </div>
        {else}
            <div class='alert alert-success'>
                <i class='fa fa-check fa-lg'></i> {gt text="A discussion topic has been created for this item." tag1=$forum}
                [<a href="{modurl modname=$module type='user' func='viewtopic' topic=$topic.topic_id}">{gt text='View topic'}</a>]
                {gt text="Updated values here will be reflected in the discussion topic."}
            </div>
            <input type="hidden" name="dizkus[createTopic]" value="1" />
        {/if}
</fieldset>