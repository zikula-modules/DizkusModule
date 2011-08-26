{modulelinks modname="Dizkus" type="admin" xhtml=1}
<h2>{gt text="Dizkus upgrade"}</h2>
<p class="z-warningmsg">{gt text="Caution! This upgrade step will do some serious changes to your Dizkus tables! Please back-up your database via a database dump before proceeding with the upgrade."}</p>
<form class="z-form" action="{modurl modname=Dizkus type=interactiveinstaller func=interactiveupgrade_to_3_2_0}" method="post">
    <div>
        <fieldset>
            <legend>{gt text="Upgrade info"}</legend>
            <div class="z-formrow">
                <label>{gt text="Old version"}:</label>
                <span>{$oldversion}</span>
            </div>
            <div class="z-formrow">
                <label>{gt text="New version"}:</label>
                <span>3.2.0</span>
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            <input type="hidden" name="authkey" value="{$authid}" />
            <input type="submit" name="submit" value="{gt text="Next"}" />
        </div>
    </div>
</form>
