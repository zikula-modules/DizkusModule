<div style="display:none;" id="topicsubjectedit_editor">
    <form id="topicsubject_{$topic.topic_id}_form" class="form-inline" action="javascript:void(0);">
        <fieldset>
            <legend>{gt text='Edit topic title'}</legend>
            <input type='hidden' id='userAllowedToEdit' value='{$topic->userAllowedToEdit()}'>
            <div class="form-group">
                <label class="sr-only" for="topicsubjectedit_subject">{gt text='topic title'}</label>
                <input type="text" class="form-control" id="topicsubjectedit_subject" name="topicsubjectedit_subject" value="{$topic.title|safetext}" />
            </div>
            <button id="topicsubjectedit_save" class="btn btn-success btn-xs" type="submit" name="Submit">{gt text="Submit"}</button>
            <button id="topicsubjectedit_cancel" class="btn btn-danger btn-xs" type="submit" name="cancel">{gt text="Cancel"}</button>
        </fieldset>
    </form>
</div>
