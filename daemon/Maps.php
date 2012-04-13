<?php
	$ProcedureMap = array(
		'100'	=>	'DaemonCore',
		'110'	=>	'DaemonDomain',
		'120'	=>	'DaemonNameserver',
		'130'	=>	'DaemonMail',
		'140'	=>	'DaemonFTP',
		'150'	=>	'DaemonSQL',
		'160'	=>	'DaemonSystem'
	);

	$StatusMap = array(
		'100'	=>	'CORE',
		'110'	=>	'DOMAIN',
		'120'	=>	'DNS',
		'130'	=>	'MAIL',
		'140'	=>	'FTP',
		'150'	=>	'SQL',
		'160'	=>	'SYSTEM',
		'200'	=>	'OK',
		'201'	=>	'WAITING',
		'202'	=>	'AUTHENTICATED',
		'203'	=>	'AUTHREQUIRED',
		'204'	=>	'CLOSING',
		'404'	=>	'DOMAIN NOT FOUND',
		'500'	=>	'INTERNAL ERROR',
		'501'	=>	'DATABASE SERVER GONE'
	);
?>