<?php
/**
 * Bleetz framework
 *
 * Fuzzy search service.
 * a collectoin of fuzzy search algorythms
 * DoubleMetaphone Functional 1.01 from http://swoodbridge.com/DoubleMetaPhone/ (copyright included)
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

/**
 * Name:		double_metaphone( $string )
 * Purpose:		Get the primary and secondary double metaphone tokens
 * Return:		Array: if secondary == primary, secondary = NULL
 */

/**
 * VERSION
 *
 * DoubleMetaphone Functional 1.01 (altered)
 *
 * DESCRIPTION
 *
 * This function implements a "sounds like" algorithm developed
 * by Lawrence Philips which he published in the June, 2000 issue
 * of C/C++ Users Journal.  Double Metaphone is an improved
 * version of Philips' original Metaphone algorithm.
 *
 * COPYRIGHT
 *
 * Slightly adapted from the class by Stephen Woodbridge.
 * Copyright 2001, Stephen Woodbridge <woodbri@swoodbridge.com>
 * All rights reserved.
 *
 * http://swoodbridge.com/DoubleMetaPhone/
 *
 * This PHP translation is based heavily on the C implementation
 * by Maurice Aubrey <maurice@hevanet.com>, which in turn
 * is based heavily on the C++ implementation by
 * Lawrence Philips and incorporates several bug fixes courtesy
 * of Kevin Atkinson <kevina@users.sourceforge.net>.
 *
 * This module is free software; you may redistribute it and/or
 * modify it under the same terms as Perl itself.
 *
 *
 * CONTRIBUTIONS
 *
 * 2002/05/17 Geoff Caplan  http://www.advantae.com
 *   Bug fix: added code to return class object which I forgot to do
 	*   Created a functional callable version instead of the class version
 *   which is faster if you are calling this a lot.
 *
 * 2013/05/04 Steen R̩mi
 *   New indentation of the code for better readability
 *   Some small alterations
 *   Replace ereg by preg_match
 *     ( ereg : This function has been DEPRECATED as of PHP 5.3.0 )
 *   Improve performance (10 - 20 % faster)
 */


/**
 * classe FZ
 * fuzzy search
 * 
 * @author CAO
 *
 */

class FZ {


