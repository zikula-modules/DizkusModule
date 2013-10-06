{include file='user/header.tpl' __templatetitle='New topic'}

<div id="newtopicpreview" style="display: none;">&nbsp;</div>

{if $preview}
    <div id="nonajaxnewtopicpreview">
        {include file='user/topic/newpreview.tpl'}
    </div>
{/if}

{form role="form"}
{formvalidationsummary}
<div class="panel panel-info">
    <div class="panel-heading">
        <h3>{gt text="New topic in '%s' forum" tag1=$forum.name}</h3>
    </div>
    <div class="panel-body">
        <div class="form-group">
            {formlabel for="title" __text="Subject line"}
            {formtextinput id="title" size="80" maxLength="100" mandatory=true cssClass="form-control"}
        </div>

        <div class="form-group">
            {formlabel for="message" __text="Message"}
            {formtextinput id="message" textMode="multiline" rows="10" cols="60" maxLength="65527" mandatory=true cssClass="form-control"}
        {if $modvars.ZikulaDizkusModule.striptags == 'yes'}
            <span class='help-block'>{gt text="No HTML tags allowed (except inside [code][/code] tags)"}</span>
        {/if}
        </div>

        <div class="form-group">
            {notifydisplayhooks eventname='dizkus.ui_hooks.post.ui_edit' id=null}
            {notifydisplayhooks eventname='dizkus.ui_hooks.topic.ui_edit' id=null}
        </div>

        {if $coredata.logged_in}
        <div><strong>{gt text="Options"}</strong></div>
        <div class="checkbox">
            {formcheckbox id="attachSignature" checked=1}
            {formlabel for="attachSignature" __text="Attach my signature"}
        </div>
        <div class="checkbox">
            {formcheckbox id="subscribe_topic" checked=1}
            {formlabel for="subscribe_topic" __text="Notify me when a reply is posted"}
        </div>
        {/if}

        {formbutton class="btn btn-success" commandName="save"   __text="Submit"}
        {formbutton class="btn btn-info" commandName="preview" __text="Preview"}
        {formbutton class="btn btn-danger" commandName="cancel" __text="Cancel"}
    </div>
</div>
{/form}

{include file='user/footer.tpl'}