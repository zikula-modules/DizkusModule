<div style="font-size:12px; letter-spacing:0; margin-top:5px; text-align:left;" id="topicsubjectedit_editor">
    <form id="topicsubject_{$topic.topic_id}_form" class="z-form z-linear" action="javascript:void(0);">
        <fieldset>
            <legend>{gt text='New subject name'}</legend>
            <div class="z-formrow">
                <input type="text" style="width: 50%;" id="topicsubjectedit_subject" name="topicsubjectedit_subject" value="{$topic.topic_title|safetext}" />
                <span class="z-buttons">
                    {button id="topicsubjectedit_save" class="dzk_detachable z-bt-small" src="button_ok.png" set="icons/extrasmall" __alt="Submit" __title="Submit" __text="Submit"}
                    {button id="topicsubjectedit_cancel" class="dzk_detachable z-bt-small" src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel" __text="Cancel"}
                </span>
            </div>
        </fieldset>
    </form>
</div>
