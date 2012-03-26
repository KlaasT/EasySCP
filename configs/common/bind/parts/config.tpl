{foreach item=c from=$DNS_Config}
zone "{$c.DOMAIN_NAME}" {
	type	master;
	file	"{$DB_DIR}/{$c.DOMAIN_NAME}.zone";
	notify	YES;
};
{/foreach}