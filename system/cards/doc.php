<?php defined('SYSPATH') OR die('No direct access allowed.');

class doc_compiler {

  	function doc_open($parser,$attrs) {
  		$compiled="";
  		return $compiled;
  	}

  	function doc_close($parser) {
		$compiled="";
		return $compiled;
	}
 
  	function head_open($parser,$attrs) {
  		$compiled="";
		return $compiled;
  	}

  	function head_close($parser) {
  		$compiled="";
		return $compiled;
  	}

  	function chapter_open($parser,$attrs) {
		$compiled="";
	  	if (isset($attrs["TITLE"])) {
			$title=$attrs["TITLE"];
			$compiled.='<h2>'.$title.'</h2>';
	  	}
	  	return $compiled;
	}

  	function chapter_close($parser) {
		$compiled="";
		return $compiled;
  	}

 	function section_open($parser,$attrs) {
 		$compiled="";
 			
		if (isset($attrs["TITLE"])) {
			$title=$attrs["TITLE"];
			$compiled.='<h3>'.$title.'</h3>';
	  	}
  		return $compiled;
  	}
  	
  	function section_close($parser) {
  		$compiled="";
  		
  		return $compiled;
  	}
  	 
 	function para_open($parser,$attrs) {
		$compiled="<p>";
   		return $compiled;
  	}
  	
  	function para_close($parser) {
  		$compiled="</p>";
  		return $compiled;
  	}
  	 
  	function source_open($parser,$attrs) {
  		$compiled="<pre class=\"php\">";
  		return $compiled;
  	}
  	
  	function source_close($parser) {
  		$compiled="</pre>";
  		return $compiled;
  	}
  	 
  	function code_open($parser,$attrs) {
  		$compiled="<pre class=\"php\">";
  		return $compiled;
  	}
  	
  	function code_close($parser) {
  		$compiled="</pre>";
  		return $compiled;
  	}
  	 
}

$GLOBALS["doc"]=new doc_compiler;
 
?>