<?php
/**
 * Bleetz framework
 *
 * Ressource service. RS
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

// All object types must be different from 0
define('RSCTYPE_EMPTY', 0);
define('RSCTYPE_STRING', 1);
define('RSCTYPE_NUMBER', 2);
define('RSCTYPE_FILE', 4);
define('RSCTYPE_FILE_IMAGE', 8);
define('RSCTYPE_PATH', 16);
define('RSCTYPE_PATH_IMAGE', 32);
define('RSCTYPE_TEXT', 64);
define('RSCTYPE_ADRESSE', 128);

class Ressource {
	var $rsc_id=-1;
	var $rsc_type;
	var $obj_id;
	var $rsc_hook;
	var $loaded=false;

	/**
	 * Charge une ressource 
	 * 
	 * @param  $rsc_id : type de l'objet
	 *
	 * @return  boolean
	 */
 	function LoadId($rsc_id) {
		if ($this->loaded===true) return True;
		if ($this->rsc_id>0) ;
		else if ($rsc_id>0)  $this->rsc_id=$rsc_id;
		else return False;

		//$str_signature=$salt_str.".".$hook;
		//$inf_sign=hexdec(hash("crc32b", $str_signature));
		 
		$db=DB::connect();
	
		$q  = "SELECT *, f.* FROM app_ressources as f ";
		$q.= " LEFT OUTER JOIN app_file_path as p ON p.rsc_id=f.rsc_id  ";
		$q.= " LEFT OUTER JOIN app_text as t ON t.rsc_id=f.rsc_id  ";
		$q.= " LEFT OUTER JOIN app_numbers as n ON n.rsc_id=f.rsc_id  ";
		$q.= " LEFT OUTER JOIN app_adresses as a ON a.rsc_id=f.rsc_id  ";
		$q.= " LEFT OUTER JOIN app_file_binary as b ON b.rsc_id=f.rsc_id  ";
		$q .= " WHERE f.rsc_id=".$rsc_id;

		$db->query($q);
	
		if ($db->next_record()) {
			$result=$db->Record;
		} else {
			return False ;
		}
		//$this->inf_sign=$inf_sign;
		$this->rsc_id=$result["rsc_id"];
		$this->obj_id=$result["obj_id"];
		$this->rsc_hook=$result["rsc_hook"];
		$this->rsc_code=$result["rsc_code"];
		$this->rsc_type=$result["rsc_type"];

		if (($this->rsc_type & RSCTYPE_STRING) !=0) {
			$this->text_data=$result["text_data"];
		}

		if (($this->rsc_type & RSCTYPE_TEXT) == RSCTYPE_TEXT) {
			$this->text_data=$result["text_data"];
		} 
		if (($this->rsc_type & RSCTYPE_NUMBER) !=0) {
			$this->num_data=$result["num_data"];
		}
		if (($this->rsc_type & RSCTYPE_ADRESSE) !=0) {
			$this->adr_rue=$result["adr_rue"];
			$this->adr_ville=$result["adr_ville"];
			$this->adr_cp=$result["adr_cp"];
			$this->adr_email=$result["adr_email"];
			$this->adr_tel=$result["adr_tel"];
			$this->adr_gps_lat=$result["adr_gps_lat"];
			$this->adr_gps_long=$result["adr_gps_long"];
		}
		if (($this->rsc_type & (RSCTYPE_PATH | RSCTYPE_PATH_IMAGE)) !=0) {
			$this->file_path=$result["file_path"];
			$this->file_name=$result["file_name"];
		}
		if (($this->rsc_type & RSCTYPE_FILE) !=0) {
			$this->file_bolb=$result["file_bolb"];
		}

		//define('RSCTYPE_PATH_IMAGE', 32);
		//define('RSCTYPE_FILE_IMAGE', 8);

		$this->loaded=True;
		return True;
	}
	
	/**
	 * Charge une ressource 
	 * 
	 * @param  $obj_id : id de l'objet
	 * @param  $hook :  nom de ressource
	 *
	 * @return  boolean
	 */
 	function Load($obj_id, $hook) {
		if ($this->loaded===true) return True;
		if ($this->obj_id>0) ;
		else if ($obj_id>0)  $this->obj_id=$obj_id;
		else return False;

		$db=DB::connect();
	
		$q  = "SELECT *, f.* FROM app_ressources as f ";
		$q.= " LEFT OUTER JOIN app_file_path as p ON p.rsc_id=f.rsc_id  ";
		$q.= " LEFT OUTER JOIN app_text as t ON t.rsc_id=f.rsc_id  ";
		$q.= " LEFT OUTER JOIN app_numbers as n ON n.rsc_id=f.rsc_id  ";
		$q.= " LEFT OUTER JOIN app_adresses as a ON a.rsc_id=f.rsc_id  ";
		$q.= " LEFT OUTER JOIN app_file_binary as b ON b.rsc_id=f.rsc_id  ";
		$q .= " WHERE obj_id=".$obj_id;
		$q .= " AND rsc_hook='".addslashes($hook)."'";
		//echo $q;
		$db->query($q);
	
		if ($db->next_record()) {
			$result=$db->Record;
		} else {
			return False ;
		}
	
		//$this->inf_sign=$inf_sign;
		$this->rsc_id=$result["rsc_id"];
		$this->obj_id=$result["obj_id"];
		$this->rsc_hook=$result["rsc_hook"];
		$this->rsc_code=$result["rsc_code"];
		$this->rsc_type=$result["rsc_type"];
		
		if (($this->rsc_type & RSCTYPE_STRING) !=0) {
			$this->text_data=$result["text_data"];
		}
		
		if (($this->rsc_type & RSCTYPE_TEXT) == RSCTYPE_TEXT) {
			$this->text_data=$result["text_data"];
		}
		if (($this->rsc_type & RSCTYPE_NUMBER) !=0) {
			$this->num_data=$result["num_data"];
		}
		if (($this->rsc_type & RSCTYPE_ADRESSE) !=0) {
			$this->adr_rue=$result["adr_rue"];
			$this->adr_ville=$result["adr_ville"];
			$this->adr_cp=$result["adr_cp"];
			$this->adr_email=$result["adr_email"];
			$this->adr_tel=$result["adr_tel"];
			$this->adr_gps_lat=$result["adr_gps_lat"];
			$this->adr_gps_long=$result["adr_gps_long"];
		}
		if (($this->rsc_type & (RSCTYPE_PATH | RSCTYPE_PATH_IMAGE)) !=0) {
			$this->file_path=$result["file_path"];
			$this->file_name=$result["file_name"];
		}
		if (($this->rsc_type & RSCTYPE_FILE) !=0) {
			$this->file_bolb=$result["file_bolb"];
		}
		
		$this->loaded=True;
		return True;
	}
		
	/**
	 * Charge une ressource 
	 * 
	 * @param  $obj_id : id de l'objet
	 * @param  $hook :  nom de ressource
	 *
	 * @return  boolean
	 */
 	function Save( $hook, $code) {
		if ($this->loaded===false) {
			if ($this->rsc_id<=0) return False;
			$r->LoadId($this->rsc_id);
		}

		$db=DB::connect();
	
		//d�truire les textes
		$q  = "DELETE FROM app_text ";
		$q .= " WHERE ";
		$q .= " rsc_id=".$this->rsc_id.";";
	
		$q  = "UPDATE app_ressources as r SET ";
		$q .= "rsc_hook='".addslashes($hook)."',";
		$q .= "rsc_code='".$code."'";
		$q .= " WHERE r.rsc_id='".$this->rsc_id."' ";
		if ($db->query($q)) {
			$this->rsc_hook=$hook;
			$this->rsc_code=$code;
			return True;		
		}
		return false;
	}
	
	/**
	 * Cr�er une nouvelle ressource et l'ajoute �  la base de donn�e
	 * 
	 * @param  $salt_str : type de l'objet
	 * @param  $hook : constructeur de l'objet
	 * @param  $obj_id : nature de l'objet
	 *
	 * @return  object instance
	 */
  function Create($obj_id, $hook, $type, $code=0 ) {
  	$db=DB::connect();
	
	//$str_signature=$salt_str.".".$hook;
	//$inf_sign=hexdec(hash("crc32b", $str_signature));
	  	  
	$q  = "INSERT INTO app_ressources (";
	$q .= "obj_id,";
	$q .= "rsc_hook,";
	$q .= "rsc_code,";
	$q .= "rsc_type";
	$q .= ") ";
	$q .= "VALUES ( ";
	$q .= "'".$obj_id."', ";
	$q .= "'".$hook."', ";
	$q .= "'".$code."', ";
	$q .= "'".$type."' ";
	$q .= ")";
	$rsc_id=$db->exec_insert($q);
	if ($rsc_id===false) return false;
	  //.... et la verif???
	
	$this->rsc_id=$rsc_id;
	$this->obj_id=$obj_id;
	$this->rsc_hook=$hook;
	$this->rsc_code=$code;
	$this->rsc_type=$type;
			
	$this->loaded=True;
	return true;
  }  

	/**
	 * Nettoyage de la ressource
	 * 
	 * @param  $deletefile : boool : detruit le fichier rattache si true
	 *
	 * @return  object instance
	 */
  function Clean( $deletefile=false ) {
  	$db=DB::Connect();
  	
  	$db->begin_transaction();
  	  	
  	$q  = "DELETE FROM app_text ";
  	$q .= " WHERE ";
  	$q .= " rsc_id=".$this->rsc_id.";";
  	
  	$q .= "DELETE FROM app_numbers ";
  	$q .= " WHERE ";
  	$q .= " rsc_id=".$this->rsc_id.";";
  	
  	$q .= "DELETE FROM app_adresses ";
  	$q .= " WHERE ";
  	$q .= " rsc_id=".$this->rsc_id.";";
  	
  	$q .= "DELETE FROM app_file_binary ";
  	$q .= " WHERE ";
  	$q .= " rsc_id=".$this->rsc_id.";";
  	
  	$q .= "DELETE FROM app_file_path ";
  	$q .= " WHERE ";
  	$q .= " rsc_id=".$this->rsc_id.";";
  	$db->exec($q);
 	$result=$db->end_transaction();
		
	return $result;
   }
   
   /**
    * Enregistre les donn�es $text dans la ressource
    * dans la langue specifi�e
    *
    * @param  $lang : langue de destination
    * @param  $text : texte dans la langue
    *
    * @return  bool
    */
   function StoreTextData($text, $lang="fr_FR") {
   	if (($this->rsc_type & RSCTYPE_TEXT) ==0) {
		ER::Collect("Le type de ressource ne correspond pas");
		return false;
	}
   	$db=DB::Connect();
   
   	$db->begin_transaction();
      
   	$str_signature=$text;
   	$str_hash_sgn=hexdec(hash("crc32b", $str_signature));
   	
   	$q = "SELECT text_hash ";
   	$q.= "	FROM app_text ";
   	$q.= "	WHERE rsc_id=".$this->rsc_id."";
   	$db->query($q);
   	
   	if ($db->next_record()) {
   		$old_str_hash_sgn=$db->Record["text_hash"];
   		//what should we do???
   		//delete perhaps but not always
   		$q  = "INSERT INTO app_text_locale (";
   		//$q .= "rsc_id,";
   		$q .= "text_hash,";
   		$q .= "text_lang,";
   		$q .= "text_data_loc";
   		$q .= ") ";
   		$q .= "VALUES ( ";
   		//$q .= "".$this->rsc_id.",";
   		$q .= "".$str_hash_sgn.",";
   		$q .= "'".$lang."',";
   		$q .= "'".addslashes($text)."'";
   		$q .= ")";
   		$db->exec_silent($q);
   			 
   		//should be the same but who knows
   		$q  = "UPDATE app_text SET ";
		$q .= "text_hash='".$str_hash_sgn."',";
		$q .= "text_data='".addslashes($text)."'";
		$q .= " WHERE rsc_id=".$this->rsc_id."";
   		$db->exec($q);
   		//echo $q;
   	} else {	   	
	   	$q  = "INSERT INTO app_text_locale (";
	   	//$q .= "rsc_id,";
	   	$q .= "text_hash,";
	   	$q .= "text_lang,";
	   	$q .= "text_data_loc";
	   	$q .= ") ";
	   	$q .= "VALUES ( ";
	   	//$q .= "".$this->rsc_id.",";
	   	$q .= "".$str_hash_sgn.",";
	   	$q .= "'".$lang."',";
	   	$q .= "'".addslashes($text)."'";
	   	$q .= ")";
		if (!$db->exec_silent($q)) {
			$q  = "UPDATE  app_text_locale SET ";
			//addslahses or not????
			$q .= "text_data_loc='".addslashes($text)."' ";
			$q .= " WHERE text_hash=".$str_hash_sgn;
			$q .= " AND text_lang='".$lang."'";
			$db->exec($q);
		}

		$q  = "INSERT INTO app_text (";
		$q .= "rsc_id,";
		$q .= "text_hash,";
		//$q .= "text_lang,";
		$q .= "text_data";
		$q .= ") ";
		$q .= "VALUES ( ";
		$q .= "".$this->rsc_id.",";
		$q .= "".$str_hash_sgn.",";
		//$q .= "'".$lang."',";
		$q .= "'".addslashes($text)."'";
		$q .= ")";
		$db->exec($q);
		
   	}

   	$q  =" DELETE FROM app_text_locale";
   	$q .=" USING app_text_locale";
   	$q .=" LEFT OUTER JOIN app_text AS t ON t.text_hash = app_text_locale.text_hash";
   	$q .=" WHERE rsc_id IS NULL";
   	$db->exec($q);
   	
   	$result=$db->end_transaction();
   
   	return $result;
   }
     
   /**
    * Enregistre les donn�es $text dans la ressource
    * dans la langue specifi�e
    *
    * @param  $lang : langue de destination
    * @param  $text : texte dans la langue
    *
    * @return  bool
    */
   function StoreText($text, $lang="fr_FR") {
   	if (($this->rsc_type & RSCTYPE_TEXT) ==0) {
   		ER::Collect("Le type de ressource ne correspond pas");
   		return false;
   	}

   	return $this->StoreTextData($text, $lang);
   }
   
   /**
	 * Enregistre les donn�es $text dans la ressource
	 * dans la langue specifi�e
	 *
	 * @param  $lang : langue de destination
	 * @param  $text : texte dans la langue
	 *
	 * @return  bool
	 */
	function StoreString($data, $lang="fr_FR") {
		if (($this->rsc_type & RSCTYPE_STRING) ==0) {
			ER::Collect("Le type de ressource ne correspond pas");
			return false;
		}
		return $this->StoreTextData($data, $lang);
	}
	
	  /**
	   * Rattache le chemin fichier � un objet
	   *
	   * @return  static db connection
	   */
	function AttachFile($name, $path, $type=1) {
		//echo $this->rsc_type ;
		if (($this->rsc_type & RSCTYPE_PATH_IMAGE) ==0) {
			ER::Collect("Le type de ressource ne correspond pas");
			//echo ER::report();
			return false;
		}
		
		$db=DB::Connect();
	
		$db->begin_transaction();
							
		//detruire le fichier...
		$q  = "DELETE FROM app_file_path ";
		$q .= " WHERE ";
		$q .= " rsc_id=".$this->rsc_id.";";
		$db->exec($q);
		
		$q  = "INSERT INTO app_file_path (";
		$q .= "rsc_id,";
		$q .= "file_name,";
		$q .= "file_path";
		$q .= ") ";
		$q .= "VALUES ( ";
		$q .= "$this->rsc_id,";
		$q .= "'$name',";
		$q .= "'$path'";
		$q .= ")";
		$db->exec($q);
		//echo $q;
		//Should update app_file for file type
		//a mime type could be usefull...
	
		$result=$db->end_transaction();
		 
		$this->file_path="$path";
		$this->file_name=$name;
	
		return $result;
	}
	
}

