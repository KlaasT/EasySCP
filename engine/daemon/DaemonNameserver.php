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

	$sql_query = "
	    SELECT
			*
	    FROM
			`domain` `d`
		INNER JOIN
			`domain_dns` `ns` 
		ON (`d`.`domain_id`=`ns`.`domain_id`)
	    WHERE
			domain_name = :domain_name
	";

	// Einzelne Schreibweise
	$dns_a = array();
	$dns_mx = array();
	$dns_ns = array();
	
	$domainresult = DB::prepare($sql_query);
	$domainresult->bindValue(":domain_name", $Input);
	$n=0;
	while ($row = $domainresult->fetch())
	{
		if ($row['domain_type']=="A" or $row['domain_type']=="AAAA" or $row['domain_type']=="CNAME") {
			$dns_a[] = array(
				"DOMAIN_DNS"	=>	$row['domain_dns'],
				"DOMAIN_TEXT"	=>	$row['domain_text'],
				"DOMAIN_TYPE"	=>	$row['domain_type'],
			);
		}
		else if ($row['domain_type']=="NS") {
			$n++;
			
			if ($n==1) {
				$template->assign("PRIMARY_NS", $row['domain_text']);
			}
			$dns_ns[] = array(
				"DOMAIN_TEXT"	=>	$row['domain_text'],
			);
		}
		else if ($row['domain_type']=="MX") {
			$dns_mx[] = array(
				"DOMAIN_TEXT"	=>	$row['domain_text'],
			);
		}
	}
	
	require_once('Smarty/Smarty.class.php');
	$tpl = new Smarty();
	$tpl->caching = false;
	$tpl->setCompileDir('/tmp/templates_c/');
	$tpl->setTemplateDir(
		array(
			'EasySCP' => '/etc/easyscp/'
		)
	);
	$template->assign(
		array(
			"A"	=>	$dns_a,
			"NS"	=>	$dns_ns,
			"MX"	=>	$dns_mx,
			"DOMAIN_NAME"	=>	$Input,
		)
	);
	
	$sql_query = "
	    SELECT
			*
	    FROM
			`domain` `d`
		INNER JOIN
			`admin` `a` 
		ON (`d`.`domain_admin_id`=`a`.`admin_id`)
	    WHERE
			domain_name = :domain_name
	";
	
	$result = DB::prepare($sql_query);
	$result->bindValue(":domain_name", $Input);
	$row = $result->fetch();
	
	$template->assign(
		array(
			"ZONE_MASTER"	=>	$row['email'],
		)
	);
	
	$config = $tpl->fetch("bind/parts/zone.tpl");
	file_put_contents($cfg->BIND_DB_DIR.'/'.$Input.'.zone', $config);
	
	$template->assign(
		array(
			"DB_DIR"	=>	$cfg->BIND_DB_DIR,
		)
	);
	
	$dns_config = array();
	
	while ($row = $domainresult->fetch())
	{
		$dns_config[] = array(
			"DOMAIN_NAME"	=>	$row['domain_name'],
		);	
	}	

	$template->assign("DNS_Config", $dns_config);
	$config = $tpl->fetch("bind/parts/config.tpl");
	file_put_contents($cfg->BIND_DB_DIR.'/zones.conf', $config);
	System_Daemon::info('Alles Ok');

	return true;
}
?>