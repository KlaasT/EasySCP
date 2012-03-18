<VirtualHost {$DOMAIN_IP}:443>

	<IfModule suexec_module>
		SuexecUserGroup {$DOMAIN_UID} {$DOMAIN_GID}
	</IfModule>

	ServerAdmin		webmaster@{$DOMAIN_NAME}
	DocumentRoot	{$WWW_DIR}/{$DOMAIN_NAME}/htdocs

	ServerName      {$DOMAIN_NAME}
	ServerAlias     www.{$DOMAIN_NAME} {$DOMAIN_NAME} {$DOMAIN_UID}.{$BASE_SERVER_VHOST}

	SSLEngine       On
	SSLCertificateFile {$SSL_CERT_DIR}/easyscp_{$DOMAIN_NAME}-cert.pem
	SSLCertificateKeyFile {$SSL_KEY_DIR}/easyscp_{$DOMAIN_NAME}-key.pem

	Alias /errors	{$WWW_DIR}/{$DOMAIN_NAME}/errors/

	RedirectMatch permanent ^/ftp[\/]?$		http://{$BASE_SERVER_VHOST}/ftp/
	RedirectMatch permanent ^/pma[\/]?$		http://{$BASE_SERVER_VHOST}/pma/
	RedirectMatch permanent ^/webmail[\/]?$	http://{$BASE_SERVER_VHOST}/webmail/
	RedirectMatch permanent ^/easyscp[\/]?$	http://{$BASE_SERVER_VHOST}/

	ErrorDocument 401 /errors/401.html
	ErrorDocument 403 /errors/403.html
	ErrorDocument 404 /errors/404.html
	ErrorDocument 500 /errors/500.html
	ErrorDocument 503 /errors/503.html

	<IfModule mod_cband.c>
		CBandUser {$DOMAIN_NAME}
	</IfModule>

{if isset($AWSTATS) && $AWSTATS == true }
	ProxyRequests Off

	<Proxy *>
		Order deny,allow
		Allow from all
	</Proxy>

	ProxyPass			/stats  http://localhost/stats/{$DOMAIN_NAME}
	ProxyPassReverse	/stats  http://localhost/stats/{$DOMAIN_NAME}

	<Location /stats>
		<IfModule mod_rewrite.c>
			RewriteEngine on
			RewriteRule ^(.+)\?config=([^\?\&]+)(.*) $1\?config={$DOMAIN_NAME}&$3 [NC,L]
		</IfModule>
		AuthType Basic
		AuthName "Statistics for domain {$DOMAIN_NAME}"
		AuthUserFile /var/www/virtual/{$DOMAIN_NAME}/.htpasswd
		AuthGroupFile /var/www/virtual/{$DOMAIN_NAME}/.htgroup
		Require group statistics
	</Location>
{/if}

{if isset($DOMAIN_CGI) && $DOMAIN_CGI == true }
    ScriptAlias /cgi-bin/ /var/www/virtual/{$DOMAIN_NAME}/cgi-bin/
    <Directory /var/www/virtual/{$DOMAIN_NAME}/cgi-bin>
        AllowOverride AuthConfig
        #Options ExecCGI
        Order allow,deny
        Allow from all
    </Directory>
{/if}

	<Directory {$WWW_DIR}/{$DOMAIN_NAME}/htdocs>
		Options -Indexes Includes FollowSymLinks MultiViews
		AllowOverride All
		Order allow,deny
		Allow from all
	</Directory>

{if isset($DOMAIN_PHP) && $DOMAIN_PHP == true}
	<IfModule mod_fcgid.c>
		<Directory {$WWW_DIR}/{$DOMAIN_NAME}/htdocs>
			FCGIWrapper /var/www/fcgi/{$DOMAIN_NAME}/php5-fcgi-starter .php
			Options +ExecCGI
		</Directory>
		<Directory "/var/www/fcgi/{$DOMAIN_NAME}">
			AllowOverride None
			Options +ExecCGI MultiViews -Indexes
			Order allow,deny
			Allow from all
		</Directory>
	</IfModule>
{/if}

    Include {$CUSTOM_SITES_CONFIG_DIR}/{$DOMAIN_NAME}.conf

</VirtualHost>

<IfModule mod_cband.c>
    <CBandUser {$DOMAIN_NAME}>
         CBandUserLimit 1024Mi
         CBandUserScoreboard /var/www/scoreboards/{$DOMAIN_NAME}
         CBandUserPeriod 4W
         CBandUserPeriodSlice 1W
         CBandUserExceededURL http://login.st-city.net/errors/bw_exceeded.html
    </CBandUser>
</IfModule>
