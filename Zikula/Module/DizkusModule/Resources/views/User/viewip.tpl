{include file='User/header.tpl'}

<div class="panel panel-default">
    <div class='panel-heading'>
        <h2>{gt text="User IP and account information"}</h2>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td>{gt text="IP address"}</td>
            <td width="100%">{$viewip.poster_ip}</td>
        </tr>
        <tr>
            <td>{gt text="Host"}</td>
            <td>{$viewip.poster_host}</td>
        </tr>
        <tr>
            <td style='white-space:nowrap;'>{gt text="Usernames posting from same IP"}</td>
            <td>
                <ul>
                {foreach item='user' from=$viewip.users}
                <li>{$user.uname|profilelinkbyuname}&nbsp;({gt text="%s posts" tag1=$user.postcount})</li>
                {/foreach}
                </ul>
            </td>
        </tr>
        </tbody>
    </table>
    <div class='panel-footer'>
        <a class="btn btn-warning" href="{route name='zikuladizkusmodule_user_viewtopic' topic=$topicId}" title="{gt text="Back to the topic"}">{gt text="Back to the topic"}</a>
    </div>
</div>

{include file='User/footer.tpl'}
