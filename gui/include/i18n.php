<?php
/**
 * EasySCP a Virtual Hosting Control Panel
 * Copyright (C) 2010-2012 by Easy Server Control Panel - http://www.easyscp.net
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @link 		http://www.easyscp.net
 * @author 		EasySCP Team
 */

/**
 * Configure base settings for translations
 */

$cfg = EasySCP_Registry::get('Config');

// Specify location of translation tables
bindtextdomain("EasySCP", $cfg->GUI_ROOT_DIR."/locale");
bind_textdomain_codeset("EasySCP", 'UTF-8');

// Choose domain
textdomain("EasySCP");

/**
 * false: don't set (not even auto),
 * null: set if missing,
 * true: force update from session/default, anything else: set it as a language
 */

/**
 * Translates a given string into the selected language, if exists
 *
 * @param string $msgid string to translate
 * @param mixed $substitution Prevent the returned string from being replaced with html entities
 * @return Translated or original string
 */
function tr($msgid, $substitution = false) {

	$encoding = 'UTF-8';

	$msgstr = gettext($msgid);

	if ($msgid == 'encoding' && $msgstr == 'encoding') {
		$msgstr = 'UTF-8';
	}

	// Detect comments and strip them if $msgid == $msgstr
	// e.g. tr('_: This is just a comment\nReal message to translate here')
	if ( substr($msgid, 0, 3) == '_: ' &&  $msgid == $msgstr &&
			count($l = explode("\n", $msgid)) > 1) {
		unset($l[0]);
		$msgstr = implode("\n", $l);
	}

	// Replace values
	if (func_num_args() > 1) {
		$argv = func_get_args();
		unset($argv[0]); //msgid

		if (is_bool($argv[1])) {
			unset($argv[1]);
		}

		$msgstr = vsprintf($msgstr, $argv);
	}

	if (!$substitution) {
		$msgstr = replace_html(htmlentities($msgstr, ENT_COMPAT, $encoding));
	}

	return $msgstr;
}

/**
 * Gets the available languages in the system
 *
 * @return Array of available languages
 */
function getLanguages() {
	$languages = array(
		'de_DE' => 'Deutsch (Deutschland) - German (Germany)',
		'en_GB' => 'English - English',
		'fr_FR'	=> 'Français (France) - French (France)'
	);
	return $languages;
}

/**
 * Creates a list of all current installed languages
 */
function gen_def_language() {

	$cfg = EasySCP_Registry::get('Config');
	$tpl = EasySCP_TemplateEngine::getInstance();

	$languages = getLanguages();

	foreach ($languages as $lang => $language_name) {
		$tpl->append(
			array(
				'LANG_VALUE'	=> $lang,
				'LANG_SELECTED'	=> ($lang === $cfg->USER_INITIAL_LANG) ? $cfg->HTML_SELECTED : '',
				'LANG_NAME'		=> tohtml($language_name)
			)
		);
	}
}

/**
 * Replaces special encoded strings back to their original signs
 *
 * @param string $string String to replace chars
 * @return String with replaced chars
 */
function replace_html($string) {
	$pattern = array(
		'#&lt;[ ]*b[ ]*&gt;#i',
		'#&lt;[ ]*/[ ]*b[ ]*&gt;#i',
		'#&lt;[ ]*strong[ ]*&gt;#i',
		'#&lt;[ ]*/[ ]*strong[ ]*&gt;#i',
		'#&lt;[ ]*em[ ]*&gt;#i',
		'#&lt;[ ]*/[ ]*em[ ]*&gt;#i',
		'#&lt;[ ]*i[ ]*&gt;#i',
		'#&lt;[ ]*/[ ]*i[ ]*&gt;#i',
		'#&lt;[ ]*small[ ]*&gt;#i',
		'#&lt;[ ]*/[ ]*small[ ]*&gt;#i',
		'#&lt;[ ]*br[ ]*(/|)[ ]*&gt;#i'
	);

	$replacement = array(
		'<b>',
		'</b>',
		'<strong>',
		'</strong>',
		'<em>',
		'</em>',
		'<i>',
		'</i>',
		'<small>',
		'</small>',
		'<br />'
	);

	$string = preg_replace($pattern, $replacement, $string);

	return $string;
}
?>