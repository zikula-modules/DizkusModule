{gt text="Move post from one topic to another" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='User/header.tpl'}

{form cssClass="form-horizontal" role="form"}
{formvalidationsummary}
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{$templatetitle}</h2>
    </div>
    <div class='panel-body'>
        <div class="form-group">
            {formlabel for="to_topic" text="ID of target topic" class="col-lg-3 control-label"}
            <div class="col-lg-9">
                {formintinput id="to_topic_id" size="10" maxLength="20" mandatory=true cssClass='form-control'}
            </div>
        </div>
        <div class="col-lg-offset-3 col-lg-9">
            {formbutton class="btn btn-success" commandName="move" __text="Move post"}
            {formbutton class="btn btn-danger" commandName="cancel" __text="Cancel"}
        </div>
    </div>
</div>
{/form}

{include file='User/footer.tpl'}