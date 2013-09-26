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


class Variables {

}

class VR {
	private static $var_spool=null;
	private static $activeScope="global";
	
	static function scope($scope) {
		
	}

	static function set($name, $val, $scope="") {
		if (self::$var_spool === null) self::$var_spool=new Variables();
		VR::$var_spool->$name=$val;
	}

	static function get($name, $scope="") {
		if (self::$var_spool === null) return "";
		return VR::$var_spool->$name;
	}

	/**
	 *  Charge une variables dans la vue... synonime de set
	 *   la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 *
	 * @param string $varname	: le nom de la variable
	 * @param mixed $value		: la valeur a enregistrer
	 * @param string $scope		: le namespece de la variable
	 */
	function set_variable($varname, $value, $scope="") {
		if (!empty($scope)) $varname=$scope."__".$varname;
		//il va falloir changer a, risque de dump...
		//$this->$varname=$value;
		$this->$varname=$value;
	}

	/**
	 * Charge les variables dans la vue...
	 * la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 *
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	function set_variables($values, $scope="") {
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
	function set_variables_utf8_encode($values, $scope="") {
		if (is_array($values))
			foreach($values as $k => $val) {
			$this->set($k, $v, $scope);
			//$this->$k=utf8_encode_if($val);
		};
	}
}


?>
