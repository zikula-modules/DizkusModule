{adminheader}
<h3>
    <span class="icon-wrench"></span>&nbsp;{gt text="Settings"}
</h3>
<div id="dizkus_admin">

    {form cssClass="form-horizontal" role="form"}
    {formvalidationsummary}

    <fieldset>
        <legend>{gt text="General settings"}</legend>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="forum_enabled" __text='Forums are accessible to visitors'}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="forum_enabled"}
                <p class="help-block alert alert-info">
                    {gt text="If the 'Forums are accessible to visitors' setting is deactivated then only administrators will have access to the forums. You can temporarily deactivate this setting to take the forums off-line when you need to perform maintenance."}
                </p>
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="forum_disabled_info" __text="Message displayed if forums are disabled"}
            <div class="col-lg-9">
                {formtextinput id="forum_disabled_info" textMode="multiline" rows="3" cols="40" text=$modvars.ZikulaDizkusModule.forum_disabled_info cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="indexTo" __text="Redirect forum index to forum id"}
            <div class="col-lg-9">
                {formtextinput id="indexTo" text=$modvars.ZikulaDizkusModule.indexTo size="5" maxLength="10" cssClass='form-control'}
                <p class="help-block alert alert-info">
                    {gt text="Leave blank to use standard forum index."}
                </p>
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="email_from" __text="Sender address for e-mail messages from forums"}
            <div class="col-lg-9">
                {formemailinput id="email_from" text=$modvars.ZikulaDizkusModule.email_from size="30" maxLength="100" cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="hot_threshold" __text="'Hot topic' threshold (default: 20)"}
            <div class="col-lg-9">
                {formintinput id="hot_threshold" text=$modvars.ZikulaDizkusModule.hot_threshold size="3" maxLength="3" minValue=2 maxValue=100 cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="posts_per_page" __text="Posts per page in topic index (default: 15)"}
            <div class="col-lg-9">
                {formintinput id="posts_per_page" text=$modvars.ZikulaDizkusModule.posts_per_page size="3" maxLength="3" minValue=1 maxValue=100 cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="topics_per_page" __text="Topics per page in forum index (default: 15)"}
            <div class="col-lg-9">
                {formintinput id="topics_per_page" text=$modvars.ZikulaDizkusModule.topics_per_page size="3" maxLength="3" minValue=5 maxValue=100 cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="url_ranks_images" __text="Path to rank images"}
            <div class="col-lg-9">
                {formtextinput id="url_ranks_images" text=$modvars.ZikulaDizkusModule.url_ranks_images size="30" maxLength="100" cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="ajax" __text="Enable user-side ajax"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="ajax"}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="solved_enabled" __text="Enable solved option in topics"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="solved_enabled"}
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text="Forum Search settings"}</legend>
        {* fulltext disabled until technology available
        <div class="form-group">
        {formlabel class="col-lg-3 control-label" for="fulltextindex" __text="Enable full-text index field searching"}
        {formcheckbox cssClass='form-control' id="fulltextindex"}
        <p class="help-block alert alert-info">{gt text="Notice: For searches with full-text index fields, you need MySQL 4 or later; the feature does not work with InnoDB databases. This flag will normally be set during installation, when the index fields have been created. Search results may be empty if the query string is present in too many postings. This is a feature of MySQL. For more information, see <a href=\"http://dev.mysql.com/doc/mysql/en/fulltext-search.html\" title=\"Full-text search in MySQL\">'Full-text search in MySQL'</a> in the MySQL documentation."}</p>
        </div>
        <div class="form-group">
        {formlabel class="col-lg-3 control-label" for="extendedsearch" __text="Enable extended full-text search in internal search"}
        {formcheckbox cssClass='form-control' id="extendedsearch"}
        <p class="help-block alert alert-info">{gt text="Notice: Extended full-text searching enables queries like '+Dizkus -Skype' to find posts that contain 'Dizkus' but not 'Skype'. Requires MySQL 4.01 or later. For more information, see <a href=\"http://dev.mysql.com/doc/mysql/en/fulltext-boolean.html\" title=\"Extended full-text search in MySQL\">'Full-text search in MySQL'</a> in the MySQL documentation."}</p>
        </div>
        *}
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="showtextinsearchresults" __text="Show text in search results"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="showtextinsearchresults"}
                <p class="help-block alert alert-info">{gt text="Notice: Deactivate the 'Show text in search results' setting for high-volume sites if you need to improve search performance, or if you need to be attentive to constant cleaning of the search results table."}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="minsearchlength" __text="Minimum number of characters in search query string (1 minimum)"}
            <div class="col-lg-9">
                {formintinput id="minsearchlength" text=$modvars.ZikulaDizkusModule.minsearchlength size="2" maxLength="2" minValue=1 maxValue=50 cssClass='form-control'}
            </div>
        </div>

        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="maxsearchlength" __text="Maximum number of characters in search query string (50 maximum)"}
            <div class="col-lg-9">
                {formintinput id="maxsearchlength" text=$modvars.ZikulaDizkusModule.maxsearchlength size="2" maxLength="2" minValue=1 maxValue=50 cssClass='form-control'}
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text="User-related settings"}</legend>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="post_sort_order" __text="Default sort order for posts"}
            <div class="col-lg-9">
                {formdropdownlist id="post_sort_order" items=$post_sort_order_options selectedValue=$modvars.ZikulaDizkusModule.post_sort_order cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="signature_start" __text="Beginning of signature"}
            <div class="col-lg-9">
                {formtextinput id="signature_start" textMode="multiline" rows="3" cols="40" text=$modvars.ZikulaDizkusModule.signature_start|default:'' cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="signature_end" __text="End of signature"}
            <div class="col-lg-9">
                {formtextinput id="signature_end" textMode="multiline" rows="3" cols="40" text=$modvars.ZikulaDizkusModule.signature_end|default:'' cssClass='form-control'}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="signaturemanagement" __text="Enable signature management via forum user settings"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="signaturemanagement"}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="removesignature" __text="Strip user signatures from posts"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="removesignature"}
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text="Security settings"}</legend>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="log_ip" __text="Log IP addresses"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="log_ip"}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="striptags" __text="Strip HTML tags from new posts"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="striptags"}
                <p class="help-block alert alert-info">{gt text="Notice: Setting 'Strip HTML tags from new posts' to enabled does not affect the content of '[code][/code]' BBCode tags."}</p>
            </div>
        </div>

        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="timespanforchanges" __text="Number of hours during which non-moderators are allowed to edit their post (leave blank for unlimited)"}
            <div class="col-lg-9">
                <span class="col-lg-3">
                    {formintinput id="timespanforchanges" text=$modvars.ZikulaDizkusModule.timespanforchanges size="3" maxLength="3" cssClass='form-control'}
                </span>
                {gt text="hours"}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="striptagsfromemail" __text="Strip HTML tags from outgoing email post content"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="striptagsfromemail"}
                <p class="help-block alert alert-info">{gt text="Strip action occurs post filter hook action."}</p>
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="notifyAdminAsMod" __text="Admin to notify with Moderator notifications"}
            <div class="col-lg-9">
                {formdropdownlist id="notifyAdminAsMod" items=$admins selectedValue=$modvars.ZikulaDizkusModule.notifyAdminAsMod cssClass='form-control'}
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text="Other settings"}</legend>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="m2f_enabled" __text="Enable Mail2Forum"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="m2f_enabled" disabled=true}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="rss2f_enabled" __text="Enable RSS2Forum"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="rss2f_enabled" disabled=true}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="favorites_enabled" __text="Enable favourites"}
            <div class="col-lg-9">
                {formcheckbox cssClass='form-control' id="favorites_enabled"}
            </div>
        </div>
        <div class="form-group">
            {formlabel class="col-lg-3 control-label" for="deletehookaction" __text="Action to be performed when 'delete' hook is called"}
            <div class="col-lg-9">
                {formdropdownlist id="deletehookaction" items=$deletehook_options selectedValue=$modvars.ZikulaDizkusModule.deletehookaction cssClass='form-control'}
            </div>
        </div>
    </fieldset>

    <div class="col-lg-offset-3 col-lg-9">
        {formbutton id="submit" commandName="submit" __text="Save" class="btn btn-success"}
        {formbutton id="restore" commandName="restore" __text="Restore defaults" class="btn btn-danger"}
    </div>

    {/form}

</div>

{adminfooter}