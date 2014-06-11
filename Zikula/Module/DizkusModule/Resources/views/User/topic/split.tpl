{gt text="Split topic" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='User/header.tpl'}

{form cssClass="form-horizontal" role="form"}
{formvalidationsummary}
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{$templatetitle}</h2>
    </div>
    <div class='panel-body'>
        <div class="col-lg-offset-3 col-lg-9 alert alert-info">
            {gt text="Notice: This will split the topic before the selected posting."}
        </div>
        <div class="form-group">
            {formlabel for="newsubject" __text="Subject for new topic" class="col-lg-3 control-label"}
            <div class="col-lg-9">
                {formtextinput id="newsubject" size="40" maxLength="100" cssClass='form-control'}
            </div>
        </div>
        <div class="col-lg-offset-3 col-lg-9">
            {formbutton class="btn btn-success" commandName="split" __text="Split"}
            {formbutton class="btn btn-danger" commandName="cancel" __text="Cancel"}
        </div>
    </div>
</div>
{/form}

{include file='User/footer.tpl'}