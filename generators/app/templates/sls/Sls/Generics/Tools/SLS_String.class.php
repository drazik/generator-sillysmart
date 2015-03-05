<?php
/**
 * Tool SLS_String - String Treatment
 * 
 * @access static
 * @author Laurent Bientz 
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Tools
 * @since 1.0  
 */
class SLS_String 
{
	/**
	* Validate the type 'string'
	* 
	* @access public static	
	* @param string $element the string to test
	* @param bool $require_content true if you want to check that the string is not empty, else false
	* @return bool $isString true if really a string, else false
	* @see SLS_String::validateArray
	* @since 1.0
	* @example 
	* var_dump(SLS_String::validateString(1234));
	* // will produce : false	
	* @example 
	* var_dump(SLS_String::validateString("",true));
	* // will produce : false
	* @example 
	* var_dump(SLS_String::validateString("",false));
	* // will produce : true
	*/
	public static function validateString($element, $require_content = true)
	{
		return (!is_string($element)) ? false : ($require_content && $element == '' ? false : true);
	}
	
	/**
	* Validate the type 'array'
	*	
	* @access public static
	* @param array $element the array to test
	* @param bool $require_content true if you want to check that the array is not empty, else false
	* @return bool $isArray true if array, else false
	* @see SLS_String::validateString
	* @since 1.0
	* @example 
	* var_dump(SLS_String::validateString(1234));
	* // will produce : false	
	* @example 
	* var_dump(SLS_String::validateString(array(),true));
	* // will produce : false
	* @example 
	* var_dump(SLS_String::validateString(array(),false));
	* // will produce : true
	*/
	public static function validateArray($element, $require_content = true)
	{
		return (!is_array($element)) ? false : ($require_content && empty($element) ? false : true);
	}

	/**
	 * Validate an email
	 *	 
	 * @access public static
	 * @param string $email the email to test
	 * @return bool $isEmail true if it's a correct email, else false
	 * @see SLS_String::isIp
	 * @see SLS_String::isValidUrl
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::validateEmail("laurent@sillysmart.org"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_String::validateEmail("laurent@sillysmart"));
	 * // will produce : false
	 */
	public static function validateEmail($email)
	{		
		return (preg_match("/[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,3}/i", $email)) ? true : false;
	}
	
	/**
	 * Equivalent to native php function array_multisort() but recursive on the columns
	 *
	 * @access public static	 
	 * @param array $data the array to sort
	 * @param array $keys arobrescence of the sort
	 * @return array $result the array sorted
	 * @since 1.0
	 * @example 
	 * $test = array(
	 * 					0 => array(
	 * 							"day" => "04",
	 * 							"month" => "05",
	 * 							"year" => "2007"),
	 * 					1 => array(
	 * 							"day" => "03",
	 * 							"month" => "03",
	 * 							"year" => "2007"),
	 * 					2 => array(
	 * 							"day" => "07",
	 * 							"month" => "04",
	 * 							"year" => "2007")
	 * 				);
	 * print_r(SLS_String::arrayMultiSort($test,array(
	 * 													array(
	 * 															'key' => 'year',
	 * 															'sort'=>'desc'),
	 * 													array(
	 * 															'key' =>'month',
	 * 															'sort'=>'desc'),
	 * 													array(
	 * 															'key' =>'day',
	 * 															'sort'=>'desc')
	 * 												)));
	 * // will produce :
	 * array(	0 => array(
	 * 					"day" => "04",
	 * 					"month" => "05",
	 * 					"year" => "2007"),
	 * 			1 => array(
	 * 					"day" => "07",
	 *					"month"	=> "04",
	 * 					"year" => "2007"),
	 * 			2 => array(
	 * 					"day"	=> "03",
	 * 					"month"	=> "03",
	 * 					"year"	=> "2007")
	 * 		); 
	 */
	public static function arrayMultiSort($data,$keys)
	{   
		foreach ($data as $key => $row)
			foreach ($keys as $k)
				$cols[$k['key']][$key] = $row[$k['key']];    
		
		$idkeys=array_keys($data);
		
		$i=0;
		foreach ($keys as $k)
		{
			if($i>0)
				$sort.=',';
			$sort.='$cols['.$k['key'].']';
			if($k['sort'])
				$sort.=',SORT_'.strtoupper($k['sort']);
			if($k['type'])
				$sort.=',SORT_'.strtoupper($k['type']);
			$i++;
		}
		$sort.=',$idkeys';
		
		$sort='array_multisort('.$sort.');';
		eval($sort);
		
		foreach($idkeys as $idkey)
			$result[$idkey]=$data[$idkey];
		
		return $result;
	}

	/**
	 * Convert symbols to html entities
	 *	 
	 * @access public static
	 * @param string $string the string to convert
	 * @return string $string the string converted
	 * @see SLS_String::convertSymbolsToUFT8
	 * @see SLS_String::convertSymbolsToIsoEntities
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::convertSymbolsToHtmlEntities("© SillySmart ©"));
	 * // will produce "&copy; SillySmart &copy;"
	 */
	public static function convertSymbolsToHtmlEntities($string)
	{
		static $symbols =
		array(
		'‚', 'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹', 'Œ', '‘', '’', '“', '”',
		'•', '–', '—', '˜', '™', 'š', '›', 'œ', 'Ÿ', '€', 'Æ', 'Á', 'Â', 'À', 'Å',
		'Ã', 'Ä', 'Ç', 'Ð', 'É', 'Ê', 'È', 'Ë', 'Í', 'Î', 'Ì', 'Ï', 'Ñ', 'Ó', 'Ô',
		'Ò', 'Ø', 'Õ', 'Ö', 'Þ', 'Ú', 'Û', 'Ù', 'Ü', 'Ý', 'á', 'â', 'æ', 'à', 'å',
		'ã', 'ä', 'ç', 'é', 'ê', 'è', 'ð', 'ë', 'í', 'î', 'ì', 'ï', 'ñ', 'ó', 'ô',
		'ò', 'ø', 'õ', 'ö', 'ß', 'þ', 'ú', 'û', 'ù', 'ü', 'ý', 'ÿ', '¡', '£', '¤',
		'¥', '¦', '§', '¨', '©', 'ª', '«', '¬', '­', '®', '¯', '°', '±', '²', '³',
		'´', 'µ', '¶', '·', '¸', '¹', 'º', '»', '¼', '½', '¾', '¿', '×', '÷', '¢',
		'…', 'µ');
		static $entities =
		array(
		'&#8218;',  '&#402;',   '&#8222;',  '&#8230;',  '&#8224;',  '&#8225;',  '&#710;',
		'&#8240;',  '&#352;',   '&#8249;',  '&#338;',   '&#8216;',  '&#8217;',  '&#8220;',
		'&#8221;',  '&#8226;',  '&#8211;',  '&#8212;',  '&#732;',   '&#8482;',  '&#353;',
		'&#8250;',  '&#339;',   '&#376;',   '&#8364;',  '&aelig;',  '&aacute;', '&acirc;',
		'&agrave;', '&aring;',  '&atilde;', '&auml;',   '&ccedil;', '&eth;',    '&eacute;',
		'&ecirc;',  '&egrave;', '&euml;',   '&iacute;', '&icirc;',  '&igrave;', '&iuml;',
		'&ntilde;', '&oacute;', '&ocirc;',  '&ograve;', '&oslash;', '&otilde;', '&ouml;',
		'&thorn;',  '&uacute;', '&ucirc;',  '&ugrave;', '&uuml;',   '&yacute;', '&aacute;',
		'&acirc;',  '&aelig;',  '&agrave;', '&aring;',  '&atilde;', '&auml;',   '&ccedil;',
		'&eacute;', '&ecirc;',  '&egrave;', '&eth;',    '&euml;',   '&iacute;', '&icirc;',
		'&igrave;', '&iuml;',   '&ntilde;', '&oacute;', '&ocirc;',  '&ograve;', '&oslash;',
		'&otilde;', '&ouml;',   '&szlig;',  '&thorn;',  '&uacute;', '&ucirc;',  '&ugrave;',
		'&uuml;',   '&yacute;', '&yuml;',   '&iexcl;',  '&pound;',  '&curren;', '&yen;',
		'&brvbar;', '&sect;',   '&uml;',    '&copy;',   '&ordf;',   '&laquo;',  '&not;',
		'&shy;',    '&reg;',    '&masr;',   '&deg;',    '&plusmn;', '&sup2;',   '&sup3;',
		'&acute;',  '&micro;',  '&para;',   '&middot;', '&cedil;',  '&sup1;',   '&ordm;',
		'&raquo;',  '&frac14;', '&frac12;', '&frac34;', '&iquest;', '&times;',  '&divide;',
		'&cent;',   '...',      '&micro;');

		if (self::validateString($string, false)) 
		{
			return str_replace($symbols, $entities, $string);
		}
		else 
		{
			return $string;
		}
	}
	
