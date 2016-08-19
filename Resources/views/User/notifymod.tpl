{gt text="Notify moderator about this posting" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='User/header.tpl'}

<div class="panel panel-warning">
    <div class="panel-heading">
        <h2>{$templatetitle}</h2>
    </div>
    <div class="panel-body">
        {include file='User/post/single.tpl'}

        <div class="alert alert-warning">
            {gt text="A moderator will be notified about the selected posting.<br />Valid reasons are: Copyright violations, personal insults and so on.<br />The following are not valid reasons for moderation: Typos, difference of opinion on the topic et cetera."}
        </div>
        {form role='form'}
        {formvalidationsummary}
        <div class="form-group">
            {formlabel for="comment" __text="Your comment:"}
            {formtextinput id="comment" textMode="multiline" rows="6" cols="60" cssClass="form-control"}
        </div>
        {formbutton class="btn btn-success" commandName="send" __text="Send"}
        {formbutton class="btn btn-danger" commandName="cancel" __text="Cancel"}
        {/form}
    </div>
</div>

{include file='User/footer.tpl'}
