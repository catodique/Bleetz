<?php defined('SYSPATH') OR die('No direct access allowed.');

class cal_compiler {

  	function loop_open($parser,$attrs) {
	$level=$parser->level;
	$comp ="<?php { ?>";
	$type="";
	if (isset($attrs["TYPE"])) $type=$attrs["TYPE"];
	$date='date("Y-m-d",time())';
	if (isset($attrs["DATE"])) $date='"'.$attrs["DATE"].'"';
	switch ($type) {
	case "yearmonths" :;
		$comp = "<?php \n".
				'global $week_start_day;'."\n".
				'$getdate=strtotime('.$date.');'."\n".
				'$year_stop=date("Y", $getdate);'."\n".
				'$first_of_year	= $year_stop."-"."01"."-"."01";'."\n".
				'$loop_monthdate 	= strtotime($first_of_year);'."\n".
				'while (true) {'."\n".
				'	$loop_year 	= date("Y", $loop_monthdate);'."\n".
				'	$this->start_day = date("Y-m-d", $loop_monthdate);'."\n".
				'	if ($loop_year>$year_stop) break;'."\n".
				'	$loop_monthdate 	= strtotime("+1 month", $loop_monthdate);'."\n".
				'	?>';	
				break;
	case "monthweeks" :;
		$comp = "<?php \n".
				'global $week_start_day;  '.
				'$getdate=strtotime('.$date.');'.
				'$first_of_month	= date("Y", $getdate)."-".date("m", $getdate)."-"."01";'.
				'$loop_weekdate 	= strtotime(dateOfWeek($first_of_month, $week_start_day));'.
				'$month_stop 		= date("m", strtotime("+1 month", $getdate));'.	
				'while (true) {  '.
				'	$loop_month 	= date("m", $loop_weekdate);'.
				'	$this->start_day = date("Y-m-d", $loop_weekdate); '.
				'	if ($loop_month==$month_stop) break;'.
				'	$loop_weekdate 	= strtotime("+7 day", $loop_weekdate); '.
				'	?>';	
				break;
	case "weekdays" :
		$comp = "<?php \n".
				'global $langtype, $week_start_day;  '.
				'$getdate='.$date.';'.
				'$loop_daydate	= strtotime(dateOfWeek($getdate, $week_start_day));'.
				'for ($j=0; $j< 7; $j++) {  '.
				//'	$this->year 	= date("Y", $start_day);'.
				//'	$this->month 	= date("m", $start_day);'.
				//'	$this->day 		= date("d", $start_day);'.
				'	$this->day_num 		= date("w", $loop_daydate); '.
				'	$this->weekday 		= $langtype[$this->day_num]; '.
				'	$this->loop_day = date("Y-m-d", $loop_daydate); '.
				'	$loop_daydate 		= strtotime("+1 day", $loop_daydate); '.
				'	?>';
	break;
	default:;
	};
	return $comp;
	}

  	function loop_close($parser) {
	$comp="<?php }; ?>";
	return $comp;
	}
 
  	function switch_open($parser,$attrs) {
	$ntm=""; $it=""; $im="";
	if (isset($attrs["NOTTHISMONTH"])) $ntm=$attrs["NOTTHISMONTH"];
	if (isset($attrs["ISTODAY"])) $it=$attrs["ISTODAY"];
	if (isset($attrs["ISMONTH"])) $im=$attrs["ISMONTH"];
	$comp="<?php if (false) { ?>";
	if ($im=="on") {
		$comp="<?php { ?>";
	}
	return $comp;
	}

  	function switch_close($parser) {
	return "<?php } ?>";
	}

  	function month_open($parser,$attrs) {
	$date='date("Y-m-d",time())';
	if (isset($attrs["DATE"])) $date='"'.$attrs["DATE"].'"';
	if (isset($attrs["TYPE"])) $type=$attrs["TYPE"];
	switch ($type) {
	case "name" :;
	$comp ='<?php global $monthsofyear_lang; echo ';
	$comp.='$monthsofyear_lang[1*date("m", strtotime('.$date.'))-1];';
	$comp.='?>';
	break;
	case "num" :;
	default:;
	$comp="<?php echo ";
	$comp.='date("m", strtotime('.$date.'));';
	$comp.='?>';
	};
	return $comp;
	}

  	function month_close($parser) {
	$comp="";
	return $comp;
	}

 	function day_open($parser,$attrs) {
		$date='date("Y-m-d",time())';
		if (isset($attrs["DATE"])) $date='"'.$attrs["DATE"].'"';
		$comp="<?php echo ";
		$comp.='date("d", strtotime('.$date.'));';
		$comp.='?>';
		return $comp;
	}

  	function day_close($parser) {
	return "";
	}

   	function year_open($parser,$attrs) {
		$date='date("Y-m-d",time())';
		if (isset($attrs["DATE"])) $date='"'.$attrs["DATE"].'"';
		$comp="<?php echo ";
		$comp.='date("Y", strtotime('.$date.'));';
		$comp.='?>';
		return $comp;
	}

  	function year_close($parser) {
		$comp = "";
		return $comp;
	}
 
	function week_open($parser,$attrs) {
		$date='date("Y-m-d",time())';
		if (isset($attrs["DATE"])) $date='"'.$attrs["DATE"].'"';
		$comp="<?php echo ";
		$comp.='date("w", strtotime('.$date.'));';
		$comp.='?>';
		return $comp;
	}
	
   	function week_close($parser) {
		return $comp;
	}

 }

 $GLOBALS["cal"]=new cal_compiler;
 
?>