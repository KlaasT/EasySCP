<?php

class DaemonDomain {
	
	public function execute($Input) {
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
		if ( $row = DB::execute($sql_param, true) ){
		    echo var_dump($row);
		}
		/*
		$cfg = EasySCP_Registry::get('Config');
		$sql = EasySCP_Registry::get('Db');
		$tpl = EasySCP_TemplateEngine::getInstance();
		
		$runningOk = true;
		
		
		$query = "
			SELECT
				`domain_name`,
				`domain_gid`,
				`domain_uid`,
				`domain_status`,
				`domain_ssl`,
				`ssl_key`,
				`ssl_cert`,
				`ssl_status`
				`domain_ip_id`,
				`domain_php`,
				`domain_cgi`
			FROM
				`domain`
			WHERE
				`domain_name` = ?
		";
		
		$res = exec_query($sql, $query, $Input);
		$row = $res->fetchRow();
		
		$tpl->assign(array(
			"DOMAIN_NAME"	=>	$row['domain_name'],
			"DOMAIN_GID"	=>	$row['domain_gid'],
			"DOMAIN_UID"	=>	$row['domain_uid'],
			"DOMAIN_CGI"	=>	$row['domain_cgi'],
			"DOMAIN_PHP"	=>	$row['domain_php'],
		));
		
		$query = "
			SELECT
				`ip_number`
			FROM
				`server_ips`
			WHERE
				`ip_id` = ?
		";
		
		$res = exec_query($sql, $query, $row['domain_ip_id']);
		$row = $res->fetchRow();
		
		$tpl->assign(array(
			"DOMAIN_IP"	=>	$row['ip_number'],
		));
		
		$config = $tpl->fetch("apache/vhost.tpl");
		
		file_put_contents($cfg->APACHE_SITES_DIR.'/'.$Input.'.conf', $config);
		
		return $runningOk;
		*/
		System_Daemon::info('Domain läuft');
		
		// return $runningOk;
	}
}
?>