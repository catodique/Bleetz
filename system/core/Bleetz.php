<?php defined('BLEETZ') OR die('No direct access allowed.');
/*------------------------------------------------------------------------------*
 |        Bleetz framework                  								 	|
 +------------------------------------------------------------------------------+
 |        Copyright (c) 2000                                                 	|
 |                  Bleetz Corporation.                                  		|
 |                  Carmelo Guarneri	(catodique@hotmail.com)   				|
 |                  Pascal Parole		( )            							|
 |					All rights reserved. 										|
 +------------------------------------------------------------------------------+
 * Note : Code is released under the GNU LGPL									*
 *
 * Please do not change the header of this file
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * See the GNU Lesser General Public License for more details.
 *------------------------------------------------------------------------------*/
/**
 * Fonctions et variables specifiques au framework bleetz
 *
 *        Copyright (c) 2000
 *                      Bleetz Corporation.
 *                      Carmelo Guarneri and Pascal Parole.
 *						All rights reserved.
 */


date_default_timezone_set("Europe/Berlin");

function utf8_encode_if($in_str) {
	$cur_encoding = mb_detect_encoding($in_str) ;
	if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8"))
		return $in_str;
	else
		return utf8_encode($in_str);
}

function utime () {
	$time = explode( " ", microtime());
	$usec = (double)$time[0];
	$sec = (double)$time[1];
	return $sec + $usec;
}

final class Bleetz {

	/*
	 * The singleton instance of the context
	 */
	public static $context;
	/*
	 * config variables for system
	 */
	public static $config=array();
	/*
	 * check the speed..
	 */
	public static $start_2000;
	
	// Internal caches and write status
	//private static $enableClassCache 	= false;
	//private static $require_cache 		= array();
	private static $save_require_cache 	= false;
	private static $language_cache 		= array();
	private static $save_language_cache = false;
	private static $internal_cache 		= array();

	public static function start() {
		
		Bleetz::$start_2000 = utime();
		
		$conffile=realpath(DOCROOT.config::$directories["SRVPATH"]."/Config.php");
		
		if ($conffile===false)
			die("No boot file, check your configuration");
		
		$conffile=preg_replace('/^\w:/','',str_replace('\\','/',$conffile));
		
		require $conffile;
		
		CFG::LoadDirectories();
		CFG::Load("Bleetz");
		
		//load libraries
		Bleetz::load_library("Controller.php");
		Bleetz::load_library("Context.php");
		
		//should be a service???
		Bleetz::load_library("Parser.php");
		Bleetz::load_library("View.php");	
		//Error service is always required...
		Bleetz::load_service("Error");
		
		//à définir dans les services des auto configs...
		Bleetz::load_services(CFG::$services);
		
		CFG::Set("BASE_URL", $_SERVER["SCRIPT_NAME"]."/");
		
		if (CLASS_CACHE===true) {
			if (file_exists("./require_cache")) {
				if (! ($file = fopen("./require_cache", "r")) ) {
					if (DODEBUG) {
						echo "Error opening file require_cache for reading";
						//exit;
					}
					Bleetz::enableClassCache(false);
				} else {
					$prg_text=fread($file, filesize("./require_cache"));
					Bleetz::$require_cache=unserialize($prg_text);
				}
	
			}
		}
		
		$context=new Context_core();
		
		//get the request infos
		/*
		 echo $_SERVER["QUERY_STRING"];
		$postdata = file_get_contents("php://input");
		echo $postdata;
		*/
		// Recuperation des donnees postÈes
		$sys_form_vars=array();
		if ($_GET) {
			$sys_form_vars=array_merge($sys_form_vars, $_GET);
			$post=true;
			//echo "get";
		};
		if ($_POST) {
			$sys_form_vars=array_merge($sys_form_vars, $_POST);
			$get=true;
			//echo "post";
		};
		
		$page_signature=uniqid();
		$sys_form_vars["page_signature_runtime"]=$page_signature;
		
		$context->sys_form_vars=$sys_form_vars;
		
		// get the path infos
		$context->client_path_info=@$_SERVER["PATH_INFO"];
		
		//que faire avec ça?
		if (!isset($sys_form_vars["redirect"])) $sys_form_vars["redirect"]=$context->client_path_info;
		
		//POUR l'instant... à revoir
		//$sys_form_vars["lang"]="fr_FR";
		//chercher la langue a appliquer dans la session
		//a mettre dans la config???
		//non
		define('LC_LANG_ACTIVE', "fr_FR");
		
		//Bleetz::$content appellé a disparaitre
		Bleetz::$context=$context;
		CT::$context=$context;
		
		return $context;
	}

	public static function end() {

		$end = utime();
		$runtime2 = $end - Bleetz::$start_2000;
		
		//echo "\n Completed in $runtime2 seconds<br> \n";
		
		if (CLASS_CACHE===true) {
		if (Bleetz::$save_require_cache) {
			//echo "save";
			$file = fopen("./require_cache","w");

			$text=serialize(Bleetz::$require_cache);

			fwrite($file, $text, strlen($text));
			fclose($file);
		}
		}
	}

	//a creer
	// $type : library, service, controller, card
	//public static function load($type,$file) {
		
