<div id="dizkus">

    <input id="topic_id" name="topic" type="hidden" value="{$topic.topic_id}">
    {if $modvars.ZikulaDizkusModule.ajax}
        {* JS files not loaded via header like other templates *}
        {pageaddvar name='javascript' value='jQuery'}
        {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.User.ViewTopic.js'}
        {pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.Dizkus.Tools.js'}
    {/if}
    {pageaddvar name="jsgettext" value="module_dizkus_js:Dizkus"}

    {userloggedin assign='userloggedin'}

    <h2>{gt text="%s Comment" plural="%s Comments" tag1=$pager.numitems-1 count=$pager.numitems-1}</h2>

    {pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}

    <div id="dzk_postinglist">
        <ul>
            {counter start=0 print=false assign='post_counter'}
            {foreach key='num' item='post' from=$posts}
                {if !$post.isFirstPost}
                    {counter}
                    <li class="post_{$post.post_id}">
                        {include file='user/post/single.tpl'}
                    </li>
                {/if}
            {/foreach}
            <li id="quickreplyposting" class="hidden">&nbsp;</li>
            <li id="quickreplypreview" class="hidden">&nbsp;</li>
        </ul>
    </div>

    {pager show='post' rowcount=$pager.numitems limit=$pager.itemsperpage posvar='start'}

    {if ($permissions.comment eq true)}
        {include file='user/topic/quickreply.tpl'}
    {/if}

    {include file='user/moderatedBy.tpl' forum=$topic.forum}

    {include file='user/topic/translations.tpl'}

</div>