<?php 
/**
 * Bleetz framework
 *
 * Error service. ER
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


class Error  {
	var $error;
	var $trace;
	
	function set($error, $trace) {
		$this->error=$error;
		$this->trace=$trace;
	}
}

/**
 * Error management
 * 
 * @author CAO
 *
 */
class ER {
	/**
	 * stack of errors for error management
	 */
	public static $errors= array();

	/**
	 * Remove error stack
	 * 
	 * @return  void		
	 */
	static function clear() {
		ER::$errors=array();
	}
	/**
	 * Enregistre une erreur dans la pile des erreurs
	 * 
	 * @param string $string 	: le texte de l'erreur avec la syntaxe de printf
	 * @param mixed args 		: plusieurs variables � afficher avec $string
	 */
	static function collect($string) {
		$trace=debug_backtrace();
		$ta= func_get_args();
		unset($ta[0]);
		array_values($ta);
		$string=vsprintf($string,$ta);
		ER::$errors[]=ER::Spawn($string, $trace);
	}
	
	/**
	 * Enregistre une erreur dans la pile des erreurs avec la trace du programme pour deboguage
	 * 
	 * @param unknown $string
	 * @param unknown $trace
	 * @return Error
	 */
	static function spawn($string, $trace) {
		$e=new Error();
		$e->set($string, $trace);
		return $e;
	}
	
	/**
	 * Renvoie une chaine avec toute les erreurs rencontr�s
	 * 
	 * @param number $traceLevel 	: � voir... c'est le niveau de la trace qu'on va afficher... -1 pour tout???
	 * @return string, void			: le texte � afficher
	 */
	static function report($traceLevel=0) {
		$error_string="";
		for ($i=0;$i<sizeof(ER::$errors);$i++) {
			$error_string.=ER::$errors[$i]->error."<br/>";
			if (DODEBUG) {
				//ici il faut revoir, pour l'insant on affiche  tout, depend du parametre $tracelevel
				//$error_string.=Bleetz::trace_entry(ER::$errors[$i]->trace[$traceLevel], false);
				$error_string.=ER::backtrace(ER::$errors[$i]->trace, false);
			}
		}
		//et les warnings???
		//peut etre un service warning???
		//ou encore un type warning???
		return $error_string;
	}
	
	// � transfere, soit dans erreur , soit dans debug
	public static function trace_entry($trace_entry, $show_func=true)
	{
		$entry=$trace_entry;
		$temp = '';
	
		if (isset($entry['file']))
		{
			$temp .= preg_replace('!^'.preg_quote(DOCROOT).'!', '', $entry['file']). " ligne " . $entry['line'];
			//''. preg_replace('!^'.preg_quote(DOCROOT).'!', '', $entry['file'], $entry['line']);
		}
	
		$temp .= '<pre>';
	
		if ($show_func) {
	
			if (isset($entry['class']))
			{
				// Add class and call type
				$temp .= $entry['class'].$entry['type'];
			}
	
			// Add function
			$temp .= $entry['function'].'( ';
				
			// Add function args
			if (isset($entry['args']) AND is_array($entry['args']))
			{
				// Separator starts as nothing
				$sep = '';
					
				while ($arg = array_shift($entry['args']))
				{
					if (is_string($arg) AND is_file($arg))
					{
						// Remove docroot from filename
						$arg = preg_replace('!^'.preg_quote(DOCROOT).'!', '', $arg);
					}
						
					$temp .=  $sep.htmlspecialchars(print_r($arg, TRUE));
						
					// Change separator to a comma
					$sep = ', ';
				}
			}
			$temp .= ' )';
		}
	
		$temp .= '</pre>';//</li>';
	
		return $temp;
	
	}
	
	/**
	 * Displays nice backtrace information.
	 * @see http://php.net/debug_backtrace
	 *
	 * @param   array   backtrace generated by an exception or debug_backtrace
	 * @return  string
	 */
	public static function backtrace($trace)
	{
		if ( ! is_array($trace))
			return;
	
		// Final output
		$output = array();
	
		foreach ($trace as $entry)
		{
			$temp = '<li>';
				
			$temp.=ER::trace_entry($entry);
				
			$temp.='</li>';
	
			$output[] = $temp;
		}
	
		return '<ul class="backtrace">'.implode("\n", $output).'</ul>';
	}
}

?>
