<div class="main_menu">
	<ul class="icons">
		{if isset($CUSTOM_BUTTONS)}
			{section name=i loop=$BUTTON_NAME}
			<li><a href="{$BUTTON_LINK[i]}" {$BUTTON_TARGET[i]} title="{$BUTTON_NAME[i]}"><span class="{$BUTTON_ICON[i]} icon_link">&nbsp;</span></a></li>
			{/section}
		{/if}
	</ul>
</div>
