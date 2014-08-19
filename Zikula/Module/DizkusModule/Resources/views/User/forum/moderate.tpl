{include file='User/header.tpl' __templatetitle='Moderate'}

{if $forum.topics}
    {form cssClass="form-horizontal" role="form"}
    {formvalidationsummary}
        <p class='clearfix'>
            <a class='btn btn-warning pull-right' href="{route name='zikuladizkusmodule_user_viewforum' forum=$forum.forum_id}">{gt text="Go back to normal forum view"}</a>
        </p>
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h2><i class='fa fa-wrench'></i>&nbsp;{gt text='Moderating'}&nbsp;{$forum.name|safetext}&nbsp;{gt text='topics'}</h2>
            </div>
            {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start' route='zikuladizkusmodule_user_moderateforum'}
            {include file='User/forum/forumtopicstable.tpl' topics=$forum.topics moderate=true}
            {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start' route='zikuladizkusmodule_user_moderateforum'}
        </div>

        <div class="alert alert-danger">{gt text="Warning! You will not be prompted for confirmation. Clicking on 'Submit' will immediately execute the chosen action."}</div>
        <fieldset>
            <legend>{gt text="Moderator options"}</legend>
            <div class="form-group">
                {formlabel for="mode" __text="Actions" class="col-lg-3 control-label"}
                <div class="col-lg-9">
                    {formdropdownlist id="mode" items=$actions cssClass='form-control'}
                </div>
            </div>
            <div class="form-group">
                {formlabel for="moveto" __text="Choose target forum to move topic(s) to" class="col-lg-3 control-label"}
                <div class="col-lg-9">
                    {formdropdownlist id="moveto" items=$forums cssClass='form-control'}
                </div>
            </div>
            <div class="form-group">
                {formlabel for="createshadowtopic" __text="Create shadow topic" class="col-lg-3 control-label"}
                <div class="col-lg-9">
                    <div class="checkbox">
                        {formcheckbox id="createshadowtopic"}
                    </div>
                </div>
            </div>
            <div class="form-group">
                {formlabel for="jointotopic" __text="To join topics, select the target topic here" class="col-lg-3 control-label"}
                    <span class='col-lg-5'>
                        {formdropdownlist id="jointotopic" items=$topicSelect cssClass='form-control'}
                    </span>
                    <span class='col-lg-1'>
                    {formlabel for="jointo" __text="or target topic #"}
                    </span>
                    <span class='col-lg-2'>
                        {formintinput id="jointo" size="5" maxLength="10" cssClass='form-control'}
                    </span>
            </div>
        </fieldset>
        <div class='form-group'>
            <div class="col-lg-offset-3 col-lg-9">
                {formbutton class="btn btn-success" commandName="submit"  __text="Submit"}
                {formbutton class="btn btn-danger" commandName="cancel"   __text="Cancel"}
            </div>
        </div>
    {/form}
{else}
    <div class="alert alert-info">
        <strong>{gt text="There are no topics in the forum '%s' to moderate." tag1=$forum.name|safetext}</strong>
    </div>
{/if}

{include file='User/footer.tpl'}