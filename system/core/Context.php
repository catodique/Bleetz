<?php defined('SYSPATH') OR die('No direct access allowed.');
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

function ob_file_callback($buffer)
{
  global $monCache;
  fwrite($monCache,$buffer);
  echo $buffer;
}

Class Context_core {
	//
	var $client_path_info;
	//
	var $sys_form_vars;
	//User logged in
	var $user;
	// running controller
	var $controller;
	// running action
	var $action;
	// actions stack
	var $actions;

	/**
	 * Analyse URL in order to extract actions.
	 *
	 * @return  void
	 */
	function compile_url($url) {
		$path=substr($url,1,strlen($url));
		$action_strings=explode("-",$path);

		$actions=array();
		$last_controller="";
		while (list($k,$v) = each($action_strings)) {
			//echo $v;

			//echo $controller.$action."<br>";
			if ($v!="") {
				$case_values=explode("=",$v);
				if (isset($case_values[1])) {
					$v2=$case_values[0];
					$action_string=explode(".",$v2);
					$controller="";
					$action="";
					if (isset($action_string[1])) {
						$controller=$action_string[0];
						$last_controller=$controller;
						$action=$action_string[1];
					} else if (isset($action_string[0])) {
						if ($last_controller!="") {
							$action=$action_string[0];
							$controller=$last_controller;
						}
					};
					$action_item=array("controller"=>$controller,"action"=>strtolower($action), "case_items"=>0);
					$case_default_controller=$controller;
					$case_items=explode("*",$case_values[1]);
					$nbr_case=0;
					while (list($k,$v) = each($case_items)) {
						$nbr_case++;
						$action_string=explode(".",$v);
						$case_controller="";
						$case_action="";
						if (isset($action_string[1])) {
							$case_controller=$action_string[0];
							$case_action=$action_string[1];
						} else if (isset($action_string[0])) {
							if ($case_default_controller!="") {
								$case_action=$action_string[0];
								$case_controller=$case_default_controller;
							}
						};

						$action_item[]=array("controller"=>$case_controller,"action"=>strtolower($case_action));
					}
					$action_item["case_items"]=$nbr_case;
					$actions[]=$action_item;
					//verification des permissions du controlleur
					//verification des permissions de l'action
				} else {
					$action_string=explode(".",$v);
					$controller="";
					$action="";
					if (isset($action_string[1])) {
						$controller=$action_string[0];
						$last_controller=$controller;
						$action=$action_string[1];
					} else if (isset($action_string[0])) {
						if ($last_controller!="") {
							$action=$action_string[0];
							$controller=$last_controller;
						} else continue;
					};
					$actions[]=array("controller"=>$controller,"action"=>strtolower($action), "case_items"=>0);
					//verification des permissions du controlleur
					//verification des permissions de l'action
				}
			}
		}

		return $actions;
	}

	/**
	 * validate actions.
	 * VŽrifie que les controlleurs sont bien chargŽs
	 *
	 * @return  true if ok
	 */
	function validate() {
		//$done=array();
		reset($this->actions);
		while (list($k,$v) = each($this->actions)) {
			// v = array( controller, action)
			// achanger par v = controller.method
			$controller=$v["controller"];
			$action=$v["action"];
			if (!CT::validate_action($controller.".".$action)) 
				return false;
			/*
			if (!isset($this->$controller)) {

				if (Bleetz::load_controller($controller)===false) return false;

				$action=$v["action"];
				if (!method_exists($this->$controller,$action)) { // erreur action inexistante
					if (DODEBUG) {
						ER::collect("action not defined %s in controller %s", $action, $controller);
						//echo ER::report();
					}
					return false;
				}
			}*/

			//$done[$controller]=true;
			if ($v["case_items"]>0) {
				for ($item=0; $item<$v["case_items"]; $item++) {
					$v2=$v[$item];
					
					$controller=$v2["controller"];
					$action=$v2["action"];
					if (!CT::validate_action($controller.".".$action)) 
						return false;
					/*
					if (!isset($this->$controller)) {

						if (Bleetz::load_controller($controller)===false) return false;

						$action=$v2["action"];
						if (!method_exists($this->$controller,$action)) { // erreur action inexistante
							if (DODEBUG) {
								ER::collect("action not defined %s in controller %s", $action, $controller);
								//echo ER::report();
							}
							return false;
						}
					}
					*/
				}
			}

		}
		return true;
	}

	/**
	 * authorize actions.
	 *
	 * @return true if ok
	 */
	function authorize() {
		if ((US::$user->auth["usr_login"]==SYS_USER_ROOT_NAME)&&(US::$user->auth["grp_name"]==SYS_USER_ROOT_GROUP)&&(US::$user->auth["usr_id"]==SYS_USER_ROOT_ID) ) 
			return true;
		reset($this->actions);
		while (list($k,$v) = each($this->actions)) {
			$controller=$v["controller"];
			//ER::collect("");
			//echo ER::report();
			if ($this->$controller->authorize( $v["action"])===false) return false;
			if ($v["case_items"]>0) {
				for ($item=0; $item<$v["case_items"]; $item++) {
					$controller=$v[$item]["controller"];
					if ($this->$controller->authorize($v[$item]["action"])===false) return false;
				}
			}
		}
		return true;
	}

	/**
	 * Initialise application, gather information, analyse URL for actions.
	 *
	 * @return  void
	 */
	function open() {
		//recuperation de la page demande
		if (!empty($this->client_path_info)) {
			$actions=$this->compile_url($this->client_path_info);
			if (sizeof($actions)==0) { // page par defaut du site
				//a remplacer par l'index utilisateur
				//BASE_URL
				$url=BASE_URL.MAIN.".".INDEX;
				URL::redirect($url);
			}
			$this->actions=$actions;
		} else { //page par defaut du site
			//a remplacer par l'index utilisateur
			$url=BASE_URL.MAIN.".".INDEX;
			URL::redirect($url);
		}

		if ($this->validate()) {
		//if (Bleetz::config["Auth"]===true) {//TODO
			if (true) {

			US::checkpoint();

			if (!$this->authorize()) {
				//perform login
				//redirect to login page...
				//US::login...
				//URL::login...
				header('HTTP/1.1 401 Unauthorized');
				//ou page de demande de connection ou retour
				// exemple : vous n'avez pas accs ˆ la page demandŽe
				// connectez vous....
				URL::redirect("admin.login");
			};

			}
		} else {
			$this->actions=array();
			//par la suite
			//BZ::E404
			if (!CT::validate_action(E404)) {
				ER::Report();
				exit;
			}
			$ar=explode('.',E404);
			$controller=$ar[0];
			$action=$ar[1];
			$this->actions[]=array("controller"=>$controller, "action"=>$action, "case_items"=>0 );
/*
			if (!isset($this->admin)) {
				//BZ::LoadController("admin");
				echo CTLPATH;
				require_once CTLPATH."admin.php";
				$this->admin=$admin;			//
			}
*/
		}
	}

	/**
	 * run an action.
	 *
	 * @return whatever the action return
	 */
	function run_action($action ) { //controller, $action) {
		$this->controller=$action["controller"];
		$controller=$this->controller;
		$this->action=$action["action"];
		$action=$this->action;

		//run action
		$retval = $this->$controller->$action($this->sys_form_vars);
		
		DB::release_all();

		return $retval;
	}

	/**
	 * Execute URL.
	 *
	 * @return  void
	 */
	function run() {
		global $use_static_cache, $monCache;

		///static cache management....
		if ($use_static_cache) {
			//nommage page
			$static_naming="";
			reset($this->actions);
			while (list($k,$v) = each($this->actions)) {
				$controller=$v["controller"];
				$cache=$this->$controller->security[$v["action"]]["cache"];
				if ($cache!='0') {
					//manque le param
					$static_naming=$v["controller"]."_".$v["action"];

					$params_names=explode(",",$this->$controller->security[$v["action"]]["params"]);
					while (list($n,$nv) = each($params_names)) {
						if (isset($this->sys_form_vars[$nv])) $static_naming.="_".$nv."-".$this->sys_form_vars[$nv];
					}
					break;
				}
			}

			//verification si statique
			if ($static_naming!=="") {
				$force_cache="";
				if (isset($this->sys_form_vars["regen"])) $force_cache=$this->sys_form_vars["regen"];

				$save_cache=$force_cache==="1";

				$static_name=SCHPATH.$static_naming;
				// verification du cache
				if (!file_exists($static_name)) {
					$save_cache=true;
				} else {
					$ftime=filemtime($static_name);
					$ctime=time();
					$delay=$ctime-$ftime;
					if ($delay>$cache) $save_cache=true;
				}

				//chargement si verif ok...
				if ($save_cache) {
					$monCache = fopen($static_name,"w");
					ob_start('ob_file_callback');
				} else {
					//get cache
					//echo "fromcache";
					if (! ($monCache = fopen($static_name, "r")) ) {
							ER::collect( "Error opening file <b>%s</b> for reading", $static_name );
							echo ER::report();
							exit;
							//todo replace by
							//return false;
					};

					echo fread($monCache, filesize($static_name));
					return true;
				};
			}

		}

		reset($this->actions);
		while (list($k,$v) = each($this->actions)) {

			$todo=$this->run_action($v);//["controller"], $v["action"]);
			//echo "ddd",$todo;
			if ($v["case_items"]>0) {
				if ($todo===true) $item=0;
				else if ($todo===false) $item=1;
				else $item=$todo;
				//echo "rt",$item;
				$this->run_action($v[$item]);//["controller"], $v[$item]["action"]);

			}
		}

		///static cache management....
		if ($use_static_cache) {
			if ($static_naming!=="") {
				ob_end_flush();
				fclose($monCache);
				if (! ($monCache = fopen($static_name, "r")) ) {
						ER::collect( "Error opening file %s for reading", $static_name);
						ER::report();
						exit;
				};
				echo fread($monCache, filesize($static_name));
			}
		}
		
		return $todo;
	}

	/**
	 * Cleanup.
	 *
	 * @return void
	 */
	function close() {
		//Auth:sleep();
	}


}

