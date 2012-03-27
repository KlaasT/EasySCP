<?php
/**
 * EasySCP a Virtual Hosting Control Panel
 * Copyright (C) 2010-2012 by Easy Server Control Panel - http://www.easyscp.net
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @link 		http://www.easyscp.net
 * @author 		EasySCP Team
 */
// TODO: create .htpasswd and .htgroup
// TODO: Check if user or group already exists

/**
 * Handles DaemonDomain requests to create, modify or delete domains.
 * Includes the creation of virtual users for EasySCP.
 * 
 * @param String $Input domainname
 * @return boolean 
 */
function DaemonDomain($Input) {
	$cfg = EasySCP_Registry::get('Config');

	$sql_param = array(
		':domain_name' => $Input
	);

	$sql_query = "
		SELECT `d`.*, `s`.`ip_number`
		FROM
			`domain` AS `d`,
			`server_ips` AS `s`
		WHERE
			`d`.`domain_ip_id` = `s`.`ip_id`
		AND
			`d`.`domain_name` = :domain_name
	";

	$retVal = null;
//	 'dnschange', 'restore'
	DB::prepare($sql_query);
	if ($row = DB::execute($sql_param, true)) {
		switch ($row['domain_status']) {
			case 'toadd':
				$retVal = domainCreate($cfg, $row);
				break;
			case 'change':
				$retVal = domainChange($cfg, $row);
				break;
			case 'toenable':
				$retVal = domainEnable($cfg, $row);
				break;
			case 'todisable':
				$retVal = domainDisable($cfg, $row);
				break;
			case 'todelete':
				$retVal = domainChange($cfg, $row);
				break;
			case 'ok':
				$retVal =  true;
				break;
			default:
				System_Daemon::warning("Don't know what to do with " . $row['domain_status']);
				$retVal = false;
				break;
		}
	} else {
		$retVal = false;
	}
	if($retVal == true){
		$retVal = dbSetDomainStatus('ok', $Input);
	}
	return $retVal;
}

