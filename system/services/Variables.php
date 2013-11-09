<?php 
/**
 * Bleetz framework
 *
 * service Variables
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
	static $vr=null;
	static $scope=null;
	private static $activeScope="global";
	
	static function scope($scope) {
		
	}

	static function set($name, $val, $scope="") {
		// use scope as var_spool.???
		//VR::$$scope????
		$scope="global";
		if (self::$vr === null) self::$vr=new Variables();
		if (!isset(self::$vr->$scope)) self::$vr->$scope=new Variables();
		VR::$vr->$scope->$name=$val;
	}

	static function get($name, $scope="") {
		$scope="global";
		if (self::$vr !== null)
		if (self::$vr->$scope !== null)
		if (isset(VR::$vr->$scope->$name)) return VR::$vr->$scope->$name;
		return "";
	}

	/**
	 *  Charge une variables dans la vue... synonime de set
	 *   la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 *
	 * @param string $varname	: le nom de la variable
	 * @param mixed $value		: la valeur a enregistrer
	 * @param string $scope		: le namespece de la variable
	 */
	static function set_variable($varname, $value, $scope="") {
		if (!empty($scope)) $varname=$scope."__".$varname;
		//il va falloir changer a, risque de dump...
		//$this->$varname=$value;
		self::set($varname,$value);
	}

	/**
	 * Charge les variables dans la vue...
	 * la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 *
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	static function set_variables($values, $scope="") {
		if (is_array($values) or is_object($values))
			foreach($values as $k => $val) {
				self::set_variable($k, $val, $scope);
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
		//NOOP
	}
}


?>