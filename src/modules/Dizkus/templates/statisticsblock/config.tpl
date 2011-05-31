<tr>
    <td>
        <label for="sb_template">{gt text="Name of template file" domain="module_dizkus"}</label>
    </td>
    <td>
        <input value="{$vars.sb_template|safetext}" maxlength="100" size="40" name="sb_template" id="sb_template" type="text" />
    </td>
</tr>
<tr>
    <td>
        <label for="sb_parameters">{gt text="Parameters" domain="module_dizkus"}:</label>
    </td>
    <td>
        <input value="{$vars.sb_parameters|safetext}" maxlength="100" size="40" name="sb_parameters" id="sb_parameters" type="text" /><br />
        {gt text="Notice: Enter a comma-separated list. Example: 'maxposts=5,forum_id=27'." domain="module_dizkus"}
    </td>
</tr>