/**
 * Writes Apache configuration for SSL domain and enables configuration.
 * 
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function apacheWriteSSLSiteConfig($cfg, $domainData) {

	$tpl_param = array(
		"DOMAIN_IP" => $domainData['ip_number'],
		"DOMAIN_NAME" => $domainData['domain_name'],
		"DOMAIN_GID" => $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'],
		"DOMAIN_UID" => $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'],
		"AWSTATS" => ($cfg->AWSTATS_ACTIVE == 'yes') ? true : false,
		"DOMAIN_CGI" => ($row['domain_cgi'] == 'yes') ? true : false,
		"DOMAIN_PHP" => ($row['domain_php'] == 'yes') ? true : false,
		"BASE_SERVER_VHOST" => $cfg->BASE_SERVER_VHOST,
		"WWW_DIR" => $cfg->APACHE_WWW_DIR,
		"CUSTOM_SITES_CONFIG_DIR" => $cfg->APACHE_CUSTOM_SITES_CONFIG_DIR,
		"SSL_CERT_DIR" => $cfg->SSL_CERT_DIR,
		"SSL_KEY_DIR" => $cfg->SSL_KEY_DIR,
	);
	$tpl = getTemplate($tpl_param);
	$config = $tpl->fetch("apache/parts/vhost_ssl.tpl");
	$confFile = $cfg->APACHE_SITES_DIR . '/' . $domainData['domain_name'] . '-ssl.conf';

	if (systemWriteContentToFile($confFile,$config,$cfg->ROOT_USER, $cfg->ROOT_GROUP, 0644 )){
		return true;
	}
	return false;
}

/**
 * Write redirect information for domain for SSL-redirects
 * 
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function apacheWriteSSLRedirectConfig($cfg, $domainData) {
	$tpl_param = array(
		"DOMAIN_IP" => $domainData['ip_number'],
		"DOMAIN_NAME" => $domainData['domain_name'],
		"WWW_DIR" => $cfg->APACHE_WWW_DIR,
	);

	$tpl = getTemplate($tpl_param);
	$config = $tpl->fetch("apache/parts/vhost_redirect_ssl.tpl");
	$confFile = $cfg->APACHE_SITES_DIR . '/' . $domainData['domain_name'] . '-redirect-ssl.conf';
	if (systemWriteContentToFile($confFile,$config,$cfg->ROOT_USER, $cfg->ROOT_GROUP, 0644 )){
		return true;
	}
	return false;
}

/**
 *
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function directoriesCreateHtdocsStructure($cfg, $domainData) {
	$homeDir	= $cfg->APACHE_WWW_DIR . "/" . $domainData['domain_name'];
	$sysUser	= $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'];
	$rootUser	= $cfg->ROOT_USER;
	$sysGroup	= $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'];
	$rootGroup	= $cfg->ROOT_GROUP;
	$httpGroup	= $cfg->APACHE_GROUP;

	if (!file_exists($homeDir)) {
		if (systemCreateDirectory($homeDir, $sysUser, $httpGroup, 0770)&&
			systemCreateDirectory("$homeDir/cgi-bin", $sysUser, $sysGroup, 0755)&&
			systemCreateDirectory("$homeDir/logs", $sysUser, $httpGroup, 0770)&&
			systemCreateDirectory("$homeDir/phptmp", $sysUser, $httpGroup, 0770)&&
			systemCreateDirectory("$homeDir/backups", $rootUser, $rootGroup, 0755)&&
			systemCreateDirectory("$homeDir/statistics", $sysUser, $sysGroup,0755)&&
			directoriesCreateHtdocs($cfg, $domainData)&&
			directoriesCreateError($cfg, $domainData) &&
			directoriesCreateDisabled($cfg, $domainData) ){
			
			return true;
		} else {
			System_Daemon::debug("Failed to create $homeDir!");
			return false;
		}
	} else {
		System_Daemon::debug("$homeDir already exists!");
	}
	return true;
}

/**
 *
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function directoriesCreateError($cfg, $domainData) {
	$errorDir = $cfg->APACHE_WWW_DIR . "/" . $domainData['domain_name'] . "/errors";
	$sysGroup = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'];
	$sysUser = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'];

	if (!systemCreateDirectory($errorDir, $sysUser, $sysGroup, 0775)||
		!systemCreateDirectory($errorDir . "/inc", $sysUser, $sysGroup, 0775)){
		return false;
	}

	// copy errorfiles 
	$errorFiles = array(401, 403, 404, 500, 503);
	foreach ($errorFiles as $file) {
		System_Daemon::debug($errorDir . "/" . $file . ".html");
		if (!file_exists($errorDir . "/" . $file . ".html")) {
			System_Daemon::debug("Copying file " . $errorDir . "/" . $file . ".html");
			if (!copy($cfg->ROOT_DIR . "/gui/errordocs/$file.html", $errorDir . "/" . $file . ".html")){
				return false;
			}
		}
	}

	// copy inc
	$sourceDir = dir($cfg->ROOT_DIR . "/gui/errordocs/inc");
	while (false !== $entry = $sourceDir->read()) {
		// Skip pointers 
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		if ($cfg->ROOT_DIR . "/gui/errordocs/inc/$entry" !== "$errorDir/inc/$entry") {
			if (!copy($cfg->ROOT_DIR . "/gui/errordocs/inc/$entry", "$errorDir/inc/$entry")){
				return false;
			}
		}
	}
	return true;
}

/**
 *
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function directoriesCreateDisabled($cfg, $domainData) {
	$disabledDir = $cfg->APACHE_WWW_DIR . "/" . $domainData['domain_name'] . "/disabled";
	$sysGroup = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'];
	$sysUser = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'];

	if (!systemCreateDirectory($disabledDir, $sysUser, $sysGroup) ||
		!systemCreateDirectory("$disabledDir/images", $sysUser, $sysGroup)){
		return false;
	} else {
		System_Daemon::debug("Created $disabledDir");
	}

	$tpl_param = array(
		"THEME_CHARSET" => "utf8",
		"DOMAIN_NAME" => $domainData['domain_name']
	);
	$tpl = getTemplate($tpl_param);

	// write Apache config
	$config = $tpl->fetch("apache/parts/default_index_disabled.tpl");
	$htmlFile = $disabledDir . "/index.html";
	if (!systemWriteContentToFile($htmlFile, $config, $sysUser, $cfg->APACHE_GROUP, 0644)||
		!copy($cfg->ROOT_DIR . "/gui/domain_disable_page/easyscp.css", $disabledDir . "/easyscp.css")||
		!chown($disabledDir . "/easyscp.css", $sysUser)||
		!chgrp($disabledDir . "/easyscp.css", $sysGroup)||
		!chmod($disabledDir . "/easyscp.css", 0775)){
		return false;
	}

	//copy images
	$sourceDir = dir($cfg->ROOT_DIR . "/gui/domain_disable_page/images");
	while (false !== $entry = $sourceDir->read()) {
		// Skip pointers 
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		if ("$disabledDir/images/$entry" !== $cfg->ROOT_DIR . "/gui/domain_disable_page/images/$entry") {
			if(!copy($cfg->ROOT_DIR . "/gui/domain_disable_page/images/$entry", "$disabledDir/images/$entry")){
				System_Daemon::info($cfg->ROOT_DIR . "/gui/domain_disable_page/images/$entry to $disabledDir/images/$entry");
				return false;
			}
		}
	}
	return true;
}

/**
 *
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function directoriesCreateHtdocs($cfg, $domainData) {
	$htdocsDir = $cfg->APACHE_WWW_DIR . "/" . $domainData['domain_name'] . "/htdocs";
	$sysGroup = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'];
	$sysUser = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'];

	if	(!systemCreateDirectory($htdocsDir, $sysUser, $sysGroup)||
		!systemCreateDirectory($htdocsDir . "/images", $sysUser, $sysGroup)){
		return false;
	}

	$tpl_param = array(
		"THEME_CHARSET" => "utf8",
		"DOMAIN_NAME" => $domainData['domain_name'],
		"BASE_SERVER_VHOST_PREFIX" => $cfg->BASE_SERVER_VHOST_PREFIX,
		"BASE_SERVER_VHOST" => $cfg->BASE_SERVER_VHOST
	);
	$tpl = getTemplate($tpl_param);

	$config = $tpl->fetch("apache/parts/default_index.tpl");
	$htmlFile = $htdocsDir . "/index.html";
	
	if (!systemWriteContentToFile($htmlFile,$config,$sysUser,$sysGroup,0644)||
		!copy($cfg->ROOT_DIR . "/gui/domain_default_page/easyscp.css", $htdocsDir . "/easyscp.css")||
		!chown($htdocsDir . "/easyscp.css", $sysUser)||
		!chgrp($htdocsDir . "/easyscp.css", $sysGroup)||
		!chmod($htdocsDir . "/easyscp.css", 0775)){
		return false;
	}

	//copy images
	$sourceDir = dir($cfg->ROOT_DIR . "/gui/domain_default_page/images");
	while (false !== $entry = $sourceDir->read()) {
		// Skip pointers 
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		if ("$htdocsDir/images/$entry" !== $cfg->ROOT_DIR . "/gui/domain_default_page/images/$entry") {
			if (!copy($cfg->ROOT_DIR . "/gui/domain_default_page/images/$entry", "$htdocsDir/images/$entry")){
				return false;
			}
		}
	}
	return true;
}

/**
 * Writes Apache configuration for default domain and enables configuration.
 * 
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function apacheWriteSiteConfig($cfg, $domainData) {
	$sysGroup = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'];
	$sysUser = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'];

	$tpl_param = array(
		"DOMAIN_IP" => $domainData['ip_number'],
		"DOMAIN_NAME" => $domainData['domain_name'],
		"DOMAIN_GID" => $sysUser,
		"DOMAIN_UID" => $sysGroup,
		"AWSTATS" => ($cfg->AWSTATS_ACTIVE == 'yes') ? true : false,
		"DOMAIN_CGI" => ($row['domain_cgi'] == 'yes') ? true : false,
		"DOMAIN_PHP" => ($row['domain_php'] == 'yes') ? true : false,
		"BASE_SERVER_VHOST" => $cfg->BASE_SERVER_VHOST,
		"WWW_DIR" => $cfg->APACHE_WWW_DIR,
		"CUSTOM_SITES_CONFIG_DIR" => $cfg->APACHE_CUSTOM_SITES_CONFIG_DIR,
	);
	$tpl = getTemplate($tpl_param);

	// write Apache config
	$config = $tpl->fetch("apache/parts/vhost.tpl");
	$confFile = $cfg->APACHE_SITES_DIR . '/' . $domainData['domain_name'] . '.conf';
	
	if(!systemWriteContentToFile($confFile, $config,$cfg->ROOT_USER, $cfg->ROOT_GROUP, 0644)){
		System_Daemon::warning("Failed to write $confFile");
		return false;
	}

	$tpl_param = array(
		"SELF" => $domainData['domain_name']
	);
	$tpl = getTemplate($tpl_param);
	// write Apache config
	$config = $tpl->fetch("apache/parts/custom.conf.tpl");
	$confFile = $cfg->APACHE_CUSTOM_SITES_CONFIG_DIR . '/' . $domainData['domain_name'] . '.conf';
	if(!systemWriteContentToFile($confFile, $config,$cfg->ROOT_USER, $cfg->ROOT_GROUP, 0644)){
		System_Daemon::warning("Failed to write $confFile");
		return false;
	}
	return true;
}

/**
 *
 * @param array $tpl_param
 * @return \Smarty 
 */
