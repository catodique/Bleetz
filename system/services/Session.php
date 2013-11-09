<?php
/**
 * Bleetz framework
 *
 * service Session
 *
 * @author Carmelo Guarneri (Catodique@hotmail.com) (CAO)
 * @author Nicolas Levée
 * @author Pascal Parole
 *
 * @Copyright       2000-2013
 * @Project Page    none
 * @docs            ...
 *
 * All rights reserved.
 *
 */

/**
 * Interface SE_interface
 */
interface SE_interface {

	/**
	 * @param null|string $scope
	 * @param array $opts
	 */
	public function __construct($scope = null, array $opts = array());

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function get_variable($name);

	/**
	 * @param string $name
	 * @param mixed $val
	 * @param int|null $ttl
	 */
	public function set_variable($name, $val, $ttl = null);

	/**
	 * @param array|object $values
	 * @param int|null $ttl
	 */
	public function set_variables($values, $ttl = null);

	/**
	 *
	 */
	public function remove_variables();


	/**
	 * @param string $name
	 * @param string|null $scope
	 * @return mixed
	 */
	public static function get($name, $scope = null);

	/**
	 * @param string $name
	 * @param mixed $val
	 * @param string|null $scope
	 * @param int|null $ttl
	 */
	public static function set($name, $val, $scope = null, $ttl = null);

	/**
	 *
	 */
	public static function destroy();

}


/**
 * Class SE_factory
 */
class SE_factory {

	/**
	 * @param string $driver (Client/Server)
	 * @param string|null $scope
	 * @param array $opts
	 * @throws InvalidArgumentException
	 * @return SE_interface
	 */
	public static function Factory($driver, $scope = null, array $opts = array()) {
		if (!method_exists('SE_factory', $driver))
			throw new InvalidArgumentException("Driver $driver does not exist");
		return self::$driver($scope, $opts);
	}

	/**
	 * @param string|null $scope
	 * @param array $opts
	 * @return SE_cookie
	 */
	public static function Client($scope = null, array $opts = array()) {
		return new SE_cookie($scope, $opts);
	}

	/**
	 * @param string|null $scope
	 * @param array $opts
	 * @return SE_session
	 */
	public static function Server($scope = null, array $opts = array()) {
		return new SE_session($scope, $opts);
	}

}


/**
 * Class SE_cookie
 */
class SE_cookie implements SE_interface {

	/**
	 * @var null|string
	 */
	private $_scope;

	/**
	 * @var array
	 */
	private $_opts = array(
		'ttl' => 0,
		'path' => '/',
		'domain' => null,
		'secure' => false
	);


	/**
	 * @param null|string $scope
	 * @param array $opts array( 'ttl' => int, 'path' => string, 'domain' => string, 'secure' => bool )
	 */
	public function __construct($scope = null, array $opts = array()) {
		$this->_scope = is_string($scope) && !empty($scope) ? $scope : '__default';
		$this->_opts = array_merge($this->_opts, array_intersect_key($opts, array_flip(array(
			'ttl', 'path', 'domain', 'secure'
		))));
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function get_variable($name) {
		$aScope = $_COOKIE;
		if ($this->_scope !== null) {
			if (!isset($_COOKIE[$this->_scope]))
				return null;
			$aScope = $_COOKIE[$this->_scope];
		}
		if (isset($aScope[$name]))
			return $aScope[$name];
		return null;
	}

	/**
	 * si une valeur négative du ttl est donnée on supprime la valeur
	 * @param string $name
	 * @param mixed $val
	 * @param int|null $ttl
	 */
	public function set_variable($name, $val, $ttl = null) {
		$final_ttl = ($ttl ?: $this->_opts['ttl']) ?: 0;
		$aParams = array(
			$this->_scope."[".$name."]",
			(string) $val,
			$final_ttl ? time() + $final_ttl : null,
			$this->_opts['path'],
			$this->_opts['domain'],
			$this->_opts['secure'],
		);
		call_user_func_array('setcookie', $aParams);
	}

	/**
	 * si une valeur négative du ttl est donnée on supprime les valeurs
	 * @param array|object $values
	 * @param int|null $ttl
	 */
	public function set_variables($values, $ttl = null) {
		if (is_array($values) or is_object($values)) {
			foreach($values as $k => $val) {
				$this->set_variable($k, $val, $ttl);
			}
		}
	}

	/**
	 * Suppression de toutes les vars du scope courant
	 */
	public function remove_variables() {
		setcookie($this->_scope, "", time()-3600);
	}


	/**
	 * @param string $name
	 * @param string|null $scope
	 * @return mixed
	 */
	public static function get($name, $scope = null) {
		$me = new self($scope);
		return $me->get_variable($name);
	}

	/**
	 * @param string $name
	 * @param mixed $val
	 * @param string|null $scope
	 * @param int|null $ttl
	 */
	public static function set($name, $val, $scope = null, $ttl = null) {
		$me = new self($scope);
		$me->set_variable($name, $val, $ttl);
		unset($me);
	}

	/**
	 * Suppression de tous les cookies courant
	 */
	public static function destroy() {
		foreach(array_keys($_COOKIE) as $key_name)
			setcookie($key_name, "", time()-1);
		unset($key_name);
	}
}

/**
 * Class SE_cookie
 */
class SE_session implements SE_interface {

