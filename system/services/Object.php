<?php
/**
 * Bleetz framework
 *
 * Object service.
 * Fonctions d affichage et de gestion des tables.
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
define('OBJTYPE_RESSOURCES_SET', -1);
define('OBJTYPE_SYSTEME', 10);
define('OBJTYPE_SERVICES', 100);
define('OBJTYPE_APPLICATION', 1000);

class Object  {
  var $classname = "Object";
  var $loaded=false;
  var $obj_id=-1;
  var $obj_type;
  var $obj_nature;
  var $obj_controller="";
  var $last_path;
  
  /**
   * Donne son id a l'objet mais ne le charge pas
   *
   * @return  static db connection
   */
  function InitId($obj_id) {
  	$this->obj_id=$obj_id;
  }  
  
	/**
	 * Créer une nouvel objet et l'ajoute à  la base de donnée
	 * 
	 * @param  type : type de l'objet
	 * @param  contstructeur : constructeur de l'objet
	 * @param  nature : nature de l'objet
	 *
	 * @return  object instance
	 */
  function Create($obj_controller="", $obj_nature="Object", $obj_type=OBJTYPE_RESSOURCES_SET) {

  	$db=DB::connect();
	  	   
	  $today = date("Y-m-d H:i:s");
	  
	  $q  = "INSERT INTO app_objets (";
	  $q .= "obj_type,";
	  $q .= "obj_controller,";
	  $q .= "obj_nature,";
	  $q .= "obj_dt_creation";
	  $q .= ") ";
	  $q .= "VALUES ( ";
	  $q .= "'".$obj_type."', ";
	  $q .= "'".$obj_controller."', ";
	  $q .= "'".$obj_nature."', ";
	  $q .= "'".$today."' ";
	  $q .= ")";
	  $obj_id=$db->exec_insert($q);

	  
	$this->obj_id=$obj_id;
	$this->obj_type=$obj_type;
	$this->obj_nature=$obj_nature;
	$this->obj_controller=$obj_controller;
	return true;
  }  
  
  /**
   * Charge un objet à partir de la base de donnée
   *
   * @param  obj_id : id de l'objet
   * 
   * @return  True if success
   */
  function Load($obj_id=-1) {
  	if ($this->loaded===true) return True;
	if ($this->obj_id>0) ;
	else if ($obj_id>0)  $this->obj_id=$obj_id;	
	else return False;
	
	$db=DB::connect();
	
	$q  = "SELECT * FROM app_objets ";
	$q .= " WHERE obj_id=".$this->obj_id ;
	$db->query($q);
	
	if ($db->next_record()) {
	  $result=$db->Record;
	} else {
	  return False ;
	}

	$this->obj_type=$result["obj_type"];
	$this->obj_nature=$result["obj_nature"];
	$this->obj_controller=$result["obj_controller"];
	
	$this->loaded=True;		
	return True;
  }
   
 /**
   * Enregistre les données $data dans la structure fichier
   * et les rattache à l'objet
   *
   * @return  static db connection
   */
  function StoreText($hook, $data) {
  	$db=DB::Connect();
   	
  	$db->begin_transaction();
  	$f=RS::Realize($this->obj_id, $hook, RSCTYPE_TEXT);

  	if (!$f->StoreText($data)) /*error*/;
    
  	$result=$db->end_transaction();
	if ($result===true) return $f;
	return null;
  }
   
	/**
	 * Enregistre l'image dans un fichier et sauve le chemin
	 *
	 * @param  $rsc_id : id de l'objet
	 * @param  $image : données à sauver
	 *
	 * @return  bool
	 */
	function AttachImageData($name, $image) {	
		$r=RS::Realize($this->obj_id, $name, RSCTYPE_PATH_IMAGE);
		$s=$this;
		
		$dir="images/".$s->obj_controller."/o_".$s->obj_id."/".$s->obj_nature;		
		if (file_exists($dir)) {
			if (!is_dir($dir)) {
				ER::report("Le fichier %s n'est pas un repertoire.", $dir);
				return false;
			}
		} else {
			//echo "Le repertoire $dir n'existe pas.";
			mkdir($dir, 0700, true);
		}
		
		//$fname="oi_$obj_id.png";
		$path="$dir/$name.png";
		//echo $path;
		
		imagepng($image, $path, 5);
		
		$r->AttachFile($name, $path);
		
		return true;
	}
  
  
  /**
   * Rattache un fichier à un objet
   *
   * @return  static db connection
   */
  function AttachUploadedFile($hook, $ul_array) {
  	$db=DB::Connect();
  	$s=$this;
  	 
  	$db->begin_transaction();
  	//echo "load";
  	$this->Load();
  	//$dir="images/".$this->obj_controller."/".$this->obj_id."/".$this->obj_nature;
  	$dir="images/".$s->obj_controller."/o_".$s->obj_id."/".$s->obj_nature;
  	 
  	if (file_exists($dir)) {
  		if (!is_dir($dir)) echo " $dir n'est pas un repertoire.";
  	} else {
  		//echo "Le repertoire $dir n'existe pas.";
  		mkdir($dir, 0700, true);
  	}
  	move_uploaded_file($ul_array["tmp_name"],$dir."/".$hook);
  	//test???
  	$f=FL::Realize($this->obj_id, $hook);
  	$f->AttachFile($hook, "$dir/$hook");
  	$done=$db->end_transaction();
  	if ($done) return $f;
  	return null;
  }
  
}