	/**
	 * Convert symbols to utf-8
	 *	 
	 * @access public static
	 * @param string $string the string to convert
	 * @return string $string the string converted
	 * @see SLS_String::convertSymbolsToHtmlEntities
	 * @see SLS_String::convertSymbolsToIsoEntities
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::convertSymbolsToUFT8("© SillySmart ©"));
	 * // will produce "=C2=B7 SillySmart =C2=B7"
	 */
	public static function convertSymbolsToUFT8($string)
	{
		static $symbols =
		array(
		'‚', 'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹', 'Œ', '‘', '’', '“', '”',
		'•', '–', '—', '˜', '™', 'š', '›', 'œ', 'Ÿ', '€', 'Æ', 'Á', 'Â', 'À', 'Å',
		'Ã', 'Ä', 'Ç', 'Ð', 'É', 'Ê', 'È', 'Ë', 'Í', 'Î', 'Ì', 'Ï', 'Ñ', 'Ó', 'Ô',
		'Ò', 'Ø', 'Õ', 'Ö', 'Þ', 'Ú', 'Û', 'Ù', 'Ü', 'Ý', 'á', 'â', 'æ', 'à', 'å',
		'ã', 'ä', 'ç', 'é', 'ê', 'è', 'ð', 'ë', 'í', 'î', 'ì', 'ï', 'ñ', 'ó', 'ô',
		'ò', 'ø', 'õ', 'ö', 'ß', 'þ', 'ú', 'û', 'ù', 'ü', 'ý', 'ÿ', '¡', '£', '¤',
		'¥', '¦', '§', '¨', '©', 'ª', '«', '¬', '­', '®', '¯', '°', '±', '²', '³',
		'´', 'µ', '¶', '·', '¸', '¹', 'º', '»', '¼', '½', '¾', '¿', '×', '÷', '¢',
		'?', ' ');
		static $utf8entities = 
		array(
		'=E2=80=9A', 	'=C6=92', 		'=E2=80=9E', 		'=E2=80=A6', 		'=E2=80=A0', 		'=E2=80=A1', 	'=CB=86', 		'=E2=80=B0', 
		'=C5=A0', 		'=E2=80=B9', 	'=C5=92', 			'=E2=80=98', 		'=E2=80=99', 		'=E2=80=9C', 	'=E2=80=9D', 	'=E2=80=A2', 
		'=E2=80=93', 	'=E2=80=94', 	'=CB=9C', 			'=E2=84=A2', 		'=C5=A1', 			'=E2=80=BA', 	'=C5=93', 		'=C5=B8', 
		'=E2=82=AC', 	'=C3=86', 		'=C3=81', 			'=C3=82', 			'=C3=80', 			'=C3=85', 		'=C3=83', 		'=C3=84', 
		'=C3=87', 		'=C3=90', 		'=C3=89', 			'=C3=8A', 			'=C3=88', 			'=C3=8B', 		'=C3=8D', 		'=C3=8E', 
		'=C3=8C', 		'=C3=8F', 		'=C3=91', 			'=C3=93', 			'=C3=94', 			'=C3=92', 		'=C3=98', 		'=C3=95', 
		'=C3=96', 		'=C3=9E', 		'=C3=9A', 			'=C3=9B', 			'=C3=99', 			'=C3=9C', 		'=C3=9D', 		'=C3=A1', 
		'=C3=A2', 		'=C3=A6', 		'=C3=A0', 			'=C3=A5', 			'=C3=A3', 			'=C3=A4', 		'=C3=A7', 		'=C3=A9', 
		'=C3=AA', 		'=C3=A8', 		'=C3=B0', 			'=C3=AB', 			'=C3=AD',			'=C3=AE', 		'=C3=AC', 		'=C3=AF', 
		'=C3=B1', 		'=C3=B3', 		'=C3=B4', 			'=C3=B2', 			'=C3=B8', 			'=C3=B5', 		'=C3=B6', 		'=C3=9F', 
		'=C3=BE', 		'=C3=BA', 		'=C3=BB',			'=C3=B9', 			'=C3=BC', 			'=C3=BD', 		'=C3=BF', 		'=C2=A1', 
		'=C2=A3', 		'=C2=A4', 		'=C2=A5', 			'=C2=A6', 			'=C2=A7', 			'=C2=A8', 		'=C2=A9', 		'=C2=AA', 
		'=C2=AB', 		'=C2=AC', 		'=C2=AD', 			'=C2=AE', 			'=C2=AF', 			'=C2=B0', 		'=C2=B1', 		'=C2=B2', 
		'=C2=B3', 		'=C2=B4', 		'=C2=B5', 			'=C2=B6', 			'=C2=B7', 			'=C2=B8', 		'=C2=B9', 		'=C2=BA', 
		'=C2=BB', 		'=C2=BC', 		'=C2=BD', 			'=C2=BE', 			'=C2=BF', 			'=C3=97', 		'=C3=B7', 		'=C2=A2',
		'=3F', '_');
		
		return str_replace($symbols, $utf8entities, str_replace("=", "=3D", str_replace('_', "=5F", $string)));
	}
	
	/**
	 * Make a email subject encoded into UTF-8
	 *
	 * @access public static
	 * @param string $string the string to convert
	 * @return string $string the string converted
	 * @see SLS_String::convertSymbolsToUFT8
	 * @since 1.0
	 */
	public static function makeEmailSubject($string)
	{		
		$return = "=?utf-8?Q?";
		$str = self::convertSymbolsToUFT8($string);
		if (strstr($str, "=") === false)
		{
			return $string;
		}
		else 
		{
			$strArray = explode("=", $str);
			for ($i=0;$i<count($strArray);$i++)
			{
				if (is_int($i/10) && $i != 0)
				{
					$return .= "?==?utf-8?Q?=".$strArray[$i];
				}
				elseif ($i == 0) 
				{
					$return .= $strArray[$i];
				}
				else 
				{
					$return .= "=".$strArray[$i];
				}
			}
			return $return."?=";			
		}	
	}
	
	/**
	 * Check if a given string is encoded with a proper UTF-8, if not, encode it
	 *
	 * @access public static
	 * @param string $string the string to check the charset
	 * @return string the string encoded
	 * @since 1.0
	 */
	public static function utf8Encode($string)
	{
		$charset = (is_string($string)) ? mb_detect_encoding($string) : "UTF-8";
		
		if ($charset != "UTF-8")
			$string = mb_convert_encoding($string,"UTF-8",$charset);
		
		$string = str_replace(array("\x00","\x01","\x02","\x03","\x04","\x05","\x06","\x07","\x08","\x09","\x0B","\x0C","\x0D","\x0E","\x0F","\x10","\x11","\x12","\x13","\x14","\x15","\x16","\x17","\x18","\x19","\x1A","\x1B","\x1C","\x1D","\x1E","\x1F"),"",$string);
		return $string;
	}	
	
	/**
	 * Convert symbols to iso-8859-1
	 *
	 * @access public static	 
	 * @param string $string the string to convert
	 * @return string $string the string converted
	 * @see SLS_String::convertSymbolsToHtmlEntities
	 * @see SLS_String::convertSymbolsToUFT8
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::convertSymbolsToUFT8("© SillySmart ©"));
	 * // will produce "&#169; SillySmart &#169;"
	 */
	public static function convertSymbolsToIsoEntities($string)
	{
		static $symbols =
		array(
		'‚', 'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹', 'Œ', '‘', '’', '“', '”',
		'•', '–', '—', '˜', '™', 'š', '›', 'œ', 'Ÿ', '€', 'Æ', 'Á', 'Â', 'À', 'Å',
		'Ã', 'Ä', 'Ç', 'Ð', 'É', 'Ê', 'È', 'Ë', 'Í', 'Î', 'Ì', 'Ï', 'Ñ', 'Ó', 'Ô',
		'Ò', 'Ø', 'Õ', 'Ö', 'Þ', 'Ú', 'Û', 'Ù', 'Ü', 'Ý', 'á', 'â', 'æ', 'à', 'å',
		'ã', 'ä', 'ç', 'é', 'ê', 'è', 'ð', 'ë', 'í', 'î', 'ì', 'ï', 'ñ', 'ó', 'ô',
		'ò', 'ø', 'õ', 'ö', 'ß', 'þ', 'ú', 'û', 'ù', 'ü', 'ý', 'ÿ', '¡', '£', '¤',
		'¥', '¦', '§', '¨', '©', 'ª', '«', '¬', '­', '®', '¯', '°', '±', '²', '³',
		'´', 'µ', '¶', '·', '¸', '¹', 'º', '»', '¼', '½', '¾', '¿', '×', '÷', '¢',
		'…', 'µ', '€', '&');
		static $entities =
		array(
		'&#8218;', '&#402;',  '&#8222;', '&#8230;', '&#8224;', '&#8225;', '&#710;',
		'&#8240;', '&#352;',  '&#8249;', '&#338;',  '&#8216;', '&#8217;', '&#8220;',
		'&#8221;', '&#8226;', '&#8211;', '&#8212;', '&#732;',  '&#8482;', '&#353;',
		'&#8250;', '&#339;',  '&#376;',  '&#8364;', '&#198;',  '&#225;',  '&#226;',
		'&#192;',  '&#197;',  '&#227;',  '&#228;',  '&#199;',  '&#208;',  '&#233;',
		'&#234;',  '&#200;',  '&#203;',  '&#205;',  '&#206;',  '&#236;',  '&#239;',
		'&#241;',  '&#243;',  '&#244;',  '&#210;',  '&#216;',  '&#245;',  '&#246;',
		'&#254;',  '&#218;',  '&#219;',  '&#249;',  '&#252;',  '&#253;',  '&#225;',
		'&#226;',  '&#198;',  '&#192;',  '&#197;',  '&#227;',  '&#228;',  '&#199;',
		'&#233;',  '&#234;',  '&#200;',  '&#208;',  '&#203;',  '&#205;',  '&#206;',
		'&#236;',  '&#239;',  '&#241;',  '&#243;',  '&#244;',  '&#210;',  '&#216;',
		'&#245;',  '&#246;',  '&#223;',  '&#254;',  '&#218;',  '&#219;',  '&#249;',
		'&#252;',  '&#253;',  '&#255;',  '&#161;',  '&#163;',  '&#164;',  '&#165;',
		'&#166;',  '&#167;',  '&#168;',  '&#169;',  '&#170;',  '&#171;',  '&#172;',
		'&#173;',  '&#174;',  '&#175;',  '&#176;',  '&#177;',  '&#178;',  '&#179;',
		'&#180;',  '&#181;',  '&#182;',  '&#183;',  '&#184;',  '&#185;',  '&#186;',
		'&#187;',  '&#188;',  '&#189;',  '&#190;',  '&#191;',  '&#215;',  '&#247;',
		'&#162;',  '&#133',   '&#181;',  '&#128;',	'&#38;');

		if (self::validateString($string, false)) 
			return str_replace($symbols, $entities, $string);
		else 
			return $string;
	}
	
	/**
	 * Modify a string to use it in a portion of url
	 *	
	 * @access public static
	 * @param string $string the string to convert
	 * @param string $delimiter the glue
	 * @param bool $lower true if you lowercase, else false
	 * @return string $string the string converted
	 * @see SLS_String::fullTrim
	 * @see SLS_String::removeSpecialCaracteres
	 * @see SLS_String::removeAccents
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::stringToUrl("~ enLighTment />comes from darkness.+&-"));
	 * // will produce " enlightment comes from darkness + -"
	 */
	public static function stringToUrl($string,$delimiter=" ",$lower=true)
	{
		if (self::validateString($string, false))		
			return ($lower) ? self::fullTrim(self::removeSpecialCaracteres(strtolower(self::removeAccents($string))),$delimiter) : self::fullTrim(self::removeSpecialCaracteres(self::removeAccents($string)),$delimiter);		
		else 
			return $string;
	}
	
