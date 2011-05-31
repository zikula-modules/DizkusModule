{modulelinks modname="Dizkus" type="admin" xhtml=1}

<p class="z-warningmsg">{gt text="Caution! Please back-up your database via a database dump before proceeding with the upgrade."}</p>
<form action="{modurl modname=Dizkus type=interactiveinstaller func=interactiveupgrade_to_3_0}" method="post">
    <div>
        <div style="height: 3em; vertical-align: middle; width: 90%;">
            <h1>{gt text="Dizkus upgrade"}</h1>
        </div>
        <h2>
            {gt text="Old version"}: {$oldversion}<br />
            {gt text="New version"}: 3.0<br />
        </h2>
        <div style="border: 1px solid red; padding: 4px; margin: 10px; color: red;">
            <h2>{gt text="Caution! Please back-up your database via a database dump before proceeding with the upgrade."}</h2>
        </div>
        <div>{gt text="This will upgrade your pnForum 2.7.1 forums to Dizkus 3.0, making all necessary database changes."}</div>
        <input type="hidden" name="authkey" value="{$authid}" />
        <input type="submit" name="submit" value="{gt text="Next"}" />
    </div>
</form>
