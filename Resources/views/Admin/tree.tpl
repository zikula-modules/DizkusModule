{adminheader}
<h3>
    <span class="fa fa-list"></span>
    {gt text="Forum tree"}
</h3>

<div id="dizkus_admin">

    <ul class="navbar navbar-default navbar-modulelinks navbar-modulelinks-main">
        <li>
            <a href="{route name='zikuladizkusmodule_admin_modifyforum'}" title="Create a new forum">
                <span class="fa fa-comments"></span>&nbsp;{gt text='Create a new forum'}
            </a>
        </li>
        <li>
            <a href="{route name='zikuladizkusmodule_admin_syncforums'}" title="Recalculate post and topics totals">
                <span class="fa fa-cogs"></span>&nbsp;{gt text='Recalculate post and topics totals'}
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
                {include file='Admin/subtree.tpl'}
            </tbody>

        </table>
    </div>

</div>
{adminfooter}