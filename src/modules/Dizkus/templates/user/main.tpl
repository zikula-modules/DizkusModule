{include file='user/header.tpl'}

{if $viewcat > 0}
<h2>{$tree.0.forum_name|safetext}</h2>
{else}
<h2>{gt text="Forums index page"}</h2>
{/if}

<div id="dzk_maincategorylist">
    {foreach item='category' from=$tree}
    <table class="z-admintable">
        <thead>
            <tr>
                <th colspan="2">
                    <a id="categorylink_{$category.forum_id}" class="{*if $category.new_posts == true*}{*newpostscategorylink*}{*else*}categorylink{*/if*}" title="{gt text="Go to category"} '{$category.forum_name|safetext}'" href="{modurl modname='Dizkus' type=user func=main viewcat=$category.forum_id}">{$category.forum_name|safetext}</a>
                </th>
                <th>{gt text="Topics"}</th>
                <th>{gt text="Posts"}</th>
                <th class="lastpost"><span>{gt text="Last post"}</th>
            </tr>
        </thead>
        <tbody>

            {foreach item='forum' from=$category.children}
                <tr>
                    <td class="icon">
                        {*<dt {if $forum.new_posts == true}class='new-posts'{else}class='no-new-posts'{/if} >*}
                    </td>
                    <td>
                        <a title="{gt text="Go to forum"} '{$forum.forum_name|safetext}'" href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$forum.forum_id}">{$forum.forum_name|safetext}</a><br />
                        {if $forum.forum_desc neq ''}{$forum.forum_desc|safehtml}<br />{/if}
                        {if !empty($forum.forum_mods)}
                        <em>{gt text="Moderated by"}:</em>
                        {foreach name='moderators' item='mod' key='modid' from=$forum.forum_mods}
                        {if $modid lt 1000000}{$mod|profilelinkbyuname}{else}{$mod|safetext}{/if}{if !$smarty.foreach.moderators.last}, {/if}
                        {/foreach}
                        {/if}
                    </td>

                        <td class="topics">{$forum.forum_topics|safetext}</td>
                        <td class="posts">{$forum.forum_posts|safetext}</td>
                        <td class="lastpost">
                            {if isset($forum.last_post)}
                            {include file='user/lastPostBy.tpl' last_post=$forum.last_post}
                            {else}
                            <span></span>
                            {/if}
                        </td>
                </tr>
                {foreachelse}
                <tr class="row dzk_empty" colspan=5>
                    {gt text="No forums available."}
                </tr>
                {/foreach}
                </tbody>
            </table>

        </div>
    </div>
    {/foreach}
</div>

{include file='user/footer.tpl'}