	static function double_metaphone( $string )
	{
		$primary = '';
		$secondary = '';
		$current = 0;
		$length = strlen( $string );
		$last = $length - 1;
		$original = strtoupper( $string ).'     ';
	
		// skip this at beginning of word
		if (self::string_at($original, 0, 2, array('GN','KN','PN','WR','PS'))){
			$current++;
		}
	
		// Initial 'X' is pronounced 'Z' e.g. 'Xavier'
		if (substr($original, 0, 1) == 'X'){
			$primary   .= 'S'; // 'Z' maps to 'S'
			$secondary .= 'S';
			$current++;
		}
	
		// main loop
	
		while (strlen($primary) < 4 || strlen($secondary) < 4){
			if ($current >= $length){
				break;
			}
	
			// switch (substr($original, $current, 1)){
			switch ($original[$current]){
				case 'A':
				case 'E':
				case 'I':
				case 'O':
				case 'U':
				case 'Y':
					if ($current == 0){
						// all init vowels now map to 'A'
						$primary   .= 'A';
						$secondary .= 'A';
					}
					++$current;
					break;
	
				case 'B':
					// '-mb', e.g. "dumb", already skipped over ...
					$primary   .= 'P';
					$secondary .= 'P';
	
					if (substr($original, $current + 1, 1) == 'B'){
						$current += 2;
					} else {
						++$current;
					}
					break;
	
				case '��':
					$primary   .= 'S';
					$secondary .= 'S';
					++$current;
					break;
	
				case 'C':
					// various gremanic
					if ($current > 1
					&& !self::is_vowel($original, $current - 2)
					&& self::string_at($original, $current - 1, 3, array('ACH'))
					&& (
					(substr($original, $current + 2, 1) != 'I')
					&& (
					(substr($original, $current + 2, 1) != 'E')
					|| self::string_at($original, $current - 2, 6, array('BACHER', 'MACHER'))
					)
					)
					){
						$primary   .= 'K';
						$secondary .= 'K';
						$current += 2;
						break;
					}
	
					// special case 'caesar'
					if ($current == 0
							&& self::string_at($original, $current, 6, array('CAESAR'))
					){
						$primary   .= 'S';
						$secondary .= 'S';
						$current += 2;
						break;
					}
	
					// italian 'chianti'
					if (self::string_at($original, $current, 4, array('CHIA'))){
						$primary   .= 'K';
						$secondary .= 'K';
						$current += 2;
						break;
					}
	
					if (self::string_at($original, $current, 2, array('CH'))){
	
						// find 'michael'
						if ($current > 0
								&& self::string_at($original, $current, 4, array('CHAE'))
						){
							$primary   .= 'K';
							$secondary .= 'X';
							$current += 2;
							break;
						}
	
						// greek roots e.g. 'chemistry', 'chorus'
						if ($current == 0
								&& (
										self::string_at($original, $current + 1, 5, array('HARAC', 'HARIS'))
										|| self::string_at($original, $current + 1, 3, array('HOR', 'HYM', 'HIA', 'HEM'))
								)
								&& !self::string_at($original, 0, 5, array('CHORE'))
						){
							$primary   .= 'K';
							$secondary .= 'K';
							$current += 2;
							break;
						}
	
						// germanic, greek, or otherwise 'ch' for 'kh' sound
						if ((
								self::string_at($original, 0, 4, array('VAN ', 'VON '))
							 || self::string_at($original, 0, 3, array('SCH'))
						)
								// 'architect' but not 'arch', orchestra', 'orchid'
								|| self::string_at($original, $current - 2, 6, array('ORCHES', 'ARCHIT', 'ORCHID'))
								|| self::string_at($original, $current + 2, 1, array('T', 'S'))
								|| (
										(
												self::string_at($original, $current - 1, 1, array('A','O','U','E'))
												|| $current == 0
										)
										// e.g. 'wachtler', 'weschsler', but not 'tichner'
										&& self::string_at($original, $current + 2, 1, array('L','R','N','M','B','H','F','V','W',' '))
								)
						){
							$primary   .= 'K';
							$secondary .= 'K';
						} else {
							if ($current > 0){
								if (self::string_at($original, 0, 2, array('MC'))){
									// e.g. 'McHugh'
									$primary   .= 'K';
									$secondary .= 'K';
								} else {
									$primary   .= 'X';
									$secondary .= 'K';
								}
							} else {
								$primary   .= 'X';
								$secondary .= 'X';
							}
						}
						$current += 2;
						break;
					}
	
					// e.g. 'czerny'
					if (self::string_at($original, $current, 2, array('CZ'))
							&& !self::string_at($original, $current -2, 4, array('WICZ'))
					){
						$primary   .= 'S';
						$secondary .= 'X';
						$current += 2;
						break;
					}
	
					// e.g. 'focaccia'
					if (self::string_at($original, $current + 1, 3, array('CIA'))){
						$primary   .= 'X';
						$secondary .= 'X';
						$current += 3;
						break;
					}
	
					// double 'C', but not McClellan'
					if (self::string_at($original, $current, 2, array('CC'))
							&& !(
									$current == 1
									&& substr($original, 0, 1) == 'M'
							)
					){
						// 'bellocchio' but not 'bacchus'
						if (self::string_at($original, $current + 2, 1, array('I','E','H'))
								&& !self::string_at($original, $current + 2, 2, array('HU'))
						){
							// 'accident', 'accede', 'succeed'
							if ((
									$current == 1
								 && substr($original, $current - 1, 1) == 'A'
							)
									|| self::string_at($original, $current - 1, 5,array('UCCEE', 'UCCES'))
							){
								$primary   .= 'KS';
								$secondary .= 'KS';
								// 'bacci', 'bertucci', other italian
							} else {
								$primary   .= 'X';
								$secondary .= 'X';
							}
							$current += 3;
							break;
						} else {
							// Pierce's rule
							$primary   .= 'K';
							$secondary .= 'K';
							$current += 2;
							break;
						}
					}
	
					if (self::string_at($original, $current, 2, array('CK','CG','CQ'))){
						$primary   .= 'K';
						$secondary .= 'K';
						$current += 2;
						break;
					}
	
					if (self::string_at($original, $current, 2, array('CI','CE','CY'))){
						// italian vs. english
						if (self::string_at($original, $current, 3, array('CIO','CIE','CIA'))){
							$primary   .= 'S';
							$secondary .= 'X';
						} else {
							$primary   .= 'S';
							$secondary .= 'S';
						}
						$current += 2;
						break;
					}
	
					// else
					$primary   .= 'K';
					$secondary .= 'K';
	
					// name sent in 'mac caffrey', 'mac gregor'
					if (self::string_at($original, $current + 1, 2, array(' C',' Q',' G'))){
						$current += 3;
					} else {
						if (self::string_at($original, $current + 1, 1, array('C','K','Q'))
								&& !self::string_at($original, $current + 1, 2, array('CE','CI'))
						){
							$current += 2;
						} else {
							++$current;
						}
					}
					break;
	
				case 'D':
					if (self::string_at($original, $current, 2, array('DG'))){
						if (self::string_at($original, $current + 2, 1, array('I','E','Y'))){
							// e.g. 'edge'
							$primary   .= 'J';
							$secondary .= 'J';
							$current += 3;
							break;
						} else {
							// e.g. 'edgar'
							$primary   .= 'TK';
							$secondary .= 'TK';
							$current += 2;
							break;
						}
					}
	
					if (self::string_at($original, $current, 2, array('DT','DD'))){
						$primary   .= 'T';
						$secondary .= 'T';
						$current += 2;
						break;
					}
	
					// else
					$primary   .= 'T';
					$secondary .= 'T';
					++$current;
					break;
	
				case 'F':
					if (substr($original, $current + 1, 1) == 'F'){
						$current += 2;
					} else {
						++$current;
					}
					$primary   .= 'F';
					$secondary .= 'F';
					break;
	
				case 'G':
					if (substr($original, $current + 1, 1) == 'H'){
						if ($current > 0
								&& !self::is_vowel($original, $current - 1)
						){
							$primary   .= 'K';
							$secondary .= 'K';
							$current += 2;
							break;
						}
	
						if ($current < 3){
							// 'ghislane', 'ghiradelli'
							if ($current == 0){
								if (substr($original, $current + 2, 1) == 'I'){
									$primary   .= 'J';
									$secondary .= 'J';
								} else {
									$primary   .= 'K';
									$secondary .= 'K';
								}
								$current += 2;
								break;
							}
						}
	
						// Parker's rule (with some further refinements) - e.g. 'hugh'
						if ((
								$current > 1
							 && self::string_at($original, $current - 2, 1, array('B','H','D'))
						)
								// e.g. 'bough'
								|| (
										$current > 2
										&& self::string_at($original, $current - 3, 1, array('B','H','D'))
								)
								// e.g. 'broughton'
								|| (
										$current > 3
										&& self::string_at($original, $current - 4, 1, array('B','H'))
								)
						){
							$current += 2;
							break;
						} else {
							// e.g. 'laugh', 'McLaughlin', 'cough', 'gough', 'rough', 'tough'
							if ($current > 2
									&& substr($original, $current - 1, 1) == 'U'
									&& self::string_at($original, $current - 3, 1,array('C','G','L','R','T'))
							){
								$primary   .= 'F';
								$secondary .= 'F';
							} else if (
									$current > 0
									&& substr($original, $current - 1, 1) != 'I'
							){
								$primary   .= 'K';
								$secondary .= 'K';
							}
							$current += 2;
							break;
						}
					}
	
					if (substr($original, $current + 1, 1) == 'N'){
						if ($current == 1
								&& self::is_vowel($original, 0)
								&& !self::Slavo_Germanic($original)
						){
							$primary   .= 'KN';
							$secondary .= 'N';
						} else {
							// not e.g. 'cagney'
							if (!self::string_at($original, $current + 2, 2, array('EY'))
									&& substr($original, $current + 1) != 'Y'
									&& !self::Slavo_Germanic($original)
							){
								$primary   .= 'N';
								$secondary .= 'KN';
							} else {
								$primary   .= 'KN';
								$secondary .= 'KN';
							}
						}
						$current += 2;
						break;
					}
	
					// 'tagliaro'
					if (self::string_at($original, $current + 1, 2,array('LI'))
							&& !self::Slavo_Germanic($original)
					){
						$primary   .= 'KL';
						$secondary .= 'L';
						$current += 2;
						break;
					}
	
					// -ges-, -gep-, -gel- at beginning
					if ($current == 0
							&& (
									substr($original, $current + 1, 1) == 'Y'
									|| self::string_at($original, $current + 1, 2, array('ES','EP','EB','EL','EY','IB','IL','IN','IE','EI','ER'))
							)
					){
						$primary   .= 'K';
						$secondary .= 'J';
						$current += 2;
						break;
					}
	
					// -ger-, -gy-
					if ((
							self::string_at($original, $current + 1, 2,array('ER'))
						 || substr($original, $current + 1, 1) == 'Y'
					)
							&& !self::string_at($original, 0, 6, array('DANGER','RANGER','MANGER'))
							&& !self::string_at($original, $current -1, 1, array('E', 'I'))
							&& !self::string_at($original, $current -1, 3, array('RGY','OGY'))
					){
						$primary   .= 'K';
						$secondary .= 'J';
						$current += 2;
						break;
					}
	
					// italian e.g. 'biaggi'
					if (self::string_at($original, $current + 1, 1, array('E','I','Y'))
							|| self::string_at($original, $current -1, 4, array('AGGI','OGGI'))
					){
						// obvious germanic
						if ((
								self::string_at($original, 0, 4, array('VAN ', 'VON '))
							 || self::string_at($original, 0, 3, array('SCH'))
						)
								|| self::string_at($original, $current + 1, 2, array('ET'))
						){
							$primary   .= 'K';
							$secondary .= 'K';
						} else {
							// always soft if french ending
							if (self::string_at($original, $current + 1, 4, array('IER '))){
								$primary   .= 'J';
								$secondary .= 'J';
							} else {
								$primary   .= 'J';
								$secondary .= 'K';
							}
						}
						$current += 2;
						break;
					}
	
					if (substr($original, $current +1, 1) == 'G'){
						$current += 2;
					} else {
						++$current;
					}
	
					$primary   .= 'K';
					$secondary .= 'K';
					break;
	
				case 'H':
					// only keep if first & before vowel or btw. 2 vowels
					if ((
					$current == 0
					|| self::is_vowel($original, $current - 1)
					)
					&& self::is_vowel($original, $current + 1)
					){
						$primary   .= 'H';
						$secondary .= 'H';
						$current += 2;
					} else {
						++$current;
					}
					break;
	
				case 'J':
					// obvious spanish, 'jose', 'san jacinto'
					if (self::string_at($original, $current, 4, array('JOSE'))
					|| self::string_at($original, 0, 4, array('SAN '))
					){
						if ((
								$current == 0
							 && substr($original, $current + 4, 1) == ' '
						)
								|| self::string_at($original, 0, 4, array('SAN '))
						){
							$primary   .= 'H';
							$secondary .= 'H';
						} else {
							$primary   .= 'J';
							$secondary .= 'H';
						}
						++$current;
						break;
					}
	
					if ($current == 0
							&& !self::string_at($original, $current, 4, array('JOSE'))
					){
						$primary   .= 'J';  // Yankelovich/Jankelowicz
						$secondary .= 'A';
					} else {
						// spanish pron. of .e.g. 'bajador'
						if (self::is_vowel($original, $current - 1)
								&& !self::Slavo_Germanic($original)
								&& (
										substr($original, $current + 1, 1) == 'A'
										|| substr($original, $current + 1, 1) == 'O'
								)
						){
							$primary   .= 'J';
							$secondary .= 'H';
						} else {
							if ($current == $last){
								$primary   .= 'J';
								// $secondary .= '';
							} else {
								if (!self::string_at($original, $current + 1, 1, array('L','T','K','S','N','M','B','Z'))
										&& !self::string_at($original, $current - 1, 1, array('S','K','L'))
								){
									$primary   .= 'J';
									$secondary .= 'J';
								}
							}
						}
					}
	
					if (substr($original, $current + 1, 1) == 'J'){ // it could happen
						$current += 2;
					} else {
						++$current;
					}
					break;
	
				case 'K':
					if (substr($original, $current + 1, 1) == 'K'){
						$current += 2;
					} else {
						++$current;
					}
					$primary   .= 'K';
					$secondary .= 'K';
					break;
	
				case 'L':
					if (substr($original, $current + 1, 1) == 'L'){
						// spanish e.g. 'cabrillo', 'gallegos'
						if ((
								$current == ($length - 3)
							 && self::string_at($original, $current - 1, 4, array('ILLO','ILLA','ALLE'))
						)
								|| (
										(
												self::string_at($original, $last-1, 2, array('AS','OS'))
												|| self::string_at($original, $last, 1, array('A','O'))
										)
										&& self::string_at($original, $current - 1, 4, array('ALLE'))
								)
						){
							$primary   .= 'L';
							// $secondary .= '';
							$current += 2;
							break;
						}
						$current += 2;
					} else {
						++$current;
					}
					$primary   .= 'L';
					$secondary .= 'L';
					break;
	
				case 'M':
					if ((
					self::string_at($original, $current - 1, 3,array('UMB'))
					&& (
					($current + 1) == $last
					|| self::string_at($original, $current + 2, 2, array('ER'))
					)
					)
					// 'dumb', 'thumb'
					|| substr($original, $current + 1, 1) == 'M'
							){
								$current += 2;
							} else {
								++$current;
							}
							$primary   .= 'M';
							$secondary .= 'M';
							break;
	
				case 'N':
					if (substr($original, $current + 1, 1) == 'N'){
						$current += 2;
					} else {
						++$current;
					}
					$primary   .= 'N';
					$secondary .= 'N';
					break;
	
				case '��':
					++$current;
					$primary   .= 'N';
					$secondary .= 'N';
					break;
	
				case 'P':
					if (substr($original, $current + 1, 1) == 'H'){
						$current += 2;
						$primary   .= 'F';
						$secondary .= 'F';
						break;
					}
	
					// also account for "campbell" and "raspberry"
					if (self::string_at($original, $current + 1, 1, array('P','B'))){
						$current += 2;
					} else {
						++$current;
					}
					$primary   .= 'P';
					$secondary .= 'P';
					break;
	
				case 'Q':
					if (substr($original, $current + 1, 1) == 'Q'){
						$current += 2;
					} else {
						++$current;
					}
					$primary   .= 'K';
					$secondary .= 'K';
					break;
	
				case 'R':
					// french e.g. 'rogier', but exclude 'hochmeier'
					if ($current == $last
					&& !self::Slavo_Germanic($original)
					&& self::string_at($original, $current - 2, 2,array('IE'))
					&& !self::string_at($original, $current - 4, 2,array('ME','MA'))
					){
						// $primary   .= '';
						$secondary .= 'R';
					} else {
						$primary   .= 'R';
						$secondary .= 'R';
					}
					if (substr($original, $current + 1, 1) == 'R'){
						$current += 2;
					} else {
						++$current;
					}
					break;
	
				case 'S':
					// special cases 'island', 'isle', 'carlisle', 'carlysle'
					if (self::string_at($original, $current - 1, 3, array('ISL','YSL'))){
						++$current;
						break;
					}
	
					// special case 'sugar-'
					if ($current == 0
							&& self::string_at($original, $current, 5, array('SUGAR'))
					){
						$primary   .= 'X';
						$secondary .= 'S';
						++$current;
						break;
					}
	
					if (self::string_at($original, $current, 2, array('SH'))){
						// germanic
						if (self::string_at($original, $current + 1, 4, array('HEIM','HOEK','HOLM','HOLZ'))){
							$primary   .= 'S';
							$secondary .= 'S';
						} else {
							$primary   .= 'X';
							$secondary .= 'X';
						}
						$current += 2;
						break;
					}
	
					// italian & armenian
					if (self::string_at($original, $current, 3, array('SIO','SIA'))
							|| self::string_at($original, $current, 4, array('SIAN'))
					){
						if (!self::Slavo_Germanic($original)){
							$primary   .= 'S';
							$secondary .= 'X';
						} else {
							$primary   .= 'S';
							$secondary .= 'S';
						}
						$current += 3;
						break;
					}
	
					// german & anglicisations, e.g. 'smith' match 'schmidt', 'snider' match 'schneider'
					// also, -sz- in slavic language altho in hungarian it is pronounced 's'
					if ((
							$current == 0
						 && self::string_at($original, $current + 1, 1, array('M','N','L','W'))
					)
							|| self::string_at($original, $current + 1, 1, array('Z'))
					){
						$primary   .= 'S';
						$secondary .= 'X';
						if (self::string_at($original, $current + 1, 1, array('Z'))){
							$current += 2;
						} else {
							++$current;
						}
						break;
					}
	
					if (self::string_at($original, $current, 2, array('SC'))){
						// Schlesinger's rule
						if (substr($original, $current + 2, 1) == 'H')
							// dutch origin, e.g. 'school', 'schooner'
							if (self::string_at($original, $current + 3, 2, array('OO','ER','EN','UY','ED','EM'))){
							// 'schermerhorn', 'schenker'
						if (self::string_at($original, $current + 3, 2, array('ER','EN'))){
							$primary   .= 'X';
							$secondary .= 'SK';
						} else {
							$primary   .= 'SK';
							$secondary .= 'SK';
						}
						$current += 3;
						break;
						} else {
							if ($current == 0
									&& !self::is_vowel($original, 3)
									&& substr($original, $current + 3, 1) != 'W'
							){
								$primary   .= 'X';
								$secondary .= 'S';
							} else {
								$primary   .= 'X';
								$secondary .= 'X';
							}
							$current += 3;
							break;
						}
	
						if (self::string_at($original, $current + 2, 1,array('I','E','Y'))){
							$primary   .= 'S';
							$secondary .= 'S';
							$current += 3;
							break;
						}
	
						// else
						$primary   .= 'SK';
						$secondary .= 'SK';
						$current += 3;
						break;
					}
	
					// french e.g. 'resnais', 'artois'
					if ($current == $last
							&& self::string_at($original, $current - 2, 2, array('AI','OI'))
					){
						// $primary   .= '';
						$secondary .= 'S';
					} else {
						$primary   .= 'S';
						$secondary .= 'S';
					}
	
					if (self::string_at($original, $current + 1, 1, array('S','Z'))){
						$current += 2;
					} else {
						++$current;
					}
					break;
	
				case 'T':
					if (self::string_at($original, $current, 4, array('TION'))){
						$primary   .= 'X';
						$secondary .= 'X';
						$current += 3;
						break;
					}
	
					if (self::string_at($original, $current, 3, array('TIA','TCH'))){
						$primary   .= 'X';
						$secondary .= 'X';
						$current += 3;
						break;
					}
	
					if (self::string_at($original, $current, 2, array('TH'))
							|| self::string_at($original, $current, 3, array('TTH'))
					){
						// special case 'thomas', 'thames' or germanic
						if (self::string_at($original, $current + 2, 2, array('OM','AM'))
								|| self::string_at($original, 0, 4, array('VAN ','VON '))
								|| self::string_at($original, 0, 3, array('SCH'))
						){
							$primary   .= 'T';
							$secondary .= 'T';
						} else {
							$primary   .= '0';
							$secondary .= 'T';
						}
						$current += 2;
						break;
					}
	
					if (self::string_at($original, $current + 1, 1, array('T','D'))){
						$current += 2;
					} else {
						++$current;
					}
					$primary   .= 'T';
					$secondary .= 'T';
					break;
	
				case 'V':
					if (substr($original, $current + 1, 1) == 'V'){
						$current += 2;
					} else {
						++$current;
					}
					$primary   .= 'F';
					$secondary .= 'F';
					break;
	
				case 'W':
					// can also be in middle of word
					if (self::string_at($original, $current, 2, array('WR'))){
						$primary   .= 'R';
						$secondary .= 'R';
						$current += 2;
						break;
					}
	
					if (($current == 0)
							&& (
									self::is_vowel($original, $current + 1)
									|| self::string_at($original, $current, 2, array('WH'))
							)
					){
						// Wasserman should match Vasserman
						if (self::is_vowel($original, $current + 1)){
							$primary   .= 'A';
							$secondary .= 'F';
						} else {
							// need Uomo to match Womo
							$primary   .= 'A';
							$secondary .= 'A';
						}
					}
	
					// Arnow should match Arnoff
					if ((
							$current == $last
							&& self::is_vowel($original, $current - 1)
					)
							|| self::string_at($original, $current - 1, 5, array('EWSKI','EWSKY','OWSKI','OWSKY'))
							|| self::string_at($original, 0, 3, array('SCH'))
					){
						// $primary   .= '';
						$secondary .= 'F';
						++$current;
						break;
					}
	
					// polish e.g. 'filipowicz'
					if (self::string_at($original, $current, 4,array('WICZ','WITZ'))){
						$primary   .= 'TS';
						$secondary .= 'FX';
						$current += 4;
						break;
					}
	
					// else skip it
					++$current;
					break;
	
				case 'X':
					// french e.g. breaux
					if (!(
					$current == $last
					&& (
					self::string_at($original, $current - 3, 3, array('IAU', 'EAU'))
					|| self::string_at($original, $current - 2, 2, array('AU', 'OU'))
					)
					)
					){
						$primary   .= 'KS';
						$secondary .= 'KS';
					}
	
					if (self::string_at($original, $current + 1, 1, array('C','X'))){
						$current += 2;
					} else {
						++$current;
					}
					break;
	
				case 'Z':
					// chinese pinyin e.g. 'zhao'
					if (substr($original, $current + 1, 1) == 'H'){
						$primary   .= 'J';
						$secondary .= 'J';
						$current += 2;
						break;
	
					} else if (
							self::string_at($original, $current + 1, 2, array('ZO', 'ZI', 'ZA'))
							|| (
									self::Slavo_Germanic($original)
									&& (
											$current > 0
											&& substr($original, $current - 1, 1) != 'T'
									)
							)
					){
						$primary   .= 'S';
						$secondary .= 'TS';
					} else {
						$primary   .= 'S';
						$secondary .= 'S';
					}
	
					if (substr($original, $current + 1, 1) == 'Z'){
						$current += 2;
					} else {
						++$current;
					}
					break;
	
				default:
					++$current;
	
			} // end switch
	
		} // end while
	
		// printf("<br />ORIGINAL:   %s\n", $original);
		// printf("<br />current:    %s\n", $current);
		// printf("<br />PRIMARY:    %s\n", $primary);
		// printf("<br />SECONDARY:  %s\n", $secondary);
	
		$primary = substr($primary, 0, 4);
		$secondary = substr($secondary, 0, 4);
	
		if( $primary == $secondary ){
			$secondary = NULL;
		}
	
		return array(
				'primary'	=> $primary,
				'secondary'	=> $secondary
		);
	
	} // end of function MetaPhone
	
	
	/**
	 * Name:	self::string_at($string, $start, $length, $list)
	 * Purpose:	Helper function for double_metaphone( )
	 * Return:	Bool
	 */
	static function string_at($string, $start, $length, $list){
		if ($start < 0
				|| $start >= strlen($string)
		){
			return 0;
		}
	
		foreach ($list as $t){
			if ($t == substr($string, $start, $length)){
				return 1;
			}
		}
	
		return 0;
	}
	
	
	/**
	 * Name:	self::is_vowel($string, $pos)
	 * Purpose:	Helper function for double_metaphone( )
	 * Return:	Bool
	 */
	static function is_vowel($string, $pos){
		return preg_match("[AEIOUY]", substr($string, $pos, 1));
	}
	
	
	/**
	 * Name:	Slavo_Germanic($string, $pos)
	 * Purpose:	Helper function for double_metaphone( )
	 * Return:	Bool
	 */
	
	static function Slavo_Germanic($string){
		return preg_match("W|K|CZ|WITZ", $string);
	}
			
}


?>