	/**
	 * Convert a mysql table name to php class name	 
	 *
	 * @access public static
	 * @param string $string table name
	 * @return string $string class name
	 * @see SLS_String::fullTrim
	 * @see SLS_String::removeSpecialCaracteres
	 * @see SLS_String::removeAccents
	 * @since 1.0
	 */
	public static function tableToClass($string)
	{
		if (self::validateString($string, false))		
			return self::fullTrim(ucwords(self::removeSpecialCaracteres(self::removeAccents($string))),"");
		else
			return $string;
	}
	
	/**
	 * Strip accents in a string
	 *
	 * @access public static
	 * @param string $string the string to convert
	 * @return string $string the string converted
	 * @see SLS_String::removePhpChars
	 * @see SLS_String::removeSpecialCaracteres
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::removeAccents("àeeéï");
	 * // will produce "aeeéi"
	 */
	public static function removeAccents($string)
	{
		static $search = array ("à","á","â","ã","ä","ç","è","é","ê","ë","ì","í","î",
							 	"ï","ñ","ò","ó","ô","õ","ö","ù","ú","û","ü","ý","ÿ",
							 	"À","Á","Â","Ã","Ä","Ç","È","É","Ê","Ë","Ì","Í","Î",
							 	"Ï","Ñ","Ò","Ó","Ô","Õ","Ö","Ù","Ú","Û","Ü","Ý");
		static $replac = array ("a","a","a","a","a","c","e","e","e","e","i","i","i",
								"i","n","o","o","o","o","o","u","u","u","u","y","y",
								"A","A","A","A","A","C","E","E","E","E","I","I","I",
								"I","N","O","O","O","O","O","U","U","U","U","Y");											 	
		
		if (self::validateString($string, false))
			return str_replace($search,$replac,$string);
		else 
			return $string;		
	}
	
	/**
	 * Strip all forbidden php chars
	 *
	 * @access public static
	 * @param string $string the string to clean
	 * @return string $string the string cleaned
	 * @see SLS_String::removeAccents
	 * @see SLS_String::removeSpecialCaracteres
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::removePhpChars("si.lly|[sma]/rt"));
	 * // will produce : "sillysmart"
	 */
	public static function removePhpChars($string)
	{
		static $search = array ("&","~","#","{","(","[","|","`","\\","^","'",'$',
								")","]","}","¨","£","¤","%","*",",","?",";",".",
								":","/","!","§","<",">","»","«","\n","\r","\t");
		
		static $replac = "";

		if (self::validateString($string, false))
			return str_replace($search,$replac,$string);
		else 
			return $string;
	}
	
	/**
	 * Strip special caracteres in a string
	 *
	 * @access public static
	 * @param string $string the string to convert
	 * @return string $string the string converted
	 * @see SLS_String::removeAccents
	 * @see SLS_String::removePhpChars
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::removeSpecialCaracteres("si.lly|[sma]/rt"));
	 * // will produce : "si lly sma rt"
	 */
	public static function removeSpecialCaracteres($string)
	{
		static $search = array ("²","&","~","#","{","(","[","|","`","\\","^","'","’",
								")","]","}","^","¨","£","¤","%","*",",","?",";",".",
								":","/","!","§","<",">","»","«","-","\n","\r","\t");
		
		static $replac = " ";

		if (self::validateString($string, false))
			return str_replace($search,$replac,$string);
		else 
			return $string;
	}
	
	/**
	 * Sanitize characters to filename
	 * 
	 * @access public static
	 * @param string $string the original filename
	 * @param string $delimiter char which replace all no-printables chars
	 * @return string $return the filename sanitized
	 * @since 1.0.5
	 */
	public static function sanitize($string,$delimiter="_")
	{		
		$string = html_entity_decode(trim($string),ENT_QUOTES,'UTF-8');
		$return = "";
		
		$allowed = array("a","b","c","d","e","f","g","h","i","j","k","l","m",
						 "n","o","p","q","r","s","t","u","v","w","x","y","z",
						 "0","1","2","3","4","5","6","7","8","9",$delimiter);
		
		for($i=0 ; $i<strlen($string) ; $i++)	
			$return .= ((in_array(strtolower($string{$i}),$allowed)) ? strtolower($string{$i}) : (($i>0 && in_array($return{strlen($return)-1},array($delimiter))) ? "" : $delimiter));
				
		return trim($return,$delimiter);
	}

	/**
	 * Convert all string contained in an array to their html entities	 
	 *	 
	 * @access public static
	 * @param array $element the array to convert
	 * @return array the array converted
	 * @since 1.0
	 */
	public static function convertTextToHTML($element)
	{
		return self::processFunction($element, 'htmlentities');
	}

	/**
	 * Cut a string at a given length without truncate a word	 
	 *
	 * @access public static
	 * @param string $string the string to cut
	 * @param int $length the wanted length
	 * @param bool $more if you want to concatenate '...' at the end of the string
	 * @return string $trimmed the string cut
	 * @see SLS_String::trimWordFromString
	 * @see SLS_String::trimFirstWordFromString
	 * @see SLS_String::trimLastWordFromString
	 * @see SLS_String::trimString
	 * @see SLS_String::trimStringLeft
	 * @see SLS_String::trimStringRight
	 * @see SLS_String::fullTrim
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::trimStringToLength("software is like sex, it's better when it's free",25));
	 * // will produce "software is like sex,..."
	 * @example 
	 * var_dump(SLS_String::trimStringToLength("software is like sex, it's better when it's free",25,false));
	 * // will produce "software is like sex,"
	 */
	public static function trimStringToLength($string, $length, $more=true)
	{
		if (self::validateString($string)) 
		{			
			$trimmed = $string;		
			if (strlen($trimmed) > $length) 
			{
				$trimmed = mb_substr($trimmed, 0, strrpos(substr($trimmed, 0, $length), ' '), 'UTF-8');
				if ($more === true)			
					$trimmed .= '...';				
			}
			return $trimmed;
		}
	}

	/**
	 * Delete the first or the last word of a string	 
	 *	 
	 * @access public static
	 * @param string $string the string to 'amputate'
	 * @param bool $start true if you want to cut the first word, else false for the last word
	 * @return string $trimmed the string cut
	 * @see SLS_String::trimStringToLength
	 * @see SLS_String::trimFirstWordFromString
	 * @see SLS_String::trimLastWordFromString
	 * @see SLS_String::trimString
	 * @see SLS_String::trimStringLeft
	 * @see SLS_String::trimStringRight
	 * @see SLS_String::fullTrim
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::trimWordFromString("software is like sex, it's better when it's free"));
	 * // will produce : "is like sex, it's better when it's free"
	 * @example 
	 * var_dump(SLS_String::trimWordFromString("software is like sex, it's better when it's free",false));
	 * // will produce : "software is like sex, it's better when it's"
	 */
	public static function trimWordFromString($string, $start=true)
	{
		if (self::validateString($string)) 
		{
			$trimmed = trim($string);
			if (!substr_count($trimmed, ' '))
				return $trimmed;			
			else 			
				return ($start) ? substr($trimmed, strpos($trimmed, ' ')+1, strlen($trimmed)) : substr($trimmed, 0, strrpos($trimmed, ' '));			
		}
	}

	/**
	 * Delete the first word of a string
	 *	 
	 * @access public static
	 * @param string $string the string to 'amputate'
	 * @return string $trimmed the string cut
	 * @see SLS_String::trimStringToLength
	 * @see SLS_String::trimWordFromString
	 * @see SLS_String::trimLastWordFromString
	 * @see SLS_String::trimString
	 * @see SLS_String::trimStringLeft
	 * @see SLS_String::trimStringRight
	 * @see SLS_String::fullTrim
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::trimFirstWordFromString("software is like sex, it's better when it's free"));
	 * // will produce : "is like sex, it's better when it's free"
	 */
	public static function trimFirstWordFromString($string)
	{
		return self::trimWordFromString($string, true);
	}

	/**
	 * Delete the last word of a string
	 *	 
	 * @access public static
	 * @param string $string the string to 'amputate'
	 * @return string $trimmed the string cut
	 * @see SLS_String::trimStringToLength
	 * @see SLS_String::trimWordFromString
	 * @see SLS_String::trimFirstWordFromString
	 * @see SLS_String::trimString
	 * @see SLS_String::trimStringLeft
	 * @see SLS_String::trimStringRight
	 * @see SLS_String::fullTrim
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::trimLastWordFromString("software is like sex, it's better when it's free"));
	 * // will produce : "software is like sex, it's better when it's"
	 */
	public static function trimLastWordFromString($string)
	{
		return self::trimWordFromString($string, false);
	}

	/**
	 * Perfom a left & right trim	 
	 *
	 * @access public static
	 * @param  mixed $element the element to trim (string,array)
	 * @return mixed $element the element trimed (string,array)
	 * @see SLS_String::trimStringToLength
	 * @see SLS_String::trimWordFromString
	 * @see SLS_String::trimFirstWordFromString
	 * @see SLS_String::trimLastWordFromString
	 * @see SLS_String::trimStringLeft
	 * @see SLS_String::trimStringRight
	 * @see SLS_String::fullTrim
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::trimString("  software is like sex,   it's better when it's free  "));
	 * // will produce : "software is like sex,   it's better when it's free"
	 */
	public static function trimString($element)
	{
		return self::processFunction($element, 'trim');
	}

	/**
	 * Perfom a left trim	 
	 *
	 * @access public static
	 * @param  mixed $element the element to trim (string,array)
	 * @return mixed $element the element trimed (string,array)
	 * @see SLS_String::trimStringToLength
	 * @see SLS_String::trimWordFromString
	 * @see SLS_String::trimFirstWordFromString
	 * @see SLS_String::trimLastWordFromString
	 * @see SLS_String::trimString
	 * @see SLS_String::trimStringRight
	 * @see SLS_String::fullTrim
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::trimStringLeft("  software is like sex,   it's better when it's free  "));
	 * // will produce : "software is like sex,   it's better when it's free  "
	 */
	public static function trimStringLeft($element)
	{
		return self::processFunction($element, 'ltrim');
	}

