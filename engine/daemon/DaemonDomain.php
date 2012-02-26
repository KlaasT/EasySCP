<?php
/**
 * @param $Input
 * @return bool
 */
function DaemonDomain($Input) {
	$cfg = EasySCP_Registry::get('Config');

	if ( DB::getInstance() ){
		System_Daemon::info('Database access success.');
	}

	$sql_param = array(
	    ':domain_name' => $Input
	);
	$sql_query = "
	    SELECT
			*
	    FROM
			domain
	    WHERE
			domain_name = :domain_name
	";

	// Einzelne Schreibweise
	DB::prepare($sql_query);
	if ( $row = DB::execute('', $sql_param, true) ){
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
		$tpl->assign(
			array(
				"DOMAIN_NAME"	=>	$row['domain_name'],
				"DOMAIN_GID"	=>	$row['domain_gid'],
				"DOMAIN_UID"	=>	$row['domain_uid'],
				"DOMAIN_CGI"	=>	$row['domain_cgi'],
				"DOMAIN_PHP"	=>	$row['domain_php'],
			)
		);

		$config = $tpl->fetch("apache/parts/vhost.tpl");

		file_put_contents($cfg->APACHE_SITES_DIR.'/'.$Input.'.conf', $config);

	}

	System_Daemon::info('Alles Ok');

	return true;
}
?>