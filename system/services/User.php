<?php
/**
 * Bleetz framework
 *
 * service USER US RL GP
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

define("SYS_USER_ROOT_ID", 1);
define("SYS_USER_ROOT_NAME", "root");
define("SYS_USER_ROOT_GROUP", "root");

class RL {
	/**
	 * global variables for objects
	 */

	/**
	 * Créer une instance d'objet après l'avoir ajoutée a la base de donnée
	 *
	 * @return  object instance
	 */
	static function spawn() {
		return null;
	}

	/**
	 * détruit une instance d'objet avec l'id objet (à l'aide de son controlleur?)
	 * renvoie une instance objet si il existe et NULL sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  void
	 */
	static function delete($obj_id) {
	}

	/**
	 * Crée une instance d'objet avec l'id objet et la charge (et appelle son controlleur?)
	 * renvoie une instance objet si il existe et NULL sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  object instance
	 */
	static function load($obj_id) {
		return null;
	}

	/**
	 * Créé ne instance objet avec un id objet mais ne la charge pas
	 * et ne vérifie pas son existence
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return object instance
	 */
	static function mode($obj_id) {
		return new Object();
	}

	/**
	 * Recupere la liste des roles
	 *
	 * @return  array list of roles
	 */
	static function getList() {
		$q = "SELECT ";
		$q.= " * ";
		$q.= " FROM ";
		$q.= " sys_roles  ";
		
		//maybe it should be done same as save-record
		$data=DB::getRecords( $q);
		return $data;
	}

}

class GP {
	/**
	 * global variables for objects
	 */

	/**
	 * Créer une instance d'objet après l'avoir ajoutée a la base de donnée
	 *
	 * @return  object instance
	 */
	static function spawn() {
		return null;
	}

	/**
	 * détruit une instance d'objet avec l'id objet (à l'aide de son controlleur?)
	 * renvoie une instance objet si il existe et NULL sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  void
	 */
	static function delete($obj_id) {
	}

	/**
	 * Crée une instance d'objet avec l'id objet et la charge (et appelle son controlleur?)
	 * renvoie une instance objet si il existe et NULL sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  object instance
	 */
	static function load($obj_id) {
		return null;
	}

	/**
	 * Recupere la liste des roles
	 *
	 * @param  grp_id : id du groupe
	 * 
	 * @return  array list of roles
	 */
	static function getRoles($grp_id) {
		$q = "SELECT ";
		$q.= " * ";
		$q.= " FROM ";
		$q.= " sys_roles as r, lnk_group_has_roles as l ";
		$q.= " WHERE ";
		$q.= " r.rle_id= l.rle_id";
		$q.= " AND ";
		$q.= " grp_id= $grp_id";
		
		//maybe it should be done same as save-record
		$data=DB::getRecords( $q);
		return $data;
	}
	
	/**
	 * Créé ne instance objet avec un id objet mais ne la charge pas
	 * et ne vérifie pas son existence
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return object instance
	 */
	static function mode($obj_id) {
		return new Object();
	}

}

//à revisiter entièrement
class User {
  var $classname = "User_core";
  var $error;
  var $auth = array();            ## Data array
  var $lifetime = 15;             ## Max allowed idle time in minutes before
                                  ## reauthentication is necessary.
                                  ## If set to 0, auth never expires.

  var $refresh = 0;               ## Refresh interval in minutes.
                                  ## When expires auth data is refreshed
                                  ## from db using auth_refreshlogin()
                                  ## method. Set to 0 to disable refresh

