{adminheader}
<h3>
    <span class="fa fa-trophy"></span>&nbsp;{gt text="Assign honorary rank"}
</h3>

<div id="dizkus_admin">

    {gt text='Create an honorary rank' assign='createtext'}
    {capture assign='createlink'}<strong><a href='{route name='zikuladizkusmodule_admin_ranks' ranktype='1'}'>{$createtext}</a></strong>{/capture}
    <p class="alert alert-info">{gt text="In this page, you can select particular users and assign them honorary ranks. %s. Only users that have posted in the forum may be assigned a rank." tag1=$createlink}</p>

    <div class="rankuser-alphanav text-center">
        {pagerabc printempty=true posvar="letter" route='zikuladizkusmodule_admin_assignranks'}
    </div>

    {form}
    {formvalidationsummary}
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>{gt text="User name"}</th>
                <th>{gt text="Rank"}</th>
            </tr>
        </thead>
        <tbody>
        {foreach item='user' from=$allusers}
            <tr>
                <td>{$user.user.uname|profilelinkbyuname}</td>
                <td>
                    <select name="setrank[{$user.user.uid}]" class='form-control input-sm'>
                        <option value="0" {if (($user.rank.rank_id eq 0) || (empty($user.rank.rank_id)))}selected="selected"{/if}>{gt text="No rank"}</option>
                        {foreach item=rank from=$ranks}
                            <option value="{$rank.rank_id}" {if $user.rank.rank_id eq $rank.rank_id}selected="selected"{/if}>{$rank.title}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
        {foreachelse}
            <tr class="danger"><td colspan="2">{gt text="No users found"}</td></tr>
        {/foreach}
        </tbody>
    </table>

    {pager rowcount=$usercount limit=$perpage posvar="page" display="page" maxpages="20" class="text-center" route='zikuladizkusmodule_admin_assignranks'}

    <div class="col-lg-offset-3 col-lg-9">
        <input type="hidden" name="lastletter" value="{$letter|safetext}" />
        <input type="hidden" name="page" value="{$page|safetext}" />
        {formbutton id="submit" commandName="submit" __text="Submit" class="btn btn-success"}
    </div>
    {/form}
</div>    
{adminfooter}