/**
 * 
 * @author CAO
 *
 */
class OB {
	/**
	 * global variables for objects
	 */

	/**
	 * Créer une instance d'objet après l'avoir ajoutée a la base de donnée
	 * 
	 * @param  type : type de l'objet
	 * @param  contstructeur : constructeur de l'objet
	 * @param  nature : nature de l'objet
	 *
	 * @return  object instance
	 */
	static function Spawn($obj_controller="", $obj_nature="Object", $obj_type=OBJTYPE_RESSOURCES_SET ) {
		$o=new Object();
		$o->Create( $obj_controller, $obj_nature, $obj_type);
		return $o;
	}
	
	/**
	 * détruit l'objet avec l'id objet dans la base de donnée (à l'aide de son controlleur?)
	 * renvoie True si succés
	 *
	 * @param  obj_id : id de l'objet
	 * 
	 * @return  boolean
	 */
	static function Delete($obj_id=-1) {
		if ($obj_id<=0) {
			return False;
		}
		$db=DB::connect();
		$db->begin_transaction();
		
	  	$q  = "DELETE FROM app_text  ";
	  	$q .=" USING app_text ";
	   	$q .=" LEFT OUTER JOIN app_ressources AS r ON r.rsc_id = app_text.rsc_id";
 	  	$q .= " WHERE ";
	  	$q .= " obj_id=".$obj_id.";";
	  	$done=$db->exec($q);
	  	
	  	$q = "DELETE FROM app_numbers ";
	  	$q .=" USING app_numbers ";
	   	$q .=" LEFT OUTER JOIN app_ressources AS r ON r.rsc_id = app_numbers.rsc_id";
	  	$q .= " WHERE ";
	  	$q .= " obj_id=".$obj_id.";";
	  	$done=$db->exec($q);
	  	
	  	$q = "DELETE FROM app_adresses ";
	  	$q .=" USING app_adresses ";
	   	$q .=" LEFT OUTER JOIN app_ressources AS r ON r.rsc_id = app_adresses.rsc_id";
	  	$q .= " WHERE ";
	  	$q .= " obj_id=".$obj_id.";";
	  	$done=$db->exec($q);
	  	
	  	$q = "DELETE FROM app_file_binary ";
	  	$q .=" USING app_file_binary ";
	   	$q .=" LEFT OUTER JOIN app_ressources AS r ON r.rsc_id = app_file_binary.rsc_id";
	  	$q .= " WHERE ";
	  	$q .= " obj_id=".$obj_id.";";
	  	$done=$db->exec($q);
	  	
	  	$q = "DELETE FROM app_file_path ";
	  	$q .=" USING app_file_path ";
	   	$q .=" LEFT OUTER JOIN app_ressources AS r ON r.rsc_id = app_file_path.rsc_id";
	  	$q .= " WHERE ";
	  	$q .= " obj_id=".$obj_id.";";
	  	$done=$db->exec($q);
	  	
  		$q  = "DELETE FROM app_ressources ";
		$q .= " WHERE ";
		$q .= " obj_id=".$obj_id ;
		$done=$db->exec($q);
		
		
		$q  = "DELETE FROM app_objets ";
		$q .= " WHERE ";
		$q .= " obj_id=".$obj_id ;
		$done=$db->exec($q);
		
		$done=$db->end_transaction();
		
		//echo ER::report();
		return $done;
	}
	
