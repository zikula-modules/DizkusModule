{img src='icon_dbaccess.gif' assign='progressicon'}
{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="options" size="small"}
    <h3>{gt text="Edit forum tree"}</h3>
</div>

<div id="dizkus_admin">

    <p class="z-informationmsg">{gt text="Notice: You can use drag and drop to arrange the order of forums and categories within the forum tree. Your changes will be saved when you click on the 'Save tree order' button."}</p>

    <div class="z-clearfix" style="margin-top: 1em;">
        {button class="z-floatleft z-button z-bt-small createnewcategory" src="edit_add.png" set="icons/extrasmall" __alt="Create new category" __title="Create new category" __text="Create new category"}
        <img id="progressnewcategoryimage" style="visibility: hidden;" src="images/ajax/indicator.white.gif" width="16" height="16" alt="{gt text="Working. Please wait..."}" />
        <div id="dizkusinformation" class="z-floatleft" style="margin-left: 3em; visibility: hidden;">&nbsp;</div>
    </div>

    <ul id="category">
        {foreach item='category' from=$categorytree}
        {include file='ajax/singlecategory.tpl'}
        {/foreach}
    </ul>

    <input type="hidden" id="authid" name="authid" value="" />

    {button class="z-floatleft z-button z-bt-small createnewcategory" src="edit_add.png" set="icons/extrasmall" __alt="Create new category" __title="Create new category" __text="Create new category"}

    <script type="text/javascript">
        // <![CDATA[
        var storingnewsortorder = '{{gt text="Storing new sort order..."}}';
        // ]]>
    </script>

</div>

{adminfooter}