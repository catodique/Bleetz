<?php 
/**
 * Bleetz framework
 *
 * Validation library.
 * should be a service? or auto validate?
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

class Validation_Core {

	// Filters
	protected $pre_filters = array();
	protected $post_filters = array();

	// Rules
	protected $raw_rules = array();
	protected $rules = array();

	// Rules that are allowed to run on empty fields
	//protected $empty_rules = array('required', 'matches');

	// Errors
	protected $errors = array();
	protected $messages = array();

	// Checks if there is data to validate.
	protected $submitted;

	/**
	 * Magic clone method, clears errors and messages.
	 *
	 * @return  void
	 */
	public function __clone() {
		$this->errors = array();
		$this->messages = array();
	}

	/**
	 * Creates a new Validation instance.
	 *
	 * @param   array   array to use for validation
	 * @return  object
	 */
	public static function factory(array $array)
	{
		return new Validation($array);
	}

	/**
	 * Sets the unique "any field" key and creates an ArrayObject from the
	 * passed array.
	 *
	 * @param   array   array to validate
	 * @return  void
	 */
	public function Validation_core(array $raw_rules)
	{
		// The array is submitted if the array is not empty
		$this->submitted = ! empty($rules);
		$this->raw_rules = $raw_rules;
		$this->compile_rules();
		//parent::__construct($array, ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);
	}

	/**
	 *
	 *
	 * @return  void
	 */
	public function compile_rules() {
		reset($this->raw_rules);
		while (list($k, $v) = each($this->raw_rules) ) {
			//echo $k, $v, "<br>";
			preg_match_all('/\w+\(/', $v,$match, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
			for ($i=0;$i<sizeof($match[0]);$i++) {
			$powerword=substr($match[0][$i][0],0, strlen($match[0][$i][0])-1);
			$rule["powerword"] = $powerword;
			$begin =strlen($match[0][$i][0])+$match[0][$i][1];
			if (isset($match[0][$i+1])) $end=$match[0][$i+1][1];
			else $end=strlen($v);
			$params=substr($v,$begin,$end-$begin);
			$params=substr($params, 0, strrpos($params,")"));
			$rule["params"]=$params;
			$this->rules[$k][]=$rule;
			};
		}
	}

	/**
	 * Add an error to an input.
	 *
	 * @param   string  unique error name
	 * @return  object
	 */
	public function validate(&$d) {
		$val=true;
		$this->d=$d;
		while (list($field,$v)=each($this->rules)) {
			if (isset($d[$field])) {
				$rules=$this->rules[$field];
				//var_dump($rules);
				for ($i=0; $i<sizeof($rules);$i++) {
					$eval='return $this->'.$rules[$i]["powerword"];
					$eval.='($d[$field]';
					if (!empty($rules[$i]["params"])) $eval.=",".$rules[$i]["params"];
					$eval.=")";
					//echo $eval."<br>";
					$todo=eval ("?><?php ".$eval.";?>");

					if ($todo===false) {
						$this->add_error($field, $rules[$i]["powerword"]);
						$val=false;
						break;
					}
				}
			}
		}
		return $val;
	}

	/**
	 * Add an error to an input.
	 *
	 * @chainable
	 * @param   string  input name
	 * @param   string  unique error name
	 * @return  object
	 */
	public function add_error($field, $name)
	{
		$this->errors[$field] = $name;

		return $this;
	}

	/**
	 * Sets or returns the message for an input.
	 *
	 * @chainable
	 * @param   string   input key
	 * @param   string   message to set
	 * @return  string|object
	 */
	public function message($input = NULL, $message = NULL)
	{
		if ($message === NULL)
		{
			if ($input === NULL)
			{
				$messages = array();
				$keys     = array_keys($this->messages);

				foreach ($keys as $input)
				{
					$messages[] = $this->message($input);
				}

				return implode("\n", $messages);
			}

			// Return nothing if no message exists
			if (empty($this->messages[$input]))
				return '';

			// Return the HTML message string
			return $this->messages[$input];
		}
		else
		{
			$this->messages[$input] = $message;
		}

		return $this;
	}

	/**
	 * Return the errors array.
	 *
	 * @param   boolean  load errors from a lang file
	 * @return  array
	 */
	public function errors($file = NULL)
	{
		//var_dump($this->errors);
		require_once(APPPATH."i18n/fr_FR/validation.php");
		$this->lang=$lang;
		$errors="";
		reset($this->errors);
		foreach ($this->errors as $input => $error) {
			$errors.=sprintf($this->lang[$error], $input)."<br>";
		}
		return $errors;

	/*
		if ($file === NULL)
		{
			return $this->errors;
		}
		else
		{

			$errors = array();
			foreach ($this->errors as $input => $error)
			{
				// Key for this input error
				$key = "$file.$input.$error";

				if (($errors[$input] = Kohana::lang($key)) === $key)
				{
					// Get the default error message
					$errors[$input] = Kohana::lang("$file.$input.default");
				}
			}

			return $errors;
		}
		*/
	}

	/**
	 * Rule: required. Generates an error if the field has an empty value.
	 *
	 * @param   mixed   input value
	 * @return  bool
	 */
	public function required($str)
	{
		return ! ($str === '' OR $str === NULL OR $str === FALSE);
	}

	/**
	 * Rule: filter. Generates an error if the field doesn't match $regexp.
	 *
	 * @param   mixed   input value
	 * @return  bool
	 */
	public function filter($str, $regexp)
	{
		return ! preg_match($regexp, $str);
	}

	/**
	 * Rule: filter. Generates an error if the field doesn't match $duplicate.
	 *
	 * @param   mixed   input value
	 * @return  bool
	 */
	public function match($str, $duplicate)
	{
		//echo $str," ",$duplicate;
		return ($str === $this->d[$duplicate]);
	}

	/**
	 * Rule: unique. Generates an error if the field isn't a unique primary key in $table.
	 *
	 * @param   mixed   input value
	 * @return  bool
	 */
	public function unique($str, $table, $inputfield)
	{
		$db=DB::Connect();
        $q = "SELECT * from ".$table." where ".$inputfield."='".$str . "'";
		$ret=True;
        $db->query($q);
        if ($db->next_record()) {
			$ret=False;
        }
		DB::Release();
 		return $ret;
	}

	/**
	 * Rule: matches. Generates an error if the field does not match one or more
	 * other fields.
	 *
	 * @param   mixed   input value
	 * @param   array   input names to match against
	 * @return  bool
	 */
	public function matches($str, array $inputs)
	{
		foreach ($inputs as $key)
		{
			if ($str !== (isset($this[$key]) ? $this[$key] : NULL))
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Rule: length. Generates an error if the field is too long or too short.
	 *
	 * @param   mixed   input value
	 * @param   array   minimum, maximum, or exact length to match
	 * @return  bool
	 */
	public function length($str, array $length)
	{
		if ( ! is_string($str))
			return FALSE;

		$size = utf8::strlen($str);
		$status = FALSE;

		if (count($length) > 1)
		{
			list ($min, $max) = $length;

			if ($size >= $min AND $size <= $max)
			{
				$status = TRUE;
			}
		}
		else
		{
			$status = ($size === (int) $length[0]);
		}

		return $status;
	}

	/**
	 * Rule: depends_on. Generates an error if the field does not depend on one
	 * or more other fields.
	 *
	 * @param   mixed   field name
	 * @param   array   field names to check dependency
	 * @return  bool
	 */
	public function depends_on($field, array $fields)
	{
		foreach ($fields as $depends_on)
		{
			if ( ! isset($this[$depends_on]) OR $this[$depends_on] == NULL)
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Rule: chars. Generates an error if the field contains characters outside of the list.
	 *
	 * @param   string  field value
	 * @param   array   allowed characters
	 * @return  bool
	 */
	public function chars($value, array $chars)
	{
		return ! preg_match('![^'.implode('', $chars).']!u', $value);
	}

} // End Validation

class Validation extends Validation_Core {}

?>