function getTemplate($tpl_param) {
	require_once('Smarty/Smarty.class.php');
	$tpl = new Smarty();
	$tpl->caching = false;
	$tpl->setTemplateDir(
			array(
				'EasySCP' => '/etc/easyscp/'
			)
	);
	$tpl->setCompileDir('/tmp/templates_c/');

	// echo var_dump($row);
	$tpl->assign($tpl_param);

	return $tpl;
}

/**
 * Create system user and group
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function systemCreateUserGroup($cfg, $domainData) {
	$sysGroup = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'];
	$sysUser = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'];
	$sysGID = $domainData['domain_gid'];
	$sysUID = $domainData['domain_uid'];
	$homeDir = $cfg->APACHE_WWW_DIR . "/" . $domainData['domain_name'];
	$retVal = null;
	$retStr = null;

	// add group and user for BSD has a different format
	if (strcmp($cfg->ROOT_GROUP, 'wheel') == 0) {
		$cmdGroup = $cfg->CMD_GROUPADD . " $sysGroup -g $sysGID";
		$cmdUser = $cfg->CMD_USERADD . " $sysUser -c virtual-user -d $homeDir -g " .
				"$sysGroup -s /bin/false -u $sysUID";
	} else {
		$cmdGroup = $cfg->CMD_GROUPADD . " -g $sysGID $sysGroup";
		$cmdUser = $cfg->CMD_USERADD . " -c virtual-user -d $homeDir -g " .
				"$sysGroup -s /bin/false -u $sysUID $sysUser";
	}

	$retStr = system($cmdGroup,$retVal);
	if ($retVal==0||$retVal==9){
			System_Daemon::info("Group $sysGroup ($sysGID) created");
	} else {
		System_Daemon::warning("Group $sysGroup ($sysGID) not created (return: $retVal)");
		System_Daemon::warning($retStr);
		return false;
	}
	
	$retVal = null;
	
	$retStr = system($cmdUser, $retVal);
	if ($retVal == 0||$retVal==9) {
		System_Daemon::info("User $sysUser ($sysUID) created");
	} else {
		System_Daemon::warning("User $sysUser ($sysUID) not created (return: $retVal)");
		System_Daemon::warning($retStr);
		return false;
	}
	return true;
}

/**
 *
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function domainDisable($cfg, $domainData) {
	if (!apacheEnableSite($domainData['domain_name'] . '-disabled.conf')||
		!apacheDisableSite($domainData['domain_name'] . '.conf')||
		!apacheDisableSite($domainData['domain_name'] . '-ssl.conf')||
		!apacheDisableSite($domainData['domain_name'] . '-redirect-ssl.conf')){
		return false;
	}

	// reload Apache web-server
	$retVal = null;
	system($cfg->CMD_HTTPD . " reload",$retVal);
	return $retVal==0 ? true : false;
}

/**
 *
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function domainDelete($cfg, $domainData) {
	if (!apacheDisableSite($domainData['domain_name'] . '-disabled.conf') ||
		!apacheDisableSite($domainData['domain_name'] . '.conf') ||
		!apacheDisableSite($domainData['domain_name'] . '-ssl.conf')||
		!apacheDisableSite($domainData['domain_name'] . '-redirect-ssl.conf')||
		!unlink($cfg->APACHE_SITES_DIR . '/' . $domainData['domain_name'] . '.conf')||
		!unlink($cfg->APACHE_SITES_DIR . '/' . $domainData['domain_name'] . '-ssl.conf')||
		!unlink($cfg->APACHE_SITES_DIR . '/' . $domainData['domain_name'] . '-disabled.conf')||
		!unlink($cfg->APACHE_SITES_DIR . '/' . $domainData['domain_name'] . '-redirect-ssl.conf')){
		return false;
	}

	// reload Apache web-server
	$retVal = null;
	system($cfg->CMD_HTTPD . " reload",$retVal);
	if ($retVal!=0){
		return false;
	}

	//delete user and group
	$sysGroup = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'];
	$sysUser = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'];
	$cmdGroup = $cfg->CMD_GROUPDEL . " $sysGroup";
	$cmdUser = $cfg->CMD_USERDEL . " $sysUser";
	exec($cmdUser);
	exec($cmdGroup);

	//delete directories
	$homeDir = $cfg->APACHE_WWW_DIR . "/" . $domainData['domain_name'];
	$cmd = $cfg->CMD_RM . " -rf $homeDir";
	exec($cmd);
	System_Daemon::warning("Deleted $homeDir");

	//delete domain from db
	$sql_param = array(
		':domain_name' => $domainName
	);

	$sql_query = "
		DELETE FROM `domain`
		WHERE
			`domain_name` = :domain_name
	";

	DB::prepare($sql_query);
	if ($row = DB::execute($sql_param, true)) {
		
	}
	return true;
}

/**
 *
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function domainEnable($cfg, $domainData) {
	switch ($domainData['ssl_status']) {
		// Default Domain-Konfiguration
		case 0:
			if (!apacheEnableSite($domainData['domain_name'] . '.conf')||
				!apacheDisableSite($domainData['domain_name'] . '-ssl.conf')||
				!apacheDisableSite($domainData['domain_name'] . '-redirect-ssl.conf')||
				!apacheDisableSite($domainData['domain_name'] . '-disabled.conf')){
				System_Daemon::warning("Failed to enable domain ".$domainData['domain_name'] . ' 0');
				return false;
			}
			break;
		// SSL und non-SSL gemeinsam
		case 2:
			if (!apacheEnableSite($domainData['domain_name'] . '.conf')||
				!apacheEnableSite($domainData['domain_name'] . '-ssl.conf')||
				!apacheDisableSite($domainData['domain_name'] . '-redirect-ssl.conf')||
				!apacheDisableSite($domainData['domain_name'] . '-disabled.conf')||
				!writeSSLKeys($domainData)){
				System_Daemon::warning("Failed to enable domain ".$domainData['domain_name'] . ' 1');
				return false;
			}
			break;
		// Nur SSL mit redirect auf SSL
		case 1:
			if (!apacheEnableSite($domainData['domain_name'] . '-ssl.conf')||
				!apacheEnableSite($domainData['domain_name'] . '-redirect-ssl.conf')||
				!apacheDisableSite($domainData['domain_name'] . '.conf')||
				!apacheDisableSite($domainData['domain_name'] . '-disabled.conf')||
				!writeSSLKeys($domainData)){
				System_Daemon::warning("Failed to enable domain ".$domainData['domain_name']) . ' 2';
				return false;
			}
			break;
	}

	// reload Apache web-server
	$retVal = null;
	system($cfg->CMD_HTTPD . " reload",$retVal);
	return $retVal==0 ? true : false;
}

/**
 * Create new Domain
 * 
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function domainCreate($cfg, $domainData) {
	if (systemCreateUserGroup($cfg, $domainData) &&
		directoriesCreateHtdocsStructure($cfg, $domainData)&&
		directoriesCreateFCGI($domainData) &&
		apacheWriteSiteConfig($cfg, $domainData)&&
		apacheWriteSSLSiteConfig($cfg, $domainData)&&
		apacheWriteSSLRedirectConfig($cfg, $domainData)&&
		apacheWriteDisabledSiteConfig($cfg, $domainData)){
		if (!domainEnable($cfg, $domainData)){
			System_Daemon::warning("Failed to enable ".$domainData['domain_name']);
			return false;
		} else {
			System_Daemon::info("Successfully created domain ".$domainData['domain_name']);
			return true;
		}
	} else {
		System_Daemon::warning("Creation of domain ".$domainData['domain_name']." failed!");
		return false;		
	}
}

/**
 *
 * @param type $cfg
 * @param array $domainData 
 * @return boolean 
 */
