<?php defined('SYSPATH') OR die('No direct access allowed.');

class bleetz_compiler {

  	function block_open($parser,$attrs) {
	$level=$parser->level;
	$comment="";
	if (isset($attrs["NAME"])) $comment=$attrs["NAME"];
	$mode="";
	if (isset($attrs["MODE"])) $mode=$attrs["MODE"];
	$user="*";
	if (isset($attrs["USER"])) $user=$attrs["USER"];
	$group="*";
	if (isset($attrs["GROUP"])) $group=$attrs["GROUP"];
	$level=0;
	if (isset($attrs["ROLE"])) $role=$attrs["ROLE"];
	$comp="";
	/*
	switch ($type) {
	case "hidden" :;
	case "login" :;
	//if ($this->open_door($user,$group,$level))
	default:;
	};
	*/
	return $comp;
	}

  	function block_close($parser) {
	$level=$parser->level;
	$comp="";
	if (isset($parser->data[$level]["attrs"]["NAME"])) {
		$content=$parser->data[$level]["attrs"]["NAME"];
		$comment=@$parser->data[$level]["attrs"]["COMMENT"];
		//echo $comment;
		if ($comment!="") 
			$parser->components[$content]="<!-- BEGIN BLOCK $level : $content $comment -->".$parser->data[$level]["compiled"]."<!-- END BLOCK $level : $content -->";
		else 
			$parser->components[$content]=$parser->data[$level]["compiled"];
	}
	return $comp;
	}
 
  	function content_open($parser,$attrs) {
	$content="";
	if (isset($attrs["NAME"])) {
		$content=$attrs["NAME"];
		if (isset($parser->components[$content])) { 
			$textdata=$parser->components[$content];
		}
	}
	if (isset($attrs["HTMLCODE"])) {
		$textdata=$attrs["HTMLCODE"];
	}
	$compiled="";
	if (!empty($textdata)) { 
		//$textdata=$parser->components[$content];
		
		$modchar=@$attrs["MODE"];
		
		//boucle sur la chaine de modificateurs
		// for $modchar[$i] etc
		switch($modchar) {
			case "":
				$compiled=$textdata;
				break;
				//minuscule
			case "u" :
				$compiled=strtolower($textdata);
				break;
				//majuscule
			case "l" :
				$compiled=strtoupper($textdata);
				break;
				//clean of possible php code
			case "c" :
				//todo , do nothing just now
				$compiled=$textdata;
				break;
				//decode
			case "d" :
				$compiled=html_entity_decode($textdata);
				break;
				//escape for javascript with "
			case "S" :
				$compiled=str_replace("\"","\\\"",str_replace("\n", "",$textdata));
				break;
				//escape for javascript with '
			case "s" :
				$compiled=str_replace("'","\'",str_replace("\n", "",$textdata));
				break;
				//escape
			case "e" :
				//todo , do nothing just now
				$compiled=$textdata;
				break;
			default:
				//error
				$compiled=$textdata;
		}
		
	};
	return $compiled;
	}

  	function content_close($parser) {
	$compiled="";
	$parser->data[$parser->level]["compiled"]="";
	return $compiled;
  	}

   	function script_open($parser,$attrs) {
		$comp = "<?php \n";
		return $comp;
	}

  	function script_close($parser) {
		$comp = "\n?>";
		return $comp;
	}
	
  	function echo_open($parser,$attrs) {
	$comp="<?php echo \"";
	if (isset($attrs["TEXT"])) {
		$text=$attrs["TEXT"];
		$text=str_replace("$", "\$this->", $text);
		$comp.=$text;
	};
	$comp.="\";\n ?>";
	return $comp;
	}

  	function echo_close($parser) {
	$comp="";
	return $comp;
	}

 	function else_open($parser,$attrs) {
	return "<?php }else { ?>";
	}

  	function else_close($parser) {
	return "";
	}

