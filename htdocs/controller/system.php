<?php defined('BLEETZ') OR die('No direct access allowed.');
/*---------------------------------------------------------------------------*
 |        Bleetz framework                  								 |
 +---------------------------------------------------------------------------+
 |        Copyright (c) 2000                                                 |
 |                      Bleetz Corporation.                                  |
 |                      Carmelo Guarneri and Pascal Parole.                  |
 |						All rights reserved. 								 |
 +---------------------------------------------------------------------------+
 *---------------------------------------------------------------------------*/

//gerer le mot de passe
// User=*; Group=*; Role=*; type=rwv; validate=name, required=name
Class system extends Controller_core {
	
	//mettre les infos de validations
	
	function login(&$d) { // User=*; Group=*; Level=0;
		$login=@$d["login"];
		$password=@$d["password"];
		if (US::checkLogin($login, $password)) {
			$d["url"]=US::$user->auth["grp_controller"].".".US::$user->auth["grp_index"];
			//$d=$auth;
			return true;
		} else {
			return false;
		}
		//US::checkLogin();
		//URL::redirect();
		//return Bleetz::$context->login($d["usr_login"], $d["password"]);
	}
	
	function log($d) { // User=*; Group=*; Level=0;
		US::checkLogin();
		HDR::redirect(".");
		//return Bleetz::$context->login($d["usr_login"], $d["password"]);
	}

	function logout($d) { // User=*; Group=*; Level=0;
		US::logout();
		HDR::redirect(".");
		return true;
	}
	
	/**
	 * Liste des objets
	 * param et return ˆ travers le $d
	 *
	 * @return true or false
	 */
	function getObjectsList(&$d) { // Type=w;
	
		$data=OB::QueryList($d);
				 
		$d["data"]=$data;
	
		return $data['status']=="success";
	}
	
	/**
	 * Liste des ressources  
	 * param et return ˆ travers le $d
	 * 
	 * @return true or false
	 */
	function getRscList(&$d) { // Type=w;
				
		$d["rsc_lang"]=LC_LANG_ACTIVE;
		$data=SET::QueryList($d);
					
		$d["data"]=$data;
		
		return $data['status']=="success";
	}
	
	/**
	 * Liste des ressources strings
	 * param et return ˆ travers le $d
	 *
	 * @return true or false
	 */
	function getStringList(&$d) { // Type=w;
	
		$d["rsc_lang"]=LC_LANG_ACTIVE;
		$data=RS::QueryList($d["obj_id"], $d, RSCTYPE_STRING);
			
		$d["data"]=$data;
	
		return $data['status']=="success";
	}
	
	function AddRsc(&$d) { // Type=w;
		//crŽe une nouvelle ressource "fiches", "modele"
		$o=SET::Realize($d["obj_controller"], $d["obj_nature"]);

		return ($o!==null);
	}
	
	function AddRscString(&$d) { // Type=w;
		//crŽe une nouvelle information 

		$i=RS::Realize($d["obj_id"], $d["rsc_hook"], RSCTYPE_STRING, $d["rsc_code"]);
		
		return ($i!==null);
	}
	
	function updtRscString(&$d) { // Type=w;

		//return RS::Delete($d["rsc_id"]);
		$r=RS::Save($d["rsc_id"], $d["rsc_hook"], $d["rsc_code"] );
		
		return $r;
		
	}
	
	function delRscString(&$d) { // Type=w;

		return RS::Delete($d["rsc_id"]);
		
	}
	
	/**
	 * Modifier une ressource, 
	 * @param obj_controller utilisateur
	 * @param obj_nature utilisateur
	 * 
	 * @return boolean
	 */
	function updateRsc(&$d) { // User=*; Group=*; Level=0;

		$db=DB::connect();
		//verifier si il y a dŽjˆ des informations ou des fichiers rattachŽs
		//et si c'est le cas , interdire toute modif ˆ cause de la signature crc32
		
		$result=$db->begin_transaction();
				
		//ajouter le mot de passe
		$q  = "UPDATE app_objets SET ";
		$q .= "obj_controller ='".$d["obj_controller"]."', ";
		$q .= "obj_nature ='".$d["obj_nature"]."' ";
		$q.= " WHERE obj_id='".$d["obj_id"]."'";

		$db->exec($q);
		
		$result=$db->end_transaction();

		return $result;
 	}
 	
 	/**
 	 * Detruire une ressource,
 	 * 
 	 * @param obj_id 
 	 *
 	 * @return boolean
 	 */
 	function deleteRsc(&$d) { // User=*; Group=*; Level=0;
 		return SET::Delete($d["obj_id"]);
 	}
	
 	/**
 	 * Liste des utilisateurs
 	 * param et return ˆ travers le $d
 	 *
 	 * @return true or false
 	 */
 	function getUsersList(&$d) { // Type=w;
 	
		$q = "SELECT  * ";
		$q.= " FROM  sys_users as u, sys_groups as g";
		$q.= " WHERE g.grp_id=u.grp_id";
		//maybe it should be done same as save-record
		$data=DB::queryRecords($d, $q, "usr_id");
 		 			
 		$d["data"]=$data;
 	
 		return $data['status']=="success";
 	}
 	
	/**
	 * Ajouter un utilisateur, le login est le email en general
	 * accesible ˆ travers un call
	 * CREER UNE CLASSE AVEC METHODES STATIQUE POUR AJOUTER ENREGISTREMENT USER...
	 * RENVOYANT UNE INSTANCE EVENTUELLE...
	 * param et return ˆ travers le $d
	 * @param login utilisateur
	 * @param email utilisateur
	 * @param password utilisateur
	 * @param usr_type : type d'utilisateur
	 * @return usr_id
	 */
	function addUser(&$d) { // User=*; Group=*; Level=0;
		$db=DB::connect();
		
		if (empty($d["usr_email"])) {
			ER::collect("Email is required for login");
			return False;
		}
		if (empty($d["usr_password"])) {
			ER::collect("Password is required for login");
			return False;
		}

		if (empty($d["usr_login"])) $d["usr_login"]=uniqid("user_");
		
		$result=$db->begin_transaction();
		
		if (!isset($d["obj_type"])) $d["obj_type"]=OBJTYPE_SYSTEME;
		else if (empty($d["obj_type"])) $d["obj_type"]=OBJTYPE_SYSTEME;
		else if ($d["obj_type"]==0) $d["obj_type"]=OBJTYPE_SYSTEME;
		
		if (empty($d["obj_controller"])) $d["obj_controller"]="system";
		$d["obj_nature"]="user";
		
		$o=OB::Spawn($d["obj_controller"],"user", $d["obj_type"]);
		
		$obj_id=$o->obj_id;
		
		if (empty($d["usr_type"])) $d["usr_type"]=0;
		
		$crypt_password=hash("sha256",$d["usr_password"]);
		
		$today = date("Y-m-d H:i:s");
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
		$q .= "'".$crypt_password."', ";
		$q .= "'".$today."', ";
		$q .= "'".$d["usr_type"]."' ";
		$q .= ")";
		$db->exec($q);
		//echo $q;
		$d["usr_id"]=$obj_id;//=obj_id
		
		$result=$db->end_transaction();

		return $result;
 	}
 	
	/**
	 * Modifier un utilisateur, le login est le email en general
	 * accesible ˆ travers un call
	 * CREER UNE CLASSE AVEC METHODES STATIQUE POUR AJOUTER ENREGISTREMENT USER...
	 * RENVOYANT UNE INSTANCE EVENTUELLE...
	 * param et return ˆ travers le $d
	 * @param login utilisateur
	 * @param email utilisateur
	 * @param password utilisateur
	 * @param usr_type : type d'utilisateur
	 * @return usr_id
	 */
	function updateUser(&$d) { // User=*; Group=*; Level=0;

		$db=DB::connect();
		
		if (empty($d["usr_login"])) $d["usr_login"]=uniqid("user_");
		
		$result=$db->begin_transaction();

		//ajouter le mot de passe
		$q  = "UPDATE sys_users SET ";
		$q .= "grp_id ='".$d["grp_id"]."', ";
		$q .= "usr_login='".$d["usr_login"]."', ";
		if (!empty($d["usr_password"])) {
			$crypt_password=hash("sha256",$d["usr_password"]);
			$q .= "usr_password='".$crypt_password."', ";;
		}
		$q .= "usr_email='".addslashes($d["usr_email"])."' ";
		$q.= " WHERE ";
		$q.= "usr_id='".$d["usr_id"]."'";

		$db->exec($q);
		
		$result=$db->end_transaction();

		return $result;
 	}
 	
 	/**
	 * Charger un utilisateur
	 * accesible ˆ travers un call
	 * param et return ˆ travers le $d
	 * @param usr_id : id utilisateur
	 * @return true or false
	 */
	function fetchUser(&$d) { // private
		//DONNER LE TYPE usr_type permettras de distinguer et d'adapter.......
		if (empty($d["usr_id"])) {
			ER::collect("Un identifiant doit tre fourni.");
			return False;
		}
		$db=DB::connect();
	
		$q = "SELECT  * ";
		$q.= " FROM sys_users  ";
		$q.= " WHERE usr_id='".$d["usr_id"]."'";
		$db->query($q) ;
	
		if ($db->next_record()) {
			$result=$db->Record;
		}
		else {
			ER::collect("Il n'existe aucun enregistrement avec cet identifiant.");
			return False ;
		}
		
		$d["user"]=$result;
	
		return True;
	}

	/**
	 * Detruire  un utilisateur
	 * 
	 * @param usr_id : id user
	 * 
	 * @return true or false
	 */
	function deleteUser(&$d) { // User=*; Group=*; Level=0;
		if (empty($d["usr_id"])) {
			ER::collect("Un identifiant doit tre fourni.");
			return False;
		}

		$db=DB::connect();
		$result=$db->begin_transaction();
		$today = date("Y-m-d H:i:s");
		//ajouter le mot de passe
		$q  = "DELETE FROM sys_users  ";
		$q .= " WHERE usr_id=".$d["usr_id"];
		//echo $q;
		$db->exec($q);

		//detruire les ressources associŽes
		$q  = "DELETE FROM app_objets  ";
		$q .= " WHERE obj_id=".$d["usr_id"];
		//echo $q;
		$db->exec($q);
		
		//nettoyage
		$q  ="DELETE FROM app_objets";
		$q .=" USING app_objets";
		$q .=" LEFT OUTER JOIN sys_users ON usr_id = obj_id ";
		$q .=" WHERE obj_nature =  'user'";
		$q .=" AND usr_id IS NULL";
		$db->exec($q);
		
		return $db->end_transaction();		
	}
	
	/**
	 * CrŽer  un groupe
	 * @param grp_name : nom groupe
	 * @return true or false
	 */
	function getGroupList(&$d) { // User=*; Group=*; Level=0;
		$q = "SELECT  * ";
		$q.= " FROM sys_groups  ";
		//maybe it should be done same as save-record
		$data=DB::queryRecords($d, $q, "grp_id");
		
		$d["data"]=$data;
		
		return $data['status']=="success";
	}
	
	/**
	 * CrŽer  un groupe
	 * @param grp_name : nom groupe
	 * @return true or false
	 */
	function addGroup(&$d) { // User=*; Group=*; Level=0;
		$db=DB::connect();
		$result=$db->begin_transaction();
		$today = date("Y-m-d H:i:s");
		//ajouter le mot de passe
		$q  = "INSERT INTO sys_groups (";
		//$q .= "grp_id,";
		//$q .= "grp_creation,";
		$q .= "grp_name,";
		$q .= "grp_controller,";
		$q .= "grp_index,";
		$q .= "grp_shell,";
		$q .= "grp_comment";
		$q .= ") ";
		$q .= "VALUES ( ";
		//$q .= "'".$today."', ";
		$q .= "'".($d["grp_name"])."', ";
		$q .= "'".($d["grp_controller"])."', ";
		$q .= "'".($d["grp_index"])."', ";
		$q .= "'".($d["grp_shell"])."', ";
		$q .= "'".addslashes($d["grp_comment"])."' ";
		$q .= ")";
		$d["grp_id"]=$db->exec_insert($q);
		
		return $db->end_transaction();		
	}

	/**
	 * modifier un groupe
	 * @param grp_name : nom groupe
	 * @return true or false
	 */
	function updateGroup(&$d) { // User=*; Group=*; Level=0;
		if (empty($d["grp_id"])) {
			ER::collect("Un identifiant doit tre fourni.");
			return False;
		}
	
		$db=DB::connect();
		$result=$db->begin_transaction();
		$today = date("Y-m-d H:i:s");
		//ajouter le mot de passe
		$q  = "UPDATE sys_groups SET ";
		$q .= "grp_name='".$d["grp_name"]."',";
		$q .= "grp_controller='".$d["grp_controller"]."',";
		$q .= "grp_index='".$d["grp_index"]."',";
		$q .= "grp_shell='".$d["grp_shell"]."',";
		$q .= "grp_comment='".addslashes($d["grp_comment"])."' ";
		$q .= " WHERE grp_id=".$d["grp_id"];
		$db->exec($q);
		//echo $q;
	
		return $db->end_transaction();
	}
		
	/**
	 * Lier les roles au groupe
	 * @param grp_name : nom groupe
	 * @return true or false
	 */
	function linkGroupRoles(&$d) { // User=*; Group=*; Level=0;
		if (empty($d["grp_id"])) {
			ER::collect("Un identifiant doit tre fourni.");
			return False;
		}
		$db=DB::connect();
		$result=$db->begin_transaction();
		$today = date("Y-m-d H:i:s");
		//ajouter le mot de passe
		$q  = "DELETE FROM lnk_group_has_roles  ";
		$q .= " WHERE grp_id=".$d["grp_id"];
		$db->exec($q);
		
		$roles=RL::getList();
		for ($i=0; $i<sizeof($roles);$i++) {
			$rle_id=$roles[$i]["rle_id"];
			$link="rle_link.".$rle_id;
			if (@$d[$link]==1) {
				//insert...
				$q  = "INSERT INTO lnk_group_has_roles ( ";
				$q .= "grp_id,";
				$q .= "rle_id";
				$q .= ") ";
				$q .= " VALUES ( ";
				$q .= "'".$d["grp_id"]."', ";
				$q .= "'".$rle_id."' ";
				$q .= ")";
				$db->exec($q);
			}
		}
		return $db->end_transaction();		
	}
	
	/**
	 * Detruire  un groupe
	 * @param grp_id : id groupe
	 * 
	 * @return true or false
	 */
	function deleteGroup(&$d) { // User=*; Group=*; Level=0;
		if (empty($d["grp_id"])) {
			ER::collect("Un identifiant doit tre fourni.");
			return False;
		}

		$db=DB::connect();
		$result=$db->begin_transaction();
		$today = date("Y-m-d H:i:s");
		//ajouter le mot de passe
		$q  = "DELETE FROM sys_groups  ";
		$q .= " WHERE grp_id=".$d["grp_id"];
		//echo $q;
		$db->exec($q);
		
		return $db->end_transaction();		
	}
	
	function fetchGroup(&$d) { // private
	
		//DONNER LE TYPE usr_type permettras de distinguer et d'adapter.......
		if (empty($d["grp_id"])) {
			ER::collect("Un identifiant doit tre fourni.");
			return False;
		}
		$db=DB::connect();
	
		$q = "SELECT  * ";
		$q.= " FROM sys_groups  ";
		$q.= " WHERE grp_id='".$d["grp_id"]."'";
		$db->query($q) ;
	
		if ($db->next_record()) {
			$result=$db->Record;
		}
		else {
			ER::collect("Il n'existe aucun enregistrement avec cet identifiant.");
			return False ;
		}
		
		$d["group"]=$result;		
			
		return True;
	}
	
	function fetchGroups(&$d) { //  User=*; Group=*; Level=0;
	
		$q = "SELECT * ";
		$q.= " FROM sys_groups  ";
	
		$data=DB::getRecords( $q);
			
		//$d data
		$d["groups"]=$data;
	
		return True;
	}
	
	function droplistGroups(&$d) { //  User=*; Group=*; Level=0;
		$db=DB::connect();
	
		$q = "SELECT * ";
		$q.= " FROM sys_groups  ";
		$db->query($q) ;
	
		$list=array();
		while ($db->next_record()) {
			$r=array();
			$r["id"]=$db->Record["grp_id"];
			$r["text"]=$db->Record["grp_name"];
			$list[]=$r;
		};

		$data=array();
		$data['status'] = 'success';
		$data['items'] = $list;
		
		echo json_encode($data);
		
		return True;
	}
	
	/**
	 * Lite des roles
	 * 
	 * @return true or false
	 */
	function getRolesList(&$d) { // User=*; Group=*; Level=0;
		$q = "SELECT * ";
		$q.= " FROM sys_roles  ";
		//maybe it should be done same as save-record
		$data=DB::queryRecords($d, $q, "rle_id");
		
		$d["data"]=$data;
		
		return $data['status']=="success";
	}
	
	/**
	 * CrŽer  un groupe
	 * @param grp_name : nom groupe
	 * @return true or false
	 */
	function addRole(&$d) { // User=*; Group=*; Level=0;
		$db=DB::connect();
		$result=$db->begin_transaction();
		$today = date("Y-m-d H:i:s");
		//ajouter le mot de passe
		$q  = "INSERT INTO sys_roles (";
		//$q .= "grp_id,";
		//$q .= "grp_creation,";
		$q .= "rle_name,";
		$q .= "rle_comment";
		$q .= ") ";
		$q .= "VALUES ( ";
		//$q .= "'".$today."', ";
		$q .= "'".addslashes($d["rle_name"])."', ";
		$q .= "'".addslashes(@$d["rle_comment"])."' ";
		$q .= ")";
		$d["rle_id"]=$db->exec_insert($q);
		
		return $db->end_transaction();		
	}

	/**
	 * CrŽer  un groupe
	 * @param grp_name : nom groupe
	 * @return true or false
	 */
	function updateRole(&$d) { // User=*; Group=*; Level=0;
		if (empty($d["rle_id"])) {
			ER::collect("Un identifiant doit tre fourni.");
			return False;
		}
	
		$db=DB::connect();
		$result=$db->begin_transaction();
		$today = date("Y-m-d H:i:s");
		//ajouter le mot de passe
		$q  = "UPDATE sys_roles SET ";
		$q .= "rle_name='".addslashes($d["rle_name"])."',";
		$q .= "rle_comment='".addslashes($d["rle_comment"])."' ";
		$q .= " WHERE rle_id=".$d["rle_id"];
		$db->exec($q);
	
		return $db->end_transaction();
	}
	

	/**
	 * CrŽer  un groupe
	 * @param grp_name : nom groupe
	 * @return true or false
	 */
	function deleteRole(&$d) { // User=*; Group=*; Level=0;
		if (empty($d["rle_id"])) {
			ER::collect("Un identifiant doit tre fourni.");
			return False;
		}

		$db=DB::connect();
		$result=$db->begin_transaction();
		$today = date("Y-m-d H:i:s");
		//ajouter le mot de passe
		$q  = "DELETE FROM sys_roles  ";
		$q .= " WHERE rle_id=".$d["rle_id"];
		$db->exec($q);
		
		return $db->end_transaction();		
	}
	
	function fetchRole(&$d) { // private
	
		//DONNER LE TYPE usr_type permettras de distinguer et d'adapter.......
		if (empty($d["rle_id"])) {
			ER::collect("Un identifiant doit tre fourni.");
			return False;
		}
		$db=DB::connect();
	
		$q = "SELECT * ";
		$q.= " FROM sys_roles  ";
		$q.= " WHERE rle_id='".$d["rle_id"]."'";
		$db->query($q) ;
	
		if ($db->next_record()) {
			$result=$db->Record;
		}
		else {
			ER::collect("Il n'existe aucun enregistrement avec cet identifiant.");
			return False ;
		}
		
		$d["role"]=$result;
	
		return True;
	}

	// a mettre dans une classe HTTP
	function outputJSON($data) {//private
		header("Content-Type: application/json;charset=utf-8");
		echo json_encode($data);
	}
	
}

$system=new system();

?>