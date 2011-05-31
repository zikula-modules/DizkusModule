{include file='user/header.tpl'}

<div class="z-statusmsg">
    <h3>{gt text="Thanks for your submission."}</h3>
    <ul>
        <li><a href="{modurl modname=Dizkus type=user func=viewtopic topic=$topic.topic_id}" title="{gt text="Click here to go the new topic"}">{gt text="Click here to go the new topic"}</a></li>
        <li><a href="{modurl modname=Dizkus type=user func=viewforum forum=$topic.forum_id}" title="{gt text="Click here to go back to the forum"}">{gt text="Click here to go back to the forum"}</a></li>
    </ul>
</div>

{include file='user/footer.tpl'}