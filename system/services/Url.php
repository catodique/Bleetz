<?php 
/**
 * Bleetz framework
 *
 * service URL
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

class URL
{	
	//UNAUTHORIZED
	//
	
	// Redirect
	// ---------------------------------------------------------------------------
	public static function redirect($url = '')
	{
		header('Location: '.$url);
		//die???
		exit;
	}
}


?>