class RS {
	//
	public static $rs_hooks=array();
		
	/**
	 * Cr�er une instance de ressource apr�s l'avoir ajout�e a la base de donn�e
	 *
	 * @param  obj_id : id de l'objet
	 * @param  hook : identificateur
	 * @param  type : type de l'objet
	 *
	 * @return  object instance
	 */
	static function Spawn($obj_id, $hook, $type, $code=0 ) {

		$i=new Ressource();
		if ($i->Create($obj_id, $hook, $type, $code)) return $i;
		return null;
	}
	
	/**
	 * Charge une information si elle existe, la cr��e sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  True if success
	 */
	static function Realize( $obj_id, $hook, $type, $code=0/*, $data="", $lang="fr_FR"*/ ) {
		$db=DB::Connect();
	
	  	//$db->begin_transaction();

	  	$i=new Ressource();
		$r=null;
		if ($i->Load( $obj_id, $hook )) $r=$i;
		if ($r==null) {
			if ($i->Create( $obj_id, $hook, $type, $code)) $r=$i;
		}

		return $r;
	}

	/**
	 * Charge une information si elle existe, la cr��e sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  True if success
	 */
	static function LoadId( $rsc_id) {
		$r=new Ressource();
		//$o->Init($obj_id);
		if ($r->LoadId( $rsc_id )) return $r;
		return null;
	}

