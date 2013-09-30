<div style="font-size:12px; letter-spacing:0; margin-top:5px; text-align:left;display:none;" id="topicsubjectedit_editor">
    <form id="topicsubject_{$topic.topic_id}_form" class="z-form z-linear" action="javascript:void(0);">
        <fieldset>
            <legend>{gt text='Edit topic title'}</legend>
            <div class="z-formrow">
                <input type='hidden' id='userAllowedToEdit' value='{$topic->userAllowedToEdit()}'>
                <input type="text" style="width: 50%;" id="topicsubjectedit_subject" name="topicsubjectedit_subject" value="{$topic.title|safetext}" />
                <span class="">
                    <button id="topicsubjectedit_save" class="btn btn-success btn-xs" type="submit" name="Submit">{gt text="Submit"}</button>
                    <button id="topicsubjectedit_cancel" class="btn btn-danger btn-xs" type="submit" name="cancel">{gt text="Cancel"}</button>
                </span>
            </div>
        </fieldset>
    </form>
</div>
