<VirtualHost {$DOMAIN_IP}:80>

    ServerAdmin     webmaster@{$DOMAIN_NAME}
    DocumentRoot    {$WWW_DIR}/{$DOMAIN_NAME}/htdocs

    ServerName      {$DOMAIN_NAME}
	
	RewriteEngine On
    {literal}
	RewriteCond %{SERVER_PORT} 80
	{/literal}
    RewriteRule ^(.*)$ https://{$DOMAIN_NAME}$1 [R,L]

</VirtualHost>