function domainChange($cfg, $domainData) {
	if (apacheWriteSiteConfig($cfg, $domainData) &&
		apacheWriteSSLSiteConfig($cfg, $domainData)&&
		apacheWriteSSLRedirectConfig($cfg, $domainData)&&
		apacheWriteDisabledSiteConfig($cfg, $domainData)&&
		domainEnable($cfg, $domainData)){
		return true;
	}else {
		return false;
	}
}

/**
 *
 * @param String $directory directory path
 * @param String $user system user
 * @param String $group system group
 * @param int $perm directory permission
 * @return boolean 
 */
function systemCreateDirectory($directory, $user, $group, $perm = 0775) {
	if (!file_exists($directory)) {
		if (mkdir($directory, $perm, true)) {
			System_Daemon::debug("Created $directory with permission $perm");
			if (!chown($directory, $user)) {
				System_Daemon::warning("Failed to change ownership of $directory to $user");
			} else {
				System_Daemon::debug("Changed ownership of $directory to $user");
			}
			if (!chgrp($directory, $group)) {
				System_Daemon::warning("Failed to change group ownership of $directory to $group");
			}else {
				System_Daemon::debug("Changed group ownership of $directory to $group");
			}
			return true;
		} else {
			System_Daemon::warning("Failed to create $directory with permission $perm");
			return false;
		}
	} else {
		return true;
	}
}

