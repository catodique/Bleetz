<?php defined('BLEETZ') OR die('No direct access allowed.');
/**
 * Bleetz framework
 *
 * Controller core library.
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

Class Controller_core {
	var $syserror;
	/**
	 * Permissions par action
	 *
	 * @return  void
	*/
	var $security=array();
	
	/**
	 * Init
	 *
	 * @return  void
	*/
	function Controller_core() {
		//if (Bleetz::config["Auth"]===true) {
		if (true) {
			$this->load_armor();
		}
	}

	/**
	 * Verify permission on an action
	 *
	 * @return  void
	 */
	function authorize($action) {
		//if (Bleetz::config["Auth"]===true) {
		//echo  $action;
		$user=US::$user;

		if (!isset($this->security[$action])) {
			if (DODEBUG) {
				//ACHANGER
				ER::collect("private action %s",$action);
				echo ER::report();
			}
			return false;
		} else {
			switch ($this->security[$action]["user"]) {
			//un utilisateur quelconque mais pas le guest
			case "?" :
				if ($user->auth["usr_login"]==="guest") return false;
				break;
			//un utilisateur quelconque mme le guest
			case "*" : break;
			default : 
				if ($user->auth["usr_login"]!==$this->security[$action]["user"]) return false;
			}
			//echo "ok user";
			
			switch ($this->security[$action]["group"]) {
			//un groupe quelconque 
			case "*" : break;
			default : 
				if (empty($user->auth["grp_name"])) return false;
				$ug=$user->auth["grp_name"].",";
				$sg=$this->security[$action]["group"].",";
				if (strpos($sg,$ug)===false) return false;
				//echo strpos($sg,$ug);
				//echo $action,$ug,$sg;
				//exit;
			}
			//echo "ok group";
			switch ($this->security[$action]["role"]) {
			case "?" :
				if ($user->auth["rle_name"]==="guest") return false;
				break;
			case "*" : break;
			default : 
			//echo $user->auth["usr_group"].",",$this->security[$action]["group"];
			//pas bon
			// a faire avec deux sets....
				$group_ok=strpos($user->auth["rle_name"].",",$this->security[$action]["role"]);
				if ($group_ok===false) return false;
			}
			//echo "ok role";
		};
		return True;
	}
	
	/**
	 * compile armor code for security and static caching
	 *
	 * @return php code for armor
	 */
	 function compile_armor($class_file) {

		$security="<?php defined('SYSPATH') OR die('No direct access allowed.');\n\n";;
		$security.='$security'." = array (\n";
		$class_text=file_get_contents($class_file);
		if ($class_text ===false ) {
		 	ER::collect( "Error opening file <b>%s</b> for reading", $class_file);
		 	echo ER::report();
		 	//exit;
		} else {
			//armure de classe
			preg_match('/\s*class\s*.*\{\s*(.*)\s/i', $class_text, $match);
			$controller_security=array ("user"=>"*", "group"=>"*", "role"=>"*", "type"=>"", "mode"=>"pgcsi", "cache"=>"0", "validate"=>"0", "required"=>"");
			preg_match_all('/(\w+)\s*=\s*([\w\*\?,]*);/', $match[1], $match, PREG_SET_ORDER);
			//var_dump($match);
			$sm1=sizeof($match);
			for ($j=0; $j<$sm1;$j++) {
				$controller_security[strtolower($match[$j][1])]=$match[$j][2];
			}

			//armure de fonction
			preg_match_all('/function\s*(\w+)\s*\((.*)\)\s*{(.*)\s/i', $class_text,$match, PREG_SET_ORDER);
			$sm1=sizeof($match);
			for ($i=0;$i<$sm1;$i++) {
				if ( preg_match("/\/\/\s*private\s*/",$match[$i][3])===0) {
				$security.="'".strtolower($match[$i][1])."' => array( ";
				preg_match_all('/(\w+)\s*=\s*([\w\*]*);/', $match[$i][3], $match2, PREG_SET_ORDER);
				$function_security=$controller_security;
				$sm2=sizeof($match2);
				for ($j=0; $j<$sm2;$j++) {
					$function_security[strtolower($match2[$j][1])]=$match2[$j][2];
				}
				//user : user acl
				//group : group acl
				//role :role acl
				//type : r: read; w: write; v : view // not important
				//mode : p: post; g : get; c : ???;s: session; i: ????/// authorized var
				//cache : static cache or not???
				//params : ?????
				//validate : validate data.... used with a validation array
				//required: list of fields // required data....
				
				foreach ( array ("user", "group", "role", "type", "mode", "cache", "validate", "required") as $kval) {
					$security.="'".$kval."'=>'".$function_security[$kval]."'";
					if ($kval!=="param") $security.=",";
				}
				$security.=")";
				if ($i+1<$sm1) $security.=",";
				$security.="\n";
				}
			}
		}
		$security.=")\n\n?>";
		
		return $security;
	}
	
	/**
	 * Loading armor code for security
	 * compile and write armor file if necessary
	 *
	 * @return  void
	*/
	function load_armor() {
		$controller_name=get_class($this);
		$security_file=ARMPATH.$controller_name.".php";
		$force_armor=false;
		if ($force_armor|!file_exists($security_file)) {
			if (! ($file = fopen($security_file, "w")) ) {
	 			ER::collect("Error opening file <b>%s</b> for writing",$security_file);
	 			echo ER::report();
	 			exit;
			} else {
				$class_file=CTLPATH.$controller_name.".php";
				$security=$this->compile_armor($class_file);
				fwrite($file, $security, strlen($security));
				fclose($file);
			}
		} else {
			$stime=filemtime($security_file);
			$class_file=CTLPATH.$controller_name.".php";
			$ctime=filemtime($class_file);
			if($stime<$ctime)
	 		if (! ($file = fopen($security_file, "w")) ) {
	 			ER::collect("Error opening file <b>%s</b> for writing",$security_file);
	 			echo ER::report();
	 			exit;
	 		} else {
				$class_file=CTLPATH.$controller_name.".php";
				$security=$this->compile_armor($class_file);
				fwrite($file, $security, strlen($security));
				fclose($file);
			}
		}
		require_once($security_file);
		$this->security=$security;
	}
	
	function call($controller, $action, &$d) { // private

		if (!method_exists($controller,$action)) { // erreur action inexistante
			if (DODEBUG) {
				ER::collect("action not defined %s in controller %s", $action, $controller);
			}
			return false;
		}
		return Bleetz::$context->$controller->$action($d);
	
	}
	
}
  
?>