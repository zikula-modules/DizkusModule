<li id="category_{$category.cat_id}" class="catline existing">
    <div class="z-clearfix">
        <div class="{if $newcategory <> true}dzk_handle {/if}z-clearfix">
            <div id="categorytitle_{$category.cat_id}" style="float: left; width: 60%;">
                {if $newcategory <> true}
                <a title="{gt text="Visit this forum category"}" href="{modurl modname=Dizkus type=user func=main viewcat=$category.cat_id}">{$category.cat_title}</a> (ID:{$category.cat_id})
                {else}
                {$category.cat_title} ({gt text="new category"})
                {/if}
            </div>
            <div class="z-buttons" style="float: right; width: 30%; text-align: right; padding-right: 1em;">
                <button id="hidecategory_{$category.cat_id}" class="z-bt-small" style="display: none;" title="{gt text="Hide category"}">{img modname='Dizkus' src="icon_hide.gif"}</button>
                <button id="showcategory_{$category.cat_id}" class="z-bt-small" style="display: none;" title="{gt text="Show category"}">{img modname='Dizkus' src="icon_show.gif"}</button>
                <button id="hideforumlist_{$category.cat_id}" class="z-bt-small" style="display: none;" title="{gt text="Hide forums"}">{img modname='Dizkus' src="icon_hideforums.gif"}</button>
                <button id="showforumlist_{$category.cat_id}" class="z-bt-small" title="{gt text="Show forums"}">{img modname='Dizkus' src="icon_showforums.gif"}</button>
                <button id="addforum_{$category.cat_id}" class="z-bt-small" title="{gt text="Create forum"}">{img modname='Dizkus' src="icon_addforum.gif" __alt="Create forum" }</button>
                {if $newcategory == true}
                <button id="canceladdcategory_{$category.cat_id}" class="z-bt-small" title="{gt text="Cancel"}">{img modname='Dizkus' src="icon_cancel.gif" __alt="Cancel" }</button>
                {/if}
                <img id="progresscategoryimage_{$category.cat_id}" style="visibility: hidden; margin-left: 5px;" src="images/ajax/indicator.white.gif" width="16" height="16" alt="{gt text="Working. Please wait..."}" />
            </div>
        </div>
        <form class="z-form" id="editcategoryform_{$category.cat_id}" action="javascript: void(0);" method="post">
            <div id="editcategorycontent_{$category.cat_id}" style="{if $newcategory <> true}display: none; {/if}margin: 0 1em;">
                <fieldset>
                    <legend>{gt text="Edit category title"}</legend>
                    <input type="hidden" name="cat_id" value="{$category.cat_id}" />
                    <div class="z-formrow">
                        <label for="cat_title_{$category.cat_id}">{gt text="Category"}</label>
                        <input id="cat_title_{$category.cat_id}" name="cat_title" type="text" value="{$category.cat_title}" size="50" maxlength="100" />
                    </div>
                    <div class="z-formrow"  {if $category.forums|@count <> 0} style="display: none;"{/if} id="deletecategory_{$category.cat_id}">
                        <label for="delete_{$category.cat_id}">{gt text="Delete this category"}</label>
                        <input name="delete" id="delete_{$category.cat_id}" type="checkbox" value="delete" />
                    </div>
                    {if $newcategory eq true}
                    <input type="hidden" name="add" value="add" />
                    {/if}
                </fieldset>
                <div class="z-formbuttons z-buttons">
                    {button id="submitcategory_`$category.cat_id`" src="button_ok.png" set="icons/extrasmall" __alt="Save" __title="Save" __text="Save"}
                </div>
            </div>
        </form>
    </div>
    <ul id="cid_{$category.cat_id}" class="dzk_treeforumlist" style="display: none;">
        <li id="emptycategory_{$category.cat_id}" class="z-informationmsg" style="{if $category.forums|@count <> 0}display: none; {/if}">
            {gt text="This category does not yet contain any forums."}
        </li>
        {if $category.forums|@count <> 0}
        {foreach item='forum' from=$category.forums}
        {include file='ajax/singleforum.tpl'}
        {/foreach}
        {/if}
    </ul>
</li>
