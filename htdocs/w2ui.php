<?php
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
define('BLEETZ', true);
require "var/config.php";

//global definition constants, should not be changed
define('DOCROOT', preg_replace('/^\w:/','',str_replace('\\','/',realpath('.'))).'/');

$bootfile=realpath(DOCROOT.config::$directories["SYSPATH"]."/Bleetz.php");

if ($bootfile===false)
	die("No boot file, check your configuration");

$bootfile=preg_replace('/^\w:/','',str_replace('\\','/',$bootfile));

require $bootfile;

$context=Bleetz::start();

$d=$context->sys_form_vars;

// ˆ rajouter dasn l'armure eventuellement
//var_dump($d);
switch ($d["cmd"]) {
	case "save-record" :
		$context->sys_form_vars=$d["record"];
		break;
	case "get-records" :
		break;
	default :
		//error???
}

$context->client_path_info="/".$d[$d["cmd"]];

$context->open();

$data=array();
$data['status'] = 'success';
$data['message'] = "";
if ($context->run()) {
	switch ($d["cmd"]) {
		case "save-record" :
			$data["record"]=$context->sys_form_vars;
			break;
		case "get-records" :
			$data=$context->sys_form_vars["data"];
			break;
		default :
			//error???
	}
} else {
	$data['status'] = 'error';
	$data['message'] = ER::report();
}

header("Content-Type: application/json;charset=utf-8");
//var_dump($data);
echo json_encode($data);

$context->close();

Bleetz::end();

//echo "\n Completed in $runtime2 seconds<br> \n";

?>