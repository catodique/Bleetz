<?php
/**
 * Bleetz framework
 *
 * Controller core library.
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

$context->open();

$context->run();

$context->close();

Bleetz::end();

?>