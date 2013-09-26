<?php 
/**
 * Bleetz framework
 *
 * Object Locale. LC
 * Fonctions de gestion de la langue
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

define('LC_LANG_DEFAULT', "00000");
//dŽfini dans bleetz start
//define('LC_LANG_ACTIVE', "fr_FR");

class LC {

}

//config
$week_start_day = 'Monday';
//config

$langtype 					= array ("Di","Lu","Ma","Me","Je","Ve","Sa");
$daysofweek_lang			= array ('Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi');
$daysofweekshort_lang		= array ('Dim','Lun','Mar','Mer','Jeu','Ven','Sam');
$daysofweekreallyshort_lang	= array ('D','L','M','M','J','V','S');
$monthsofyear_lang			= array ('Janvier','F&eacute;vrier','Mars','Avril','Mai','Juin','Juillet','Ao&ucirc;t','Septembre','Octobre','Novembre','D&eacute;cembre');
$monthsofyearshort_lang		= array ('Jan','F&eacute;v','Mar','Avr','Mai','Juin','Juil','Ao&ucirc;t','Sep','Oct','Nov','D&eacute;c');

// localizeDate() - similar to strftime but uses our preset arrays of localized
// months and week days and only supports %A, %a, %B, %b, %e, and %Y
// more can be added as needed but trying to keep it small while we can
function localizeDate($format, $timestamp) {
	global $daysofweek_lang, $daysofweekshort_lang, $daysofweekreallyshort_lang, $monthsofyear_lang, $monthsofyear_lang, $monthsofyearshort_lang;
	$year = date("Y", $timestamp);
	$month = date("n", $timestamp)-1;
	$day = date("j", $timestamp);
	$dayofweek = date("w", $timestamp);
	$weeknumber = date("W", $timestamp);
	$replacements = array(
			'%Y' =>	$year,
			'%e' => $day,
			'%B' => $monthsofyear_lang[$month],
			'%b' => $monthsofyearshort_lang[$month],
			'%A' => $daysofweek_lang[$dayofweek],
			'%a' => $daysofweekshort_lang[$dayofweek],
			'%W' => $weeknumber,
			'%d' => sprintf("%02d", $day)
	);
	$date = str_replace(array_keys($replacements), array_values($replacements), $format);
	return $date;

}
// dateOfWeek() takes a date in Ymd and a day of week in 3 letters or more
// and returns the date of that day. (ie: "sun" or "sunday" would be acceptable values of $day but not "su")
function dateOfWeek($Ymd, $day) {
	global $week_start_day;
	//global $phpiCal_config;
	//config

	//fin config

	//if (isset($phpical_config->week_start_day)) $week_start_day = $phpiCal_config->week_start_day;

	$timestamp = strtotime($Ymd);
	$num = date('w', strtotime($week_start_day));
	$start_day_time = strtotime((date('w',$timestamp)==$num ? "$week_start_day" : "last $week_start_day"), $timestamp);
	$ret_unixtime = strtotime($day,$start_day_time);
	// Fix for 992744
	// $ret_unixtime = strtotime('+12 hours', $ret_unixtime);
	$ret_unixtime += (12 * 60 * 60);
	$ret = date('Y-m-d',$ret_unixtime);
	return $ret;
}

function get_accepted_languages() {
	$httplanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$languages = array();
	if (empty($httplanguages)) {
		return $languages;
	}

	foreach (preg_split('/,\s*/', $httplanguages) as $accept) {
		$result = preg_match('/^([a-z]{1,8}(?:[-_][a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accept, $match);

		if (!$result) {
			continue;
		}
		if (isset($match[2])) {
			$quality = (float)$match[2];
		}
		else {
			$quality = 1.0;
		}

		$countries = explode('-', $match[1]);
		$region = array_shift($countries);
		$country_sub = explode('_', $region);
		$region = array_shift($country_sub);

		foreach($countries as $country)
			$languages[$region . '_' . strtoupper($country)] = $quality;

		foreach($country_sub as $country)
			$languages[$region . '_' . strtoupper($country)] = $quality;

		$languages[$region] = $quality;
	}

	return $languages;
}

?>