{gt text="Move post from one topic to another" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
<form class="z-form"  action="{modurl modname=Dizkus type=user func=movepost}" method="post">
    <div>
        <input type="hidden" name="post" value="{$post.post_id}" />
        <input type="hidden" name="from_topic" value="{$post.topic_id}" />
        <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
        <fieldset id="dzk_movepost">
            <legend>{gt text="Move post"}</legend>
            <div class="z-formrow">
                <label for="to_topic">{gt text="ID of target topic"}</label>
                <span>
                    <input type="text" id="to_topic" name="to_topic" value="" size="10" maxlength="20" />
                </span>
            </div>
        </fieldset>
        <div class="z-formbuttons">
            <button class="dzk_img ok" type="submit" name="submit" value="{gt text="Move post"}">{gt text="Move post"}</button>
        </div>
    </div>
</form>

{include file='user/footer.tpl'}