class CT {
	static $context;
	
	static function validate_action($action) {
		//echo $action;
		$ar=explode('.',$action);
		$controller=$ar[0];
		$method=$ar[1];
		if (!isset(CT::$context->$controller)) {
			//CT::load_controller???
			// deplacer le chargement du controleur sur CT...
			if (Bleetz::load_controller($controller)===false) {
				ER::collect("controller not defined %s !", $controller);
				//E404???
				return false;
			}
		}
		if (!method_exists($controller,$method)) { // erreur action inexistante
			if (DODEBUG) {
				ER::collect("action not defined %s in controller %s", $method, $controller);
				//E404???
				//bloquer ou non?
				//non on s'en charge aprs
				//echo ER::report();
			}
			return false;
			return null;
			//return $null_action ou NULL_ACTION
		}
		
		//return 
		return true;
		$action =new Action();
		$action->controller=$controller;
		$action->method=$method;
		return $action;
		//return action;
	}
	
	static function call($controller, $action, &$d) { // private
	
		if (!CT::validate_action($controller.".".$action)) {
			echo ER::report();
			//decider de la pertinance de cet exit
			// dans certain cas cet exit ne devrait pas se faire
			// return false ou encore parametre ou encore faire plusierus fonction
			// call, force_call, check_call ˆ voir
			// ou eventuellement mettre une variable contextuelle qui modifie ce comportement
			// CT:exitoncall ou quelque chose comme a...
			exit;
		}
		//authorize???
		//par la suite CT::validate_action renvoie un object de classe Action
		if (!method_exists($controller,$action)) { // erreur action inexistante
			if (DODEBUG) {
				ER::collect("action not defined %s in controller %s", $action, $controller);
			}
			return false;
		}
		//CT::$context
		return Bleetz::$context->$controller->$action($d);
	
	}
	//static function forceCall($controller, $action, &$d) { // private
	
}

?>