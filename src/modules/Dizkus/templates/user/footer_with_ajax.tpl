{* Rename this to user/footer.tpl if you want to use it*}

<div id="dzk_footer" class="z-clearfix">
    <script type="text/javascript">
        // <![CDATA[
        new Ajax.PeriodicalUpdater(
        'dzk_footer',
        document.location.pnbaseURL + 'index.php',
        {
            method: 'get',
            parameters: 'module=Dizkus&type=ajax&func=forumusers',
            frequency: 60
        });
        // ]]>
    </script>
    <noscript>
        {include file=dizkus_ajax_forumusers.html}
    </noscript>
</div>
<p id="dzk_footer_line">{gt text="Powered by "}<a href="https://github.com/zikula-modules/Dizkus" title="Dizkus forum software for Zikula">Dizkus {modgetinfo modname=Dizkus info=version}</a></p>


{*

This is the end of the main Dizkus div, see dizkus_user_header for more information

*}

</div>
