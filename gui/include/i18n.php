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
 * Configure base settings for gettetxt
 */

$cfg = EasySCP_Registry::get('Config');

// get current Language
$setLocale = $cfg->USER_INITIAL_LANG . '.UTF8';

// Set language to current Language
setlocale(LC_MESSAGES, $setLocale);

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
 * Set and return the current language
 *
 * @param string|null $newlang New language to be used or NULL to use existing
 * @param boolean $force If TRUE, $newlang will be forced
 * @return string Current language
 */
function curlang($newlang = null, $force = false) {

	$cfg = EasySCP_Registry::get('Config');
	static $language = null;

	// We store old value so if $language is changed old value is returned
	$_language = $language;

	// Forcibly set $language to $newlang (use with CARE!)
	if ($force) {
		$language = $newlang;
		return $_language;
	}

	if (is_null($language) || (!is_null($newlang) && $newlang !== false)) {

		if ($newlang === true || ((is_null($newlang) || $newlang === false) &&
			is_null($language))) {

			$newlang = (isset($_SESSION['user_def_lang']))
				? $_SESSION['user_def_lang'] : $cfg->USER_INITIAL_LANG;
		}

		if ($newlang !== false) {
			$language = $newlang;
		}
	}

	return (!is_null($_language)) ? $_language : $language;
}

/**
 * Translates a given string into the selected language, if exists
 *
 * @param string $msgid string to translate
 * @param mixed $substitution Prevent the returned string from being replaced with html entities
 * @return Translated or original string
 */
function tr($msgid, $substitution = false) {

	$msgstr = gettext($msgid);

	return $msgstr;

	// Print a test message
	// echo gettext("Update checking is disabled!");
	// echo '<br />';

	// Or use the alias _() for gettext()
	// echo _("Have a nice day");

	/*
	// Detect whether $substitution is really $substitution or just a value to
	// be replaced in $msgstr
	if (!is_bool($substitution)) {
		$substitution = false;
	}

	$setLocale = 'de_DE';
	$encoding = 'UTF-8';

	// $setLocale = curlang();
	// Set language to German
	putenv('LC_ALL='.$setLocale);
	setlocale(LC_ALL, $setLocale);

	// Specify location of translation tables
	// bindtextdomain("myPHPApp", "./locale");
	//bindtextdomain("EasySCP", "./locale");
	// bindtextdomain($setLocale, "./locale");
	// bindtextdomain($setLocale, $cfg->GUI_ROOT_DIR.'/locale');
	bindtextdomain("EasySCP", $cfg->GUI_ROOT_DIR.'/locale');

	echo $cfg->GUI_ROOT_DIR.'/locale';

	// Choose domain
	// textdomain("myPHPApp");
	textdomain("EasySCP");
	// textdomain($setLocale);

	$msgstr = gettext($msgid);

	if ($msgid == 'encoding' && $msgstr == 'encoding') {
		$msgstr = $encoding;
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

	*/

	/*
	static $cache = array();
	static $stmt = null;

	// Detect whether $substitution is really $substitution or just a value to
	// be replaced in $msgstr
	if (!is_bool($substitution)) {
		$substitution = false;
	}

	$lang = curlang();

	$encoding = 'UTF-8';

	if (isset($cache[$lang][$msgid])) {
		$msgstr = $cache[$lang][$msgid];
	} else {

		$msgstr = $msgid;

		if (!$substitution) {
			// $substitution is true in this call because we need it that way
			// and to prevent an infinite loop
			$encoding = tr('encoding', true);
		}

		// Prepare the query only once to improve performances
		if(is_null($stmt)) {
			$query = "
				SELECT
					`msgstr`
				FROM
					`$lang`
				WHERE
					`msgid` = :msgid
				;
			";

			$stmt = EasySCP_Registry::get('Pdo')->prepare($query);
		}

		// Execute the query
		$stmt->execute(array(':msgid' => $msgid ));

		$rs = $stmt->fetch(PDO::FETCH_ASSOC);

		if($rs){
			if ($rs['msgstr'] != ''){
				$msgstr = $rs['msgstr'];
			} else {
				$msgstr = $msgid;
			}

		}

	}

	if ($msgid == 'encoding' && $msgstr == 'encoding') {
		$msgstr = $encoding;
	}

	// Detect comments and strip them if $msgid == $msgstr
	// e.g. tr('_: This is just a comment\nReal message to translate here')
	if ( substr($msgid, 0, 3) == '_: ' &&  $msgid == $msgstr &&
			count($l = explode("\n", $msgid)) > 1) {
		unset($l[0]);
		$msgstr = implode("\n", $l);
	}

	$cache[$lang][$msgid] = $msgstr;

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
	*/
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