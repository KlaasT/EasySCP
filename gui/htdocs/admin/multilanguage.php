<?php
/**
 * EasySCP a Virtual Hosting Control Panel
 * Copyright (C) 2010-2011 by Easy Server Control Panel - http://www.easyscp.net
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

require '../../include/easyscp-lib.php';

check_login(__FILE__);

$cfg = EasySCP_Registry::get('Config');

$tpl = EasySCP_TemplateEngine::getInstance();
$template = 'admin/multilanguage.tpl';

if (isset($_POST['uaction']) && $_POST['uaction'] == 'upload_language') {
	importLanguageFile();
}

showLang($tpl);

// static page messages
$tpl->assign(
	array(
		'TR_PAGE_TITLE'				=> tr('EasySCP - Admin/Internationalisation'),
		'TR_MULTILANGUAGE'			=> tr('Internationalisation'),
		'TR_INSTALLED_LANGUAGES'	=> tr('Installed languages'),
		'TR_LANGUAGE'				=> tr('Language'),
		'TR_MESSAGES'				=> tr('Messages'),
		'TR_LANG_REV'				=> tr('Date'),
		'TR_DEFAULT'				=> tr('Panel Default'),
		'TR_ACTION'					=> tr('Action'),
		'TR_SAVE'					=> tr('Save'),
		'TR_INSTALL_NEW_LANGUAGE'	=> tr('Install new language'),
		'TR_LANGUAGE_FILE'			=> tr('Language file'),
		'TR_INSTALL'				=> tr('Install'),
		'TR_EXPORT'					=> tr('Export'),
		'TR_MESSAGE_DELETE'			=> tr('Are you sure you want to delete %s?', true, '%s')
	)
);

gen_admin_mainmenu($tpl, 'admin/main_menu_settings.tpl');
gen_admin_menu($tpl, 'admin/menu_settings.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/**
 * Prepares page data to show available languages
 *
 * @param  EasySCP_TemplateEngine $tpl an EasySCP_TemplateEngine instance
 * @return void
 */
