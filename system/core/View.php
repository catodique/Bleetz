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

/**
 * 
 * @author CAO
 *
 */
Class View_core {
	var $toeval="";
	var $namespace="";
	
	/**
	 * Le constructeur sert uniquement � d�finir le namespace
	 * ie le repertoire sous template qui contient le template
	 * si ce template n'existe pas dans ce repertoire, on va chercher dans le repertoire parent
	 * 
	 * @param null|string $namespace
	 * 
	 * 
	 */
	function __construct($ns="") {
		$this->namespace=$ns;
	}
	
	/*
	 * Affiche le template
	 */
	function render() {
		///BIG HACK MAMA
		//for variable controller environment
		//$this->set_variable("controlleur", Bleetz::$context->controller);
		
		eval ("?>".$this->toeval);
	}

	/**
	 * Renvoie une chaine de caract�res avec le template
	 * 
	 * @return string
	 */
	function renderStr() {
		///BIG HACK MAMA
		//$this->set_variable("controlleur", Bleetz::$context->controller);
		
		ob_start();

		eval ("?>".$this->toeval);
		
		$out = ob_get_contents();
		
		ob_end_clean();
		return $out;
	}

	/**
	 * Charge le templates et l'analyse
	 * a rajouter un parametre de 'namespace' pour les variables
	 * la variable est pr�fix�e avec le namespace a utiliser notement dans les boucles
	 *
	 * @param  $style : le template 'maitre'
	 * @param  $components : les composants du template 'maitre'
	 *
	 * @return  void
	 */
	function load_template_string($string) {
		$parser =new Parser_core();
		$compiled=$parser->parse($string);
		//var_dump($parser);
		//$this->set_variables($parser->extern);
		$this->toeval=$compiled;
	}
	
	/**
	 * Charge le templates et l'analyse
	 * a rajouter un parametre de 'namespace' pour les variables
	 * la variable est pr�fix�e avec le namespace a utiliser notement dans les boucles
	 *
	 * @param  $style : le template 'maitre'
	 * @param  $components : les composants du template 'maitre'
	 *
	 * @return  void
	 */
	function get_filepath($file) {
			$filename=TPLPATH.$this->namespace."/".$file;
			$ltime=@file_exists($filename);
			
			if ($ltime===false) {
				$filename=TPLPATH.$file;
				$ltime=@filemtime($filename);
				if ($ltime===false) $filename=null;
			}
			return $filename;
	}
	
	/**
	 * Compilation des templates
	 *
	 * @param  $style : le template 'maitre'
	 * @param  $components : les composants du template 'maitre'
	 *
	 * @return  void
	 */
	function parse_component ($style, $page_components) {
		$parser =new Parser_core();
		
		//global $script_image_path, $template_image_path, $translate_image_path;
		while  (list ($k,$d)=each($page_components)) {
			$parser->compiling=$d;
			//$tpl_filename=TPLPATH.$this->namespace."/".$d;
			$tpl_filename=$this->get_filepath($d);
			if ($tpl_filename===null) {
				if (DODEBUG) {
					ER::collect("Template file not found <b>%s</b> for action request : <b>%s.%s</b>", TPLPATH.$d, Bleetz::$context->controller,Bleetz::$context->action);
					//what should we do????
					echo ER::report();
				}
				return false;
			}
			//echo $tpl_filename;
			$str = implode("", @file($tpl_filename));
			//echo $str;
				
	
			/*obsolete
			if (TRANSLATE_IMAGE_PATH===true)
				$str=str_replace($template_image_path ,$script_image_path,$str);
			*/
			$parser->parse($str);
				
		}
	
		$parser->compiling=$style;
		$tpl_filename=$this->get_filepath($style);
		//echo $tpl_filename;
		if (($style!="") and ($tpl_filename!==null)) {
			$str = implode("", @file($tpl_filename));
		} else {
			$str='<bleetz:content name="" \/>';
			//echo "str",$str;
			//echo "Template file not found <b>".TPLPATH.$d."</b> for action request : <b>".Bleetz::$context->controller.".".Bleetz::$context->action."</b>";
			//return false;
		}

		//echo $str;
		$parser->page=$parser->parse($str);
		//echo $parser->page;
		return $parser->page;
	}
	
	/**
	 * Charge le templates et l'analyse
	 * a rajouter un parametre de 'namespace' pour les variables
	 * la variable est pr�fix�e avec le namespace a utiliser notement dans les boucles
	 *
	 * @param  $style : le template 'maitre'
	 * @param  $components : les composants du template 'maitre'
	 *
	 * @return  void
	 */
	//should be static
	function check_filemtime($file) {
			$ltime=@filemtime(TPLPATH.$this->namespace."/".$file);
			//echo TPLPATH.$this->namespace."/".$file."<br>";
			//var_dump($ltime);
			if ($ltime===false) {
				$ltime=@filemtime(TPLPATH.$file);
			}
			return $ltime;
	}
	
	/**
   * Charge le templates et l'analyse
   * a rajouter un parametre de 'namespace' pour les variables
   * la variable est pr�fix�e avec le namespace a utiliser notement dans les boucles
   *
   * @param  $style : le template 'maitre'
   * @param  $components : les composants du template 'maitre'
   * 
   * @return  void
   */
	function load_template($style, $components) {

		/*
		 * � revoir sous cette forme...
		 
		 $toeval=get_cached_file();
		 if ($toeval==null) {
		 	$parser =new Parser_core();
			$compiled=$parser->parse_component_2($style, $components);
			if (! ($file = fopen($cached_filename, "w")) ) {
				ER::collect( "Error opening file <b>%s</b> for writing", $cached_filename);
				echo ER::report();
				exit;
			} else {
				fwrite($file, $compiled, strlen($compiled));
				fclose($file);
			}
			
		 }
		 
		 
		 */
		$cached_name= $this->namespace."-".Bleetz::$context->controller."-".Bleetz::$context->action;

		$cached_filename=DCHPATH.$cached_name;
		if (FORCE_COMPILE|!file_exists($cached_filename)) {
			$compiled=$this->parse_component($style, $components);
			//echo DCHPATH.$cached_name;
			if (! ($file = fopen($cached_filename, "w")) ) {
				ER::collect( "Error opening file <b>%s</b> for writing", $cached_filename);
				echo ER::report();
				exit;
			} else {
				fwrite($file, $compiled, strlen($compiled));
				fclose($file);
			}
		} else {
			$ltime=$this->check_filemtime($style);
			//echo $style;
			if ($ltime===false) {
				ER::collect( "The template file %s was cached then destroyed... Check templates.", TPLPATH.$style);
				echo ER::report();
				exit;
			}
			while (list($k,$d)=each($components)) {
				$ttime=$this->check_filemtime($d);
				if ( $ttime>$ltime ) $ltime=$ttime;
			};
			$ctime=filemtime($cached_filename);
			if($ctime<$ltime) {
				//$parser = new Parser_core();
				$compiled=$this->parse_component($style, $components);
				if (! ($file = fopen($cached_filename, "w")) ) {
					ER::collect( "Error opening file <b>%s</b> for writing", $cached_filename);
					echo ER::report();
					exit;
				} else {
					fwrite($file, $compiled, strlen($compiled));
					fclose($file);
				}
			} else {
				if (! ($file = fopen($cached_filename, "r")) ) {
					ER::collect( "Error opening file <b>%s</b> for reading", $cached_filename);
					echo ER::report();
					exit;
				} else {
					//echo $cached_filename;
					$fsize=filesize($cached_filename);
					$compiled=@fread($file, $fsize);
					
					if ($compiled===false||($fsize==0)) {
						$parser = new Parser_core();
						$compiled=$this->parse_component($style, $components);
						if (! ($file = fopen($cached_filename, "w")) ) {
							ER::collect( "Error opening file <b>%s</b> for writing", $cached_filename);
							echo ER::report();
							exit;
						} else {
							fwrite($file, $compiled, strlen($compiled));
							//fclose($file);
						}
					}
					fclose($file);
				};
			}
		}
		$this->toeval=$compiled;
	}

	/**
	 *  Charge une variables dans la vue...
	 *   a rajouter un parametre de 'namespace' pour les variables
	 *   la variable est pr�fix�e avec le namespace a utiliser notement dans les boucles
	 * 
	 * @param string $varname	: le nom de la variable
	 * @param mixed $value		: la valeur a enregistrer
	 * @param string $scope		: le namespce de la variable
	 */
	function set($varname, $value, $scope="") {
		VR::set_variable($varname, $value, $scope);
	}
	/**
	 *  Charge une variables dans la vue... synonime de set
	 *   la variable est pr�fix�e avec le namespace a utiliser notement dans les boucles
	 * 
	 * @param string $varname	: le nom de la variable
	 * @param mixed $value		: la valeur a enregistrer
	 * @param string $scope		: le namespece de la variable
	 */
	function set_variable($varname, $value, $scope="") {
		VR::set_variable($varname, $value, $scope);
	}
	
	/**
   	 * Charge les variables dans la vue...
   	 * la variable est pr�fix�e avec le namespace a utiliser notement dans les boucles
	 * 
	 * @param array $values : les paires (nom, valeur) a enregistrer
	 * @param string $scope
	 */
	function set_variables($values, $scope="") {
		VR::set_variables( $values, $scope);
	}

	/**
   	 * Charge les variables dans la vue...
   	 * la variable est pr�fix�e avec le namespace a utiliser notement dans les boucles
	 * 
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	function set_variables_utf8_encode($values, $scope="") {
		VR::set_variables_utf8_encode( $values, $scope);
	}

	/**
	 * Charge les variables dans la vue...
	 * la variable est pr�fix�e avec le namespace a utiliser notement dans les boucles
	 *
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	/*
	function set_parser_param($name, $value) {
		$this->$varname=$value;
	}
	*/
	
}

?>