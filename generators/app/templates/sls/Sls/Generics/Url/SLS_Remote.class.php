<?php
/**
 * Class Remote - Test fonctions for remote request
 * 
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.Url
 * @since 1.0 
 */
class SLS_Remote
{
	/**
	 * Test if a remote file exists
	 *
	 * @access public static
	 * @param string $url the url
	 * @return int $exists 0 if ok, 1 if invalid url host, 2 if unable to connect
	 * @since 1.0
	 */
	public static function remoteFileExists ($url)
	{
	
		$head = "";
		$url_p = parse_url ($url);
		
		if (isset ($url_p["host"]))
			$host = $url_p["host"]; 
		else
			return 1; 
		
		if (isset ($url_p["path"]))
			$path = $url_p["path"]; 
		else
			$path = "";
		
		$fp = @fsockopen ($host, 80, $errno, $errstr, 20);
		if (!$fp)
			return 2; 
		else
		{
		   $parse = parse_url($url);
		   $host = $parse['host'];
		  
		   fputs($fp, "HEAD ".$url." HTTP/1.1\r\n" );
		   fputs($fp, "HOST: ".$host."\r\n" );
		   fputs($fp, "Connection: close\r\n\r\n" );
		   $headers = "";
		   while (!feof ($fp))
		   	$headers .= fgets ($fp, 128); 
		}
		fclose ($fp);
		$arr_headers = explode("\n", $headers);

		if (isset ($arr_headers[0]))
			$return = (strpos ($arr_headers[0], "404" ) === false) ? 0 : 1; 
		
		return $return;
	}
}
?>