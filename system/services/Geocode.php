<?php
/**
 * Bleetz framework
 *
 * Geocoding service.
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

//en cours
//sur le modele de database

/**
 * 
 * @author CAO
 *
 */
class GoogleMap {
	var $XmlData=null;
	var $XmlObject=null;
	/**
	 * 
	 * @param unknown $adress
	 * @return boolean
	 */
	function LoadAddress($adress) {
		$base_url="http://maps.googleapis.com/maps/api/geocode/xml?";
		$request_url = $base_url . "address=" . urlencode($address).'&sensor=false';
		$xml_str = file_get_contents($request_url);
		if ($xml_str===false) {
			//ER::collect("Gmap doesn't load address")
			//set error
			return false;
		}
		
		$this->$XmlData=$xml_str;
		
		$xml=simplexml_load_string($xml_str);
		if ($xml->status!="OK") {
			//ER::collect($xml->status);
			return false;
		}
		
		return true;		
	}
	
	/**
	 * 
	 * @param unknown $xml_str
	 * @return boolean
	 */
	function LoadXml($xml_str) {
		$this->$XmlData=$xml_str;
		
		$xml=simplexml_load_string($xml_str);
		if ($xml->status!="OK") {
			//ER::collect($xml->status);
			return false;
		}
		
		return true;		
	}
	
	/**
	 * 
	 * @return string
	 */
	function GetXml() {
		return $XmlData;
	}
	
	/**
	 * 
	 * @return string
	 */
	function GetLatitude() {
		return $xml->result->geometry->location->lat;
	}
	
	/**
	 * 
	 * @return string
	 */
	function GetLongitude() {
		return $xml->result->geometry->location->lng;
	}
	
	/**
	 * 
	 * @param unknown $lat
	 * @param unknown $long
	 * @param number $width
	 * @param number $height
	 * @param number $zoom
	 * @return string
	 */
	function LoadMap($lat, $long, $width=190, $height=190, $zoom=16) {
		$map_url ="http://maps.googleapis.com/maps/api/staticmap?";
		$map_url.="size=$widthx$height";
		$map_url.="&sensor=false";
		$map_url.="&visual_refresh=false";
		$map_url.="&zoom=$zoom";
		$map_url.="&markers=size:big%7Ccolor:red%7Clabel:M%7C$lat,$long";
		
		$map_image = file_get_contents($map_url);
		return $map_image;
	}
	
}

/**
 * classe gmap
 * gere la geolocalisation google
 * 
 * @author CAO
 *
 */

class GC {

	public static $gc_hooks=array();
	
	/**
	 * Connection a un service de géolocalisation
	 * Si le service est déjà ouverte, renvoie l ancien service
	 *
	 * @return  static db connection
	 */
	static function connect($service) {
		if (self::$db_hooks_used<=0) return self::spawn($service);
		return self::$gc_hooks[self::$gc_hooks_used-1];
	}
	
	/**
	 * Nouvelle connection a un ervice de géolocalisation
	 *
	 * @return NULL|Database:
	 */
	static function spawn($service) {
		self::$db_hooks_used++;
		if (self::$gc_hooks_used>self::$gc_hooks_opened) {
			
			$gc=new $service;

			self::$db_hooks[]=$db;
			self::$db_hooks_opened++;
		}
		return self::$gc_hooks[self::$gc_hooks_used-1];
	}
	
	/**
	 * LoadAddressGeocode
	 *
	 * Charge les données de géolacalisation de l'adresse
	 *
	 * @param unknown $address
	 * @return string
	 */
	static function Geocode($address)
	{
		$gmap=new GoogleMap;
	
		if ($gmap->LoadAddress($address)) return $gmap;
	
		return null;
	}
	
	/**
	 * LoadXml
	 * 
	 * 
	 * @param unknown $xml
	 * @return GoogleMap|NULL
	 */
	static function LoadXml($xml)
	{
		$gmap=new GoogleMap;
	
		if ($gmap->LoadXml($xml)) return $gmap;
	
		return null;
	}
			
}

?>