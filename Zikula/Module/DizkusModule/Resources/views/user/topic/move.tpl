{gt text="Move or join topics" assign='templatetitle'}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl' parent=$topic}

<h2>{$templatetitle}</h2>

{form cssClass="form-horizontal" role="form"}
{formvalidationsummary}
    <ul class="nav nav-tabs">
        <li class="active"><a href="#move" data-toggle="tab">Move topic</a></li>
        <li><a href="#join" data-toggle="tab">Join topics</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane clearfix active" id="move">
            <h2>{gt text="Move topic to another forum"}</h2>
            <p class="alert alert-info">{gt text="When you press the 'Move' button at the bottom of this form, the topic you have selected (and all its related posts) will be <strong>moved</strong> to the forum you have selected. Note: You will only be able to move the topic to a forum for which you are moderator. An administrator is allowed to move any topic to any forum."}</p>

            <div class="form-group">
                {formlabel for="forum_id" __text="Target forum" class="col-lg-3 control-label"}
                <div class="col-lg-9">
                    {formdropdownlist id="forum_id" items=$forums cssClass="form-control"}
                </div>
            </div>
            <div class="form-group">
                {formlabel for="createshadowtopic" __text="Create shadow topic" class="col-lg-3 control-label"}
                <div class="col-lg-9">
                    {formcheckbox id="createshadowtopic"}
                </div>
            </div>
            <div class="col-lg-offset-3 col-lg-9">
                {formbutton class="btn btn-success"  commandName="move" __text="Move topics"}
                {formbutton class="btn btn-danger" commandName="cancel"   __text="Cancel"}
            </div>
        </div>
        <div class="tab-pane clearfix" id="join">
            <h2>{gt text="Join topic with another topic"}</h2>
            <div class="form-group">
                {formlabel for="to_topic_id" __text="ID of target topic" class="col-lg-3 control-label"}
                <div class="col-lg-9">
                    {formintinput id="to_topic_id" size="10" maxLength="20" cssClass="form-control"}
                </div>
            </div>
            <div class="col-lg-offset-3 col-lg-9">
                {formbutton class="btn btn-success" commandName="join"   __text="Join topics"}
                {formbutton class="btn btn-danger" commandName="cancel"   __text="Cancel"}
            </div>
        </div>
    </div>
{/form}

{include file='user/footer.tpl'}