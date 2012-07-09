{strip}
    <ul class="linklist navlinks z-clearfix">
        <li class="icon-home">
            <a class="dzk_arrow homelink tooltips" title="{gt text='Go to forums index page'}" href="{modurl modname='Dizkus' type='user' func='main'}">{gt text="Forums index page"}</a>
        </li>




        {if $func eq 'main' AND $view_category neq -1}
        <li>
            <span class="tooltips" title="{gt text='Category'}">{gt text="&nbsp;::&nbsp;"}{$view_category_data.cat_title|safetext}</span>
        </li>
        {/if}

        {if isset($breadcrumbs)}
        {foreach from=$breadcrumbs item='breadcrumb'}
            <li>
                <span class="tooltips">
                    {gt text="&nbsp;&raquo;&nbsp;"}
                    <a href="{$breadcrumb.url}">
                        {$breadcrumb.title|safetext}
                    </a>
                </span>
            </li>
        {/foreach}
        {/if}


        {if isset($current)}
        <li>
            <span class="tooltips">
                {gt text="&nbsp;&raquo;&nbsp;"}
                {$current|safetext}
            </span>
        </li>
        {/if}


        {if isset($favorites) and $favorites}
            <li>&nbsp;<em>({gt text="Favourites"})</em></li>
        {/if}
    </ul>
{/strip}