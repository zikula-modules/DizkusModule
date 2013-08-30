<div class="z-formrow">
    <label for="cb_template">{gt text="Name of template file" domain="module_dizkus"}</label>
    <input value="{$vars.cb_template|safetext}" maxlength="100" size="40" name="cb_template" id="cb_template" type="text" />
</div>
<div class="z-formrow">
    <label for="cb_parameters">{gt text="Parameters" domain="module_dizkus"}</label>
    <input value="{$vars.cb_parameters|safetext}" maxlength="100" size="40" name="cb_parameters" id="cb_parameters" type="text" />
    <p class='z-formnote z-informationmsg'>{gt text="Notice: Enter a comma-separated list. Example: 'maxposts=5,forum_id=27'." domain="module_dizkus"}<br />
        {gt text="Allowed parameters:" domain="module_dizkus"} <span style='font-family:monospace;'>maxposts, forum_id, user_id, canread, favorites, show_m2f, show_rss</span></p>
</div>