function showLang($tpl) {

	/**
	 * @var $cfg EasySCP_Config_Handler_File
	 */
	$cfg = EasySCP_Registry::get('Config');

	/**
	 * @var $sql EasySCP_Database
	 */
	$sql = EasySCP_Registry::get('Db');

	$tables = $sql->metaTables();

	$nlang = count($tables);

	list($user_def_lang) = get_user_gui_props($sql, $_SESSION['user_id']);

	$usr_def_lng = explode('_', $user_def_lang);

	for ($i = 0; $i < $nlang; $i++) {
		$data = $tables[$i];
		$pos = strpos($data, 'lang_');

		if ($pos === false) {
			// not found... ... next :)
			continue;
		}

		$dat = explode('_', $data);

		/**
		 * @var $stmt EasySCP_Database_ResultSet
		 */
		$stmt = array();

		foreach(array(
			'easyscp_language', 'easyscp_languageSetlocaleValue',
			'easyscp_languageRevision') as $msgstr) {

			$stmt[] = exec_query(
				$sql, "SELECT `msgstr` FROM `{$tables[$i]}` WHERE `msgid` = '$msgstr'
			");
		}

		if ($stmt[0]->recordCount() == 0 || $stmt[1]->recordCount() == 0) {
			$language_name = tr('Unknown');
		} else {
			$tr_langcode = tr($stmt[1]->fields['msgstr']);

			if ($stmt[1]->fields['msgstr'] == $tr_langcode) {
				// no translation found
				$language_name = $stmt[0]->fields['msgstr'];
			} else {
				$language_name = $tr_langcode;
			}
		}

		if ($stmt[2]->recordCount() !== 0 && $stmt[2]->fields['msgstr'] != '' &&
			class_exists('DateTime')) {

			$tmp_lang = new DateTime($stmt[2]->fields['msgstr']);
			$language_revision = $tmp_lang->format('Y-m-d H:i');

			unset($tmp_lang);
		} else {
			$language_revision = tr('Unknown');
		}

		if ($cfg->USER_INITIAL_LANG == "lang_{$dat[1]}" ||
			$usr_def_lng[1] == $dat[1]) {

			$tpl->append(
				array(
					'TR_UNINSTALL'		=> false,
					'URL_DELETE'		=> '#',
					'LANGUAGE'			=> tohtml($language_name),
					'LANGUAGE_REVISION'	=> $language_revision
				)
			);

		} else {
			$tpl->append(
				array(
					'TR_UNINSTALL'		=> tr('Uninstall'),
					'URL_DELETE'		=> "language_delete.php?delete_lang=lang_{$dat[1]}",
					'LANGUAGE'			=> tohtml($language_name),
					'LANGUAGE_REVISION'	=> $language_revision
				)
			);

		}

		// Retrieving number of translated messages
		$query = "SELECT COUNT(`msgid`) AS `cnt` FROM `{$tables[$i]}`;";

		$stmt = exec_query($sql, $query);

		$tpl->append(
			array(
				'MESSAGES'		=> tr('%d messages translated', $stmt->fields['cnt'] - 5),
				'URL_EXPORT'	=> "multilanguage_export.php?export_lang=lang_{$dat[1]}",
				'INDEX'			=> $i,
				'TR_GZIPPED'	=> tr('Gzipped')
			)
		);

	}
} // end showLang()

/*******************************************************************************
 * Importation functions
 */

/**
 * Import all translation string from a language file
 *
 */
function importLanguageFile() {

	// Add new language
	$file_type = $_FILES['lang_file']['type'];
	$file = $_FILES['lang_file']['tmp_name'];

	if (empty($_FILES['lang_file']['name']) || !is_readable($file)) {
		set_page_message(
			tr('Upload file error!'),
			'error'
		);
		return;
	}

	if ($file_type != 'text/plain' && $file_type != 'application/octet-stream'
		&& $file_type != 'text/x-gettext-translation') {

		set_page_message(
			tr('You can upload only text files!'),
			'error'
		);
		return;
	} else {
		$n = stripos($_FILES['lang_file']['name'], '.po');
		if ($n !== false) {
			$ab = _importGettextFile($file, $_FILES['lang_file']['name']);
		} else {
			$ab = _importTextFile($file);
		}

		if (is_int($ab)) {
			if ($ab == 1) {
				set_page_message(
					tr('Could not read language file!'),
					'error'
				);
				return;
			} elseif ($ab == 2) {
				set_page_message(
					tr('Uploaded file is not a valid language file!'),
					'error'
				);
				return;
			}
		}

		if (empty($ab['easyscp_languageSetlocaleValue']) ||
			empty($ab['easyscp_table']) || empty($ab['easyscp_language']) ||
			!preg_match(
				'/^[a-z]{2}(_[A-Z]{2}){0,1}$/Di',
				$ab['easyscp_languageSetlocaleValue']
			) || !preg_match('/^[a-z0-9()]+$/Di', $ab['easyscp_table'])) {

			set_page_message(
				tr('Uploaded file does not contain language information!'),
				'error'
			);
			return;
		}

		$sql = EasySCP_Registry::get('Db');

		$lang_table = 'lang_' . $ab['easyscp_table'];
		$lang_update = false;

		for ($i = 0, $tables = $sql->metaTables(), $nlang = count($tables) ;
			$i < $nlang; $i++) {

			if ($lang_table == $tables[$i]) {
				$lang_update = true;
				break;
			}
		}

		if ($lang_update) {
			execute_query($sql, "DROP TABLE IF EXISTS `$lang_table`;");
		}

		$query = "
			CREATE TABLE `$lang_table` (
				`msgid` text collate utf8_unicode_ci,
				`msgstr` text collate utf8_unicode_ci,
				KEY `msgid` (msgid(25))
			) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";

		execute_query($sql, $query);

		foreach ($ab as $msgid => $msgstr) {
			$query = "
				INSERT INTO `$lang_table` (
					`msgid`, `msgstr`
				) VALUES (?, ?);
			";

			exec_query(
				$sql, $query, str_replace("\\n", "\n", array($msgid, $msgstr))
			);
		}

		if (!$lang_update) {
			write_log(
				tr(
					'%s added new language: %s', $_SESSION['user_logged'],
					$ab['easyscp_language']
				)
			);

			set_page_message(
				tr('New language installed!'),
				'success'
			);
		} else {
			write_log(
				tr(
					'%s updated language: %s', $_SESSION['user_logged'],
					$ab['easyscp_language']
				)
			);
			set_page_message(
				tr('Language was updated!'),
				'success'
			);
		}
	}
}

/**
 * Import traditional ispCP  translation file format
 *
 * @param string $file translation file
 * @return array|int
 */
function _importTextFile($file) {

    if(!($fp= fopen($file, 'r'))) return 1;

    $ab = array(
        'easyscp_languageRevision' => '',
        'easyscp_languageSetlocaleValue' => '',
        'easyscp_table' => '',
        'easyscp_language' => ''
    );

    $errors = 0;

    while (!feof($fp) && $errors <= 3) {
        $t = fgets($fp);

        $t = explode(' = ', $t);

        if (count($t) != 1) {
            $ab[$t[0]] = rtrim($t[1]);
        } else {
            $errors++;
        }
    }

    fclose($fp);

    if ($errors > 3) {
        return 2;
    }

    return $ab;
}

/**
 * Import all translation string from a PO file
 *
 * @param  string $file
 * @param  string $filename
 * @return mixed Array that contain all translation string or int on failure
 */
function _importGettextFile($file, $filename) {

    $lines = file($file);

    if (empty($lines)) return 1;

    $ab = array(
        'easyscp_languageRevision' => '',
        'easyscp_languageSetlocaleValue' => '',
        'easyscp_table' => '',
        'easyscp_language' => ''
    );

    $content = '';

    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line)) {
            $c = mb_substr($line, 0, 1);
            if ($c != '#') {
                $content .= $line."\n";
            }
        }
    }

    $content = str_replace('\\\\n', '\n', $content);

    // Parse all messages
    $offset = mb_strpos($content, 'msgid "');
    while ($offset !== false) {

        $offset1 = $offset+6;
        $offset = mb_strpos($content, 'msgstr "', $offset1);

        $s1 = mb_substr($content, $offset1+1, $offset-$offset1-3);
        $offset2 = $offset+7;

        $offset = mb_strpos($content, 'msgid "', $offset2);
        if ($offset !== false) {
            $s2 = mb_substr($content, $offset2+1, $offset-$offset2-3);
            $ab[_decodePoFileString($s1)] = _decodePoFileString($s2);
        }
    }

    // set language
    if (isset($ab['_: Localised language'])) {
        $ab['easyscp_language'] = $ab['_: Localised language'];
        unset($ab['_: Localised language']);
    } else {
        return 2;
    }

    // Parse some relevant header information
	if (isset($ab[''])) {
		$ameta = array();

		$header = explode("\n", $ab['']);

		foreach ($header as $hline) {
            $n = strpos($hline, ':');
            if ($n !== false) {
                $key = substr($hline, 0, $n);
                $ameta[$key] = trim(substr($hline, $n+1));
            }
        }

		# Retrieving language translation team
        if (isset($ameta['Language-Team'])) {
            $s = $ameta['Language-Team'];
            $n = strpos($s, '<');

            if ($n !== false) {
                $ab['easyscp_table'] = str_replace(array(' ', '(', ')'), '', mb_substr($s, 0, $n));
            }
        }

        // Getting easyscp_language Revision by PO-Revision-Date
        if (isset($ameta['PO-Revision-Date'])) {
            // trim timezone
            $n = strpos($ameta['PO-Revision-Date'], '+');
            if ($n !== false) {
                $ameta['PO-Revision-Date'] = substr($ameta['PO-Revision-Date'], 0, $n);
            }


            // currently some problems with hour/minute parsing?!
            $time = getdate(strtotime($ameta['PO-Revision-Date']));

            $ab['easyscp_languageRevision'] = sprintf(
                '%04d%02d%02d%02d%02d%02d',
                $time['year'],
                $time['mon'],
                $time['mday'],
                $time['hours'],
                $time['minutes'],
                $time['seconds']
            );
        } else {
            $ab['easyscp_languageRevision'] = strftime('%Y%m%d%H%I%S');
        }

        // get locale from file name
        $ab['easyscp_languageSetlocaleValue'] = basename($filename, '.po');

        unset($ab['']);
    } else {
        return 2;
    }

    // set default encoding to UTF-8 if not present
    if (!isset($ab['encoding'])) {
        $ab['encoding'] = 'UTF-8';
    }

    return $ab;
}


/**
 * Remove leading and trailing quotes, un-escape linefeed, cr, tab and quotes
 *
 * @param string $s
 * @return string Normalized string
 */
function _decodePoFileString($s) {

    // TODO: TEST
    $n = strpos($s, '\\');

    $result = str_replace(
        array('\\n', '\\r', '\\t', '\"'), array("\n", "\r", "\t", '"'),
	    preg_replace('/"\s+"/', '', $s)
    );

    if ($n !== false) {
        //var_dump($s);
        //var_dump($result);
    }

    return $result;
}
?>