/**
 *
 * @param String $siteName 
 * @return boolean 
 */
function apacheEnableSite($siteName) {
	global $cfg;
	$retVal = null;
	$retStr = null;
	$confFile = $cfg->APACHE_SITES_DIR . '/' . $siteName;
	
	if (file_exists($confFile)) {
		$retStr = system('a2ensite ' . $siteName, $retVal);
		if ($retVal==0){
			System_Daemon::debug("Enabled $siteName");
			return true;
		} else {
			System_Daemon::warning("Failed to enable $siteName");
			System_Daemon::warning($retStr);
			return false;
		}
	}
}

/**
 *
 * @param String $siteName 
 * @return boolean 
 */
function apacheDisableSite($siteName) {
	global $cfg;
	$retVal = null;
	$retStr = null;
	$confFile = $cfg->APACHE_SITES_DIR . '/' . $siteName;
	
	if (file_exists($confFile)) {
		$retStr = system('a2dissite ' . $siteName, $retVal);
		if ($retVal==0){
			System_Daemon::debug("Disabled $siteName");
			return true;
		} else {
			System_Daemon::warning("Failed to disable $siteName");
			System_Daemon::warning($retStr);
			return false;
		}
	} 
	return true;
}

/**
 *
 * @param type $cfg
 * @param array $domainDate 
 * @return boolean 
 */