   	function if_open($parser,$attrs) {
		$evalstr=$attrs["EVAL"];
		preg_replace('/{\$(\w+)}/','$this->\1',$evalstr);
		if (!empty($evalstr)) $comp = "<?php if (".$evalstr.") { ?>\n";
		else {
			//???
			ER::collect("La condition est vide");
			$comp="<?php { ?>\n";
		}
		return $comp;
	}

  	function if_close($parser) {
		$comp = "<?php } ?>\n";
		return $comp;
	}

	function debug_open($parser,$attrs) {
		$comp = "<?php if (DODEBUG) { ?>\n";
		return $comp;
	}
	
	function debug_close($parser) {
		$comp = "<?php } ?>\n";
		return $comp;
	}
 
	function list_open($parser,$attrs) {
		//ROADMAP
		//rajouter un attribut namespace
		// servira a enregistrer les variables de tableau dans 
		// un namespace
		$scope="";
		$tocompile=false;
		if (isset($attrs["SCOPE"])) $scope=$attrs["SCOPE"];
		//il faut vŽrifier que mon array est bien une variable...
		if (isset($attrs["ARRAY"])) {
			$array=$attrs["ARRAY"];
			if ($array[0]=="$") $tocompile=true;
		}
		$index=@$attrs["INDEX"];
   		$toindex=!empty($index);
		if ($tocompile) {
		//preg_replace('/{\$(\w+)}/','$this->\1',$attrs["ARRAY"]);
			$comp = "<?php \n";
			if ($toindex) {
				$comp.= "$index=0;\n";
			}
			$comp.="if (isset($array)) {\n";
			$comp.="reset($array);\n";
			$comp.="while (list(\$k,\$v) = each($array)) {\n";
			$comp.="\$this->set_variables(\$v,'$scope'); ?>\n";
		} else {
			$comp = "<?php \n{\n{ ?>";
		}
		return $comp;
	}
	
   	function list_close($parser) {
		$comp = "<?php \n";
		$index=@$parser->data[$parser->level]["attrs"]["INDEX"];
   		$toindex=!empty($index);
   		if ($toindex) {
			$comp.= "$index++;\n";
		}
   		$comp.= " \n};\n}\n ?>\n";
		return $comp;
	}

   	function signature_open($parser,$attrs) {
		$comp = "";//pagesignature... todo
		return $comp;
	}

  	function signature_close($parser) {
		$comp = "";
		return $comp;
	}
	
	function error_open($parser,$attrs) {
				
		$modchar=@$attrs["MODE"];
		
		//boucle sur la chaine de modificateurs
		// for $modchar[$i] etc
		switch($modchar) {
			case "":
				$compiled='ER::report()';
				break;
				//minuscule
			case "u" :
				$compiled='strtolower(ER::report())';
				break;
				//majuscule
			case "l" :
				$compiled='strtoupper(ER::report())';
				break;
				//clean of possible php code
			case "c" :
				//todo , do nothing just now
				$compiled='ER::report()';
				break;
				//escape for javascript with "
			case "S" :
				$compiled='str_replace("\"","\\\"",str_replace("\n", "",ER::report()))';
				break;
				//escape for javascript with '
			case "s" :
				$compiled='addcslashes(ER::report(), "\'\n\r\\\")';
				//$compiled='str_replace("\n", "",(ER::report()))';
				//$compiled='str_replace("\'","\\\'",str_replace("\n", "",stripslashes( ER::report()) ))';
						//str_replace("\\\\", "\\\\\\\\",ER::report())))';
				break;
				//escape
			case "e" :
				//todo , do nothing just now
				$compiled='ER::report()';
				break;
			default:
				//error
				$compiled='ER::report()';
		}
		$compiled="<?php echo $compiled;?>";
		return $compiled;
	}
	
	function error_close($parser) {
		$comp = "";
		return $comp;
	}
	
}

 $GLOBALS["bleetz"]=new bleetz_compiler;
 
?>