	/**
	 * @var null|string
	 */
	private $_scope;

	/**
	 * @var array
	 */
	private $_opts = array(
		'ttl' => 0,
		'path' => '/',
		'domain' => null,
		'secure' => false
	);


	/**
	 * @param null|string $scope
	 * @param array $opts array( 'ttl' => int, 'path' => string, 'domain' => string, 'secure' => bool )
	 */
	public function __construct($scope = null, array $opts = array()) {
		$this->_scope = is_string($scope) && !empty($scope) ? $scope : '__default';
		$this->_opts = array_merge($this->_opts, array_intersect_key($opts, array_flip(array(
			'ttl', 'path', 'domain', 'secure'
		))));
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function get_variable($name) {
		session_start();
		$var = isset($_SESSION[$this->_scope]) ? $_SESSION[$this->_scope] : null;
		$var = $var !== null && isset($var[$name]) ? $var[$name] : null;
		session_write_close();
		return $var;
	}

	/**
	 * si une valeur négative du ttl est donnée on supprime la valeur
	 * @param string $name
	 * @param mixed $val
	 * @param int|null $ttl
	 */
	public function set_variable($name, $val, $ttl = null) {
		session_set_cookie_params($ttl ?: $this->_opts['ttl'], $this->_opts['path'], $this->_opts['domain'], $this->_opts['secure']);
		session_start();
		if (!isset($_SESSION[$this->_scope]))
			$_SESSION[$this->_scope] = array();
		$_SESSION[$this->_scope][$name] = $val;
		session_write_close();
	}

	/**
	 * si une valeur négative du ttl est donnée on supprime les valeurs
	 * @param array|object $values
	 * @param int|null $ttl
	 */
	public function set_variables($values, $ttl = null) {
		if (is_array($values) or is_object($values)) {
			foreach($values as $k => $val) {
				$this->set_variable($k, $val, $ttl);
			}
		}
	}

	/**
	 * Supprime les données de session du scope courant
	 */
	public function remove_variables() {
		session_start();
		unset($_SESSION[$this->_scope]);
		session_write_close();
	}


	/**
	 * @param string $name
	 * @param string|null $scope
	 * @return mixed
	 */
	public static function get($name, $scope = null) {
		$me = new self($scope);
		return $me->get_variable($name);
	}

	/**
	 * @param string $name
	 * @param mixed $val
	 * @param string|null $scope
	 * @param int|null $ttl
	 */
	public static function set($name, $val, $scope = null, $ttl = null) {
		$me = new self($scope);
		$me->set_variable($name, $val, $ttl);
		unset($me);
	}

	/**
	 * Supprime toutes les données de session courante
	 */
	public static function destroy() {
		session_start();
		session_destroy();
	}
}


class SE {

	static function set($name, $val, $scope = null, $ttl = null) {
		//probleme avec le scope, il ne doit pas �tre un param�tre du driver
		//sinon on a un probleme pour effacer toute la session
		//implementer une fonction remove pour effacer un scope...
		$driver=CFG::get("SE_DRIVER","Session");
		SE_factory::Factory($driver, $scope)->set_variable($name, $val, $ttl);
	}

	static function get($name, $scope = null) {
		$driver=CFG::get("SE_DRIVER","Session");
		return SE_factory::Factory($driver, $scope)->get_variable($name);
	}

	static function destroy($scope = null) {
		$driver=CFG::get("SE_DRIVER","Session");
		$SE = SE_factory::Factory($driver, $scope);
		call_user_func(array($SE, $scope == null ? 'destroy' : 'remove_variables'));
	}

	/**
	 *  Charge une variables dans la vue... synonime de set
	 *   la variable est préfixée avec le namespace a utiliser notement dans les boucles
	 *
	 * @param string $varname	: le nom de la variable
	 * @param mixed $value		: la valeur a enregistrer
	 * @param string $scope		: le namespece de la variable
	 */
	static function set_variable($varname, $value, $scope = null) {
		$driver=CFG::get("SE_DRIVER","Session");
		SE_factory::$driver($scope)->set_variable($varname, $value);
	}

	/**
	 * Charge les variables dans la vue...
	 * la variable est préfixée avec le namespace a utiliser notement dans les boucles
	 *
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	static function set_variables($values, $scope = null) {
		//$driver=SE_DRIVER;
		$driver=CFG::get("SE_DRIVER","Session");
		SE_factory::$driver($scope)->set_variables($values);
	}

	/**
	 * Charge les variables dans la vue...
	 * la variable est préfixée avec le namespace a utiliser notement dans les boucles
	 *
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	static function set_variables_utf8_encode($values, $scope = null) {
		//$driver=SE_DRIVER;
		$driver=CFG::get("SE_DRIVER","Session");
		SE_factory::$driver($scope)->set_variables($values);
	}

}