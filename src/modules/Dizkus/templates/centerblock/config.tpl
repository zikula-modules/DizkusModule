<tr>
    <td>
        <label for="cb_template">{gt text="Name of template file" domain="module_dizkus"}</label>
    </td>
    <td>
        <input value="{$vars.cb_template|safetext}" maxlength="100" size="40" name="cb_template" id="cb_template" type="text" />
    </td>
</tr>
<tr>
    <td>
        <label for="cb_parameters">{gt text="Parameters" domain="module_dizkus"}</label>
    </td>
    <td>
        <input value="{$vars.cb_parameters|safetext}" maxlength="100" size="40" name="cb_parameters" id="cb_parameters" type="text" /><br />
        {gt text="Notice: Enter a comma-separated list. Example: 'maxposts=5,forum_id=27'." domain="module_dizkus"}
    </td>
</tr>
