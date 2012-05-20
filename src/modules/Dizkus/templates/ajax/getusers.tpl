<ul>
{if isset($results) and is_array($results) and count($results) gt 0}
{foreach from=$results item='result'}
<li>
<a href="{modurl modname='Dizkus' type='admin' func='managesubscriptions' username=$result.uname}">
{$result.uname|safetext}
</a>
</li>
{/foreach}
{/if}
</ul>