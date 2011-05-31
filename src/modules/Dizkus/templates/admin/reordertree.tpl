{gt text="Edit forum tree" assign=templatetitle}
{img src='icon_dbaccess.gif' assign='progressicon'}
{include file='admin/header.tpl'}

<p class="z-informationmsg">{gt text="Notice: You can use drag and drop to arrange the order of forums and categories within the forum tree. Your changes will be saved when you click on the 'Save tree order' button."}</p>

<div class="z-clearfix" style="margin-top: 1em;">
    <button class="dzk_img add z-floatleft createnewcategory">{gt text="Create new category"}</button>
    <img id="progressnewcategoryimage" style="visibility: hidden;" src="images/ajax/indicator.white.gif" width="16" height="16" alt="{gt text="Working. Please wait..."}" />
    <div id="dizkusinformation" class="z-floatleft" style="margin-left: 3em; visibility: hidden;">&nbsp;</div>
</div>


<ul id="category">
    {foreach item='category' from=$categorytree}
    {include file='ajax/singlecategory.tpl'}
    {/foreach}
</ul>

<input type="hidden" id="authid" name="authid" value="" />

<button class="z-clearfix dzk_img add z-floatleft createnewcategory">{gt text="Create new category"}</button>

<script type="text/javascript">
    // <![CDATA[
    var storingnewsortorder = '{{gt text="Storing new sort order..."}}';
    // ]]>
</script>

{include file='admin/footer.tpl'}
