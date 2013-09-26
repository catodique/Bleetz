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
	// Base URL
	// ---------------------------------------------------------------------------
	public static function base($ShowIndex = FALSE)
	{
		if($ShowIndex AND !MOD_REWRITE)
		{
			// Include "index.php"
			return(BASE_URL.'index.php/');
		}
		else
		{
			// Don't include "index.php"
			return(BASE_URL);
		}
	}
	
	
	// Page URL
	// ---------------------------------------------------------------------------
	public static function page($path = FALSE)
	{
		if(MOD_REWRITE)
		{
			return(url::base().$path);
		}
		else
		{
			return(url::base(TRUE).$path);
		}
	}
	
	
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
