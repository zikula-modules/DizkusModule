{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="group" size="small"}
    <h3>{gt text="Assign honorary rank"}</h3>
</div>

<div id="dizkus_admin">

    <p class="z-informationmsg">{gt text="In this page, you can select particular users and assign them honorary ranks."}</p>

    <div class="rankuser-alphanav z-center">
        {if $allow_star eq true}
        [{pagerabc posvar="letter" separator="&nbsp;|&nbsp;" names="*;A;B;C;D;E;F;G;H;I;J;K;L;M;N;O;P;Q;R;S;T;U;V;W;X;Y;Z;?" forwardvars="module,type,func"}&nbsp;]
        {else}
        [{pagerabc posvar="letter" separator="&nbsp;|&nbsp;" names="A;B;C;D;E;F;G;H;I;J;K;L;M;N;O;P;Q;R;S;T;U;V;W;X;Y;Z;?" forwardvars="module,type,func"}&nbsp;]
        {/if}
    </div>

    {pager rowcount=$usercount limit=$perpage posvar="page" display="page" maxpages="20" template="pageritems.html" class="z-center"}

    <form class="z-form" action="{modurl modname=Dizkus type=admin func=assignranks}" method="post">
        <table class="z-admintable">
            <thead>
                <tr>
                    <th>{gt text="User name"}</th>
                    <th>{gt text="Rank"}</th>
                </tr>
            </thead>
            <tbody>
                {foreach item=user from=$allusers}
                <tr class="{cycle values=z-odd,z-even}">
                    <td>{$user.uname|profilelinkbyuname}</td>
                    <td>
                        <select name="setrank[{$user.uid}]">
                            <option value="0" {if $user.rank_id eq 0}selected="selected"{/if}>{gt text="No rank"}</option>
                            {foreach item=rank from=$ranks}
                            <option value="{$rank.rank_id}" {if $user.rank_id eq $rank.rank_id}selected="selected"{/if}>{$rank.rank_title}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                {foreachelse}
                <tr class="z-admintableempty"><td colspan="2">{gt text="No asssigned ranks found"}</td></tr>
                {/foreach}
            </tbody>
        </table>

        <div class="z-formbuttons z-buttons">
            <input type="hidden" name="lastletter" value="{$letter|safetext}" />
            <input type="hidden" name="page" value="{$page|safetext}" />
            {button src="button_ok.png" set="icons/extrasmall" __alt="Submit" __title="Submit" __text="Submit"}
        </div>
    </form>

</div>

{adminfooter}