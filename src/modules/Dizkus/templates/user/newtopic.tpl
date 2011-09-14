{include file='user/header.tpl'}

<div id="newtopicpreview" style="display: none;">&nbsp;</div>

{if $preview}
<div id="nonajaxnewtopicpreview">
    {include file='user/newtopicpreview.tpl'}
</div>
{/if}

<div id="dzk_newtopic" class="forum_post post_bg2 dzk_rounded">
    <div class="inner">

        <div class="dzk_subcols z-clearfix">
            <form class="dzk_form" id="newtopicform" action="{modurl modname='Dizkus' type='user' func='newtopic'}" method="post" enctype="multipart/form-data">
                <div>
                    <input type="hidden" id="forum" name="forum" value="{$newtopic.forum_id}" />
                    <input type="hidden" id="quote" name="quote" value="" />
                    <input type="hidden" id="authid" name="authid" value="" />
                    <fieldset>
                        <legend class="post_header">{gt text="New topic in forum"}:&nbsp;<a href="{modurl modname='Dizkus' type='user' func='viewforum' forum=$newtopic.forum_id}">{$newtopic.forum_name}{*|modcallhooks*}</a></legend>
                        <div>
                            <div id="dizkusinformation" style="visibility: hidden;">&nbsp;</div>
                            <div>
                                <label for="subject">{gt text="Subject line"}</label><br />
                                <input style="width: 98%" class="lumicula_textarea" type="text" name="subject" size="80" maxlength="100" id="subject" tabindex="0" value="{$newtopic.subject|safehtml}" />
                            </div>
                                
                                <br />
                                {notifydisplayhooks eventname='dizkus.ui_hooks.editor.display_view' id='message'}
                                <textarea class="lumicula_textarea" id="message" name="message" rows="10" cols="60" style="width:98%;">{$newtopic.message}</textarea>
                                {if isset($hooks.MediaAttach)}{$hooks.MediaAttach}{/if}
                                {if $coredata.Dizkus.striptags == 'yes'}
                                <p>{gt text="No HTML tags allowed (except inside [code][/code] tags)"}</p>
                                {/if}

                                <div class="dzk_subcols z-clearfix">
                                    <ul>
                                        <li><strong>{gt text="Options"}</strong></li>
                                        {if $coredata.logged_in}
                                        <li>
                                            <input type="checkbox" id="attach_signature" name="attach_signature" checked="checked" value="1" />
                                            <label for="attach_signature">{gt text="Attach my signature"}</label>
                                        </li>
                                        <li>
                                            <input type="checkbox" id="subscribe_topic" name="subscribe_topic" {if $newtopic.subscribe_topic eq true}checked="checked"{/if} value="1" />
                                            <label for="subscribe_topic">{gt text="Notify me when a reply is posted"}</label>
                                        </li>
                                        {/if}
                                        <li id="newtopicbuttons" class="z-buttons" style="display: none;">
                                            {button id="btnCreateNewTopic" class="dzk_detachable z-bt-small" src="button_ok.png" set="icons/extrasmall" __alt="Submit" __title="Submit" __text="Submit"}
                                            {button id="btnPreviewNewTopic" class="dzk_detachable z-bt-small" src="xeyes.png" set="icons/extrasmall" __alt="Preview" __title="Preview" __text="Preview"}
                                            {button id="btnCancelNewTopic" class="dzk_detachable z-bt-small" src="button_cancel.png" set="icons/extrasmall" __alt="Cancel" __title="Cancel" __text="Cancel"}
                                        </li>
                                        <li id="nonajaxnewtopicbuttons" class="z-buttons">
                                            <input class="z-bt-ok z-bt-small" type="submit" name="submit" value="{gt text="Submit"}" />
                                            <input class="z-bt-preview z-bt-small" type="submit" name="preview" value="{gt text="Preview"}" />
                                            <input class="z-bt-cancel z-bt-small" type="submit" name="reset" value="{gt text="Cancel"}" />
                                        </li>
                                    </ul>


                            </div>
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>

    </div>
</div>

<div id="newtopicconfirmation" style="display: none;">&nbsp;</div>

<script type="text/javascript">
    // <![CDATA[
    var storingPost = '{{gt text="Storing post..."}}';
    var preparingPreview = '{{gt text="Preparing preview..."}}';
    var redirecting = '{{gt text="Redirecting you to the new topic..."}}';


    // ]]>
</script>

{include file='user/footer.tpl'}
