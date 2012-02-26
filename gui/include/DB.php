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
 * Benötigte Klasse für die Konfiguration laden
 *
 */
require_once('/etc/easyscp/easyscp_config_db.php');


/**
 * Statische Klasse für Datenbankzugriffe.
 *
 */
class DB extends DB_Config {
	private static $connectid;
	private static $stmt;

	private static $abfragen = 0;
	private static $zeit = 0;

	/**
	 * Liefert die aktuelle Instanz der Datenbank verbindung zurück.
	 * Falls noch keine Datenbank verbindung besteht wird diese aufgebaut und dann die Instanz zurückgegeben.
	 * Zusätzlich werden einige Optionen für die Datenbank verbindung gesetzt:
	 * ATTR_PERSISTENT = Die Datenbank verbindung bleibt solange bestehen bis das Script sich beendet hat.
	 */
	static public function getInstance(){
		if(!self::$connectid){
			try {
				self::$connectid = new PDO(
					'mysql:host='.self::$DB_HOST.';dbname='.self::$DB_DATABASE.';port='.self::$DB_PORT,
					self::$DB_USER,
					self::decrypt_data(self::$DB_PASSWORD),
					array(
						PDO::ATTR_PERSISTENT => true,
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
					)
				);
			}
			catch(PDOException $e){
				echo $e->getMessage();
				echo '<br />';
				echo var_dump($e);
				// echo PDO::errorCode();
				// echo PDO::errorInfo();
			}
		}
		return self::$connectid;
	}

	/**
	 * Vorbereiten eines SQL Query.
	 * 
	 * @param string $sql_query Der Query den man vorbereiten möchte (Prepared Statement).
	 * @return Liefert das Objekt eines vorbereiteten (prepared) SQL Query zurück. Das Objekt wird für spezielle PDO Funktionen benötigt (z.b. "bindValue").
	 */
	static public function prepare($sql_query){
		self::$stmt = self::getInstance()->prepare($sql_query);
		return self::$stmt;
	}

	/**
	 * Ausführen des vorbereiteten SQL Query mit optionalen Parametern/Daten.
	 *
	 * @param array $sql_param Eine Array von Parametern/Daten für den Query.
	 * @param bool $single Gibt an ob bei erfolg nur der erste Datensatz zurückgeliefert werden soll.
	 * @return Wenn single = true wird der erste Datensatz zurückgegeben, ansonsten das Objekt für die Datenbank abfrage welches dann per fetch()/fetch_all() abgerufen werden kann.
	 *
	 * Die Anzahl der zurückgegebenen Zeilen kann mittels "Object->rowCount()" ermittelt werden.
	 */
	static public function execute($sql_param, $single = false){
		try {
			self::$stmt->execute($sql_param);
			if ($single){
				return(self::$stmt->fetch());
			} else {
				return(self::$stmt);
			}
		}
			
		catch(PDOException $e) {
			echo $e->getMessage();
			// echo PDO::errorCode();
			// echo PDO::errorInfo();
		}
		return false;
	}

	/**
	 * Direktes ausführen eines SQL Query ohne optionale Parametern/Daten.
	 * 
	 * @param string $sql_query Der Query den man ausführen möchte.
	 * @param bool $single Gibt an ob bei erfolg nur der erste Datensatz zurückgeliefert werden soll.
	 * @return Wenn single = true wird der erste Datensatz zurückgegeben, ansonsten das Objekt für die Datenbank abfrage welche dann per fetch()/fetch_all()/foreach abgerufen werden kann.
	 *
	 * Die Anzahl der zurückgegebenen Zeilen kann mittels "Object->rowCount()" ermittelt werden.
	 */
	static public function query($sql_query, $single = false){
		try {
			$timer_start = microtime();
			self::$stmt = self::getInstance()->query($sql_query);
			self::$abfragen ++;
			self::$zeit += microtime_diff($timer_start,microtime());
			if ($single){
				return(self::$stmt->fetch());
			} else {
				return(self::$stmt);
			}
		}
			
		catch(PDOException $e) {
			echo $e->getMessage();
		}
		return false;
	}

	/**
	 * Vorbereiten und Ausführen eines SQL Query mit optionalen Parametern/Daten.
	 * 
	 * @param string $sql_query Der Query den man vorbereiten möchte (Prepared Statement).
	 * @param array $sql_param Eine Array von Parametern/Daten für den Query.
	 * @param bool $single Gibt an ob bei erfolg nur der erste Datensatz zurückgeliefert werden soll.
	 * @return Wenn single = true wird der erste Datensatz zurückgegeben, ansonsten das Objekt für die Datenbank abfrage welches dann per fetch()/fetch_all() abgerufen werden kann.
	 */
	static public function query_new($sql_query, $sql_param, $single = false){
		try {
			$timer_start = microtime();
			self::$stmt = self::getInstance()->prepare($sql_query);
			self::$stmt->execute($sql_param);
			self::$abfragen ++;
			self::$zeit += microtime_diff($timer_start,microtime());
			if ($single){
				//return(self::$stmt->fetch(PDO::FETCH_ASSOC));
				return(self::$stmt->fetch());
			} else {
				return(self::$stmt);
			}
		}

		catch(PDOException $e) {
			echo $e->getMessage();
		}
		return false;
	}

	/**
 	 * Anzahl der DB zugriffe
 	 *
 	 * @return Gibt die Summe der bisher stattgefundenen Datenbank zugriffe zurück
 	 */
	static public function Abfragen(){
		return self::$abfragen;
	}

	/**
 	 * Zeit der DB Abfragen
 	 *
 	 * @return Gibt die Zeit in ms zurück, welche für die bisher stattgefundenen Datenbank zugriffe benötigt wurde
 	 */
	static public function Zeit(){
		return self::$zeit;
	}

	/**
	 * Decrypt data
	 *
	 * @param string $data Encrypted data
	 * @return string Decrypted data
	 */
	static public function decrypt_data($data) {

		if ($data == '') {
			return '';
		}

		if (extension_loaded('mcrypt')) {

			$text = @base64_decode($data);
			$td = @mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');

			// Initialize encryption
			@mcrypt_generic_init($td, self::$DB_KEY, self::$DB_IV);

			// Decrypt encrypted string
			$decrypted = @mdecrypt_generic($td, $text);
			@mcrypt_module_close($td);

			// Show string
			return trim($decrypted);
		} else {
			echo 'Error: PHP extension "mcrypt" not loaded!';
		}
	}

	/**
	 * Encrypt data
	 *
	 * @param string $data Data for encryption
	 * @return string Encrypted data
	 */
	static public function encrypt_data($data) {
		if (extension_loaded('mcrypt')) {
			$td = @mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');

			// Initialize encryption
			@mcrypt_generic_init($td, self::$DB_KEY, self::$DB_IV);

			// Encrypt string
			$encrypted = @mcrypt_generic($td, $data);
			@mcrypt_generic_deinit($td);
			@mcrypt_module_close($td);

			$text = @base64_encode($encrypted);

			// Show encrypted string
			return trim($text);
			// return trim($encrypted);
		} else {
			echo 'ERROR: PHP extension "mcrypt" not loaded!';
		}
	}
}
?>