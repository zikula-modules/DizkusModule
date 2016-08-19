<div class="form-group">
    <label class="col-lg-3 control-label" for="dizkus_template">{gt text="Name of template file"}</label>
    <div class="col-lg-9">
        <input id="dizkus_template" type="text" class="form-control" name="dizkus[template]" value="{$vars.template|default:'recentposts.tpl'|safetext}" maxlength="100" />
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label" for="dizkus_params">{gt text="Parameters"}</label>
    <div class="col-lg-9">
        <input id="dizkus_params" type="text" class="form-control" name="dizkus[params]" value="{$vars.params|default:'maxposts=5'|safetext}" maxlength="100" />
        <p class="help-block">{gt text="Notice: Enter a comma-separated list. Example: 'maxposts=5,forum_id=27'."}<br />
            {gt text="Allowed parameters:"} <code>maxposts, forum_id, user_id, canread, favorites, show_m2f, show_rss</code></p>
    </div>
</div>