{gt text="'Ignore list' settings" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>

<p class="z-informationmsg">
    {gt text="Users who are being ignored by a topic poster cannot post messages under this topic when 'strict' level is active. When 'medium' level is active, they can reply but their postings will generally not be shown to users who are ignoring the poster. Also, e-mail notifications will not be sent. Concealed postings will be shown when you click on the posting."}
</p>

{form cssClass="z-form"}
{formvalidationsummary}
<fieldset>
    <legend>{gt text="General settings"}</legend>
    <div class="z-formrow">
        {formlabel for="ignorelist_myhandling" __text="Level of ostracism"}
        {formdropdownlist id="ignorelist_myhandling" items=$ignorelist_options selectedValue=$ignorelist_myhandling}
    </div>
</fieldset>
<div class="z-formbuttons z-buttons">
    {formbutton commandName="update" __text="Save" class="z-bt-ok"}
</div>
{/form}

{include file='user/footer.tpl'}
