{pageaddvar name="stylesheet" value="modules/Dizkus/style/style.css"}
{pageaddvar name="javascript" value="javascript/Ajax/prototype.js"}
{pageaddvar name="javascript" value="javascript/helpers/Zikula.js"}

<div id="newposts" style="margin: 0 0 1.3em 0; border-bottom: 1px #666 dashed;">
    <script type="text/javascript">
        // <![CDATA[
        new Ajax.PeriodicalUpdater(
                'newposts',
                Zikula.Config.baseURL + 'ajax.php',
        {
                    method: 'post',
                    parameters: 'module=Dizkus&type=ajax&func=newposts',
                    frequency: 60
                });
        // ]]>
    </script>
    <noscript>
    {include file="Ajax/newposts.tpl"}
    </noscript>
</div>