	/**
	 * Perfom a right trim	 
	 *
	 * @access public static
	 * @param  mixed $element the element to trim (string,array)
	 * @return mixed $element the element trimed (string,array)
	 * @see SLS_String::trimStringToLength
	 * @see SLS_String::trimWordFromString
	 * @see SLS_String::trimFirstWordFromString
	 * @see SLS_String::trimLastWordFromString
	 * @see SLS_String::trimString
	 * @see SLS_String::trimStringLeft
	 * @see SLS_String::fullTrim
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::trimStringRight("  software is like sex,   it's better when it's free  "));
	 * // will produce : "  software is like sex,   it's better when it's free"
	 */
	public static function trimStringRight($element)
	{
		return self::processFunction($element, 'rtrim');
	}
	
	/**
	 * Perform a full trim	 
	 *
	 * @access public static
	 * @param string $element the string to trim
	 * @param string $delimiter the glue
	 * @return string the string trimed
	 * @see SLS_String::trimStringToLength
	 * @see SLS_String::trimWordFromString
	 * @see SLS_String::trimFirstWordFromString
	 * @see SLS_String::trimLastWordFromString
	 * @see SLS_String::trimString
	 * @see SLS_String::trimStringLeft
	 * @see SLS_String::trimStringRight
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::fullTrim("  software is like sex,   it's better when it's free  "));
	 * // will produce : "softwareislikesex,it'sbetterwhenit'sfree"
	 * @example 
	 * var_dump(SLS_String::fullTrim("  software is like sex,   it's better when it's free  ","_"));
	 * // will produce : "__software_is_like_sex,___it's_better_when_it's_free__"
	 */
	public static function fullTrim($element,$delimiter="")
	{		
		return implode($delimiter,self::processFunction(explode(' ',$element),'trim'));
	}
	
	/**
	 * Cut a string until the first delimiter
	 *
	 * @access public static
	 * @param string $element the string to cut
	 * @param string $delimiter the delimiter
	 * @return string $string the string cut
	 * @see SLS_String::substrBeforeLastDelimiter
	 * @see SLS_String::substrAfterLastDelimiter
	 * @see SLS_String::substrAfterFirstDelimiter
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::substrBeforeFirstDelimiter("silly.smart","."));
	 * // will produce : "silly"
	 * @example 
	 * var_dump(SLS_String::substrBeforeFirstDelimiter("silly.smart.rules","."));
	 * // will produce : "silly"
	 */
	public static function substrBeforeFirstDelimiter($string,$delimiter)
	{
		$stringExploded = explode($delimiter,$string);
		
		if (count($stringExploded)>0 && self::validateString($stringExploded[0]))
			return $stringExploded[0];
		else 
			return $string;			
	}
	
	/**
	 * Cut a string until the last delimiter
	 *
	 * @access public static
	 * @param string $element the string to cut
	 * @param string $delimiter the delimiter
	 * @return string $string the string cut
	 * @see SLS_String::substrBeforeFirstDelimiter
	 * @see SLS_String::substrAfterLastDelimiter
	 * @see SLS_String::substrAfterFirstDelimiter
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::substrBeforeLastDelimiter("silly.smart","."));
	 * // will produce : "silly"
	 * @example 
	 * var_dump(SLS_String::substrBeforeLastDelimiter("silly.smart.rules","."));
	 * // will produce : "silly.smart"
	 */
	public static function substrBeforeLastDelimiter($string,$delimiter)
	{
		$stringExploded = explode($delimiter,$string);
		$newString = "";
		
		if (count($stringExploded)>0 && self::validateString($string))
		{
			for ($i=0 ; $i<$count=(count($stringExploded)-1) ; $i++)
			{
				$newString .= $stringExploded[$i];
				if ($i<$count-1) $newString .= $delimiter;
			}
			return $newString;
		}		
		else 
			return $string;		
	}
	
	/**
	 * Cut a string from the last delimiter
	 *
	 * @access public static
	 * @param string $element the string to cut
	 * @param string $delimiter the delimiter
	 * @return string $string the string cut
	 * @see SLS_String::substrBeforeFirstDelimiter
	 * @see SLS_String::substrBeforeLastDelimiter
	 * @see SLS_String::substrAfterFirstDelimiter
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::substrAfterLastDelimiter("silly.smart","."));
	 * // will produce : "smart"
	 * @example 
	 * var_dump(SLS_String::substrAfterLastDelimiter("silly.smart.rules","."));
	 * // will produce : "rules"
	 */
	public static function substrAfterLastDelimiter($string,$delimiter)
	{
		$stringExploded = explode($delimiter,$string);
		
		if (count($stringExploded)>0 && self::validateString($stringExploded[count($stringExploded)-1]))
			return $stringExploded[count($stringExploded)-1];
		else 
			return $string;			
	}
	
	/**
	 * Cut a string from the first delimiter
	 *
	 * @access public static
	 * @param string $element the string to cut
	 * @param string $delimiter the delimiter
	 * @return string $string the string cut
	 * @see SLS_String::substrBeforeFirstDelimiter
	 * @see SLS_String::substrBeforeLastDelimiter
	 * @see SLS_String::substrAfterLastDelimiter
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::substrAfterFirstDelimiter("silly.smart","."));
	 * // will produce : "smart"
	 * @example 
	 * var_dump(SLS_String::substrAfterFirstDelimiter("silly.smart.rules","."));
	 * // will produce : "smart.rules"
	 */
	public static function substrAfterFirstDelimiter($string,$delimiter)
	{
		$stringExploded = explode($delimiter,$string);
		$newString = "";
		
		if (count($stringExploded)>0 && self::validateString($string))
		{
			for ($i=1 ; $i<$count=count($stringExploded) ; $i++)
			{
				$newString .= $stringExploded[$i];
				if ($i<$count-1) $newString .= $delimiter;
			}				
			return $newString;
		}		
		else 
			return $string;		
	}

	/**
	 * Add escape character before quotes or double quotes
	 *	 
	 * @access public static
	 * @param string $element the element to escape
	 * @param string $type QUOTES ' or DOUBLE_QUOTES "
	 * @return string $string the string quoted
	 * @see SLS_String::addSlashesToString
	 * @see SLS_String::trimSlashesFromString
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::addSlashes("sls is goin' to rules","QUOTES"));
	 * // will produce : "sls is goin\' to rules"
	 * @example 
	 * var_dump(SLS_String::addSlashes('sls is goin to " rules',"DOUBLE_QUOTES"));
	 * // will produce : "sls is goin' to \" rules"
	 */
	public static function addSlashes($element, $type="QUOTES")
	{
		if ($type != "QUOTES" && $type != "DOUBLE_QUOTES")
			return false;
		
		return ($type == "QUOTES") ? str_replace("'", "\'", $element) : str_replace("\"", "\\\"", $element);
	}
	
	/**
	 * Add escape character on a string or on a array
	 *	 
	 * @access public static
	 * @param  mixed $element the element to escape (string,array)
	 * @param bool $check_gpc true if you want to check before if the magic quotes are enabled
	 * @return mixed $escaped the element escaped (string,array)
	 * @see SLS_String::addSlashes
	 * @see SLS_String::trimSlashesFromString
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::addSlashesToString("sls is goin' to \" rules"));
	 * // will produce : "sls is goin\' to \" rules"
	 */
	public static function addSlashesToString($element, $check_gpc=true)
	{
		return ($check_gpc && get_magic_quotes_gpc()) ? $element : self::processFunction($element, 'addslashes');
	}
	
	/**
	 * Strip escape character on a string or on a array	 	 
	 *
	 * @access public static
	 * @param  mixed $element the element to stipslashe (string,array)
	 * @param bool $check_gpc true if you want to check before if the magic quotes are enabled
	 * @return mixed $escaped the element strip escaped (string,array)
	 * @see SLS_String::addSlashes
	 * @see SLS_String::addSlashesToString
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::trimSlashesFromString("sls is goin\' to \\\" rules"));
	 * // will produce : "sls is goin' to " rules"
	 */
	public static function trimSlashesFromString($element, $check_gpc = true)
	{
		return ($check_gpc && !get_magic_quotes_gpc()) ? $element : self::processFunction($element, 'stripslashes');
	}

	/**
	 * Call a given function recursively	 
	 *	 
	 * @access public static
	 * @param mixed $element the parameter or the array of parameters of the function (string,array)
	 * @param string $function function name
	 * @return mixed $element the return function (string,array)
	 * @since 1.0
	 */
	public static function processFunction($element, $function)
	{
		if (function_exists($function) === true) 
		{
			if (self::validateArray($element, false) === false) 
			{
				return $function($element);
			}
			else 
			{
				foreach ($element as $key => $val) 
				{
					if (self::validateArray($element[$key], false)) 
					{
						$element[$key] = self::processFunction($element[$key], $function);
					}
					else 
					{
						$element[$key] = $function($element[$key]);
					}
				}
			}
		}
		return $element;
	}
	
	/**
	 * Return the content delimited by 2 bounds
	 * 
	 * @access public static
	 * @param string $contentSrc haystack
	 * @param string $boundLeft left bound
	 * @param string $boundRight right bound	 
	 * @return array array of results of found occurrences
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::getBoundContent("<ul><li>laurent@sillysmart.org</li><li>florian@sillysmart.org</li><li>charles@sillysmart.org</li></ul>","<li>","</li>"));
	 * // will produce :
	 * array(	"laurent@sillysmart.org",
  	 * 			"florian@sillysmart.org",
  	 * 			"charles@sillysmart.org"
	 * )
	 */
	public static function getBoundContent($contentSrc,$boundLeft,$boundRight)
	{			
		preg_match_all('/' . preg_quote($boundLeft,'/') . '(.*)'. preg_quote($boundRight,'/').'/U', $contentSrc, $results);		
		return $results[1];		
	}

