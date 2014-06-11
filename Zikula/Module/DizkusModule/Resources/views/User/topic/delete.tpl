{if $modvars.ZikulaDizkusModule.ajax}
    {pageaddvar name="javascript" value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.User.TopicDelete.js'}
{/if}
{gt text="Delete topic" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='User/header.tpl' parent=$topic_id}

{form cssClass="form-horizontal"}
{formvalidationsummary}
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{gt text='Delete Topic'}: {$title}</h2>
    </div>
    <div class='panel-body'>
        <div class="col-lg-offset-3 col-lg-9 alert alert-danger">
            {gt text="Confirmation prompt: Do you really want to permanently delete this topic and all posts under this topic?"}
        </div>
        <div class="form-group">
            {formlabel for="sendReason" __text='Send a reason to the poster' class="col-lg-3 control-label"}
            <div class="col-lg-9">
                <div class="checkbox">
                    {formcheckbox id="sendReason" value=false}
                </div>
            </div>
        </div>
        <div class="form-group" id="dizkus_reason_container" {if $modvars.ZikulaDizkusModule.ajax}style="display:none"{/if}>
            {formlabel for="reason" text='Write a reason' class="col-lg-3 control-label"}
            {gt text='Your post "%s" was deleted, because ' tag1=$title assign='reason'}
            <div class="col-lg-9">
                {formtextinput id="reason" textMode="multiline" rows="3" cols="40" cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {notifydisplayhooks eventname='dizkus.ui_hooks.topic.ui_delete' id=$topic_id}
        </div>
        <div class="col-lg-offset-3 col-lg-9">
            {formbutton class="btn btn-success" commandName="save" __text="Delete"}
            {formbutton class="btn btn-danger" commandName="cancel" __text="Cancel"}
        </div>
    </div>
</div>      
{/form}

{include file='User/footer.tpl'}

