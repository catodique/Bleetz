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

	/*
	 * Affiche le template
	 */
	function render() {
		///BIG HACK MAMA
		//for variable controller environment
		$this->set_variable("controlleur", Bleetz::$context->controller);
		
		eval ("?>".$this->toeval);
	}

	/**
	 * Renvoie une chaine de caractres avec le template
	 * 
	 * @return string
	 */
	function renderStr() {
		///BIG HACK MAMA
		$this->set_variable("controlleur", Bleetz::$context->controller);
		
		ob_start();
				
		eval ("?>".$this->toeval);
		
		$out = ob_get_contents();
		
		ob_end_clean();
		return $out;
	}

	/**
	 * Charge le templates et l'analyse
	 * a rajouter un parametre de 'namespace' pour les variables
	 * la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
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
   * la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
   *
   * @param  $style : le template 'maitre'
   * @param  $components : les composants du template 'maitre'
   * 
   * @return  void
   */
	function load_template($style, $components) {

		$cached_name= Bleetz::$context->controller."_".Bleetz::$context->action;

		$cached_filename=DCHPATH.$cached_name;
		if (FORCE_COMPILE|!file_exists($cached_filename)) {
			$parser =new Parser_core();
			$compiled=$parser->parse_component_2($style, $components);
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
			$ltime=@filemtime(TPLPATH.$style);
			if ($ltime===false) {
				ER::collect( "The template file %s was cached then destroyed... Check templates.", TPLPATH.$style);
				echo ER::report();
				exit;
			}
			while (list($k,$d)=each($components)) {
				$ttime=filemtime(TPLPATH.$d);
				if ( $ttime>$ltime ) $ltime=$ttime;
			};
			$ctime=filemtime($cached_filename);
			if($ctime<$ltime) {
				$parser = new Parser_core();
				$compiled=$parser->parse_component_2($style, $components);
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
					$compiled=@fread($file, filesize($cached_filename));
					if ($compiled===false) {
						$parser = new Parser_core();
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
					fclose($file);
				};
			}
		}
		$this->toeval=$compiled;
	}

	/**
	 *  Charge une variables dans la vue...
	 *   a rajouter un parametre de 'namespace' pour les variables
	 *   la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 * 
	 * @param string $varname	: le nom de la variable
	 * @param mixed $value		: la valeur a enregistrer
	 * @param string $scope		: le namespce de la variable
	 */
	function set($varname, $value, $scope="") {
		if (!empty($scope)) $varname=$scope."__".$varname;
		$this->$varname=$value;
		// a remplacer
		//VAR::set...
	}
	/**
	 *  Charge une variables dans la vue... synonime de set
	 *   la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 * 
	 * @param string $varname	: le nom de la variable
	 * @param mixed $value		: la valeur a enregistrer
	 * @param string $scope		: le namespece de la variable
	 */
	function set_variable($varname, $value, $scope="") {
		if (!empty($scope)) $varname=$scope."__".$varname;
		//il va falloir changer a, risque de dump...
		//$this->$varname=$value;
		$this->$varname=$value;
	}
	
	/**
   	 * Charge les variables dans la vue...
   	 * la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 * 
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	function set_variables($values, $scope="") {
		if (is_array($values) or is_object($values))
		foreach($values as $k => $val) {
			$this->set($k, $val, $scope);
		} 
	}

	/**
   	 * Charge les variables dans la vue...
   	 * la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 * 
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	function set_variables_utf8_encode($values, $scope="") {
		if (is_array($values))
		foreach($values as $k => $val) {
			$this->set($k, $v, $scope);
		//$this->$k=utf8_encode_if($val);
		};
	}

	/**
	 * Charge les variables dans la vue...
	 * la variable est prŽfixŽe avec le namespace a utiliser notement dans les boucles
	 *
	 * @param array $values : les valeurs a enregistrer
	 * @param string $scope
	 */
	function set_parser_param($name, $value) {
		$this->$varname=$value;
	}
	
}

?>