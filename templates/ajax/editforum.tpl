<form class="z-form" id="editforumform_{$forum.forum_id}" name="editforumform_{$forum.forum_id}" action="javascript:void(0);" method="post">
    <div>
        <fieldset>
            {if $newforum eq true}
            <legend>{gt text="Create forum"}</legend>
            {else}
            <legend>{gt text="Edit forum"}</legend>
            {/if}

            <div class="z-formrow">
                <label for="forum_name">{gt text="Forum"}</label>
                <input type="text" id="forum_name" name="forum_name" size="70" maxlength="150" value="{$forum.forum_name|safetext}" />
            </div>

            {if $newforum neq true}
            <div class="z-formrow">
                <label for="forum_info">{gt text="Forum Information"}</label>
                <span id="forum_info">
                    {boardstats type='forumtopics' id=$forum.forum_id assign=topiccount}
                    {boardstats type='forumposts' id=$forum.forum_id assign=postcount}
                    <a title="{gt text="Visit this forum"}" href="{modurl modname='Dizkus' type='user' func='viewforum forum=$forum.forum_id}">
                        {$topiccount}&nbsp;{if $topiccount eq 1}{gt text="Topic"}{else}{gt text="Topics"}{/if}&nbsp;/&nbsp;
                        {$postcount}&nbsp;{if $postcount eq 1}{gt text="Post"}{else}{gt text="Posts"}{/if}
                    </a>
                </span>
            </div>
            {/if}

            <div class="z-formrow">
                <label for="forum_desc">{gt text="Description"}</label>
                <textarea id="forum_desc" name="desc" rows="10" cols="60">{$forum.forum_desc|safehtml}</textarea>
            </div>

            <div class="z-formrow">
                <label for="moduleref">{gt text="Hooked module"}</label>
                <select id="moduleref" name="moduleref" size="1">
                    {foreach item='hookmod' from=$hooked_modules}
                    <option value="{$hookmod.id}" {if $hookmod.sel == true}selected="selected"{/if}>{$hookmod.name}</option>
                    {foreachelse}
                    <option value="0" selected="selected">{gt text="No hooked module found."}</option>
                    {/foreach}
                </select>
                <p class="z-formnote z-sub">{gt text="Notice: This is used for the comments feature. All submissions within this module will be posted within this forum. The list only includes modules for which Dizkus has been activated as a hook."}</p>
            </div>
            {*
            <div class="z-formrow">
                <label for="pncategory">< !--[gt text="Select category"]-- ></label>
                < !--[$categoryselector]-- >
            </div>
            *}
            <div class="z-formrow">
                <label for="forum_mods">{gt text="Moderation"}</label>
                <span id="forum_mods" class="z-formnote">
                    {gt text="Current moderator(s):"}<br />
                    {foreach item='mod' from=$moderators}
                    {$mod.uname|safetext}&nbsp;{if $mod.uid > 1000000}({gt text="Group"}){/if}&nbsp;(<input type="checkbox" name="rem_mods[]" value="{$mod.uid}" />&nbsp;{gt text="Remove"})<br />
                    {foreachelse}
                    {gt text="No moderator appointed"}<br /><br />
                    {/foreach}
                    {gt text="Add new moderator:"}<br />
                    <select id="mods" name="mods[]" size="10" multiple="multiple">
                        <option value="0">{gt text="None"}</option>
                        {foreach item='group' from=$groups}
                        <option value="{$group.gid}">{$group.name|safetext} ({gt text="Group"})</option>
                        {/foreach}
                        {foreach item='user' from=$users}
                        <option value="{$user.uid}">{$user.uname|safetext}</option>
                        {/foreach}
                    </select>
                </span>
            </div>

            <div id="extsource" class="z-formrow">
                <label for="pop3_active">{gt text="External source"}</label>
                {foreach item='extsource' from=$externalsourceoptions key='num'}
                <div class="z-formlist">
                    <input id="extsource_{$num}_{$forum.forum_id}" type="radio" name="extsource" value="{$num}" {$extsource.checked} />
                    <label for="extsource_{$num}_{$forum.forum_id}">{$extsource.name}</label>&nbsp;{$extsource.ok}
                </div>
                {/foreach}
            </div>
        </fieldset>

        <fieldset id="pnlogindata_{$forum.forum_id}" {if $forum.externalsource == 0}style="display:none;"{/if}>
            <div class="z-formrow">
                <label for="pnuser">{gt text="User name"}</label>
                <input type="text" id="pnuser" name="pnuser" maxlength="60" size="30"  value="{$forum.pop3_pnuser}" />
            </div>
            <div class="z-formrow">
                <label for="pnpassword">{gt text="Password"}</label>
                <input type="password" id="pnpassword" name="pnpassword" maxlength="60" size="30"  value="{$forum.pop3_pnpassword}" />
            </div>
            <div class="z-formrow">
                <label for="pnpasswordconfirm">{gt text="Password (repeat for verification)"}</label>
                <input type="password" id="pnpasswordconfirm" name="pnpasswordconfirm" maxlength="60" size="30"  value="{$forum.pop3_pnpassword}" />
            </div>
        </fieldset>

        <fieldset id="mail2forum_{$forum.forum_id}" {if $forum.externalsource <> 1}style="display:none;"{/if}>
            <legend>{gt text="Mail2Forum"}</legend>
            <div class="z-formrow">
                <label for="pop3_test">{gt text="Perform POP3 test after saving"}</label>
                <input type="checkbox" id="pop3_test" name="pop3_test" value="1" />
            </div>
            <div class="z-formrow">
                <label for="pop3_server">{gt text="POP3 server"}</label>
                <input type="text" id="pop3_server" name="pop3_server" maxlength="60" size="30" value="{$forum.pop3_server}" /><br />
            </div>
            <div class="z-formrow">
                <label for="pop3_port">{gt text="POP3 port"}</label>
                <input type="text" id="pop3_port" name="pop3_port" maxlength="5" size="5" value="{$forum.pop3_port}" />
            </div>
            <div class="z-formrow">
                <label for="pop3_login">{gt text="POP3 log-in name"}</label>
                <input type="text" id="pop3_login" name="pop3_login" maxlength="60" size="30"  value="{$forum.pop3_login}" />
            </div>
            <div class="z-formrow">
                <label for="pop3_password">{gt text="POP3 password"}</label>
                <input type="password" id="pop3_password" name="pop3_password" maxlength="60" size="30"  value="{$forum.pop3_password}" />
            </div>
            <div class="z-formrow">
                <label for="pop3_passwordconfirm">{gt text="POP3 password (repeat for verification)"}</label>
                <input type="password" id="pop3_passwordconfirm" name="pop3_passwordconfirm" maxlength="60" size="30"  value="{$forum.pop3_password}" />
            </div>
            <div class="z-formrow">
                <label for="pop3_matchstring">{gt text="Rule"}</label>
                <input type="text" id="pop3_matchstring" name="pop3_matchstring" maxlength="255" size="30"  value="{$forum.pop3_matchstring}" />
                <p class="z-formnote z-sub">{gt text="Notice: This rule is a regular expression applied to posts incoming via e-mail, in order to prevent spam postings. If there is no rule here then no checks will be performed."}</p>
            </div>
        </fieldset>

        <fieldset id="rss2forum_{$forum.forum_id}" {if $forum.externalsource <> 2}style="display:none;"{/if}>
            <legend>{gt text="RSS2Forum"}</legend>
            <div class="z-formrow">
                <label for="rssfeed">{gt text="Select RSS feed"}</label>
                <select name="rssfeed" size="1">
                    <option value="0">{gt text="None"}</option>
                    {foreach item='feed from=$rssfeeds}
                    <option value="{$feed.fid}" {if $feed.fid == $forum.externalsourceurl}selected="selected"{/if}>{$feed.name|safetext} ({$feed.url})</option>
                    {/foreach}
                </select>
            </div>
        </fieldset>

        {if $newforum eq true}
        <input type="hidden" name="add" value="add" checked="checked" />
        {else}
        <fieldset>
            <legend>{gt text="Delete"}</legend>
            <div class="z-formrow">
                <label for="delete_{$forum.forum_id}">{gt text="Delete this forum"}</label>
                <input type="checkbox" id="delete_{$forum.forum_id}" name="delete" value="delete" />
            </div>
        </fieldset>
        {/if}

        <div class="z-formbuttons z-buttons">
            <input type="hidden" name="forum_id" value="{$forum.forum_id}" />
            <input type="hidden" name="cat_id" value="{$forum.cat_id}" />
            {button id="submitforum_`$forum.forum_id`" src="button_ok.png" set="icons/extrasmall" __alt="Save" __title="Save" __text="Save"}
        </div>

    </div>
</form>
