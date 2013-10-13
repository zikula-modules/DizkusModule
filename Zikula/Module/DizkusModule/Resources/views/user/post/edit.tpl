{gt text="Edit post" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

{if $preview}
    <div id="editpostpreview" style="margin:1em 0;">
        {include file='user/post/single.tpl'}
    </div>
{/if}

<div id="dzk_newtopic" class="panel panel-info">
    <div class="panel-heading">
        <h3>{gt text="Edit post"}: {$topic_subject|safetext}</h3>
    </div>
    <div class="panel-body">
        {form role='form'}
        {formvalidationsummary}
        <div id="dizkusinformation" style="visibility: hidden;">&nbsp;</div>
        {*if $post.moderate eq true OR $post.edit_subject eq true}
        <div>
        <label for="subject">{gt text="Subject line"}</label><br />
        <input style="width: 98%" type="text" name="subject" size="80" maxlength="100" id="subject" tabindex="0" value="{$post.topic_subject|safehtml}" />
        </div>
        {/if*}
        <div class="form-group">
            {formlabel for="post_text" __text="Message body"}<br />
            {formtextinput cssClass="form-control" textMode="multiline" id="post_text" rows="10" cols="60"}
            {if $modvars.ZikulaDizkusModule.striptags == 'yes'}
                <p class='help-block'>{gt text="No HTML tags allowed (except inside [code][/code] tags)"}</p>
            {/if}
        </div>
        <div class="form-group">
            <div class='col-md-4'>
            {if $moderate eq true}
                <div><strong>{gt text="Options"}</strong></div>
                {if !$isFirstPost}
                    <div class="checkbox">
                        {formcheckbox id="delete"}
                        {formlabel for="delete" __text="Delete post"}
                        {notifydisplayhooks eventname='dizkus.ui_hooks.post.ui_delete' id=$post_id}
                    </div>
                {/if}
                <div class="checkbox">
                    {formcheckbox id="attachSignature"}
                    {formlabel for="attachSignature" __text="Attach my signature"}
                </div>
            {/if}

                {formbutton id="submit"  commandName="submit"  __text="Save"    class="btn btn-success"}
                {formbutton id="preview" commandName="preview" __text="Preview" class="btn btn-info"}
                {formbutton id="cancel"  commandName="cancel"  __text="Cancel"  class="btn btn-danger"}
            </div>
            <div class='col-md-8'>
                {notifydisplayhooks eventname='dizkus.ui_hooks.post.ui_edit' id=$post_id}
            </div>
        </div>


        {/form}
    </div>
 </div>
{include file='user/footer.tpl'}