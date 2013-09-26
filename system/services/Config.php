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
 */

class CFG extends Config
{
	static $conf;
		
	static function Load($service) {
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
	
	static function Get($name, $service="") {
		return constant ( $service . $name );
	}
	
	static function Set($name, $value, $service="") {
		if (!empty($cname)) $cname=$service."_".$name;
		else $cname=$name;
		return define ( $cname, $value);
	}
	
	
}


?>