function apacheWriteDisabledSiteConfig($cfg, $domainData) {
	$tpl_param = array(
		"DOMAIN_IP" => $domainData['ip_number'],
		"DOMAIN_NAME" => $domainData['domain_name'],
		"DOMAIN_GID" => $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'],
		"DOMAIN_UID" => $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'],
		"AWSTATS" => ($cfg->AWSTATS_ACTIVE == 'yes') ? true : false,
		"DOMAIN_CGI" => ($row['domain_cgi'] == 'yes') ? true : false,
		"DOMAIN_PHP" => ($row['domain_php'] == 'yes') ? true : false,
		"BASE_SERVER_VHOST" => $cfg->BASE_SERVER_VHOST,
		"WWW_DIR" => $cfg->APACHE_WWW_DIR,
	);

	$tpl = getTemplate($tpl_param);

	$config = $tpl->fetch("apache/parts/vhost_disabled.tpl");
	$confFile = $cfg->APACHE_SITES_DIR . '/' . $domainData['domain_name'] . '-disabled.conf';
	if (systemWriteContentToFile($confFile, $config, $cfg->ROOT_USER, $cfg->ROOT_GROUP, 0644)){
		return true;
	} else{
		return false;
	}
}

/**
 *
 * @param String $status 
 * @return boolean 
 */
