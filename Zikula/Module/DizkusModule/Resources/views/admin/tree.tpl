{adminheader}
<h3>
    <span class="icon-list"></span>
    {gt text="Forum tree"}
</h3>

<div id="dizkus_admin">

    <ul class="navbar navbar-default navbar-modulelinks navbar-modulelinks-main">
        <li>
            <a href="{modurl modname=$module type='admin' func='modifyForum'}" title="Create a new forum">
                <span class="icon-comments"></span>&nbsp;{gt text='Create a new forum'}
            </a>
        </li>
        <li>
            <a href="{modurl modname=$module type='admin' func='syncforums'}" title="Recalculate post and topics totals">
                <span class="icon-cogs"></span>&nbsp;{gt text='Recalculate post and topics totals'}
            </a>
        </li>
    </ul><br />

    <div class="panel panel-default">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="100%">{gt text="Name"}</th>
                    <th nowrap="">{gt text="Actions"}</th>
                </tr>
            </thead>
            <tbody>
                {include file='admin/subtree.tpl'}
            </tbody>

        </table>
    </div>

</div>
{adminfooter}