	/**
	 * login.
	 *
	 * log a user with login and password
	 *
	 * @return  void
	 *
	function login($login, $password) {
		//recuperer les authentifications
		//$user = new User_core;
		//$this->user=$user;
		$ret=$this->user->login($login, $password);
		if ($ret) {
			session_start();
			//compile _url($redirect)
			//try authorize
			$_SESSION["token_id"]=$this->user->auth["usr_id"];
			$_SESSION["exp_token"]=$this->user->auth["exp"];
			session_regenerate_id();
			session_write_close();
		}
		return $ret;
	}
  */
  	/**
	 * logout. mettre dasn user
	 *
	 * log a user with login and password
	 *
	 * @return  void
	 */
	function logout() {
		session_start();
		session_destroy();
		session_start();
		$this->guest_login();
		$_SESSION["token_id"]=$this->auth["usr_id"];
		$_SESSION["exp_token"]=$this->auth["exp"];
		session_regenerate_id();
		session_write_close();
	}
/**************************************************************************
   * name: logout()
   * created by:
   * description:
   * parameters:
   * returns:
   **************************************************************************
  function logout() {
  //echo "logout<br>";
    //session_unset();
    $this->in=false;
    $this->guest_login();
    //$GLOBALS["BLEETZ_DEBUG"]=1;
    return True;
  }
*/
  /**
   * already logged, just come back
   * @author
   *
   * @returns true if ok
   */
	function authenticate($uid) {
		$ret=true;
		
		if ($uid==-1) {
			$this->guest_login();
		} else {
			$db = DB::Connect();

			$q = "SELECT * , GROUP_CONCAT( r.rle_name ) AS in_roles ";
			$q.= "FROM sys_users AS u, sys_groups AS g ";
			$q.= "LEFT OUTER JOIN lnk_group_has_roles AS l ON l.grp_id = g.grp_id ";
			$q.= "LEFT OUTER JOIN sys_roles AS r ON r.rle_id = l.rle_id ";
			$q.= "WHERE u.grp_id = g.grp_id ";
			$q.= "AND usr_id =  '$uid' ";
			$q.= "GROUP BY u.usr_id ";
			
			$db->query($q) ;

			if ($db->next_record()) {
				$this->auth["usr_login"] 	= $db->Record["usr_login"];
				$this->auth["usr_email"] 	= $db->Record["usr_email"];
				$this->auth["usr_id"] 		= $db->Record["usr_id"];
				$this->auth["usr_roles"]    = $db->Record["in_roles"].",";
				$this->auth["grp_name"]    	= $db->Record["grp_name"];
				$this->auth["grp_shell"]    = $db->Record["grp_shell"];
				$this->auth["grp_controller"]    = $db->Record["grp_controller"];
				$this->auth["grp_index"]    = $db->Record["grp_index"];
				$this->auth["usr_password"]    = $db->Record["usr_password"];
				$this->auth["exp"]			= time() + (60 * $this->lifetime);
				//$this->auth["refresh"] 		= time();
			}
			else {
				$this->error = "erreur d'identification";
				$this->guest_login();
				$ret=False;
			}
			DB::release();
		}
		return $ret;
	}

  /**
   * default guest access
   * @author Carmelo Guarneri
   *
   * @returns true if ok
   */
  function guest_login() {
    # Authenticate as nobody
    $this->auth["usr_login"] 	= "guest";
    $this->auth["usr_id"] 		= null;
    $this->auth["usr_level"]    = 0;
    $this->auth["usr_group"]    = "guest";
    $this->auth["exp"]     = 0x7fffffff;
    $this->auth["refresh"] = 0x7fffffff;

    return True;
  }

   /**
   * default guest access
   * @author
   *
   * @returns true if ok
   */
  function login($login, $password) {
		$db=DB::connect();
		if ($db==null) return false;
		
		$crypt_password=hash("sha256",$password);
		
		
		$q = "SELECT * , GROUP_CONCAT( r.rle_name ) AS in_roles ";
		$q.= "FROM sys_users AS u, sys_groups AS g ";
		$q.= "LEFT OUTER JOIN lnk_group_has_roles AS l ON l.grp_id = g.grp_id ";
		$q.= "LEFT OUTER JOIN sys_roles AS r ON r.rle_id = l.rle_id ";
		$q.= "WHERE u.grp_id = g.grp_id ";
		$q.= "AND (usr_login =  '$login' or usr_email='$login') ";
		$q.= "AND usr_password =  '$crypt_password' ";
		$q.= "GROUP BY u.usr_id ";
		
		$db->query($q) ;

		if ($db->next_record()) {
		  $result=$db->Record;
		} else {
		  ER::collect("Cet utilisateur n'existe pas");
		  return False ;
		}
		DB::release();
		    # Authenticate as nobody
		$this->auth["usr_login"] 	= $db->Record["usr_login"];
		$this->auth["usr_email"] 	= $db->Record["usr_email"];
		$this->auth["usr_id"] 		= $db->Record["usr_id"];
		$this->auth["usr_roles"]    = $db->Record["in_roles"].",";
		$this->auth["grp_name"]    	= $db->Record["grp_name"];
		$this->auth["grp_shell"]    = $db->Record["grp_shell"];
		$this->auth["grp_controller"]    = $db->Record["grp_controller"];
		$this->auth["grp_index"]    = $db->Record["grp_index"];
		$this->auth["usr_password"]    = $db->Record["usr_password"];
		$this->auth["exp"]			= time() + (60 * $this->lifetime);
		//$this->auth["refresh"] 		= 0x7fffffff;

		return True;
  }

}

class US {
	/**
	 * global variables for users
	 */
	static $user;
	

