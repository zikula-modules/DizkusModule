{ajaxheader modname='Dizkus'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_tools.js'}
{pageaddvar name='javascript' value='modules/Dizkus/javascript/dizkus_admin.js'}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<div id="dizkus_admin">

    {form cssClass="z-form"}
    {formvalidationsummary}

    <fieldset>
        <legend>{gt text="General settings"}</legend>

        <div class="z-formrow">
            {formlabel for="forum_enabled" __text='Forums are accessible to visitors'}
            {formcheckbox id="forum_enabled" checked=$forum_enabled_checked}
        </div>
        <p class="z-formnote z-informationmsg">
            {gt text="If the 'Forums are accessible to visitors' setting is deactivated then only administrators will have access to the forums. You can temporarily deactivate this setting to take the forums off-line when you need to perform maintenance."}
        </p>
        <div class="z-formrow">
            {formlabel for="forum_disabled_info" __text="Message displayed if forums are disabled"}
            {formtextinput id="forum_disabled_info" textMode="multiline" rows="3" cols="40" text=$coredata.Dizkus.forum_disabled_info}
        </div>
        <div class="z-formrow">
            {formlabel for="email_from" __text="Sender address for e-mail messages from forums"}
            {formtextinput id="email_from" text=$coredata.Dizkus.email_from size="30" maxLength="100"}
        </div>
        <div class="z-formrow">
            {formlabel for="hot_threshold" __text="'Hot topic' threshold"}
            {formintinput id="hot_threshold" text=$coredata.Dizkus.hot_threshold size="3" maxLength="3" minValue=2 maxValue=999}
        </div>
        <div class="z-formrow">
            {formlabel for="posts_per_page" __text="Posts per page in topic index (default:20)"}
            {formintinput id="posts_per_page" text=$coredata.Dizkus.posts_per_page size="3" maxLength="3" minValue=5 maxValue=999}
        </div>
        <div class="z-formrow">
            {formlabel for="topics_per_page" __text="Topics per page in forum index (default:15)"}
            {formintinput id="topics_per_page" text=$coredata.Dizkus.topics_per_page size="3" maxLength="3" minValue=5 maxValue=999}
        </div>
        <div class="z-formrow">
            {formlabel for="hideusers" __text="Hide users in forum admin interface"}
            {formcheckbox id="hideusers" checked=$hideusers_checked}
        </div>
        <div class="z-formrow">
            {formlabel for="url_ranks_images" __text="Path to rank images"}
            {formtextinput id="url_ranks_images" text=$coredata.Dizkus.url_ranks_images size="30" maxLength="100"}
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text="Advanced settings"}</legend>
        <div class="z-warningmsg">
            {gt text="Caution! Inappropriate settings here can lead to unwanted side effects. You are recommended not to alter the settings below unless you fully understand what the results will be. <br /><br />Database name: '%s'; type: '%s'; version: '%s'." tag1=$dbname tag2=$dbtype tag3=$dbversion}
        </div>
        <div class="z-formrow">
            {formlabel for="fulltextindex" __text="Enable full-text index field searching"}
            {formcheckbox id="fulltextindex" checked=$fulltextindex_checked}
            <p class="z-formnote z-informationmsg">{gt text="Notice: For searches with full-text index fields, you need MySQL 4 or later; the feature does not work with InnoDB databases. This flag will normally be set during installation, when the index fields have been created. Search results may be empty if the query string is present in too many postings. This is a feature of MySQL. For more information, see <a href=\"http://dev.mysql.com/doc/mysql/en/fulltext-search.html\" title=\"Full-text search in MySQL\">'Full-text search in MySQL'</a> in the MySQL documentation."}</p>
        </div>
        <div class="z-formrow">
            {formlabel for="extendedsearch" __text="Enable extended full-text search in internal search"}
            {formcheckbox id="extendedsearch" checked=$extendedsearch_checked}
            <p class="z-formnote z-informationmsg">{gt text="Notice: Extended full-text searching enables queries like '+Dizkus -Skype' to find posts that contain 'Dizkus' but not 'Skype'. Requires MySQL 4.01 or later. For more information, see <a href=\"http://dev.mysql.com/doc/mysql/en/fulltext-boolean.html\" title=\"Extended full-text search in MySQL\">'Full-text search in MySQL'</a> in the MySQL documentation."}</p>
        </div>
        <div class="z-formrow">
            {formlabel for="showtextinsearchresults" __text="Show text in search results"}
            {formcheckbox id="showtextinsearchresults" checked=$showtextinsearchresults_checked}
            <p class="z-formnote z-informationmsg">{gt text="Notice: Deactivate the 'Show text in search results' setting for high-volume sites if you need to improve search performance, or if you need to be attentive to constant cleaning of the search results table."}</p>
        </div>
        <div class="z-formrow">
            {formlabel for="minsearchlength" __text="Minimum number of characters in search query string (1 minimum)"}
            {formintinput id="minsearchlength" text=$coredata.Dizkus.minsearchlength size="2" maxLength="2" minValue=1 maxValue=50}
        </div>

        <div class="z-formrow">
            {formlabel for="maxsearchlength" __text="Maximum number of characters in search query string (50 maximum)"}
            {formintinput id="maxsearchlength" text=$coredata.Dizkus.maxsearchlength size="2" maxLength="2" minValue=1 maxValue=50}
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text="User-related settings"}</legend>
        <div class="z-formrow">
            {formlabel for="post_sort_order" __text="Sort order for posts"}
            {formdropdownlist id="post_sort_order" items=$post_sort_order_options selectedValue=$coredata.Dizkus.post_sort_order}
        </div>
        <div class="z-formrow">
            {formlabel for="signature_start" __text="Beginning of signature"}
            {formtextinput id="signature_start" textMode="multiline" rows="3" cols="40" text=$coredata.Dizkus.signature_start|default:''}
        </div>
        <div class="z-formrow">
            {formlabel for="signature_end" __text="End of signature"}
            {formtextinput id="signature_end" textMode="multiline" rows="3" cols="40" text=$coredata.Dizkus.signature_end|default:''}
        </div>
        <div class="z-formrow">
            {formlabel for="signaturemanagement" __text="Enable signature management via forum user settings"}
            {formcheckbox id="signaturemanagement" checked=$signaturemanagement_checked}
        </div>
        <div class="z-formrow">
            {formlabel for="removesignature" __text="Strip user signatures from posts"}
            {formcheckbox id="removesignature" checked=$removesignature_checked}
        </div>
        <div class="z-formrow">
            {formlabel for="newtopicconf" __text="Display confirmation when a new topic has been created"}
            {formcheckbox id="newtopicconf" checked=$newtopicconfirmation_checked}
        </div>
        <div class="z-formrow">
            {if $contactlist_available eq true}
            {formlabel for="ignorelist_handling" __text="Highest-allowed 'ignore list' ostracism level"}
            {formdropdownlist id="ignorelist_handling" items=$ignorelist_options selectedValue=$coredata.Dizkus.ignorelist_handling}
            <p class="z-formnote z-informationmsg">{gt text="Users who are being ignored by a topic poster cannot post messages under this topic when 'strict' level is active. When 'medium' level is active, they can reply but their postings will generally not be shown to users who are ignoring the poster. Also, e-mail notifications will not be sent. Concealed postings will be shown when you click on the posting."}</p>
            {else}
            <p class="z-formnote z-informationmsg">{gt text="Notice: 'Ignore list' support is not currently available. The <a href=\"http://code.zikula.org/contactlist/\">ContactList</a> module must be installed for this feature to be operative."}</p>
            {/if}
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text="Security settings"}</legend>
        <div class="z-formrow">
            {formlabel for="log_ip" __text="Log IP addresses"}
            {formcheckbox id="log_ip" checked=$log_ip_checked}
        </div>
        <div class="z-formrow">
            {formlabel for="striptags" __text="Strip HTML tags from new posts"}
            {formcheckbox id="striptags" checked=$striptags_checked}
            <p class="z-formnote z-informationmsg">{gt text="Notice: Setting 'Strip HTML tags from new posts' to enabled does not affect the content of '[code][/code]' BBCode tags."}</p>
        </div>

        <div class="z-formrow">
            {formlabel for="timespanforchanges" __text="Number of hours during which edits to posts are allowed (Maximum:72)"}
            <span>
                {formintinput id="timespanforchanges" text=$coredata.Dizkus.timespanforchanges size="3" maxLength="3" minValue=1 maxValue=72}
                {gt text="hours"}
            </span>
        </div>
    </fieldset>

    <fieldset>
        <legend>{gt text="Other settings"}</legend>

        <div class="z-formrow">
            {formlabel for="slimforum" __text="Hide category view when there is only one category"}
            {formcheckbox id="slimforum" checked=$slimforum_checked}
        </div>
        <div class="z-formrow">
            {formlabel for="m2f_enabled" __text="Enable Mail2Forum"}
            {formcheckbox id="m2f_enabled" checked=$m2f_enabled_checked}
        </div>
        <div class="z-formrow">
            {formlabel for="rss2f_enabled" __text="Enable RSS2Forum"}
            {formcheckbox id="rss2f_enabled" checked=$rss2f_enabled_checked}
        </div>
        <div class="z-formrow">
            {formlabel for="favorites_enabled" __text="Enable favourites"}
            {formcheckbox id="favorites_enabled" checked=$favorites_enabled_checked}
        </div>
        <div class="z-formrow">
            {formlabel for="deletehookaction" __text="Action to be performed when 'delete' hook is called"}
            {formdropdownlist id="deletehookaction" items=$deletehook_options selectedValue=$coredata.Dizkus.deletehookaction}
        </div>
    </fieldset>

    <div class="z-formbuttons z-buttons">
        {formbutton id="submit" commandName="submit" __text="Save" class="z-bt-ok"}
        {formbutton id="restore" commandName="restore" __text="Restore defaults" class="z-bt-delete"}
    </div>

    {/form}

</div>

{adminfooter}