	/**
	 * Give the string litteral of a number (1st, 2nd, 3rd, 4th)	 
	 *	 
	 * @access public static
	 * @param int $value the number
	 * @return string the string litteral
	 * @see SLS_String::getPluralString
	 * @since 1.0
	 */
	public static function getOrdinalString($value)
	{
		static $ords = array('th', 'st', 'nd', 'rd');
		if ((($value %= 100) > 9 && $value < 20) || ($value %= 10) > 3) 		
			$value = 0;		
		return $ords[$value];
	}

	/**
	 * Give the plural of a string	 	
	 *	 
	 * @access public static
	 * @param string $value the initial string
	 * @param string $append the character to stick
	 * @return string the string modified
	 * @see SLS_String::getOrdinalString
	 * @since 1.0
	 */
	public static function getPluralString($value, $append='s')
	{
		return ($value == 1) ? $value : $value.$append;
	}

	/**
	 * Escape new lines from a string (\n)
	 *	
	 * @access public static 
	 * @param mixed $element the string or the array from which we want to escape new lines (string,array)
	 * @return mixed $trimed the string or the array escaped (string,array)
	 * @see SLS_String::trimCarriageReturnsFromString
	 * @since 1.0
	 */
	public static function trimNewlinesFromString($element)
	{
		if (self::validateArray($element, false) === false)
			return str_replace("\n", '', $element);		 
		else 
		{
			foreach ($element as $key => $val) 
			{
				if (self::validateArray($element[$key], false))
					$element[$key] = self::trimNewlinesFromString($element[$key]);				
				else
					$element[$key] = str_replace("\n", '', $element[$key]);				
			}
		}
		return $element;
	}
	
	/**
	 * Escape carriage return from a string (\r)
	 *
	 * @access public static
	 * @param mixed $element the string or the array from which we want to escape carriage return (string,array)
	 * @return mixed $trimed the string or the array escaped (string,array)
	 * @see SLS_String::trimNewlinesFromString
	 * @since 1.0
	 */
	public static function trimCarriageReturnsFromString($element)
	{
		if (self::validateArray($element, false) === false)
			return str_replace("\r", '', $element);		
		else 
		{
			foreach ($element as $key => $val) 
			{
				if (self::validateArray($element[$key], false))				
					$element[$key] = self::trimCarriageReturnsFromString($element[$key]);				
				else				
					$element[$key] = str_replace("\r", '', $element[$key]);
			}
		}
		return $element;
	}

	/**
	 * Give a file extension
	 *	
	 * @access public static 
	 * @param string $string file name
	 * @return string file extension
	 * @see SLS_String::getFileName
	 * @see SLS_String::getFileSize
	 * @see SLS_String::getFormatFileSize
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::getFileExtension("silly.smart.sls"));
	 * // will produce : "sls"
	 */
	public static function getFileExtension($string)
	{
		if (self::validateString($string))		
			return substr($string, (strrpos($string, '.') ? strrpos($string, '.')+1 : strlen($string)), strlen($string));		 
		else		
			return $string;		
	}

	/**
	 * Give the file name without the extension
	 *
	 * @access public static
	 * @param string $string file name
	 * @return string file name without extension
	 * @see SLS_String::getFileExtension
	 * @see SLS_String::getFileSize
	 * @see SLS_String::getFormatFileSize
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::getFileName("silly.smart.sls"));
	 * // will produce : "silly.smart"
	 */
	public static function getFileName($string)
	{
		if (self::validateString($string))
			return substr($string, 0, (strrpos($string, '.') ? strrpos($string, '.') : strlen($string)));		
		else
			return $string;		
	}

	/**
	 * Get a file size readable	 
	 *	 
	 * @access public static
	 * @param string $file file path
	 * @param bool $round if you want to round the file
	 * @return string the file size
	 * @see SLS_String::getFileExtension
	 * @see SLS_String::getFileName
	 * @see SLS_String::getFormatFileSize
	 * @since 1.0
	 */
	public static function getFileSize($file, $round=false)
	{
		if (@file_exists($file)) 
		{
			$value = 0;
			$size = filesize($file);
			return self::getFormatFileSize($size,$round);
		} 
		else
			return 0;		
	}

	/**
	 * Format a file size
	 *	 
	 * @access public static
	 * @param int $size file size
	 * @param bool $round if you want to round the file
	 * @return string the file size
	 * @see SLS_String::getFileExtension
	 * @see SLS_String::getFileName
	 * @see SLS_String::getFileSize
	 * @since 1.0
	 */
	public static function getFormatFileSize($size, $round = false) 
	{
		if ($size >= 1073741824) 
		{
			$value = round($size/1073741824*100)/100;
			return  ($round) ? round($value) . 'Gb' : "{$value}Gb";
		} 
		else if ($size >= 1048576) 
		{
			$value = round($size/1048576*100)/100;
			return  ($round) ? round($value) . 'Mb' : "{$value}Mb";
		} 
		else if ($size >= 1024) 
		{
			$value = round($size/1024*100)/100;
			return  ($round) ? round($value) . 'kb' : "{$value}kb";
		} 
		else 
		{
			return "$size bytes";
		}
	}

	/**
	 * Count the number of words in a string	 
	 *	 
	 * @access public static
	 * @param string $string the string to count
	 * @param bool $real_words true if you want to omit special chars
	 * @return int the number of words
	 * @see SLS_String::countSentences
	 * @see SLS_String::countParagraphs
	 * @see SLS_String::countLines
	 * @see SLS_String::getStringInformation
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::countWords("SillySmart is a lightweight and flexible MVC Framework written in PHP5, based on XML/XSL's parsing."));
	 * // will produce : 15
	 */
	public static function countWords($string, $real_words = true)
	{
		if (self::validateString($string)) 
		{
			if ($real_words == true)
				$string = preg_replace('/(\s+)[^a-zA-Z0-9](\s+)/', ' ', $string);			
			return (count(split('[[:space:]]+', $string)));
		} 
		else
			return 0;		
	}

	/**
	 * Count the number of sentences in a string	 
	 *	 
	 * @access public static
	 * @param string $string the string to count	 
	 * @return int the number of sentences
	 * @see SLS_String::countWords
	 * @see SLS_String::countParagraphs
	 * @see SLS_String::countLines
	 * @see SLS_String::getStringInformation
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::countSentences("SillySmart is a lightweight and flexible MVC Framework written in PHP5. It's based on XML/XSL's parsing."));
	 * // will produce : 2
	 */
	public static function countSentences($string)
	{
		if (self::validateString($string))		
			return preg_match_all('/[^\s]\.(?!\w)/', $string, $matches);		 
		else		
			return 0;		
	}

	/**
	 * Count the number of paragraphs in a string	 
	 *	 
	 * @access public static
	 * @param string $string the string to count	 
	 * @return int the number of paragraphs
	 * @see SLS_String::countWords
	 * @see SLS_String::countSentences
	 * @see SLS_String::countLines
	 * @see SLS_String::getStringInformation
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::countParagraphs(
	 * "SillySmart is a lightweight and flexible MVC Framework written in PHP5.
     * It's based on XML/XSL's parsing."
     * ));
     * // will produce : 2
	 */
	public static function countParagraphs($string)
	{
		if (self::validateString($string)) 
		{
			$string = str_replace("\r", "\n", $string);
			return count(preg_split('/[\n]+/', $string));
		} 
		else		
			return 0;		
	}
	
	/**
	 * Count the number of lines in a string	 
	 *	 
	 * @access public static
	 * @param string $string the string to count	 
	 * @return int the number of lines
	 * @see SLS_String::countWords
	 * @see SLS_String::countSentences
	 * @see SLS_String::countParagraphs
	 * @see SLS_String::getStringInformation
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::countLines(
	 * "SillySmart is a lightweight and flexible MVC Framework written in PHP5.
     * It's based on XML/XSL's parsing."
     * ));
     * // will produce : 2
	 */
	public static function countLines($string)
	{
		if (self::validateString($string)) 					
			return count(preg_split('/[\n]+/', $string));		 
		else		
			return 0;
		
	}

	/**
	 * Give informations about a string :
	 * - Number of characters
	 * - Number of words
	 * - Number of sentences
	 * - Number of paragraphs
	 *
	 * @access public static
	 * @param string $string the string from which you want to have informations
	 * @param bool $real_words true if you want to omit special chars
	 * @return array $info array described string informations
	 * @see SLS_String::countWords
	 * @see SLS_String::countSentences
	 * @see SLS_String::countParagraphs
	 * @see SLS_String::countLines
	 * @since 1.0
	 * var_dump(SLS_String::getStringInformation(
	 * "SillySmart is a lightweight and flexible MVC Framework written in PHP5.
     * It's based on XML/XSL's parsing."
     * ));
     * // will produce :
     * array(
  	 * 		"character"	=> 89
  	 * 		"word"		=> 16
  	 * 		"sentence"	=> 2
  	 * 		"paragraph"	=> 2
  	 * 		"lines"		=> 2
	 * )
	 */
	public static function getStringInformation($string, $real_words=true)
	{
		if (self::validateString($string)) 
		{
			$info = array();
			$info['character'] = ($real_words) ? preg_match_all('/[^\s]/', $string, $matches) : strlen($string);
			$info['word']      = self::countWords($string, $real_words);
			$info['sentence']  = self::countSentences($string);
			$info['paragraph'] = self::countParagraphs($string);
			$info['lines'] 	   = self::countLines($string);
			return $info;
		} 
		else
			return null;		
	}

	/**
	 * Check if a string starts with another	 
	 * 	 
	 * @access public static
	 * @param string $hay the string in which you search
	 * @param string $needle the string searched
	 * @return bool true if the string starts with the other, else false
	 * @see SLS_String::endsWith
	 * @see SLS_String::contains
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::startsWith("Silly Smart","Silly"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_String::startsWith("Silly Smart","SillySmart"));
	 * // will produce : false
	 */
	public static function startsWith($hay, $needle)
	{
		return (empty($needle)) ? false : ($needle === $hay or strpos($hay, $needle) === 0);
	}

