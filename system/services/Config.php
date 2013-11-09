<?php
/**
 * Bleetz framework
 *
 * service Session
 *
 * @author Carmelo Guarneri (Catodique@hotmail.com) (CAO)
 * @author Pascal Parole
 *
 * @Copyright       2000-2013
 * @Project Page    none
 * @docs            ...
 *
 * All rights reserved.
 *
 * lots of thingsd to do here
 * -transfert some bleetz func here
 */

class CFG extends Config
{
	static $conf=null;

	static function Load($service) {
		if (self::$conf === null) self::$conf=new Config;
		//var_dump(self::$conf);
		if (isset(self::$$service) && is_array(self::$$service))
			foreach(self::$$service as $k => $val) {
				define($k, $val);
			}
	}

	static function LoadDirectories() {
		if (is_array(self::$directories))
			foreach(self::$directories as $k => $val) {
				define($k, preg_replace('/^\w:/','',str_replace('\\','/',realpath(DOCROOT.$val))).'/');
			}
	}

	static function get($name, $service="") {
		//var_dump(self::$conf);
		if (!isset(self::$conf->$service)) return null;
		$serv=self::$conf->$service;
		return $serv[$name];
		//return constant ( $service . $name );
	}

	static function set($name, $value, $service="") {
		if (self::$conf === null) self::$conf=new self;
		if (!isset(self::$conf->$service)) self::$conf->$service=array();
		//var_dump(self::$conf);
		//echo $name;
		//self::$conf->$service->$name=$value;
		$serv=self::$conf->$service;
		$serv[$name]=$value;
		self::$conf->$service=$serv;
		//var_dump(self::$conf);
		return true;
	}


}


?>