function dbSetDomainStatus($status, $domainName) {
	$sql_param = array(
		':domain_name' => $domainName,
		':domain_status' => $status
	);

	$sql_query = "
		UPDATE `domain`
		SET `domain_status`=:domain_status
		WHERE
			`domain_name` = :domain_name
	";

//	System_Daemon::info($status . $domainName);
//	System_Daemon::info($sql_query);
	DB::prepare($sql_query);
	if ($row = DB::execute($sql_param, false)) {
		System_Daemon::debug("Status set to $status for domain $domainName");
		return true;
	} else {
		System_Daemon::warning("Setting status to $status for domain $domainName failed!");
		return false;
	}
}

/**
 *
 * @global type $cfg
 * @param array $domainData
 * @return boolean  
 */
function directoriesCreateFCGI($domainData) {
	global $cfg;
	$fcgiDir = $cfg->PHP_STARTER_DIR . "/" . $domainData['domain_name'];
	$sysGroup = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_gid'];
	$sysUser = $cfg->APACHE_SUEXEC_USER_PREF . $domainData['domain_uid'];

	System_Daemon::debug("Creating $fcgiDir");
	if(!systemCreateDirectory($fcgiDir, $sysUser, $sysGroup, 0555)||
		!systemCreateDirectory("$fcgiDir/php5", $sysUser, $sysGroup, 0555)){
		return false;
	}

	//file 
	if (!file_exists($fcgiDir."/php5-fcgi-starter")){
		$tpl_param = array(
			"DOMAIN_NAME" => $domainData['domain_name'],
			"WWW_DIR" => $cfg->APACHE_WWW_DIR,
			"PHP_STARTER_DIR" => $cfg->PHP_STARTER_DIR,
			"PHP5_FASTCGI_BIN" => $cfg->PHP5_FASTCGI_BIN
		);

		$tpl = getTemplate($tpl_param);

		$config = $tpl->fetch("fcgi/parts/php5-fcgi-starter.tpl");
		if (!systemWriteContentToFile($fcgiDir . '/php5-fcgi-starter', $config, $sysUser, $sysGroup, 0550)){
			return false;
		}
	}
	if (!file_exists($fcgiDir."/php5/browscap.ini")){
		
		if (!copy($cfg->CONF_DIR . "/fcgi/parts/master/php5/browscap.ini", $fcgiDir . '/php5/browscap.ini')||
			!chown($fcgiDir . '/php5/browscap.ini', $sysUser)||
			!chgrp($fcgiDir . '/php5/browscap.ini', $sysGroup)||
			!chmod($fcgiDir . '/php5/browscap.ini', 0440)){
			return false;
		}
	}
	if (!file_exists($fcgiDir."/php5/php.ini")){
		$tpl_param = array(
			"DOMAIN_NAME"		=> $domainData['domain_name'],
			"WWW_DIR"			=> $cfg->APACHE_WWW_DIR,
			"PHP_STARTER_DIR"	=> $cfg->PHP_STARTER_DIR,
			"PEAR_DIR"			=> $cfg->PEAR_DIR,
			"PHP_TIMEZONE"		=> $cfg->PHP_TIMEZONE
		);
		$tpl = getTemplate($tpl_param);

		$config = $tpl->fetch("fcgi/parts/php5/php.ini");
		if (!systemWriteContentToFile($fcgiDir . '/php5/php.ini', $config, $sysUser, $sysGroup, 0440)){
			return false;
		}
	}
	return true;
}

