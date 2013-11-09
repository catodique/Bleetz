<?php defined('SYSPATH') OR die('No direct access allowed.');

class eco_compiler {

  	function image_open($parser,$attrs) {
		$compiled="";
		//echo "eco:image";
		if (isset($attrs["NAME"])) {
		//echo "yes eco:image";
			$name=$attrs["NAME"];
			//$parser->extern["eco"][]=array("name"=>$name, "type"=>RSCTYPE_FILE_IMAGE);
			//var_dump($parser);
			
			if (isset($attrs["WIDTH"])) $width="width:".$attrs["WIDTH"]."px;";
			else $width="width:auto;";
			if (isset($attrs["HEIGHT"])) $height="height:".$attrs["HEIGHT"]."px;";
			else $height="height:auto;";
			$color="background-color:#099;";
			
			//$edit_mode=$parser->extern["eco"]["edit"];
			$edit_mode=true;
			
			if ($edit_mode) 
				$compiled.='<div style="'.$width.$height.$color.'display:inline-block;" onclick="openFileBox(\''.$name.'\')">';
			$compiled.='<?php $r=RS::Load(VR::$vr->global->obj_id, "'.$name.'"); if ($r===null) $r=RS::Load(@VR::$vr->global->prt_obj_id, "'.$name.'"); ?>';
			//$compiled.='<image src="<?php echo "../images/".$this->obj_controller."/o_".$this->obj_id."/".$this->obj_nature."/'.$name.'.png"; ? >" />';
			
			$compiled.='<image src="<?php echo "../".$r->file_path; ?>" />';
			if ($edit_mode) 
				$compiled.="</div>";
		}
		return $compiled;
	}

  	function image_close($parser) {
		$compiled="";
		$parser->data[$parser->level]["compiled"]="";
		return $compiled;
	}
 
  	function text_open($parser,$attrs) {
		$compiled="";
		if (isset($attrs["NAME"])) {
			$name=$attrs["NAME"];
			//$edit_mode=$parser->extern["eco"]["edit"];
			$edit_mode=true;
			//$parser->extern["eco"][]=array("name"=>$name, "type"=>RSCTYPE_TEXT);
			$color="background-color:#990;";
			if ($edit_mode)
				$compiled.='<div style="'.$color.'" onclick="openTextBox(\''.$name.'\')">';
			$compiled.='<?php $r=RS::Load(VR::$vr->global->obj_id, "'.$name.'"); if ($r===null) $r=RS::Load(@VR::$vr->global->prt_obj_id, "'.$name.'");';
			$compiled.='if (!($r===null)) {  echo $r->text_data;  } else echo "text<br/>empty"; ?> ';
			if ($edit_mode)
				$compiled.="</div>";
		}
		return $compiled;
  	}

  	function text_close($parser) {
		$compiled="";
		$parser->data[$parser->level]["compiled"]="";
		return $compiled;
  	}

  	function field_open($parser,$attrs) {
		$compiled="";
	  	if (isset($attrs["NAME"])) {
			$name=$attrs["NAME"];

			$edit_mode=false;
			if (isset($attrs["EDIT"])) $edit_mode=($attrs["EDIT"]=="true");
			//if ($edit_mode) $edit_mode=$parser->extern["eco"]["edit"];
			
			$color="background-color:#990;";
			if ($edit_mode)
				$compiled.='<div style="'.$color.'" onclick="openFieldBox(\''.$name.'\')">';
			$compiled.='<?php if (!empty(VR::$vr->global->'.$name.')) echo VR::$vr->global->'.$name.'; else echo "field<br/>empty";?> ';
			//$compiled.='<?php echo @$this->'.$name.'; ? > ';
			if ($edit_mode)
				$compiled.="</div>";
	  	}
	  	return $compiled;
	}

  	function field_close($parser) {
		$compiled="";
		$parser->data[$parser->level]["compiled"]="";
		return $compiled;
  	}

  	function qrcode_open($parser,$attrs) {
  		$compiled="";
  		return $compiled;
  	}
  	
  	function qrcode_close($parser) {
  		$compiled="";
  		return $compiled;
  	}
  	 
}

 $GLOBALS["eco"]=new eco_compiler;
 
?>