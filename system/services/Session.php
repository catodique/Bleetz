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

class SE
{
	
	static function set($name, $val) {
		session_start();
		$_SESSION[$name]=$val;
		session_regenerate_id();
		session_write_close();
		
	}
	
	static function get($name) {
		session_start();
		$val="";
		if (isset($_SESSION[$name])) $val=$_SESSION[$name];
		session_regenerate_id();
		session_write_close();
		return $val;
	}

	/**
	 *  Charge une variables dans la vue... synonime de set
	 *   la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 *
	 * @param string $varname	: le nom de la variable
	 * @param mixed $value		: la valeur a enregistrer
	 * @param string $scope		: le namespece de la variable
	 */
	static function set_variable($varname, $value) {
		
	}
	
	/**
	 * Charge les variables dans la vue...
	 * la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 *
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	static function set_variables($values) {
		if (is_array($values) or is_object($values))
			foreach($values as $k => $val) {
			$this->set($k, $val, $scope);
		}
	}
	
	/**
	 * Charge les variables dans la vue...
	 * la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 *
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	static function set_variables_utf8_encode($values, $scope="") {
		if (is_array($values))
			foreach($values as $k => $val) {
			$this->set($k, $v, $scope);
			//$this->$k=utf8_encode_if($val);
		};
	}
	
}


?>