/**
 *
 * @param type $fileName
 * @param type $content
 * @param type $user
 * @param type $group
 * @param type $perm
 * @return boolean 
 */
function systemWriteContentToFile($fileName, $content, $user, $group, $perm){
	if (file_put_contents($fileName, $content)){
		if (!chown($fileName, $user)){
			System_Daemon::warning("Failed to change ownership of $fileName to $user");
			return false;
		}
		if (!chgrp($fileName, $group)){
			System_Daemon::warning("Failed to change group ownership of $fileName to $group");
			return false;
		}
		if (!chmod($fileName, $perm)){
			System_Daemon::warning("Failed to change permission of $fileName to $perm");
			return false;
		}
		return true;
	} else {
		System_Daemon::warning("Failed to write content to $fileName");
		return false;
	}
}

/**
 * Verify if SSL-key and certificate corresponds. Write key and certificate 
 * @global type $cfg
 * @param array $domainData
 * @return boolean 
 */
function writeSSLKeys($domainData){
	global $cfg;
	$certFile = $cfg->SSL_CERT_DIR . '/easyscp_' . $domainData['domain_name'] . '-cert.pem';
	$keyFile = $cfg->SSL_KEY_DIR . '/easyscp_' . $domainData['domain_name'] . '-key.pem';
	$cert = $domainData['ssl_cert'];
	$key = $domainData['ssl_key'];
	
	if (openssl_x509_check_private_key($cert, $key)) {
		if (!systemWriteContentToFile($certFile, $domainData['ssl_cert'], $cfg->ROOT_USER, $cfg->ROOT_GROUP, 0644) ||
			!systemWriteContentToFile($keyFile, $domainData['ssl_key'], $cfg->ROOT_USER, $cfg->ROOT_GROUP, 0640)) {
			return false;
		}
	} else{
		System_Daemon::warning("Certificate and key don't match");
		return false;
	}

	return true;
}
?>