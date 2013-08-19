<div class="z-formrow">
    <label for="sb_template">{gt text="Name of template file" domain="module_dizkus"}</label>
    <input value="{$vars.sb_template|safetext}" maxlength="100" size="40" name="sb_template" id="sb_template" type="text" />
</div>
<div class="z-formrow">
    <label for="sb_parameters">{gt text="Parameters" domain="module_dizkus"}:</label>
    <input value="{$vars.sb_parameters|safetext}" maxlength="100" size="40" name="sb_parameters" id="sb_parameters" type="text" />
    <p class='z-formnote z-informationmsg'>{gt text="Notice: Enter a comma-separated list. Example: 'maxposts=5,forum_id=27'." domain="module_dizkus"}<br />
    {gt text="Allowed parameters:" domain="module_dizkus"} <span style='font-family:monospace;'>maxposts, forum_id, user_id, canread, favorites, show_m2f, show_rss</span></p>
</div>