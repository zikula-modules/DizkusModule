{include file='user/header.tpl'}

<h2>{gt text="User IP and account information"}</h2>

<div class="z-form">
    <div class="z-formrow">
        <strong class="z-label">{gt text="IP address"}</strong>
        <span>{$viewip.poster_ip}</span>
    </div>
    <div class="z-formrow">
        <strong class="z-label">{gt text="Host"}</strong>
        <span>{$viewip.poster_host}</span>
    </div>
    <div class="z-formrow">
        <strong class="z-label">{gt text="User names of users who posted from this IP, plus post counts"}</strong>
        {foreach item=user from=$viewip.users}
        <div class="z-formnote">{$user.uname|profilelinkbyuname}&nbsp;({gt text="%s posts" tag1=$user.postcount})</div>
        {/foreach}
    </div>
</div>

<p class="gobacklink">
    <a class="previoustopiclink" href="{modurl modname=Dizkus type=user func=viewtopic topic=$topic_id}" title="{gt text="Back to the topic"}">{gt text="Back to the topic"}</a>
</p>

{include file='user/footer.tpl'}