	/**
	 * Charge une information si elle existe, la cr��e sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  True if success
	 */
	static function Load( $obj_id, $hook) {
		$r=new Ressource();
		//$o->Init($obj_id);
		if ($r->Load( $obj_id, $hook )) return $r;
		return null;
	}
	
	/**
	 * Modifie une ressource 
	 * 
	 * @param  $obj_id : id de l'objet
	 * @param  $hook :  nom de ressource
	 *
	 * @return  boolean
	 */
 	static function Save( $rsc_id, $hook, $code=0) {
 		$r=self::LoadId($rsc_id);
 		if ($r!==null) {
 			if ($r->Save($hook, $code)) return $r;
 		}
 		return false;
 	}
	
	/**
	 * d�truit l'objet avec l'id objet dans la base de donn�e (� l'aide de son controlleur?)
	 * renvoie True si succ�s
	 *
	 * @param  obj_id : id de l'objet
	 * 
	 * @return  boolean
	 */
	static function Delete($rsc_id=-1) {
		if ($rsc_id<=0) {
			ER::collect("Pas d'identifiant");
			return False;
		}
		
		$db=DB::connect();
		$db->begin_transaction();
		
	  	$q  = "DELETE FROM app_adresses ";
		$q .= " WHERE ";
		$q .= " rsc_id=".$rsc_id.";";
		
	  	$q .= "DELETE FROM app_text ";
		$q .= " WHERE ";
		$q .= " rsc_id=".$rsc_id.";";
		
		$q .= "DELETE FROM app_numbers ";
		$q .= " WHERE ";
		$q .= " rsc_id=".$rsc_id.";";

		$q .= "DELETE FROM app_file_binary ";
		$q .= " WHERE ";
		$q .= " rsc_id=".$rsc_id.";";
		
		$q .= "DELETE FROM app_file_path ";
		$q .= " WHERE ";
		$q .= " rsc_id=".$rsc_id.";";
		$db->exec($q);

		$q  = "DELETE FROM app_ressources ";
		$q .= " WHERE ";
		$q .= " rsc_id=".$rsc_id ;
		$r=$db->exec($q);
		$result=$db->end_transaction();
		return $result;
	}
	
