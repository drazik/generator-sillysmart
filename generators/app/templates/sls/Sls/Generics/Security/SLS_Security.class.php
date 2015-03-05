<?php
/**
 * Manage Security
 *  
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Security
 * @since 1.0 
 */
class SLS_Security 
{
	private static $_instance;
	
	/**
	 * Contructor
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct() {}
	
	/**
	 * Singleton
	 * 
	 * @access public static
	 * @return SLS_Security $instance SLS_Security instance
	 * @since 1.0
	 */
	public static function getInstance() 
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new SLS_Security();
		}
		return self::$_instance;
	}	
	
	/**
	 * Symmetric encryption
	 *
	 * @access public static	 
	 * @param string $text the text to encrypt
	 * @param string $key the private key (default Project Private key)
	 * @return string $encrypted the text encrypted
	 * @see SLS_Security::decrypt
	 * @since 1.0
	 * @example 
	 * $encrypted = SLS_Security::getInstance()->encrypt("sillysmart");
     * var_dump($encrypted);
     * // will produce : "VyUA[..]UgCCA="
	 */	
	public static function encrypt($text,$key="")
	{
		if ($key == "")
			$key = SLS_Generic::getInstance()->getSiteConfig('privateKey');
		
		srand((double)microtime()*1000000);
		$privateKey = md5(rand(0,32000) );
		$count=0;
		$publicKey = ""; 
		for ($i=0; $i<strlen($text); $i++)  
		{
			if ($count == strlen($privateKey))
				$count = 0;
			$publicKey.= substr($privateKey,$count,1).(substr($text,$i,1) ^ substr($privateKey,$count,1) );
			$count++;
		}
		return base64_encode(SLS_Security::generateKey($publicKey,$key));
	}

	/**
	 * Symmetric encryption
	 *
	 * @access public static	 
	 * @param string $text the text to encrypt
	 * @param string $key the private key (default Project Private key)
	 * @return string $decrypted the text decrypted
	 * @see SLS_Security::crypte
	 * @since 1.0
	 * @example 
	 * $uncrypted = SLS_Security::getInstance()->decrypt("VyUA[..]UgCCA=");
     * var_dump($uncrypted);
     * // will produce : "sillysmart"
	 */	
	public static function decrypt($text,$key="")
	{
		if ($key == "")
			$key = SLS_Generic::getInstance()->getSiteConfig('privateKey');
		$text = SLS_Security::generateKey(base64_decode($text),$key);
		$decrypted = "";
		for ($i=0;$i<strlen($text);$i++)
		{
			$md5 = substr($text,$i,1);
			$i++;
			$decrypted.= (substr($text,$i,1) ^ $md5);
		}
		return $decrypted;
	}

	/**
	 * Generate a key based on private key
	 * 
	 * @access public static
	 * @param string $text the text
	 * @param string $privateKey the private key
	 * @return string $publicKey the public key
	 * @since 1.0
	 */
	public static function generateKey($text,$privateKey="")
	{
		$privateKey = md5($privateKey);
  		$count = 0;
  		$publicKey = "";
		for ($i=0;$i<strlen($text);$i++) 
		{
			if ($count == strlen($privateKey))
				$count = 0;
			$publicKey.= substr($text,$i,1) ^ substr($privateKey,$count,1);
			$count++;
		}
  		return $publicKey;
  	}
}
?>