	/**
	 * Check if a string ends with another	 
	 * 	 
	 * @access public static
	 * @param string $hay the string in which you search
	 * @param string $needle the string searched
	 * @return bool true if the string ends with the other, else false
	 * @see SLS_String::startsWith
	 * @see SLS_String::contains
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::endsWith("Silly Smart","Smart"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_String::endsWith("Silly Smart","SillySmart"));
	 * // will produce : false
	 */
	public static function endsWith($hay, $needle)
	{
		return (empty($needle)) ? false : ($needle === $hay or strpos(strrev($hay), strrev($needle)) === 0);
	}
	
	/**
	 * Check if a string contains another	 
	 * 
	 * @access public static
	 * @param string $hay the string in which you search
	 * @param string $needle the string searched
	 * @return bool true if the string contains the other, else false
	 * @see SLS_String::startsWith
	 * @see SLS_String::endsWith
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::contains("Silly Smart","Smart"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_String::contains("Silly Smart","SillySmart"));
	 * // will produce : false
	 */
	public static function contains($hay, $needle)
	{
		return (empty($needle)) ? false : ($needle === $hay or strpos($hay, $needle) !== false);
	}

	/**
	 * Clean spaces in a string	 
	 * 	 
	 * @access public static
	 * @param string $string the string in which you want to delete double space
	 * @return string $string the string modified
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::delSpace("sls  is   going..."));
	 * // will produce : "sls i going...")
	 */	
	public static function delSpace($string) 
	{
		$string = trim($string);
		while ($repl = stristr($string, "  ") !== false)
		{
			$string = str_replace("  ", " ", $string);
		}
		return $string;
	}

	/**
	 * Get the name of a file
	 *
	 * @access public static
	 * @param string $filename the path file
	 * @return string the name of the file
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::getSafeFilename("sls/doc?txt.txt"));
	 * // will produce : "slsdoctxt.txt"
	 */
	public static function getSafeFilename($filename)
	{
		$arrayForbiddenChar = array("/:/", "/\*/", "/\?/", "/\"/", "/</", "/>/", "/\|/", "/\//", "/\\\/");
		$arrayReplacementChar = array("", "", "", "", "", "", "", "", "");
		return preg_replace($arrayForbiddenChar, $arrayReplacementChar, $filename);
	}
	
	/**
	 * Check if the given string is a IP
	 * Since sls 1.0.7, added ipv6 support
	 *
	 * @access public static
	 * @param string $string the string to test
	 * @param string $type the type of ip v4|v6|both (default: both)
	 * @return bool true if the string match with an ip, else false
	 * @see SLS_String::isValidUrl
	 * @see SLS_String::validateEmail
	 * @since 1.0	 
	 * @example 
	 * var_dump(SLS_String::isIp("192.168.0.1"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_String::isIp("192.168.0"));
	 * // will produce : false
	 */
	public static function isIp($string,$type="both") 
	{
		return (($type == "v4" && self::isIpV4($string)) ||
				($type == "v6" && self::isIpV6($string)) ||
				($type == "both" && (self::isIpV4($string) || self::isIpV6($string)))) 
		? true : false;
	}
	
	/**
	 * Check if the given string is a valid IPV4
	 * 	 
	 * @param string $string the string to test
	 * @return bool true if the string match with an ipv4, else false
	 * @see SLS_String::isIp
	 * @see SLS_String::isIpV6
	 * @see SLS_String::isValidUrl
	 * @see SLS_String::validateEmail
	 * @since 1.0.7
	 * @example 
	 * var_dump(SLS_String::isIpV4("192.168.0.1"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_String::isIpV4("192.168.0"));
	 * // will produce : false
	 */
	public static function isIpV4($string)
	{
		return (preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/',$string)) ? true : false;
	}
	
	/**
	 * Check if the given string is a valid IPV6
	 * The regex matches the following IPv6 address forms. Note that these are all the same address:
	 * ° full form of IPv6: "fe80:0000:0000:0000:0204:61ff:fe9d:f156"
	 * ° drop leading zeroes: "fe80:0:0:0:204:61ff:fe9d:f156"
	 * ° collapse multiple zeroes to :: in the IPv6 address: "fe80::204:61ff:fe9d:f156"
	 * ° IPv4 dotted quad at the end: "fe80:0000:0000:0000:0204:61ff:254.157.241.86"
	 * ° drop leading zeroes, IPv4 dotted quad at the end: "fe80:0:0:0:0204:61ff:254.157.241.86"
	 * ° dotted quad at the end, multiple zeroes collapsed: "fe80::204:61ff:254.157.241.86"
	 * In addition, the regular expression matches these IPv6 forms: 
	 * ° locahost: "::1"
	 * ° link-local prefix: "fe80::"
	 * ° global unicast prefix: "2001::"
	 * 
	 * @param string $string the string to test
	 * @return bool true if the string match with an ipv6, else false
	 * @see SLS_String::isIp
	 * @see SLS_String::isIpV4
	 * @see SLS_String::isValidUrl
	 * @see SLS_String::validateEmail
	 * @since 1.0.7
	 * @example 
	 * var_dump(SLS_String::isIpV6("2001:0660:7401:0200:0000:0000:0edf:bdd7"));
	 * // will produce : true
	 * var_dump(SLS_String::isIpV6("2001:660:7401:200::edf:bdd7"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_String::isIpV6("1111:2222:3333:4444::5555:"));
	 * // will produce : false
	 */
	public static function isIpV6($string)
	{
		return (preg_match('/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/ ',$string)) ? true : false;
	}
	
	/**
	 * Check if the given string is a valid url	(check dns & headers)
	 *
	 * @access public static
	 * @param string $url the string to test
	 * @return bool true if the string match with the url, else false
	 * @see SLS_String::isIp
	 * @see SLS_String::validateEmail
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::isValidUrl("http://www.sillysmart.org"));
	 * // will produce : true
	 * @example 
	 * var_dump(SLS_String::isValidUrl("http://www.sillysmart.org/fake"));
	 * // will produce : false
	 */
	public static function isValidUrl($url) 
	{
		if (!SLS_String::startsWith($url,"http://") && !SLS_String::startsWith($url,"https://"))
			$url = "http://".$url;
		
		return (preg_match("/^(http:\/\/|https:\/\/)(([a-z0-9]([-a-z0-9]*[a-z0-9]+)?){1,63}\.)+[a-z]{2,6}/i",$url) == 1) ? true : false;
	}
	
	/**
	 * Replace ;Amp; ajax entity to &
	 *
	 * @access public static
	 * @param string $str the string to convert
	 * @return string the string converted
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::decodeAmpFromAjax("?mod=Home;Amp;smod=Welcome"));
	 * // will produce : "?mod=Home&smod=Welcome"
	 */
	public static function decodeAmpFromAjax($str)
	{
		return str_replace(";Amp;", "&", $str);
	}
	
	/**
	 * Opposite of native php function nl2br()
	 *
	 * @access public static
	 * @param string $str the string in which you
	 * @return string the string replaced
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::br2nl("Hi !<br />Welcome on Sls!"));
	 * // will produce
	 * Hi !
	 * Welcome on Sls!
	 */
	public static function br2nl($str)
	{
		return str_replace(array("<br />","<br/>","<br>","&lt;br /&gt;","&lt;br/&gt;","&lt;br&gt;"), "\n", $str);
	}
	
	/**
	 * Perform a php print_r in html
	 *
	 * @access public static
	 * @param array $var the array to display
	 * @param bool $highlight true if you want syntax coloring
	 * @return string html code displaying the array
	 * @since 1.0
	 */
	public static function printArray($var,$highlight=false)
	{
	    $input = var_export($var,true);
	    $input = preg_replace("! => \n\W+ array \(!Uims", " => Array ( ", $input);
	    $input = preg_replace("!array \(\W+\),!Uims", "Array ( ),", $input);
	    $input = str_replace("  ","\t",$input);
	    return ($highlight) ? "<pre>".str_replace('><?', '>', highlight_string('<'.'?'.$input, true))."</pre>" : "".str_replace('><?', '>', $input)."";
	}
	
	/**
	 * Perform a php var_dump in html
	 *
	 * @access public static
	 * @param array $json the object to display
	 * @return string html code displaying the object
	 * @since 1.1
	 */
	public static function printObject($json)
	{
		$result = '';
		$pos = 0;
		$strLen = strlen($json);
		$indentStr = "\t";
		$newLine = "\n";
		$prevChar = '';
		$outOfQuotes = true;

		for($i = 0; $i <= $strLen; $i++)
		{
			// Grab the next character in the string
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if($char == '"' && $prevChar != '\\')
			{
				$outOfQuotes = !$outOfQuotes;
			}
			// If this character is the end of an element,
			// output a new line and indent the next line
			else if(($char == '}' || $char == ']') && $outOfQuotes) 
			{
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++)
				{
					$result .= $indentStr;
				}
			}
			// Add the character to the result string
			$result .= $char;

			// If the last character was the beginning of an element,
			// output a new line and indent the next line
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) 
			{
				$result .= $newLine;
				if ($char == '{' || $char == '[')
				{
					$pos ++;
				}
				for ($j = 0; $j < $pos; $j++) 
				{
					$result .= $indentStr;
				}
			}
			$prevChar = $char;
		}

