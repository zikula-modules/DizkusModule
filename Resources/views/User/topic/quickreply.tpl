    <a id="reply"></a>
    <div id="dzk_quickreply" class="panel panel-info"{if $topic.status eq 1} style='display:none'{/if}>
        <div class="panel-heading">
            <h3>{gt text="Quick reply"}</h3>
        </div>
        <div class="panel-body">
            <form id="quickreplyform" role='form' action="{route name='zikuladizkusmodule_user_reply'}" method="post" enctype="multipart/form-data">
                <div class="dizkusinformation_post text-danger" id="dizkusinformation_quickreply" style='display:none;'>{img modname='core' set='ajax' src='indicator.white.gif'}</div>
                <div class="form-group">
                    <input type="hidden" id="forum" name="forum" value="{$topic.forum.forum_id}" />
                    <input type="hidden" id="topic" name="topic" value="{$topic.topic_id}" />
                    <input type="hidden" id="quote" name="quote" value="" />
                    <input type="hidden" id="returnUrl" name="returnUrl" value="{$returnUrl|default:''}" />
                    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
                    <label for="message" class="sr-only">{gt text="Message"}</label>
                    <textarea id="message" class="form-control" name="message" rows="10"></textarea>

                    {if $modvars.ZikulaDizkusModule.striptags }
                        <p class="help-block">{gt text="No HTML tags allowed (except inside [code][/code] tags)"}</p>
                    {/if}
                </div>
                <div class="form-group">
                    <div class="col-md-4">
                        {if $userloggedin}
                        <div><strong>{gt text="Options"}</strong></div>
                        <div class="checkbox">
                            <label for="attach_signature">
                            <input type="checkbox" id="attach_signature" name="attach_signature" checked="checked" value="1" />
                            {gt text="Attach my signature"}</label>
                        </div>
                        <div class="checkbox">
                            <label for="subscribe_topic">
                            <input type="checkbox" id="subscribe_topic" name="subscribe_topic" checked="checked" value="1" />
                            {gt text="Notify me when a reply is posted"}</label>
                        </div>
                        {/if}
                        <input id="btnSubmitQuickReply" class="btn btn-success" type="submit" name="submit" value="{gt text="Submit"}" />
                        <input id="btnPreviewQuickReply" class="btn btn-primary" type="submit" name="preview" value="{gt text="Preview"}" />
                        <button id="btnCancelQuickReply" class="btn btn-danger" style='display:none' type="submit" name="cancel">{gt text="Cancel"}</button>
                    </div>
                    <div class="col-md-8">
                        {notifydisplayhooks eventname='dizkus.ui_hooks.post.ui_edit' id=null}
                    </div>
                </div>

                <div class="post_footer"></div>
            </form>
        </div>
    </div>

    <div id="dzk_displayhooks">
        {notifydisplayhooks eventname='dizkus.ui_hooks.topic.ui_view' id=$topic.topic_id}
    </div>