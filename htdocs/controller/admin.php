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
Bleetz::load_controller("system");

//gerer le mot de passe
Class admin extends Controller_core { // User=root; Group=*; Mode=pgcsi; Cache=0;

	function index($d) { // 
		$page_components=array("adm_index.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$view->render();
	}

	function E404_not_found() { // User=*; Group=*
		header('HTTP/1.1 404 File Not Found');
		echo "404 File not found ";
	}

	function login($d) { // User=*; Group=*; Roles=*;
		header('HTTP/1.1 401 Unauthorized');
		$page_components=array("content"=>"adm_login.html");
		$view=new View_core;
		$view->load_template("blank.html", $page_components);
		$view->set_variables($d);
		$view->render();
	}
	
	function doc($d) { // User=*; Group=*; Role=*;
		$page_components=array("bleetzdoc.xml");
		$view=new View_core;
		$view->load_template("doc.html", $page_components);
		$view->render();
	}

	function myProfile($d) { // 
		$page_components=array("adm_user_profile.html");
		
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		
		$view->set_variables(US::$user->auth);
		//var_dump(US::$user["auth"]);
		$view->render();
		//echo ER::report();
		
	}
		
	function objets($d) { // 
		$page_components=array("adm_obj_list.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$view->render();
	}
	
	function ressources($d) { // 
		$page_components=array("adm_rsc_list.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$view->render();
	}
		
	function RscAdd($d) { // 
		$page_components=array("adm_rsc_add.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		// A REVOIR
		//$view->set("syserror", $this->syserror);
		$view->render();
	}
	
	function RscShow($d) { // 
		$page_components=array("adm_rsc_show.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables(SET::loadId($d["obj_id"]));
		$view->set_variables($d);
		$view->render();
	}
	
	function RscUpdate($d) { //
		$page_components=array("adm_rsc_updt.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables(SET::loadId($d["obj_id"]));
		$view->set_variables($d);
		// A REVOIR
		//$view->set("syserror", $this->syserror);
		$view->render();
	}
	
	function RscDelete($d) { // 
		$page_components=array("adm_rsc_del.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables(SET::loadId($d["obj_id"]));
		$view->set_variables($d);
		$view->render();
	}
	
	function RscInfosList($d) { // 
		$page_components=array("adm_rsc_infoslist.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables(SET::loadId($d["obj_id"]));
		$view->set_variables($d);
		// A REVOIR
		//$view->set("syserror", $this->syserror);
		$view->render();
	}
	
	function RscInfoLang($d) { // 
		$page_components=array("adm_rsc_infoLang.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables(RS::loadId($d["inf_id"]));
		$view->set_variables($d);
		// A REVOIR
		//$view->set("syserror", $this->syserror);
		$view->render();
	}
	
	function users($d) { // 
		$page_components=array("adm_users_list.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$view->render();
	}
	
	function UserAdd($d) { // 
		$page_components=array("adm_user_add.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$view->render();
	}
	
	function UserShow($d) { // 
		$page_components=array("adm_user_show.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		//ˆ remplacer par...
		//US::loadId($d["usr_id"]);
		$this->call("system","fetchUser", $d);
		$view->set_variables($d["user"]);
		$view->render();
	}
	
	function UserUpdate($d) { // 
		$page_components=array("adm_user_updt.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		//US::loadId($d["usr_id"]);
		$this->call("system","fetchUser", $d);
		$view->set_variables($d["user"]);
		$view->render();
	}
	
	function UserDelete($d) { // 
		$page_components=array("adm_user_del.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		//US::loadId($d["usr_id"]);
		$this->call("system","fetchUser", $d);
		$view->set_variables($d["user"]);
		$view->render();
	}
	
	function groups($d) { // 
		$page_components=array("adm_groups_list.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$view->render();
	}
		
	function GroupAdd($d) { // 
		$page_components=array("adm_group_add.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$view->render();
	}
		
	function GroupShow($d) { // 
		$page_components=array("adm_group_show.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		
		$d["groles"]=GP::getRoles($d["grp_id"]);
		$view->set_variables($d);
		
		//GP::LoadId($d["grp_id"]);
		$this->call("system","fetchGroup", $d);
		$view->set_variables($d["group"]);
		
		$view->render();
	}
		
	function GroupUpdate($d) { // 
		$page_components=array("adm_group_updt.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$this->call("system","fetchGroup", $d);
		$view->set_variables($d["group"]);
		$view->render();
	}
		
	function GroupDelete($d) { // 
		$page_components=array("adm_group_del.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		//GP::loadId($d["grp_id"]);
		$this->call("system","fetchGroup", $d);
		$view->set_variables($d["group"]);
		$view->render();
	}
		
	function GroupLinkRoles($d) { // 
		$page_components=array("adm_group_roles_link.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$d["groles"]=GP::getRoles($d["grp_id"]);
		$d["roles"]=RL::getList();
		$view->set_variables($d);
		//GP::loadId($d["grp_id"]);
		$this->call("system","fetchGroup", $d);
		//GP::LoadId($d["grp_id"])
		$view->set_variables($d["group"]);
		$view->render();
	}
		
	function roles($d) { // 
		$page_components=array("adm_roles_list.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$view->render();
	}

	function RoleAdd($d) { // 
		$page_components=array("adm_role_add.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$view->render();
	}
		
	function RoleShow($d) { // 
		$page_components=array("adm_role_show.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$this->call("system","fetchRole", $d);
		$view->set_variables($d["role"]);
		$view->render();
	}
		
	function RoleUpdate($d) { // 
		$page_components=array("adm_role_updt.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$this->call("system","fetchRole", $d);
		$view->set_variables($d["role"]);
		$view->render();
	}
		
	function RoleDelete($d) { // 
		$page_components=array("adm_role_del.html");
		$view=new View_core;
		$view->load_template("admin2.dwt", $page_components);
		$view->set_variables($d);
		$this->call("system","fetchRole", $d);
		$view->set_variables($d["role"]);
		$view->render();
	}
}

$admin=new admin();

?>