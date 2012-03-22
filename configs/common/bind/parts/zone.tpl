$TTL 12H
$ORIGIN {$DOMAIN_NAME}.
@               IN              SOA             {$PRIMARY_NS}. {$ZONE_MASTER}. (
                {$DOMAIN_SERIAL}     ; Serial
                8H              ; Refresh
                15M             ; Retry
                4W              ; Expire
                3H              ; Minimum TTL
)

{foreach item=ns from=$NS}
				IN				NS				{$ns.dns_hostname}.
{/foreach}
{foreach item=mx from=$MX}
                IN              MX      {$mx.PRIORITY}      {$MX.dns_hostname}.
{/foreach}

{foreach item=a from=$A}
{$a.DOMAIN_DNS}		IN		{$a.DOMAIN_TYPE}		{$a.DOMAIN_TEXT}
{/foreach}