	public static function load_library($file) {
		$filename=SYSPATH.$file;
		if (CLASS_CACHE===false) {
			require_once $filename;
			return;
		}
		if (empty(Bleetz::$require_cache[$filename])) {
			Bleetz::$save_require_cache=true;
			//load file
			if (!file_exists($filename)) { //404 not found
				ER::collect("file not found : <b>%s</b>", $filename);
				ER::report();
				return false;
			}
			if (! ($monProg = fopen($filename, "r")) ) {
				ER::collect("Error opening file <b>%s</b for reading", $filename);
				ER::report();
				exit;
			};
			$prg_text=fread($monProg, filesize($filename));
			Bleetz::$require_cache[$filename]=$prg_text;
			//echo $prg_text;
			//echo sizeof(Bleetz::$require_cache);
			eval ("?>".$prg_text);
		} else {
		//get back
			$prg_text=Bleetz::$require_cache[$filename];

			eval ("?>".$prg_text);
		}
	}
 
	
	public static function load_services($services_array) {
		foreach($services_array as $k => $name) {
			self::load_service($name);
		}
		
	}
	
	public static function load_service($name) {
		$filename=SRVPATH.$name.".php";
		if (CLASS_CACHE===false) {
			//if (require_once $filename) ;
			if (!(@include_once $filename)) {
				ER::collect("file not found : <b>%s</b>", $filename );
				echo ER::report();
				exit;
			}
			CFG::Load($name);
			return;
		}
		if (empty(Bleetz::$require_cache[$filename])) {
			Bleetz::$save_require_cache=true;
			//load file
			if (!file_exists($filename)) { //404 not found
				ER::collect("file not found : <b>%s</b>", $filename );
				ER::report();
				return false;
			}
			if (! ($monProg = fopen($filename, "r")) ) {
				ER::collect("Error opening file <b>%s</b> for reading", $filename);
				ER::report();
				exit;
			};
			$prg_text=fread($monProg, filesize($filename));
			Bleetz::$require_cache[$filename]=$prg_text;
			//echo $prg_text;
			//echo sizeof(Bleetz::$require_cache);
			eval ("?>".$prg_text);
			CFG::Load($name);
		} else {
		//get back
			$prg_text=Bleetz::$require_cache[$filename];
			eval ("?>".$prg_text);
			CFG::Load($name);
		}
	}

	public static function load_controller($controller) {
		
		$filename= CTLPATH.$controller.".php";
		//echo $filename,"<br/>";
		if (isset(Bleetz::$context->$controller)) return true;
		
		if (CLASS_CACHE===true and !empty(Bleetz::$require_cache[$filename])) {
			//get back
			$prg_text=Bleetz::$require_cache[$filename];
			eval ("?>".$prg_text);
			//check if the controller is well defined
			Bleetz::$context->$controller=$$controller;
			return true;
		}
		
		if (!file_exists($filename)) { //404 not found
			if (DODEBUG) {
			ER::collect("file not found : <b>%s</b>",$filename);
			}
			return false;
		}
		if (! ($monProg = fopen($filename, "r")) ) {
			if (DODEBUG) {
				ER::collect("Error opening file %s for reading",$filename);
			}
			return false;
		};
		
		if (CLASS_CACHE===false) {
			require_once $filename;
			Bleetz::$context->$controller=$$controller;
			return true;
		}
		//Bleetz::$enableClassCache===true and empty(Bleetz::$require_cache[$filename]
			Bleetz::$save_require_cache=true;
			//load file
			$prg_text=fread($monProg, filesize($filename));
			Bleetz::$require_cache[$filename]=$prg_text;
			//echo sizeof(Bleetz::$require_cache);
			eval ("?>".$prg_text);
			Bleetz::$context->$controller=$$controller;
			
			return true;
	}

	/**
	 * Fetch an i18n language item.
	 * transfert in Lang service LG:: (to create)
	 *
	 * @param   string  language key to fetch
	 * @param   array   additional information to insert into the line
	 * @return  string  i18n language string, or the requested key if the i18n item is not found
	 */
	public static function lang($key, $args = array())
	{
		// Extract the main group from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		// Get locale name
		$locale = "fr_FR";
		//self::config('locale.language.0');

		if ( ! isset(self::$internal_cache['language'][$locale][$group]))
		{
			// Messages for this group
			$messages = array();

			if ($files = self::find_file('i18n', $locale.'/'.$group))
			{
				foreach ($files as $file)
				{
					include $file;

					// Merge in configuration
					if ( ! empty($lang) AND is_array($lang))
					{
						foreach ($lang as $k => $v)
						{
							$messages[$k] = $v;
						}
					}
				}
			}

			if ( ! isset(self::$write_cache['language']))
			{
				// Write language cache
				self::$write_cache['language'] = TRUE;
			}

			self::$internal_cache['language'][$locale][$group] = $messages;
		}

		// Get the line from cache
		$line = self::key_string(self::$internal_cache['language'][$locale], $key);

		if ($line === NULL)
		{
			self::log('error', 'Missing i18n entry '.$key.' for language '.$locale);

			// Return the key string as fallback
			return $key;
		}

		if (is_string($line) AND func_num_args() > 1)
		{
			$args = array_slice(func_get_args(), 1);

			// Add the arguments into the line
			$line = vsprintf($line, is_array($args[0]) ? $args[0] : $args);
		}

		return $line;
	}

	public static function setg($var, $value) {
	}

	public static function setg_values($keyvalue) {
	}
	
}
?>