<VirtualHost {DMN_IP}:80>

    ServerAdmin     webmaster@{DMN_NAME}
    DocumentRoot    {WWW_DIR}/{DMN_NAME}/htdocs

    ServerName      {DMN_NAME}
	
	RewriteEngine On
    RewriteCond %{SERVER_PORT} 80
    RewriteRule ^(.*)$ https://{DMN_NAME}$1 [R,L]

</VirtualHost>
