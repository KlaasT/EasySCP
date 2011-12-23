{$PURCHASE_HEADER}

{if isset($MESSAGE)}
<div class="{$MSG_TYPE}" style="width: 400px;">{$MESSAGE}</div>
{/if}

<form name="addon" method="post" action="addon.php">
	<table width="400">
		<tr>
			<td colspan="2" class="content3"><strong>{$DOMAIN_ADDON}</strong></td>
		</tr>

		<tr>
			<td class="content2">{$TR_DOMAIN_NAME}</td>
			<td class="content">www.
				<input name="domainname" type="text" class="textinput" style="width:210px" />
				<br />
				<small>{$TR_EXAMPLE}</small>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr align="right">
			<td colspan="2"><input name="Submit" type="submit" class="button" value="  {$TR_CONTINUE}  " /></td>
		</tr>
	</table>
</form>
<br />

{$PURCHASE_FOOTER}