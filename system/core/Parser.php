<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Bleetz framework 
 * 
 * Un parser xml spŽcialisŽ pour analyser les balises avec namespace ( NS:balise)
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

/**
 * cards helper class
 * 
 * @author CAO
 *
 */
Class TAG {
	private static $aliases=array();
	private static $namespaces=array();
	private static $parser=null;
	
	static function setParser($parser_i) {
		//ER::collect("e");
		//echo ER::report();
		self::$parser=$parser_i;
	}
	
	/**
	 * 
	 * @param unknown $parser_i
	 */
	static function checkNamespace($namespace) {
		if (!empty(self::$aliases[$namespace])) $namespace=self::$aliases[$namespace];
		//ajouter des methodes de gestion des cartes (CARD:: ou CD:: ou CA::
		if (!file_exists(CRDPATH.$namespace.".php")) { //404 not found
			// a modifier avec string, infos...
			if (DODEBUG) {
				ER::collect("La carte <b>%s</b> n'existe pas.", $namespace);
				echo ER::report();
			}
			exit;
			return false;
		} else {
			//echo $namespace.".php";
			include_once (CRDPATH.$namespace.".php");
		}
		return true;
	}
	
	/**
	 * 
	 * @param unknown $namespace
	 * @param unknown $tag_name
	 * @return unknown
	 */
	static function open($namespace, $tag_name, $attrs) {
		//if (!empty(self::$aliases[$namespace])) $namespace=self::$aliases[$namespace];
/*
		//ajouter des methodes de gestion des cartes (CARD:: ou CD:: ou CA::					
		if (!file_exists(CRDPATH.$namespace.".php")) { //404 not found
			// a modifier avec string, infos...
			if (DODEBUG) {
				ER::collect("La carte <b>%s</b> n'existe pas.", $namespace);
				echo ER::report();
			}
			exit;
		} else {
			include_once (CRDPATH.$namespace.".php");
		}
		*/
		$method=$tag_name."_open";
		//$attrs=$this->parse_attributes($str,$pos2,$endtagpos);

		//$this->data[$this->level]["attrs"]=$attrs;
		$nsclass=$GLOBALS[$namespace];
		if (!method_exists($nsclass,$method)) { // erreur tag inexistant

			if (DODEBUG) {
				ER::collect("La carte <b>%s</b> ne gre pas le tag(open) %s", $namespace, $method );
				echo ER::report();
			}
			//sortie seche pour l'instant en attendant de blinder la gestion d'erreur
			exit;
		} else {
			$compopentag=$nsclass->$method(self::$parser,$attrs);
		}
		return $compopentag;
	}	

	/**
	 * 
	 * @param unknown $namespace
	 * @param unknown $tag_name
	 * @return unknown
	 */
	static function close($namespace, $tag_name) {
		//if (!empty(self::$aliases[$namespace])) $namespace=self::$aliases[$namespace];
		
		$method=$tag_name."_close";
		//ER::collect("e");
		//echo ER::report();
		
		$nsclass=$GLOBALS[$namespace];
		if (!method_exists($nsclass,$method)) { // erreur action inexistante
			if (DODEBUG) {
				ER::collect("La carte <b>%s</b> ne gre pas le tag(close) %s", $tag_name, $method );
				echo ER::report();
			}
			//sortie seche pour l'instant en attendant de blinder la gestion d'erreur
			exit;
		} else {
			$compclosetag= $nsclass->$method(self::$parser);
		}
		return $compclosetag;
	}
	
	static function alias($alias, $tagspace) {
		self::$aliases[$alias]=$tagspace;
	}
}
/**
 * 
 * @author CAO
 *
 */
