<?php defined('SYSPATH') OR die('No direct access allowed.');

class form_compiler {

  	function select_open($parser,$attrs) {
	$array=$attrs["ARRAY"];
	$value=$attrs["VALUE"];
	$text=$attrs["TEXT"];
	$comp='<select name="'.$attrs["NAME"].'" >';
	$comp.='<?php 
	';
	$comp.='reset('.$array.');
	';
	$comp.='while (list($k,$v) = each('.$array.')) { ?>';
	$comp.='  <option value="<?php echo $v["'.$value.'"]; ?>" ';
	$comp.='<?php if (isset($this->'.$attrs["NAME"].')) if ($v["'.$value.'"]==$this->'.$attrs["NAME"].') echo "selected"; ?>';
	$comp.='><?php echo $v["'.$text.'"]; ?></option>';
	$comp.="<?php } ?>";
	$comp.="</select >";
	return $comp;
	}

  	function select_close($parser) {
	$comp="";
	return $comp;
	}
 
 }

 $GLOBALS["form"]=new form_compiler;
 
?>