	/**
	 * Créer une instance d'objet après l'avoir ajoutée a la base de donnée
	 *
	 * @return  object instance
	 */
	static function getId() {
		return self::$user->auth["usr_id"];
	}
	/**
	 * Créer une instance d'objet après l'avoir ajoutée a la base de donnée
	 *
	 * @return  object instance
	 */
	static function spawn() {
		$db=DB::connect();
		
		if (empty($d["usr_email"])) return False;
		if (empty($d["usr_password"])) return False;
		
		if (empty($d["usr_login"])) $d["usr_login"]=uniqid("user_");
		
		$result=$db->begin_transaction();
		
		if (empty($d["obj_type"])) $d["obj_type"]=0;
		if (empty($d["obj_controller"])) $d["obj_controller"]="system";
		$d["obj_nature"]="user";
		
		$o=OB::Spawn($d["obj_type"],$d["obj_controller"],"user");
		
		$obj_id=$o->obj_id;
		
		if (empty($d["usr_type"])) $d["usr_type"]=0;
		
		$today = date("Y-m-d H:i:s");
		$d["usr_id"]=$obj_id;
		//$d["grp_id"];
		//$d["usr_login"];
		//$d["usr_email"];
		//$d["usr_password"];
		$d["usr_dt_entree"]=$today;
		//$d["usr_status"];
		
		$u=new user;
		$u->create($d);
		
			//ajouter le mot de passe
		$q  = "INSERT INTO sys_users (";
		$q .= "usr_id,";
		$q .= "grp_id,";
		$q .= "usr_login,";
		$q .= "usr_email,";
		$q .= "usr_password,";
		$q .= "usr_dt_entree,";
		$q .= "usr_type";
		$q .= ") ";
		$q .= "VALUES ( ";
		$q .= "'".$obj_id."', ";
		$q .= "'".$d["grp_id"]."', ";
		$q .= "'".$d["usr_login"]."', ";
		$q .= "'".addslashes($d["usr_email"])."', ";
		$q .= "'".addslashes($d["usr_password"])."', ";
		$q .= "'".$today."', ";
		$q .= "'".$d["usr_type"]."' ";
		$q .= ")";
		$db->exec($q);
		
		$d["usr_id"]=$obj_id;//=obj_id
		/*
		 $q  = "UPDATE sys_users SET ";
		$q .= "usr_email= '".addslashes($d["usr_email"])."'";
		//$q .= "usr_statut='".addslashes($d["usr_statut"])."' ";
		$q .= "WHERE usr_id='".$d["usr_id"]."' ";
		$db->exec($q);
		*/
		
		$result=$db->end_transaction();
		
		if ($result) {
		} else return null;
	}

	/**
	 * détruit une instance d'objet avec l'id objet (à l'aide de son controlleur?)
	 * renvoie une instance objet si il existe et NULL sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  void
	 */
	static function delete($obj_id) {
	}

	/**
	 * Crée une instance d'objet avec l'id objet et la charge (et appelle son controlleur?)
	 * renvoie une instance objet si il existe et NULL sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  object instance
	 */
	static function load($obj_id) {
		return null;
	}

	/**
	 * Autentificatoion de l'utilisateur loggué
	 * Vérifie la validité de la session utilisateur
	 *
	 * @return user instance
	 */
	static function checkpoint() {
		session_start();
		$id_token=isset($_SESSION["token_id"])?$_SESSION["token_id"]:false;
		$flash_token=isset($_SESSION["exp_token"])?$_SESSION["exp_token"]:false;
		//echo $flash_token."<br>";
		//echo $id_token.",";
		//echo $flash_token.",";
		//echo time();
		//recuperer les authentifications
		if (!(self::$user instanceof User)) {
			$user=new User;
		} else {
			$user=self::$user;
		}
		if ($id_token===false) {
			//echo "guest";
			$user->guest_login();
		} else if ($flash_token<time()) {
			session_destroy();
			session_start();
			$user->guest_login();
		} else {
			//echo(po);
			$user->authenticate($id_token);
		}
	
		self::$user=$user;
	
		$_SESSION["token_id"]=US::$user->auth["usr_id"];
		$_SESSION["exp_token"]=US::$user->auth["exp"];
		session_regenerate_id();
		session_write_close();
		
		//should it return user????
		return $user;
	}

	/**
	 * Autentificatoion de l'utilisateur loggué
	 * Vérifie la validité de la session utilisateur
	 *
	 * @return user instance
	 */
	static function checkLogin($login, $password) {
		if (!(self::$user instanceof User)) {
			self::$user=new User;
		}
		$ret=self::$user->login($login, $password);
		if ($ret) {
			session_start();
			//compile _url($redirect)
			//try authorize
			$_SESSION["token_id"]=self::$user->auth["usr_id"];
			$_SESSION["exp_token"]=self::$user->auth["exp"];
			session_regenerate_id();
			session_write_close();
		}
		return $ret;
	}

	/**
	 * Logout de l'utilisateur loggué
	 *
	 * @return user instance
	 */
	static function Logout() {
		$u=self::checkpoint();
		$u->logout();
	}
	
}


?>