		return $result;
	}
	
	/**
	 * Keep only alpha chars in the string. Replace all others chars by a space ($char)
	 *
	 * @access public static
	 * @param string $str string to transform
	 * @param string $char replacement character
	 * @return string
	 * @see SLS_String::getAlphaAccentString
	 * @since 1.0
	 * @example 
	 * var_dump(SLS_String::getAlphaString("Sls 4 u"));
	 * // will produce : "Sls u"
	 */
	public static function getAlphaString($str, $char = " ")
	{
		$str = preg_replace("/[^A-Z\ ]/i", $char, self::removeAccents($str));
		return $str;		
	}
	
	/**
	 * Keep only alpha chars and accents in the string. Replace all others chars by a special char
	 *
	 * @access public static
	 * @param string $str string to transform
	 * @param string $char replacement character
	 * @return string
	 * @see SLS_String::getAlphaString
	 * @since 1.0
	 */
	public static function getAlphaAccentString($str, $char=" ")
	{
		$str = preg_replace("/[^A-ZàáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ]/i", $char, $str);
		return $str;		
	}
	
	/**
	 * Format a xsl string using sprintf php function
	 *
	 * @access public static
	 * @param mixed any number of parameters you want to use
	 * @return string the string formated
	 * @since 1.0
	 */
	public static function formatXslString()
	{
		$args = func_get_args();
		
		if (empty($args))
			return "";
		else if (count($args) < 2)
			return $args[0];
		else
		{
			$ref = new ReflectionFunction('sprintf');
			return $ref->invokeArgs($args);			
		}
	}
	
	/**
	 * Format paginate => deprecated, remains used by User_Bo
	 *
	 * @access public static
	 * @deprecated
	 * @param int $start offset start
	 * @param int $length results by page
	 * @param int $total total results
	 * @param string $form the form id to submit
	 * @param string $aClass <a> className
	 * @param string $spanClass <span> className
	 * @param int $maxNb max number to display
	 * @param int $adj nb adj
	 * @return string html string
	 * @see SLS_String::paginate
	 * @since 1.0.1
	 */
	public static function paginateOld($start,$length,$total,$form,$aClass="pager",$spanClass="",$maxNb=10,$adj=3)
	{
		$current = ($start == 0) ? 1 : ($start/$length)+1;
		$total = ceil($total / $length);
		$pager = '';	
		
		if ($total > 1)
		{
			if ($total < ($maxNb/2 + ($adj * 2)))
			{
				$pager .= ($current == 1) ? "<span class=\"$spanClass\">1</span>" : "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt(1-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">1</a>";				
				for ($i = 2; $i<=$total; $i++)
				{
					if ($i == $current)
						$pager .= "<span class=\"$spanClass\">$i</span>";
					else
						$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt($i-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">$i</a>";
				}
			}
			else
			{
				if ($current < 2 + ($adj * 2))
				{
					$pager .= ($current == 1) ? "<span class=\"$spanClass\">1</span>" : "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt(1-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">1</a>";					
					for ($i = 2; $i < 4 + ($adj * 2); $i++)
					{
						if ($i == $current)
							$pager .= "<span class=\"$spanClass\">$i</span>";
						else
							$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt($i-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">$i</a>";
					}
					$pager .= '<span class="dot">...</span>';
					$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt($total-1-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">".($total-1)."</a>";
					$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt($total-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">$total</a>";
				}
				else if ( (($adj * 2) + 1 < $current) && ($current < $total - ($adj * 2)) )
				{
					$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt(1-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">1</a>";
					$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt(2-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">2</a>";	
					$pager .= '<span class="dot">...</span>';
					for ($i = $current - $adj; $i <= $current + $adj; $i++)
					{
						if ($i == $current)
							$pager .= "<span class=\"$spanClass\">$i</span>";
						else
							$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt($i-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">$i</a>";
					}	
					$pager .= '<span class="dot">...</span>';
					$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt($total-1-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">".($total-1)."</a>";
					$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt($total-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">$total</a>";
				}
				else
				{
					$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt(1-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">1</a>";
					$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt(2-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">2</a>";	
					$pager .= '<span class="dot">...</span>';					
					for ($i = $total - (2 + ($adj * 2)); $i <= $total; $i++)
					{
						if ($i == $current)
							$pager .= "<span class=\"$spanClass\">$i</span>";
						else
							$pager .= "<a class=\"$aClass\" href=\"#\" onclick=\"document.getElementById('start').value = parseInt($i-1) * parseInt(document.getElementById('length').value); document.getElementById('$form').submit();\">$i</a>";
					}
				}
			}
		}		
		return ($pager);
	}
	
	/**
	 * Format paginate
	 * Since sls 1.0.7: javascript removed, all links are real 
	 *
	 * @access public static
	 * @param int $start offset start
	 * @param int $length results by page
	 * @param int $total total results
	 * @param string $param name of the parameter (default: 'page')
	 * @param string $urlSuffix end of url (default: '.sls')
	 * @param string $aClass <a> className (default: 'pager_link');
	 * @param string $spanClass <span> selected className (default: 'pager_selected');
	 * @param string $dot <span>...</span> dot className (default: 'pager_dot');
	 * @param int $maxNb max number to display (default: 10)
	 * @param int $adj nb adjacent numbers (default: 3)
	 * @return string html string
	 * @since 1.0.1
	 */
	public static function paginate($start,$length,$total,$param="page",$urlSuffix=".sls",$aClass="pager_link",$spanClass="pager_selected",$dotClass="pager_dot",$maxNb=10,$adj=3)
	{
		if ($length == 0 || $total == 0)
			return '';
		
		$current = ($start == 0) ? 1 : ($start/$length)+1;
		$total = ceil($total / $length);
		$pager = '';
		
		$url = (self::startsWith($_SERVER["SERVER_PROTOCOL"],"https") ? "https://" : "http://").$_SERVER["HTTP_HOST"];
		if ($_SERVER["REQUEST_URI"] == "/")		
			$url .= ("/".($_SESSION["current_controller_translated"]."/".$_SESSION["current_action_translated"]));
		else
			$url .= (self::substrBeforeFirstDelimiter(self::substrBeforeFirstDelimiter($_SERVER["REQUEST_URI"],"."),"/".$param."/"));		
		if (self::endsWith($url,"/"))
			$url = self::substrBeforeLastDelimiter($url,"/");
		
		$query = http_build_query($_POST,"","/");
		$query = str_replace(array("%5B","%5D","=/","="),array("[","]","=|sls_empty|/","/"),preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query));		
		if (SLS_String::endsWith(trim($query),"/"))
			$query = SLS_String::substrBeforeLastDelimiter(trim($query),"/");
		if (!empty($query))
			$url .= "/".$query.((count(explode("/",$query))%2 != 0) ? "/|sls_empty|" : "");
		
		if ($total > 1)
		{
			if ($current > 1)
				$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($current-1).$urlSuffix.'" >&lt;</a>';
			
			if ($total < ($maxNb/2 + ($adj * 2)))
			{
				$pager .= ($current == 1) ? ('<span class="'.$spanClass.'">1</span>') : ('<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.(1).$urlSuffix.'">1</a>');
				
				for ($i = 2; $i<=$total; $i++)
				{
					if ($i == $current)
						$pager .= '<span class="'.$spanClass.'">'.$i.'</span>';
					else
						$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($i).$urlSuffix.'" >'.$i.'</a>';
				}
			}
			else
			{
				if ($current < 2 + ($adj * 2))
				{
					$pager .= ($current == 1) ? ('<span class="'.$spanClass.'">1</span>') : ('<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.(1).$urlSuffix.'">1</a>');
					
					for ($i = 2; $i < 4 + ($adj * 2); $i++)
					{
						if ($i == $current)
							$pager .= '<span class="'.$spanClass.'">'.$i.'</span>';
						else
							$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($i).$urlSuffix.'">'.$i.'</a>';
					}
					$pager .= '<span class="'.$dotClass.'">...</span>';
					$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($total-1).$urlSuffix.'">'.($total-1).'</a>';
					$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($total).$urlSuffix.'">'.$total.'</a>';
				}
				else if ( (($adj * 2) + 1 < $current) && ($current < $total - ($adj * 2)) )
				{
					$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.(1).$urlSuffix.'">'.(1).'</a>';
					$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.(2).$urlSuffix.'">'.(2).'</a>';	
					$pager .= '<span class="'.$dotClass.'">...</span>';
					
					for ($i = $current - $adj; $i <= $current + $adj; $i++)
					{
						if ($i == $current)
							$pager .= '<span class="'.$spanClass.'">'.$i.'</span>';
						else
							$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($i).$urlSuffix.'">'.$i.'</a>';
					}
					$pager .= '<span class="'.$dotClass.'">...</span>';
					$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($total-1).$urlSuffix.'">'.($total-1).'</a>';
					$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($total).$urlSuffix.'">'.$total.'</a>';
				}
				else
				{
					$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.(1).$urlSuffix.'">'.(1).'</a>';
					$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.(2).$urlSuffix.'">'.(2).'</a>';	
					$pager .= '<span class="'.$dotClass.'">...</span>';
								
					for ($i = $total - (2 + ($adj * 2)); $i <= $total; $i++)
					{
						if ($i == $current)
							$pager .= '<span class="'.$spanClass.'">'.$i.'</span>';
						else
							$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($i).$urlSuffix.'">'.$i.'</a>';
					}
				}
			}
			
			if ($current < $total)
				$pager .= '<a class="'.$aClass.'" href="'.$url.'/'.$param.'/'.($current+1).$urlSuffix.'" >&gt;</a>';
		}		
		return $pager;
	}
	
	/**
	 * Call a JS file with cache in prod or without 
	 * 
	 * @access public static
	 * @param string $url
	 * @return string|string
	 * @since 1.0.1
	 */
	public static function callCachingFile($url){
		if (SLS_Generic::getInstance()->isCache())
			return $url.((!self::contains($url,"?")) ? "?".SLS_Generic::getInstance()->getSiteConfig("versionName") : "");
		else
			return $url.((!self::contains($url,"?")) ? "?".sha1(uniqid()) : "");
	}
	
	/**
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * @access public static
	 * @param string $hexStr hexadecimal color value
	 * @param boolean $returnAsString if set true, returns the value separated by the separator character. Otherwise returns associative array
	 * @param string $seperator to separate RGB values. Applicable only if second parameter is true
	 * @return array or string depending on second parameter. Returns False if invalid hex color value
	 * @since 1.0.4
	 */                                                                                                
	public static function hex2RGB($hexStr, $returnAsString = false, $seperator = ',')
	{
	    $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr);
	    $rgbArray = array();
	    if (strlen($hexStr) == 6)
	    { 
	        $colorVal = hexdec($hexStr);
	        $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
	        $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
	        $rgbArray['blue'] = 0xFF & $colorVal;
	    }
	    else if (strlen($hexStr) == 3)
	    {
	        $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
	        $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
	        $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
	    } 
	    else 
	        return '';
	    
	    return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray;
	}
	
	
	/**
	 * Get file extension / mime type
	 * 
	 * @access public static
	 * @param string $value extension or mime type
	 * @param string $from 'extension' or 'mime'
	 * @return string mime type or extension
	 * @since 1.0.4
	 */
	public static function getExtensionMimeType($value,$from="extension")
	{
		$attachtypes = array('hqx'	=> "application/macbinhex40",
							 'pdf'	=> "application/pdf",
							 'pgp'	=> "application/pgp",
							 'ps'	=> "application/postscript",
							 'eps'	=> "application/postscript",
							 'ai'	=> "application/postscript",
							 'rtf'	=> "application/rtf",
							 'xls'	=> "application/vnd.ms-excel",
							 'xlsx'	=> "application/vnd.ms-excel",
							 'pps'	=> "application/vnd.ms-powerpoint",
							 'ppsx'	=> "application/vnd.ms-powerpoint",
							 'ppt'	=> "application/vnd.ms-powerpoint",
							 'pptx'	=> "application/vnd.ms-powerpoint",
							 'ppz'	=> "application/vnd.ms-powerpoint",
							 'ppzx'	=> "application/vnd.ms-powerpoint",
							 'doc'	=> "application/vnd.ms-word",
							 'docx'	=> "application/vnd.ms-word",
							 'dot'	=> "application/vnd.ms-word",
							 'dotx'	=> "application/vnd.ms-word",
							 'wrd'	=> "application/vnd.ms-word",
							 'wrdx'	=> "application/vnd.ms-word",
							 'tgz'	=> "application/x-gtar",
							 'gtar'	=> "application/x-gtar",
							 'gz'	=> "application/x-gzip",
							 'php'	=> "application/x-httpd-php",
							 'php3'	=> "application/x-httpd-php",
							 'php4'	=> "application/x-httpd-php",
							 'php5'	=> "application/x-httpd-php",
							 'js'	=> "application/x-javascript",
							 'msi'	=> "application/x-msi",
							 'swf'	=> "application/x-shockwave-flash",
							 'rf'	=> "application/x-shockwave-flash",
							 'tar'	=> "application/x-tar",
							 'zip'	=> "application/zip",
							 'au'	=> "audio/basic",
							 'mid'	=> "audio/midi",
							 'midi'	=> "audio/midi",
							 'kar'	=> "audio/midi",
							 'mp2'	=> "audio/mpeg",
							 'mp3'	=> "audio/mpeg",
							 'mpga'	=> "audio/mpeg",
							 'voc'	=> "audio/voc",
							 'vox'	=> "audio/voxware",
							 'aif'	=> "audio/x-aiff",
							 'aiff'	=> "audio/x-aiff",
							 'aifc'	=> "audio/x-aiff",
							 'wma'	=> "audio/x-ms-wma",
							 'ra'	=> "audio/x-pn-realaudio",
							 'ram'	=> "audio/x-pn-realaudio",
							 'rm'	=> "audio/x-pn-realaudio",
							 'ogg'	=> "audio/x-vorbis",
							 'wav'	=> "audio/wav",
							 'bmp'	=> "image/bmp",
							 'dib'	=> "image/bmp",
							 'gif'	=> "image/gif",							 
							 'jpg'	=> "image/jpeg",
							 'jpe'	=> "image/jpeg",
							 'jpeg'	=> "image/jpeg",
							 'jfif'	=> "image/jpeg",
							 'pcx'	=> "image/pcx",
							 'png'	=> "image/png",
							 'tif'	=> "image/tiff",
							 'tiff'	=> "image/tiff",
							 'ico'	=> "image/x-icon",
							 'pct'	=> "image/x-pict",
							 'txt'	=> "text/plain",
							 'htm'	=> "text/html",
							 'html'	=> "text/html",
							 'xml'	=> "text/xml",
							 'xsl'	=> "text/xml",
							 'dtd'	=> "text/xml-dtd",
							 'css'	=> "text/css",
							 'c'	=> "text/x-c",
							 'c++'	=> "text/x-c",
							 'cc'	=> "text/x-c",
							 'cpp'	=> "text/x-c",
							 'cxx'	=> "text/x-c",
							 'h'	=> "text/x-h",
							 'h++'	=> "text/x-h",
							 'hh'	=> "text/x-h",
							 'hpp'	=> "text/x-h",
							 'mpg'	=> "video/mpeg",
							 'mpe'	=> "video/mpeg",
							 'mpeg'	=> "video/mpeg",
							 'qt'	=> "video/quicktime",
							 'mov'	=> "video/quicktime",
							 'avi'	=> "video/x-ms-video",
							 'wm'	=> "video/x-ms-wm",
							 'wmv'	=> "video/x-ms-wmv",
							 'wmx'	=> "video/x-ms-wmx",
							 ''		=> "application/octet-stream");
		
		return ($from == "extension") ? ((!empty($attachtypes[$value])) ? $attachtypes[$value] : $attachtypes['']) : ((array_shift(array_keys($attachtypes,$value)) != "") ? array_shift(array_keys($attachtypes,$value)) : "" );
	}
	
	/**
	 * Convert an array to StdClass
	 *
	 * @access public static
	 * @param array $array input array
	 * @return StdClass $object output object
	 * @see SLS_String::objectToArray
	 * @since 1.0.5
	 */
	public static function arrayToObject($array)
	{
		if(!is_array($array))
			return $array;
		
		$object = new stdClass();
		if (is_array($array) && count($array) > 0)
		{
		  foreach ($array as $name=>$value)
		  {
		     $name = strtolower(trim($name));
		     if (!empty($name))
		        $object->$name = self::arrayToObject($value);
		  }
	      return $object;
		}
	    else
	      return false;
	}
	
	/**
	 * Convert a StdClass object to array
	 *
	 * @access public static
	 * @param StdClass $object input object
	 * @return array $array output array
	 * @see SLS_String::arrayToObject
	 * @since 1.0.7
	 */
	public static function objectToArray($object) 
	{
		if( !is_object($object) && !is_array($object))        
            return $object;        
        if(is_object($object))        
            $object = get_object_vars($object);
        return array_map(array('SLS_String', 'objectToArray'),$object);
	}
	
	/**
	 * Filter a string
	 * 
	 * @access public static
	 * @param string $input the input string to clean
	 * @param string $filter the filter to apply ('alpha'|'alnum'|'numeric'|'lower'|'lcfirst'|'upper'|'ucfirst'|'ucwords'|'trim'|'ltrim'|'rtrim'|'nospace'|'sanitize'|'striptags'|'hash')
	 * @param string $hash the type of hash (if $filter = 'hash')
	 * @return string $output the output string cleaned
	 * @see SLS_String::fullTrim
	 * @see SLS_String::sanitize
	 * @since 1.0.7
	 */
	public static function filter($input,$filter,$hash="")
	{
		$output = "";
		$filter = strtolower($filter);
		
		if (empty($input))
			return $input;
		
		switch ($filter)
		{
			case "alpha":
				$chars = array ("a","b","c","d","e","f","g","h","i","j","k","l","m",
								"n","o","p","q","r","s","t","u","v","w","x","y","z");
				for($i=0 ; $i<strlen($input) ; $i++)
					if (in_array(strtolower($input{$i}),$chars))
						$output .= $input{$i};
				break;
			case "alnum":
				$chars = array ("a","b","c","d","e","f","g","h","i","j","k","l",
								"m","n","o","p","q","r","s","t","u","v","w","x",
								"y","z","0","1","2","3","4","5","6","7","8","9");
				for($i=0 ; $i<strlen($input) ; $i++)
					if (in_array(strtolower($input{$i}),$chars))
						$output .= $input{$i};
				break;
			case "numeric":
				$chars = array ("0","1","2","3","4","5","6","7","8","9");
				for($i=0 ; $i<strlen($input) ; $i++)
					if (in_array($input{$i},$chars))
						$output .= $input{$i};				
				break;
			case "lower":
				$output = strtolower($input);
				break;
			case "lcfirst":
				$input{0} = strtolower($input{0});
				$output = $input;
				break;
			case "upper":
				$output = strtoupper($input);
				break;
			case "ucfirst":
				$output = ucfirst($input);
				break;
			case "ucwords":
				$output = ucwords($input);
				break;
			case "trim":
				$output = trim($input);
				break;
			case "ltrim":
				$output = ltrim($input);
				break;
			case "rtrim":
				$output = rtrim($input);
				break;
			case "nospace":
				$output = self::fullTrim($input);
				break;
			case "sanitize":
				$output = self::sanitize($input);
				break;
			case "striptags":
				$output = strip_tags($input);
				break;
			case "hash":
				$allowedHashes = array("sha1","md5","crc32","crypt");
				if (!in_array($hash,$allowedHashes))
					$hash = array_shift($allowedHashes);
				$function = new ReflectionFunction($hash);
				$output = $function->invokeArgs(array($input));
				break;
			default:
				$output = $input;
				break;
		}
		
		return $output;
	}
	
	/**
	 * Get absolute URL to a file img with a given suffix
	 * 
	 * @access public static
	 * @param string $file file name
	 * @param string $suffix suffix
	 * @param string $domainAlias the alias domain
	 * @return string $file absolute URL
	 * @see SLS_String::getUrlFile 
	 * @since 1.0.7
	 */
	public static function getUrlFileImg($file,$suffix,$domain="")
	{
		$generic = SLS_Generic::getInstance();
	    return $generic->getSiteConfig("protocol")."://".$generic->getSiteConfig("domainName",(empty($domain) && $generic->hasCdn()) ? $generic->getCdn() : $domain)."/".$generic->getPathConfig("files").self::substrBeforeLastDelimiter($file, ".".pathinfo($file,PATHINFO_EXTENSION)).$suffix.".".pathinfo($file,PATHINFO_EXTENSION);
	}
	
	/**
	 * Get absolute URL to a file
	 * 
	 * @access public static
	 * @param string $file file name
	 * @param string $domainAlias the alias domain
	 * @return string $file absolute URL
	 * @see SLS_String::getUrlFileImg	 
	 * @since 1.0.7
	 */
	public static function getUrlFile($file,$domain="")
	{
		$generic = SLS_Generic::getInstance();
	    return $generic->getSiteConfig("protocol")."://".$generic->getSiteConfig("domainName",(empty($domain) && $generic->hasCdn()) ? $generic->getCdn() : $domain)."/".$generic->getPathConfig("files").$file;
	}
}
?>