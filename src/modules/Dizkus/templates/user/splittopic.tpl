{gt text="Split topic" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

<h2>{$templatetitle}</h2>
<p class="z-informationmsg">{gt text="Notice: This will split the topic before the selected posting."}</p>
<form class="z-form" action="{modurl modname=Dizkus type=user func=splittopic}" method="post">
    <div>
        <input type="hidden" name="post" value="{$post.post_id}" />
        <input type="hidden" name="authid" value="{insert name='generateauthkey' module='Dizkus'}" />
        <fieldset id="dzk_splittopic">
            <legend>{gt text="Subject for new topic"}</legend>
            <div class="z-formrow">
                <label for="newsubject">{gt text="Subject"}</label>
                <input type="text" id="newsubject" name="newsubject" value="{gt text="Split"}: {$post.topic_subject}" size="40" maxlength="100" />
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            <button class="dzk_img ok" type="submit" name="submit" value="{gt text="Split topic"}">{gt text="Split topic"}</button>
        </div>
    </div>
</form>

{include file='user/footer.tpl'}
