<ul>
	<li><a href="webtools.php">{$TR_MENU_OVERVIEW}</a></li>
	<li><a href="protected_areas.php">{$TR_HTACCESS}</a></li>
	<li><a href="protected_user_manage.php">{$TR_HTACCESS_USER}</a></li>
	<li><a href="error_pages.php">{$TR_MENU_ERROR_PAGES}</a></li>
	{if isset($ISACTIVE_BACKUP)}
	<li><a href="backup.php">{$TR_MENU_DAILY_BACKUP}</a></li>
	{/if}
	{if isset($ISACTIVE_EMAIL)}
	<li><a href="{$WEBMAIL_PATH}">{$TR_WEBMAIL}</a></li>
	{/if}
	<li><a href="{$FILEMANAGER_PATH}">{$TR_FILEMANAGER}</a></li>
	{if isset($AWSTATS_PATH)}
	<li><a href="{$AWSTATS_PATH}" class="external">{$TR_AWSTATS}</a></li>
	{/if}
</ul>