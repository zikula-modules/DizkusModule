<script type="text/javascript">
    // <![CDATA[
    var clickToEdit = "{{gt text="Click to edit"}}";
    var subscribeTopic = " {{gt text='Subscribe to topic'}}";
    {{if isset($subscribe_icon)}}var subscribeTopicIcon = "{{$subscribe_icon|strip|addslashes}}";{{/if}}
    var unsubscribeTopic = " {{gt text='Unsubscribe from topic'}}";
    {{if isset($unsubscribe_icon)}}var unsubscribeTopicIcon = "{{$unsubscribe_icon|strip|addslashes}}";{{/if}}
    var lockTopic = " {{gt text='Lock topic'}}";
    var unlockTopic = " {{gt text='Unlock topic'}}";
    var stickyTopic = " {{gt text="Give this topic 'sticky' status"}}";
    {{if isset($sticky_icon)}}var stickyTopicIcon = "{{$sticky_icon|strip|addslashes}}";{{/if}}
    var unstickyTopic = " {{gt text="Remove 'sticky' status"}}";
    {{if isset($unsticky_icon)}}var unstickyTopicIcon = "{{$unsticky_icon|strip|addslashes}}";{{/if}}
    var solveTopic = " {{gt text="Mark as solved"}}";
    {{if isset($solve_icon)}}var solveTopicIcon = "{{$solve_icon|strip|addslashes}}";{{/if}}
    var unsolveTopic = " {{gt text="Mark as unsolved"}}";
    {{if isset($unsolve_icon)}}var unsolveTopicIcon = "{{$unsolve_icon|strip|addslashes}}";{{/if}}
    var zChanged = "{{gt text="Changed"}}";
    var zLoadingPost = "{{gt text="Loading post"}}";
    var zDeletingPost = "{{gt text="Deleting post"}}";
    var zUpdatingPost = "{{gt text="Updating post"}}";
    var zStoringReply = "{{gt text="Storing reply"}}";
    var zPreparingPreview = "{{gt text="Preparing preview"}}";
    // ]]>
</script>