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
 * Class TemplateEngine is the new EasySCP template engine.
 *
 * @category	EasySCP
 * @package		EasySCP_TemplateEngine
 * @copyright 	2010-2012 by EasySCP | http://www.easyscp.net
 * @author 		EasySCP Team
 */
class EasySCP_TemplateEngine {

	protected static $_instance = null;
	private $template_engine;

	/**
	 * Constructor
	 */
	protected function __construct() {
		require('Smarty/Smarty.class.php');
		$this->template_engine = new Smarty();
		$this->template_engine->caching = false;

		$this->set_globals();
	}

	/**
	 * Get an EasySCP_TemplateEngine instance
	 *
	 * Returns an {@link EasySCP_TemplateEngine} instance, only creating it if it
	 * doesn't already exist.
	 *
	 * @return EasySCP_TemplateEngine An EasySCP_TemplateEngine instance
	 */
	public static function getInstance() {

		if(is_null(self::$_instance)) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Append data to the template for loop parsing
	 *
	 * @param String $nsp_name
	 * @param String $nsp_data
	 */

	public function append($nsp_name, $nsp_data = '') {
		if (gettype($nsp_name) == "array") {
			$this->template_engine->append($nsp_name);
		} else {
			$this->template_engine->append($nsp_name, $nsp_data);
		}
	}

		/**
	 * Assign data to the template for parsing
	 *
	 * @param String $nsp_name
	 * @param String $nsp_data
	 */

	public function assign($nsp_name, $nsp_data = '') {
		if (gettype($nsp_name) == "array") {
			$this->template_engine->assign($nsp_name);
		} else {
			$this->template_engine->assign($nsp_name, $nsp_data);
		}
	}

	/**
	 * Parse data and displays the template $template
	 *
	 * @param String $template
	 */

	public function display($template) {
		// un-comment the following line to show the debug console
		// $this->template_engine->debugging = true;
		$this->template_engine->display($template);
	}

	/**
	 * Returns the EasySCP_TemplateEngine template dir
	 *
	 * @return String template_dir the current EasySCP_TemplateEngine template dir
	 */
	public function get_template_dir() {
		return $this->template_engine->getTemplateDir('EasySCP');
	}

	/**
	 * Sets the EasySCP_TemplateEngine template dir
	 *
	 * @param String $dir The new EasySCP_TemplateEngine template dir
	 */
	public function set_template_dir($dir) {
		$this->template_engine->setTemplateDir($dir);
	}

	/**
	 * Sets global variables for using in all templates
	 */
	private function set_globals() {
		$cfg = EasySCP_Registry::get('Config');

		// get current Language
		if (isset($cfg->USER_SELECTED_LANG) && $cfg->USER_SELECTED_LANG != ''){
			$setLocale = $cfg->USER_SELECTED_LANG . '.UTF8';
		} elseif (isset($cfg->USER_INITIAL_LANG) && $cfg->USER_INITIAL_LANG != ''){
			$setLocale = $cfg->USER_INITIAL_LANG . '.UTF8';
		} else {
			$setLocale = 'en_GB.UTF8';
		}

		// Set language for translation
		setlocale(LC_MESSAGES, $setLocale);

		$gui_root = $cfg->GUI_ROOT_DIR.'/';
		if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != ''){
			$TemplateDir = $gui_root . 'themes/' . $cfg->USER_INITIAL_THEME;
			$CompileDir = $gui_root . 'themes/' . $cfg->USER_INITIAL_THEME . '/templates_c/';
			$THEME_COLOR_PATH = "themes/{$cfg->USER_INITIAL_THEME}";
			if ($cfg->DEBUG) {
				$this->assign('DEBUG', true);
			}
		} else {
			$TemplateDir = $gui_root . $cfg->LOGIN_TEMPLATE_PATH;
			$CompileDir = $gui_root . $cfg->LOGIN_TEMPLATE_PATH . '/templates_c/';
			$THEME_COLOR_PATH = $cfg->LOGIN_TEMPLATE_PATH;
		}
		$this->template_engine->setTemplateDir(array('EasySCP' => $TemplateDir));
		$this->template_engine->setCompileDir($CompileDir);
		$this->assign(
			array(
				'THEME_CHARSET'		=> tr('encoding'),
				'THEME_COLOR_PATH'	=> '/' . $THEME_COLOR_PATH,
				'THEME_SCRIPT_PATH'	=> '/themes/scripts'
			)
		);
	}
}
?>