<?php defined('BLEETZ') OR die('No direct access allowed.');

class Config {
	
//Configuration for service Database
static $Database = array (
		"DBNAME"=>"bleetz",
		"DBHOST"=>"localhost",
		"DBUSER"=>"badmin",
		"DBPASS"=>"passwd",					
	);

//Configuration for system
static $Bleetz = array (
		'MAIN' => "admin",
		'INDEX' => "index",
		'E404' => "admin.e404_not_found",
		'IN_PRODUCTION' => false,
		'DODEBUG' => true,
		"CLASS_CACHE" => false,
		"STATIC_CACHE" => false,
		"FORCE_COMPILE"=>false,
		"TRANSLATE_IMAGE_PATH"=>false,
);

//the services you need
//for each service you can define a config in $(service name)
// for service Database you have $Database
//the service error is always loaded
static $services = array (
		"Session",/*"Validation",*/ "Url", "Database", "Locale", "Object", "Ressource", "User",
);

//where are my babies...
//You can change the values as you wish, but not the names...
static $directories = array(
		"SRVPATH" => "../system/services",	//services
		"SYSPATH" => "../system/core",		//systeme
		"CRDPATH" => "../system/cards",		//cartes
		"DCHPATH" => "var/dynamic_cache",	//cache statique
		"SCHPATH" => "var/static_cache",	//cache dynamique
		"ARMPATH" => "var/security",		//security
		"CTLPATH" => "controller",			//controller
		"TPLPATH" => "templates",			//templates
		);
/*

$translate_image_path=false;
$template_image_path="../images/";
$script_image_path="../../images/";

*/
}

?>