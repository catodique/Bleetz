<?php
/**
 * Bleetz framework
 *
 * Database service. DB
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

class Database  {
  var $classname = "Database";
  var $transaction_level=0;
  var $transaction_error;
  var $return="";
  var $db;


  function _connect() {
	//$db_object=new DB_source;
	$this->transaction_level=0;
	try {
	//echo "mysql:$host;dbname=d$database";
	$this->db=new PDO("mysql:host=".DBHOST.";dbname=".DBNAME,DBUSER,DBPASS, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));//PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") );
	return true;
	} catch (PDOException $e) {
	    ER::collect("Failed to get DB handle: %s", $e->getMessage() );
	    return false;
	}

  }

  function query($q) {
	$this->stmt=$this->db->query($q);
	if ($this->stmt==null) {
		$this->transaction_error=true;
		$errmess="";
		if (DODEBUG) {
    		$errmess.="<br/>".$q;
			$dberr= $this->db->errorInfo();
			$errmess.="<br/>".$dberr[2];
		};
		ER::collect("Database error %s", $errmess);
		return false;
	}
	return true;
  }

  function error_message() {
		$dberr= $this->db->errorInfo();
  		return $dberr[2];
  }

  function num_rows() {
  	//ER::collect("");
  	//echo ER::report();
	$this->stmt->rowCount();
  }

  function last_insert_id() {
	$this->db->lastInsertId();
  }

  function next_record() {
  	$this->Record=null;
	if ($this->stmt!==false) {
		//a revior dans le cas ou on ne fait pas de query
		$this->Record=$this->stmt->fetch(PDO::FETCH_ASSOC);
	} else {
		ER::collect("Bad query statement");
		return false;
	}
	return $this->Record;
  }

  function exec_silent($q) {
  	return $this->db->exec($q);
  }
  
  function exec($q) {
  	$done=$this->db->exec($q);
	if ($done===false) {
		$this->transaction_error=true;
		$errmess="";
		if (DODEBUG) {
    		$errmess.="<br/>".$q;
			$dberr= $this->db->errorInfo();
			$errmess.="<br/>".$dberr[2];
		};
		ER::collect("Database error %s", $errmess);
	}

	return $done;
  }

  function exec_insert($q) {
    if ($this->db->inTransaction()==0) {
    	$this->db->beginTransaction();
    	$done=$this->db->exec($q);
    	$id=$this->db->lastInsertId();
    	if ($done===false) {
			$errmess="";
			if (DODEBUG) {
	    		$errmess.="<br/>".$q;
				$dberr= $this->db->errorInfo();
				$errmess.="<br/>".$dberr[2];
			};
			ER::collect("Database error %s", $errmess);
			$this->db->rollBack();//????NECESSAIRE???
			return false;
		}
    	$this->db->commit();
    } else {
    	$done=$this->db->exec($q);
    	$id=$this->db->lastInsertId(); 	
		if ($done===false) {
			$this->transaction_error=true;
			$errmess="";
			if (DODEBUG) {
	    		$errmess.="<br/>".$q;
				$dberr= $this->db->errorInfo();
				$errmess.="<br/>".$dberr[2];
			};
			ER::collect("Database error %s", $errmess);
			return false;
		}
    }

	return $id;
  }
  
  function begin_transaction() {
  	if ($this->transaction_level<1) {
  	$this->transaction_error=false;
    $this->db->beginTransaction();
  	}
  	$this->transaction_level+=1;
  }
  
  function end_transaction() {
  	$this->transaction_level-=1;
  	if ($this->transaction_level>0) return true;
  	if ($this->transaction_error===false) {
  		//echo "commit";
  		$this->db->commit();
  		return true;
  	}else {
  		//echo "rollback";
  		$this->db->rollBack();
  		return false;
  	}
  }
  
}

class DB {
	/**
	 * global variables for database connections management
	 */
	public static $db_hooks=array();
	public static $db_hooks_opened=0;
	public static $db_hooks_used=0;

	/**
	 * Connection a une base de donnÈes
	 * Si la connection est dÈj‡ ouverte, recharge l ancienne connection
	 * ‡ utiliser quand on a besoin de requetes succesives...
	 *
	 * @return  static db connection
	 */
	static function connect() {
		if (self::$db_hooks_used<=0) return self::spawn();
		return self::$db_hooks[self::$db_hooks_used-1];
	}
	
	/**
	 * Nouvelle connection a une base de données au cas ou on a besoin de faire des requetes imbriquées
	 * à utiliser quand on a besoin de requetes imbriquées...
	 *
	 * @return NULL|Database:
	 */
	static function spawn() {
		self::$db_hooks_used++;
		if (self::$db_hooks_used>self::$db_hooks_opened) {
		$db=new Database;
		if ($db->_connect()===false) return null;
		self::$db_hooks[]=$db;
		self::$db_hooks_opened++;
		}
		return self::$db_hooks[self::$db_hooks_used-1];
	}

	/**
	 * Deconnection d'une base de données
	 * indique que la dernière connection ouverte n'est plus utilisée
	 * et peut etre écrasée
	 *
	 * @return void
	 */
	static function close() {
		//echo "release ",Bleetz::$context->action.self::$db_hooks_used,self::$db_hooks_used;
		if (self::$db_hooks_used>0) self::$db_hooks_used--;
		else return false;//fire error??
		return true;
	}

	/**
	 * obsolete
	 */
	static function release() {
	//le release devient obosolete
	}

	
	/**
	 * Connection a une base de données
	 * relache toutes les connections ouvertes
	 * et vérifie si les séquences connect-release ont été faites correctement
	 *
	 * @return void
	 */
	static function release_all() {
		if ((self::$db_hooks_used<0)or(self::$db_hooks_used>1)) {
			// à voir, ce n'est pas forcement necessaire
			echo "warning : error ".self::$db_hooks_used." in the spawn drop sequence for action : <b>".Bleetz::$context->controller.".".Bleetz::$context->action."<b>";
			self::$db_hooks_used=0;
		}
	}

	/**
	 * Execute une requete
	 * 
	 * @param string $q : query
	 * @return NULL  | Record array
	 */
	static function exec($q) {
		$db=DB::connect();
		if ($db->query($q)===false) return true;
		return false;	
	}
	
	/**
	 * Récupere un enregistrement
	 * 
	 * @param string $q : query
	 * @return NULL  | Record array
	 */
	static function getRecord($q) {
		$db=DB::connect();
		if ($db->query($q)===false) return null;
		
		if ($db->next_record()) {
			return $db->Record;
		} else return null;
		
	}
		
	/**
	 * Récupere des enregistrement pour la grille w2grid
	 * non fonctionnel encore
	 *
	 * @return une array avec tout les enregistrements
	 */
	static function getRecords( $p_sql) {
		$db=DB::Connect();
		
		if (!$db->query($p_sql)) return null;
		
		$values=array();
		while ($db->next_record()) {
			reset($db->Record);
			foreach ($db->Record as $k => $v) {
				$db->Record[$k]=stripslashes($v);
			}
			$values[]=$db->Record;
		};
		return $values;
	}
		
	/**
	 * Récupere des enregistrement pour la grille w2grid
	 *
	 * @return une array avec tout les enregistrements
	 */
	static function queryRecords($request, $p_sql, $index="") {
		//global $db, $dbType;
		//hack...
		$dbType=1;
	
		$db=DB::Connect();
		
		// prepare search
		$searchStr = "";
		if (isset($request['search']) && is_array($request['search'])) {
			foreach ($request['search'] as $s => $search) {
				if ($searchStr != "") $searchStr .= " ".$request['search-logic']." ";
				$operator = "=";
				$field 	  = $search['field'];
				$value    = "'".$search['value']."'";
				switch (strtolower($search['operator'])) {
	
					case 'begins with':
						$operator = ($dbType == "postgres" ? "ILIKE" : "LIKE");
						$value 	  = "'".$search['value']."%'";
						break;
	
					case 'ends with':
						$operator = ($dbType == "postgres" ? "ILIKE" : "LIKE");
						$value 	  = "'%".$search['value']."'";
						break;
	
					case 'contains':
						$operator = ($dbType == "postgres" ? "ILIKE" : "LIKE");
						$value 	  = "'%".$search['value']."%'";
						break;
	
					case 'is':
						$operator = "=";
						if (!is_int($search['value']) && !is_float($search['value'])) {
							$field = "LOWER($field)";
							$value = "LOWER('".$search['value']."')";
						} else {
							$value = "'".$search['value']."'";
						}
						break;
	
					case 'between':
						$operator = "BETWEEN";
						$value 	  = "'".$search['value'][0]."' AND '".$search['value'][1]."'";
						break;
	
					case 'in':
						$operator = "IN";
						$value 	  = "[".$search['value']."]";
						break;
				}
				$searchStr .= $field." ".$operator." ".$value;
			}
		};
		
		if (stripos($p_sql, "WHERE")!==false) {
			if ($searchStr != "") $searchStr = " AND ($searchStr)";			
		} else {
			if ($searchStr != "") $searchStr = " WHERE $searchStr";
		}
	
		// prepare sort
		$sortStr = "";
		if (isset($request['sort']) && is_array($request['sort'])) {
			foreach ($request['sort'] as $s => $sort) {
				if ($sortStr != "") $sortStr .= ", ";
				$sortStr .= $sort['field']." ".$sort['direction'];
			}
		}
		if ($sortStr != "") $sortStr = " ORDER BY ";
		else if (!empty($index)) $sortStr = " ORDER BY $index ASC ";
			
		// build sql
		//ici on peut remplacer par une recherche plus complexe sur les parametres...
		//$sql = str_ireplace("{$search}", $searchStr, $sql);
		//$sql = str_ireplace("{$order}", "{$sort}", $sql);
		//$sql = str_ireplace("{$sort}", $sortStr, $sql);
		$sql=$p_sql.$searchStr.$sortStr;
	
		// build cql (for counging)
		//if ($cql == null || $cql == "") {
		//counting
		$es=preg_split("/from/i", $sql);
		//or select count ($index)
		$cql= "SELECT COUNT(*) as count FROM ".$es[1];
			//$cql = "SELECT count(1) FROM ($sql) as grid_list_1";
		//}
		if (!isset($request['limit']))  $request['limit']  = 50;
		if (!isset($request['offset'])) $request['offset'] = 0;
	
		$sql .= " LIMIT ".$request['limit']." OFFSET ".$request['offset'];
/*
		echo $sql;
		echo "<br>";
		echo $cql;
*/

		$data = Array();
	
		// count records
		$db->query($cql);
		$db->next_record();
		$data['status'] = 'success';
		$data['total']  = $db->Record["count"];
	
		// execute sql
		$probe=$db->query($sql);
		//echo $sql;
	
		// check for error
		if ($probe === false) {
			$data = Array();
			$data['status'] = 'error';
			$data['message'] = ER::report(2);			
			return $data;
		}
		$data['records'] = array();
	
		
		$iar=explode(".", $index);
		if (isset($iar[1])) $idx=$iar[1];
		else $idx=$index;
		
		//$len = 0;
		while($db->next_record()) {
			$dt=$db->Record;
			$dt["recid"]=$db->Record[$idx];
			$data['records'][]= $dt;
		}
		return $data;
	}
	
	static function deleteRecords($table, $keyField, $data) {
		global $db;
		$res = Array();
	
		$recs = "";
		foreach ($data['selected'] as $k => $v) {
			if ($recs != "") $recs .= ", ";
			$recs .= "'".addslashes($v)."'";
		}
		$sql = "DELETE FROM $table WHERE $keyField IN ($recs)";
		$rs = $db->execute($sql);
		// check for error
		if ($db->res_errMsg != '') {
			$res['status'] = 'error';
			$res['message'] = $db->res_errMsg;
			return $res;
		}
		$res['status']  = 'success';
		$res['message'] = '';
		return $res;
	}
	
	static function saveRecord($table, $keyField, $data) {
		global $db;
	
		if ($data['recid'] == '' || $data['recid'] == '0') {
			$fields = "";
			$values = "";
			foreach ($data['record'] as $k => $v) {
				if ($k == $keyField) continue; // key field should not be here
				if ($fields != '') $fields .= ", ";
				if ($values != '') $values .= ", ";
				$fields .= addslashes($k);
				if (substr($v, 0, 2) == "__") {
					$values .= addslashes(substr($v, 2));
				} else {
					$values .= ($v == "" ? "null" : "'".addslashes($v)."'");
				}
			}
			$sql = "INSERT INTO $table($fields) VALUES($values)";
		} else {
			$values = "";
			foreach ($data['record'] as $k => $v) {
				if ($k == $keyField) continue; // key field should not be here
				if ($values != '') $values .= ", ";
				if (substr($v, 0, 2) == "__") {
					$values .= addslashes($k)." = ".addslashes(substr($v, 2));
				} else {
					$values .= addslashes($k)." = ".($v == "" ? "null" : "'".addslashes($v)."'");
				}
			}
			$sql = "UPDATE $table SET $values WHERE $keyField = ".addslashes($data['recid']);
		}
		// execute sql
		$rs = $db->execute($sql);
		// check for error
		if ($db->res_errMsg != '') {
			$res = Array();
			$res['status'] = 'error';
			$res['message'] = $db->res_errMsg;
			return $res;
		}
	
		$res = Array();
		$res['status']  = 'success';
		$res['message'] = '';
		return $res;
	}
	
	static function newRecord($table, $data) {
		global $db;
	
		$res    = Array();
		$fields = '';
		$values = '';
	
		foreach ($data as $k => $v) {
			if ($fields != '') $fields .= ",";
			if ($values != '') $values .= ",";
			$fields .= $k;
			if (substr($v, 0, 2) == "__") {
				$values .= addslashes(substr($v, 2));
			} else {
				$values .= ($v == "" ? "null" : "'".addslashes($v)."'");
			}
		}
	
		$sql = "INSERT INTO $table($fields) VALUES ($values)";
		$db->execute($sql);
		if ($db->res_errMsg != '') {
			$res['status']  = 'error';
			$res['message'] = $db->res_errMsg;
		} else {
			$res['status']  = 'success';
		}
		return $res;
	}
	
	static function getItems($sql) {
		global $db;
		$data = Array();
	
		// execute sql
		$rs = $db->execute($sql);
		// check for error
		if ($db->res_errMsg != '') {
			$data = Array();
			$data['status'] = 'error';
			$data['message'] = $db->res_errMsg;
			return $data;
		}
	
		$len = 0;
		$data['status']  = 'success';
		$data['total'] 	 = $db->res_rowCount;
		$data['items'] = Array();
		while ($rs && !$rs->EOF) {
			$data['items'][$len] = Array();
			$data['items'][$len]['id']   = $rs->fields[0];
			$data['items'][$len]['text'] = $rs->fields[1];
			foreach ($rs->fields as $k => $v) {
				if (intval($k) > 0 || $k == "0") continue;
				$data['items'][$len][$k] = $v;
			}
			$len++;
			if ($len >= $_REQUEST['max']) break;
			$rs->moveNext();
		}
		return $data;
	}
	
}

?>