Class Parser_core {
	var $level=0;
	var $begintext;
	var $endtext;
	var $data;
	var $components;
	var $compiling;
	//external parameters
	var $params;
	
/**
 * 
 * @param unknown $str
 * @param unknown $offset
 * @param unknown $end_offset
 * @return multitype:|multitype:mixed
 */
	function parse_attributes($str, $offset, &$end_offset) {
		$all = array();
		$current_offset=$offset;
		//echo substr($str, $offset+1, 30)."<br/>";
		
		while(1)
		{
			$eq = strpos($str, "=",$current_offset);
			$end = strpos($str, ">",$current_offset);
			if(strlen($str) == 0 || $eq === false || $end<$eq )
			{
				$end_offset= strpos($str, ">",$current_offset);
				return $all;
			};

			$id1 = strpos($str,"\'",$current_offset);
			$id2 = strpos($str,"\"",$current_offset);
			if(($id1 < $id2 && $id1 !== false) || $id2 === false)
			{
				$ids = $id1;
				$id = "\'";
			}
			if(($id2 < $id1 || $id1 === false) && $id2 !== false)
			{
				$ids = $id2;
				$id = "\"";
			}
			$nextid = strpos($str,$id,$ids + 1);
			$val =substr($str, $ids+1, $nextid-$ids-1);
			$scope="global";
			$val2=preg_replace('/{\$(\w+|\w+\[\'\w+\'\]|\w+\["\w+"\])}/','VR::$vr->'.$scope.'->\1',$val);
			$name = strtoupper(trim(substr($str,$current_offset,$eq-$current_offset)));
			//echo $name, $val2;
			//il faut trouver un moyen de conserver la valeur initiale
			$all[$name] = $val2;
			$current_offset=$nextid + 1;
		};
		$end_offset= strpos($str, ">",$current_offset);
		return $all;
	}
	
/**
 * return parsed text 
 * 
 * @param text $textdata
 * @return text
 */
	function parse_text($textdata) {
		//comment gerer les constantes? {CONST}???{C:CONST} {V:var}{F:func} {SERVICE::mmm}
		// $preg_exp ='/
		// $preg_exp.={\$(\w+|\w+\[\'\w+\'\]|\w+\["\w+"\])}';
		// $preg_exp.='|';
		// $preg_exp.='{\$(\w+|\w+\[\'\w+\'\]|\w+\["\w+"\])\/(\w)}';
		// $preg_exp.='|';
		// $preg_exp.='{():(\w+|\w+\[\'\w+\'\]|\w+\["\w+"\])';
		// $preg_exp.='/
		
		preg_match_all('/{\$(\w+|\w+\[\'\w+\'\]|\w+\["\w+"\])}|{\$(\w+|\w+\[\'\w+\'\]|\w+\["\w+"\])\/(\w)}/', $textdata, $match,  PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		//{(\w+):(\w+|\w+\[\'\w+\'\]|\w+\["\w+"\])}
		for ($j=0;$j<sizeof($match[2]);$j++) {
			if (is_array($match[3][$j])) $modchar=$match[3][$j][0];
			else $modchar="";
			$scope="global";
			switch($modchar) {
				//pas de modificateur
				case "":
					$varname=$match[1][$j][0];
					$varmod= "VR::\$vr->$scope->$varname";
					break;
					//minuscule
				case "u" :
					$varname=$match[2][$j][0];
					$varmod="strtoupper(VR::\$vr->$scope->$varname)";
					break;
					//majuscule
				case "l" :
					$varname=$match[2][$j][0];
					$varmod="strtolower(VR::\$vr->$scope->$varname)";
					break;
					//clean of possible php code non implemente
				case "c" :
					$varname=$match[2][$j][0];
					$varmod="strip_tags(VR::\$vr->$scope->$varname)";
					//$varmod="strip_tags(<<<TXT\nVR::\$vr->$scope->$varname\nTXT;\n)";
					break;
					//escape for javascript with "
				case "S" :
					$varname=$match[2][$j][0];
					$varmod="str_replace(\"\\\"\",\"\\\\\\\"\",str_replace(\"\\n\", \"\\\\n\",VR::\$vr->$scope->$varname))";
					break;
					//escape for javascript with '
				case "s" :
					$varname=$match[2][$j][0];
					$varmod="str_replace(\"'\",\"\\'\",str_replace(\"\\n\", \"\\\\n\",VR::\$vr->$scope->$varname)";
					break;
					//escape non iplemente
				case "e" :
					$varname=$match[2][$j][0];
					$varmod="VR::\$vr->$scope->$varname";
					break;
				default:
					//error
					$varname=$match[2][$j][0];
					$varmod="VR::\$vr->$scope->$varname";
			}
			
			$textdata=str_replace($match[0][$j][0], '<?php if (isset(VR::$vr->'.$scope.'->'.$varname.')) echo '.$varmod.'; else /*error*/; ?>', $textdata);
		}
		return $textdata;
	}

	function parse ($str) {
		global $card_path;
		
		TAG::setParser($this);
		
		$this->data["top"]="";
		$this->level=0;
		$this->data[$this->level]=array("compiled"=>"", "otag" =>"", "left"=>"", "right"=>"");
		$this->begintext=0;
		$this->endtext=0;
		//$str = implode("", @file($filename));
		$end_of_str=strlen($str);
		preg_match_all('/<\w*:|<\/\w*:/', $str,$match, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		for ($i=0;$i<sizeof($match[0]);$i++) {
			$this->endtext=$match[0][$i][1];
			$textdata=substr($str,$this->begintext,$this->endtext-$this->begintext);
			
			//$textdata=preg_replace('/{\$(\w+|\w+\[\'\w+\'\]|\w+\["\w+"\])}/','<?php if (isset($this->\1)) echo $this->\1; ? >',$textdata);
			$textdata=$this->parse_text($textdata);
			
			$pos1=$match[0][$i][1]+strlen($match[0][$i][0]);
			$pos2=strpos($str," ",$match[0][$i][1]);
			if ($pos2===false) {
				$pos2=$end_of_str;
			}
			$end = strpos($str, ">",$match[0][$i][1]);
			if ($end<$pos2) {
				$pos2=$end;
			}
			if ($str[$pos2-1]=="/") {
				$pos2=$pos2-1;
			}

			$tag_name=substr($str,$pos1,$pos2-$pos1);
			$namespace=substr($match[0][$i][0],1,strlen($match[0][$i][0])-2);
			//echo ":$namespace:$tag_name:";

			if ($namespace[0]=="/") {
				if ($this->level==0) {
					if (DODEBUG) {
						ER::collect("erreur sur le tag <b>%s</b> dans la page : <b>%s</b>", $tag_name, $this->compiling);
						echo ER::report();
					}
					exit;
				};
				if ($this->data[$this->level]["tag_name"]!=$tag_name) {
					if (DODEBUG) {
						ER::collect("le tag <b>%s</b> est attendu dans la page : <b>%s</b>", $this->data[$this->level]["tag_name"], $this->compiling);
						echo ER::report();
					}
					exit;
				}
				
				$this->data[$this->level]["compiled"].=$textdata;
				//echo htmlentities($textdata);
				/*
				$namespace=substr($namespace,1,strlen($namespace)-1);
				
				$method=$tag_name."_close";
				if (!method_exists($GLOBALS[$namespace],$method)) { // erreur action inexistante
					if (DODEBUG) {
						ER::collect("La carte <b>%s</b> ne gre pas le tag(close) %s", $tag_name, $method );
						echo ER::report();
					}
					//sortie seche pour l'instant en attendant de blinder la gestion d'erreur
					exit;
				} else {
					$compclosetag=$GLOBALS[$namespace]->$method($this);
				}*/
				
				$namespace=substr($namespace,1,strlen($namespace)-1);
				
				TAG::checkNamespace($namespace);
				
				$compclosetag=TAG::close($namespace,$tag_name);
				
				//echo $compclosetag;
				$this->data[$this->level]["ctag"]=$compclosetag;
				$endtagpos=$end;
				$this->data[$this->level-1]["compiled"] .= $this->data[$this->level]["otag"] . $this->data[$this->level]["compiled"] . $this->data[$this->level]["ctag"];

				//echo htmlentities($this->data[$this->level]["otag"] . $this->data[$this->level]["compiled"] . $this->data[$this->level]["ctag"]);

				$this->level--;
			} else {
				$this->data[$this->level]["compiled"].=$textdata;
				$this->level++;
				$this->data[$this->level]=array("compiled"=>"", "otag" =>"", "left"=>"", "right"=>"");
				$this->data[$this->level]["tag_name"]=$tag_name;

				//exit if namespace not present
				TAG::checkNamespace($namespace);
				
				$attrs=$this->parse_attributes($str,$pos2,$endtagpos);
				$this->data[$this->level]["attrs"]=$attrs;
				
				/*
				 * 
				 
				TAG::checkNamespace($namespace);
				//ajouter des methodes de gestion des cartes (CARD:: ou CD:: ou CA::
				if (!file_exists(CRDPATH.$namespace.".php")) { //404 not found
					// a modifier avec string, infos...
					if (DODEBUG) {
						ER::collect("La carte <b>%s</b> n'existe pas.", $namespace);
						echo ER::report();
					}
					exit;
				} else {
					include_once (CRDPATH.$namespace.".php");
				}
				

				/*
				$method=$tag_name."_open";
				$this->data[$this->level]["attrs"]=$attrs;
				if (!method_exists($GLOBALS[$namespace],$method)) { // erreur tag inexistant

					if (DODEBUG) {
						ER::collect("La carte <b>%s</b> ne gre pas le tag(open) %s", $namespace, $method );
						echo ER::report();
					}
					//sortie seche pour l'instant en attendant de blinder la gestion d'erreur
					exit;
				} else {
					$compopentag=$GLOBALS[$namespace]->$method($this,$attrs);
				}*/
				
				$compopentag=TAG::open($namespace, $tag_name,$attrs);

				$this->data[$this->level]["otag"]=$compopentag;
				
				if ($str[$end-1]=="/") {
					$method=$tag_name."_close";
					
					//TAG::close($namespace, $tag_name)			
					$compclosetag=$GLOBALS[$namespace]->$method($this);
					
					$this->data[$this->level]["ctag"]=$compclosetag;					
					$this->data[$this->level-1]["compiled"] .= $this->data[$this->level]["otag"] . $this->data[$this->level]["compiled"] . $this->data[$this->level]["ctag"];
					$this->level--;
				}
				
			}
			$this->begintext=$endtagpos+1;
		}
		//echo $this->data[0]["compiled"];
		$this->endtext=strlen($str);
		$textdata=substr($str,$this->begintext,$this->endtext-$this->begintext);
		//$textdata=preg_replace('/{\$(\w+|\w+\[\'\w+\'\]|\w+\["\w+"\])}/','<?php if (isset($this->\1)) echo $this->\1; ? >',$textdata);
		
		$textdata=$this->parse_text($textdata);
		
		$this->data[0]["compiled"].=$textdata;

		if ($this->level!=0) {
			if (DODEBUG) {
				ER::collect("erreur sur la sequence des tags dans la page : <b>%s</b>",$this->compiling);
				echo ER::report();
			}
			exit;
		}

		return $this->data[0]["compiled"];
	}

}

?>