	/**
	 * Crée une instance d'objet avec l'id objet et la charge (et appelle son controlleur?)
	 * renvoie une instance objet si il existe et NULL sinon 
	 *
	 * @param  obj_id : id de l'objet
	 * 
	 * @return  object instance ou null
	 */
	static function LoadId($obj_id=-1) {
		//echo "load";
		$o=new Object();
		//$o->Init($obj_id);
		if ($o->Load($obj_id)) return $o;
		return null;
	}	
	
	/**
	 * Créé ne instance objet avec un id objet mais ne la charge pas
	 * et ne vérifie pas son existence
	 *
	 * @param  obj_id : id de l'objet
	 * 
	 * @return object instance
	 */
	static function InitId($obj_id) {
		$o=new Object();
		$o->InitId($obj_id);
		return $o;
	}
	
	/**
	 * detruire un objet
	 * et ne vérifie pas son existence
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return void
	 */
	static function wipe($obj_id) {
		//definir
		if (empty($d["obj_id"])) {
			ER::report("identifiant client absent");
			return False;
		}
		
		$db=DB::connect();
		
		$q  = "DELETE FROM app_objets ";
		$q .= " WHERE ";
		$q .= " obj_id=".$d["obj_id"] ;
		$db->exec($q);
		
		DB::release();
	}

	/**
	 * Listre des objets
	 *
	 * @return  Record array
	 */
	static function QueryList($d) {
		$q = "SELECT * ";
		$q.= " FROM app_objets  ";
		$q.= " WHERE obj_type<>".OBJTYPE_RESSOURCES_SET;
		//maybe it should be done same as save-record
		//$d=array();
		$data=DB::queryRecords($d, $q, "obj_id");
		return $data;
	}
	
}

/**
 * classe ressources
 * gere des objets ressource.. descandant d'objet?
 * 
 * @author CAO
 *
 */
class SET {

	public static $set_hooks=array();
	
	/**
	 * Crée une instance de ressource avec son controlleur et sa nature
	 * Une ressource est une ensemble de données ( fichiers ou informations)
	 * utiles pour la gestion d'une classe d'objet gérée par un controlleur
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  object instance ou null
	 */
	//realize
	static function Realize($controller, $nature) {
		//echo "load";
		$o=SET::Load($controller, $nature);
		if ($o===null) {
			$o=new Object();
			if(! $o->Create($controller, $nature, OBJTYPE_RESSOURCES_SET)) return null;
			return $o;
		}
		return $o;
	}

	/**
	 * Crée une instance d'objet avec l'id objet et la charge (et appelle son controlleur?)
	 * renvoie une instance objet si il existe et NULL sinon
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  object instance ou null
	 */
	static function LoadId($obj_id=-1) {
		return OB::LoadId($obj_id);
	}

