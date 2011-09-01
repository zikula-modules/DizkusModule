{include file='user/header.tpl'}

<h2>{gt text='Forum Information'}</h2>
<div id="dzk_forumdisabled" class="z-warningmsg">
    {$coredata.Dizkus.forum_disabled_info|safehtml}
</div>

{include file='user/footer.tpl'}
