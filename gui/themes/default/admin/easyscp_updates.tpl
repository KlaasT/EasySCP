{include file='admin/header.tpl'}
<body>
	<div class="header">
		{include file="$MAIN_MENU"}
		<div class="logo">
			<img src="{$THEME_COLOR_PATH}/images/easyscp_logo.png" alt="EasySCP logo" />
			<img src="{$THEME_COLOR_PATH}/images/easyscp_webhosting.png" alt="EasySCP - Easy Server Control Panel" />
		</div>
	</div>
	<div class="location">
		<ul class="location-menu">
			
			<li><a href="../index.php?logout" class="logout">{$TR_MENU_LOGOUT}</a></li>
		</ul>
		<ul class="path">
			<li><a href="system_info.php">{$TR_MENU_OVERVIEW}</a></li>
			<li><a>{$TR_UPDATES_TITLE}</a></li>
		</ul>
	</div>
	<div class="left_menu">{include file="$MENU"}</div>
	<div class="main">
		{if isset($MESSAGE)}
		<div class="{$MSG_TYPE}">{$MESSAGE}</div>
		{/if}
		<h2 class="update"><span>{$TR_UPDATES_TITLE}</span></h2>
		{if isset($UPDATE_MESSAGE)}
		<div class="{$UPDATE_MSG_TYPE}">{$UPDATE_MESSAGE}</div>
		{/if}
		{if isset($UPDATE)}
		<table>
			<thead>
				<tr>
					<th colspan="2">{$TR_AVAILABLE_UPDATES}</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>{$TR_UPDATE}</strong></td>
					<td>{$UPDATE}</td>
				</tr>
				<tr>
					<td><strong>{$TR_INFOS}</strong></td>
					<td>{$INFOS}</td>
				</tr>
			</tbody>
		</table>
		{/if}
		<br />
		<h2 class="update"><span>{$TR_DB_UPDATES_TITLE}</span></h2>
		{if isset($DB_UPDATE_MESSAGE)}
		<div class="{$DB_UPDATE_MSG_TYPE}">{$DB_UPDATE_MESSAGE}</div>
		{/if}
		{if isset($DB_UPDATE)}
		<form action="easyscp_updates.php" method="post" id="database_update">
			<table>
				<thead>
					<tr>
						<th colspan="2">{$TR_DB_AVAILABLE_UPDATES}</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>{$TR_UPDATE}</strong></td>
						<td>{$DB_UPDATE}</td>
					</tr>
					<tr>
						<td><strong>{$TR_INFOS}</strong></td>
						<td>{$DB_INFOS}</td>
					</tr>
				</tbody>
			</table>
			<div class="buttons">
				<input type="hidden" name="execute" id='execute' value="update" />
				<input type="submit" name="Submit" value="{$TR_EXECUTE_UPDATE}" />
			</div>
		</form>
		{/if}
	</div>
{include file='admin/footer.tpl'}