	/**
	 * Charge un objet de type ressource à partir de la base de donnée
	 * Les ressources permettent de stocker des données générales utiles pour
	 * le controlleur
	 * Pour les ressources, le type, le controlleur et la nature forment une cle unique
	 * Ce n'est pas vrai pour les autres type d'objets...
	 *
	 * @param  controller : controlleur
	 * @param  nature : nature
	 *
	 * @return  True if success
	 */
	static function Load($controller, $nature) {

		$db=DB::connect();

		//il faudrat gérer les index
		$q  = "SELECT * FROM app_objets ";
		$q .= " WHERE  obj_type=".OBJTYPE_RESSOURCES_SET ;
		$q .= " AND obj_controller='".$controller."'" ;
		$q .= " AND obj_nature='".$nature."'" ;
		$db->query($q);

		if ($db->next_record()) {
			$result=$db->Record;
		} else {
			return null ;
		}

		$o=new Object();

		$o->obj_id=$result["obj_id"];
		$o->obj_type=$result["obj_type"];
		$o->obj_nature=$result["obj_nature"];
		$o->obj_controller=$result["obj_controller"];

		$o->loaded=True;
		return $o;
	}
	
	/**
	 * Enregistre les données dans un fichier et sauve le chemin
	 *
	 * @param  $rsc_id : id de l'objet
	 * @param  $data : données à sauver
	 * @param  $size : taille des données à sauver
	 *
	 * @return  bool
	 */
	static function AttachDataRID($rsc_id, $data, $size) {
		$r=RS::LoadId($rsc_id);
		$s=SET::LoadId($r->obj_id);
		
		$dir="images/".$s->obj_controller."/s_".$s->obj_id."/".$s->obj_nature;		
		if (file_exists($dir)) {
			if (!is_dir($dir)) {
				ER::report("Le fichier %s n'est pas un repertoire.", $dir);
				return false;
			}
		} else {
			//echo "Le repertoire $dir n'existe pas.";
			mkdir($dir, 0700, true);
		}
		
		$fname="r_$rsc_id";
		$path="$dir/$fname";
		
		$f=fopen($path,"w");
		if (!fwrite($f, $data, $size)) {
				ER::report("Le fichier ne peut pas être écrit.");
				return false;
		}
		
		$r->AttachFile($fname, $path);
		
		return true;
	}
	
	/**
	 * Enregistre l'image dans un fichier et sauve le chemin
	 *
	 * @param  $rsc_id : id de l'objet
	 * @param  $image : données à sauver
	 *
	 * @return  bool
	 */
	static function AttachImageRID($rsc_id, $image, $format="png") {
		$r=RS::LoadId($rsc_id);
		$s=SET::LoadId($r->obj_id);
		//var_dump($r);
		//var_dump($s);
		$dir="images/".$s->obj_controller."/s_".$s->obj_id."/".$s->obj_nature;	
		//echo$dir;	
		if (file_exists($dir)) {
			//echo "yes";
			if (!is_dir($dir)) {
				//echo "no";
				ER::report("Le fichier %s n'est pas un repertoire.", $dir);
				return false;
			}
		} else {
			//echo "no";
			//echo "Le repertoire $dir n'existe pas.";
			mkdir($dir, 0700, true);
		}
		
		switch($format) {
			case "png" :
				$fname="ri_$rsc_id.png";
				$path="$dir/$fname";
				imagepng($image, $path, 9);
				return $r->AttachFile($fname, $path);
				break;
			case "jpeg" :
				$fname="ri_$rsc_id.jpeg";
				$path="$dir/$fname";
				//echo $path;
				imagejpeg($image, $path);
				
				return $r->AttachFile($fname, $path);
				break;
		}
		
		return true;
	}
	
	/**
	 * Detruire la ressource
	 *
	 * @param  obj_id : id de l'objet
	 *
	 * @return  bool
	 */
	static function Delete($obj_id=-1) {
		return OB::Delete($obj_id);
	}

	/**
	 * Listre des sets de ressources
	 *
	 * @return  Record array
	 */
	static function QueryList($d) {
		$q = "SELECT * ";
		$q.= " FROM app_objets  ";
		$q.= " WHERE obj_type=".OBJTYPE_RESSOURCES_SET;
		//maybe it should be done same as save-record
		//$d=array();
		$data=DB::queryRecords($d, $q, "obj_id");
		return $data;
	}
		
}

?>