	/**
	 * Charge une information si elle existe, la cr��e sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  True if success
	 */
	static function QueryList($obj_id, $d, $type=RSCTYPE_EMPTY) {

		//provisoire, deviendras une constante
		$lang=$d["rsc_lang"];
		$q = "SELECT *, r.* ";
		$q.= " FROM app_ressources as r ";

		switch ($type) {
			case RSCTYPE_STRING :
				//$q.= " LEFT JOIN app_text as s ON s.rsc_id=r.rsc_id AND s.text_lang='$lang' ";
				$q.= " LEFT JOIN app_text as s ON s.rsc_id=r.rsc_id";
				break;
			case RSCTYPE_PATH_IMAGE :
				$q.= " LEFT JOIN app_file_path as p ON p.rsc_id=r.rsc_id";// AND p.file_text_lang='$lang' ";
				break;
			case RSCTYPE_TEXT :
				//$q.= " LEFT JOIN app_text as t ON t.rsc_id=r.rsc_id AND t.text_lang='$lang' ";
				$q.= " LEFT JOIN app_text as t ON t.rsc_id=r.rsc_id";
				break;		
			default :
				ER::collect("ressource de type indefini");
				//???
		}
		$q.= " WHERE obj_id=".$obj_id;
		if ($type!=RSCTYPE_EMPTY)
		$q.= " AND (rsc_type & ".$type.")<>0";
		
		//echo $q;
		//maybe it should be done same as save-record
		//pour un getlist
		//$d=array();
		$data=DB::queryRecords($d, $q, "r.rsc_id");

		return $data;
	}
	
}

?>