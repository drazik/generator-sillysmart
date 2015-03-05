<?php
/**
 * Multilanguage Management
 * 
 * @author Florian Collot
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Generics.Lang 
 * @since 1.0
 */
class SLS_Lang
{
	private $_generic;
	private $_lang;
	private $_session;
	private $_cookie;
	private $_cookieName;
	private $_sideSessionName;
	
	/**
	 * Constructor
	 *
	 * @access public	 
	 * @since 1.0
	 */
	public function __construct() 
	{
		$this->_generic = SLS_Generic::getInstance();
		$this->_session = $this->_generic->getObjectSession();
		$this->_cookieName = ($this->_generic->getSide() == 'sls') ? md5($this->_generic->getSiteConfig("projectName")."-SLS_Management") : md5($this->_generic->getSiteConfig("projectName")."-User-Lang");
		$this->_cookie = new SLS_Cookie($this->_cookieName);		
		$lang = ($this->_generic->getSide() == 'sls') ? 'en' : $this->_session->getParam("lang");
		$this->refreshLangSide();
		$applicationLangs = $this->getSiteLangs();
		
		// We search into the session if one lang is already defined
		if (!empty($lang) && in_array($lang, $applicationLangs)) 		
		{
			$this->_lang = $lang;
		}
		else if($this->_generic->getSiteConfig('isInstall') == 0)
		{
			// Search into the cookie the lang			
			$lang = $this->_cookie->__get("lang");
			
			if (!empty($lang) && in_array($lang, $applicationLangs)) 			
				$this->_lang = $lang;
			
			// Search into the browser's lang
			else 
			{
				$langsAccepted = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
				$hasLangAccepted = false;
				
				// We search if one lang into the browser match with one lang of the application				
				foreach ($langsAccepted as $value) 
				{
					if (in_array(strtolower(substr($value, 0, 2)), $applicationLangs) && is_file($this->_generic->getPathConfig("coreGenericLangs")."generic.".substr($value, 0, 2).".lang.php") && is_file($this->_generic->getPathConfig("genericLangs")."site.".substr($value, 0, 2).".lang.php"))
					{
						$hasLangAccepted = true;
						$lang = substr($value, 0, 2);
						break;
					}
				}
				// If any langs has been found, take the default language defined in the back office
				if (!$hasLangAccepted)				
					$lang = $this->_generic->getSiteConfig("defaultLang");				
			}
		}
		
		// Load lang files
		include ($this->_generic->getPathConfig("coreGenericLangs")."generic.".substr($lang, 0, 2).".lang.php");
		if ($this->_generic->getSide() == 'user' && !$this->_generic->getSiteConfig("isInstall"))
			include ($this->_generic->getPathConfig("genericLangs")."site.".substr($lang, 0, 2).".lang.php");
		$this->_lang = $lang; 
		
		// Set the lang into the cookie and into the session
		$this->setCookieLang();
		$this->setSessionLang();
	}
	
	/**
	 * Refresh the language & the application side
	 *
	 * @access public
	 * @since 1.0
	 */
	public function refreshLangSide()
	{		
		$this->_sideSessionName = 'current_lang';
	}
	
	/**
	 * Set the lang into the session
	 *
	 * @access public
	 * @since 1.0
	 */
	public function setSessionLang() 
	{
		$this->refreshLangSide();
		$this->_session->setParam($this->_sideSessionName, $this->_lang);
	}
	
	/**
	 * Set the lang into the cookie
	 *
	 * @access public
	 * @since 1.0
	 */
	public function setCookieLang() 
	{
		$this->refreshLangSide();		
		$this->_cookie->__set('lang', $this->_lang);
		$this->_cookie->save(time()+60*60*24*30);
	}
	
	/**
	 * Get a translation for a given key
	 * 
	 * @access public
	 * @param string $key
	 * @param string $lang
	 * @return string translation
	 * @since 1.1
	 */
	public function translate($key,$lang="")
	{
		// Current lang
		if (empty($lang))
			$lang = $this->_lang;
		
		// Different lang ?
		if ($lang != $this->_lang)
		{
			@include_once($this->_generic->getPathConfig("genericLangs")."site.".$lang.".lang.php");
			@include_once($this->_generic->getPathConfig("actionLangs").$this->_generic->getGenericControllerName()."/__".$this->_generic->getGenericControllerName().".".$lang.".lang.php");
			@include_once($this->_generic->getPathConfig("actionLangs").$this->_generic->getGenericControllerName()."/".$this->_generic->getGenericScontrollerName().".".$lang.".lang.php");
		}
		
		return (empty($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][strtoupper($key)])) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][strtoupper($key)] : $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][strtoupper($key)];
	}
	
	/**
	 * Get the default lang
	 * 
	 * @access public
	 * @return string $lang the default lang
	 * @see SLS_Lang::getLang
	 * @see SLS_Lang::setLang
	 * @since 1.0.3
	 */
	public function getDefaultLang()
	{
		return $this->_generic->getSiteConfig("defaultLang");
	}
	
	/**
	 * Get current lang
	 *
	 * @access public
	 * @return string $lang the current lang
	 * @see SLS_Lang::getDefaultLang
	 * @see SLS_Lang::setLang
	 * @since 1.0
	 */
	public function getLang() 
	{
		return ($this->_generic->getSide() == 'sls') ? 'en' : $this->_lang;
	}
	
	/**
	 * Set the current lang
	 *
	 * @access public
	 * @param string $lang the lang to set
	 * @return bool $set true if the lang exists, else false
	 * @see SLS_Lang::getLang
	 * @see SLS_Lang::getDefaultLang
	 * @since 1.0
	 */
	public function setLang($lang) 
	{
		if (is_file($this->_generic->getPathConfig("coreGenericLangs")."generic.".substr($lang, 0, 2).".lang.php") && is_file($this->_generic->getPathConfig("genericLangs")."site.".substr($lang, 0, 2).".lang.php") && $this->isEnabledLang(substr($lang, 0, 2))) 
		{
			@include_once($this->_generic->getPathConfig("coreGenericLangs")."generic.".substr($lang, 0, 2).".lang.php");
			if ($this->_generic->getSide() == 'user')
			{
				@include_once($this->_generic->getPathConfig("genericLangs")."site.".substr($lang, 0, 2).".lang.php");
				@include_once($this->_generic->getPathConfig("actionLangs").$this->_generic->getGenericControllerName()."/__".$this->_generic->getGenericControllerName().".".$lang.".lang.php");
				@include_once($this->_generic->getPathConfig("actionLangs").$this->_generic->getGenericControllerName()."/".$this->_generic->getGenericScontrollerName().".".$lang.".lang.php");
			}
			$this->_lang = substr($lang, 0, 2);
			$this->setSessionLang();
			$this->setCookieLang();			
			return true;
		}
		return false;
	}
	
	/**
	 * Check if a lang is currently enabled
	 *
	 * @param string $lang the lang
	 * @return bool $enabled true if enabled, else false
	 * @since 1.0
	 */
	public function isEnabledLang($lang)
	{
		$result = array_shift($this->_generic->getSiteXML()->getTagsAttribute("//configs/langs/name[node()='".$lang."']","active"));
		return ($result["attribute"] == "true") ? true : false;
	}
	
	/**
	 * Load file lang for one controller	 
	 *
	 * @access public
	 * @param string $genericMode
	 * @param string $lang the lang in which we want to load the file, if empty, load the file into the current lang
	 * @since 1.1
	 */
	public function loadControllerLang($genericMode, $lang="")
	{
		if ($lang != "")
			$this->setLang($lang);
		if ($this->_generic->getSide() == 'user' && is_file($this->_generic->getPathConfig("actionLangs").$genericMode."/__".$genericMode.".".$this->_lang.".lang.php"))
			include_once($this->_generic->getPathConfig("actionLangs").$genericMode."/__".$genericMode.".".$this->_lang.".lang.php");
	}
	
	/**
	 * Load file lang for one action	 
	 *
	 * @access public
	 * @param string $genericMode
	 * @param string $genericSmode
	 * @param string $lang the lang in which we want to load the file, if empty, load the file into the current lang
	 * @since 1.0
	 */
	public function loadActionLang($genericMode, $genericSmode, $lang="")
	{
		if ($lang != "")
			$this->setLang($lang);
		if ($this->_generic->getSide() == 'user' && is_file($this->_generic->getPathConfig("actionLangs").$genericMode."/".$genericSmode.".".$this->_lang.".lang.php"))
			include ($this->_generic->getPathConfig("actionLangs").$genericMode."/".$genericSmode.".".$this->_lang.".lang.php");		
	}
	
	/**
	 * Get all the application langs	 
	 *
	 * @access public	 
	 * @param bool $all if true, return all languages, else, return onlny actives langs 
	 * @return array $langs all the application langs
	 * @since 1.0
	 * @example
	 * var_dump($lang->getSiteLangs());
	 * // will produce :
	 * array('en','..','fr');
	 */
	public function getSiteLangs($all=true) 
	{
		return ($all) ? $this->_generic->getSiteXML()->getTags("//configs/langs/name") : $this->_generic->getSiteXML()->getTags("//configs/langs/name[@active='true']");
	}
	
	/**
	 * Reset the current lang
	 *
	 * @access public
	 * @since 1.0
	 */
	public function resetLang()
	{
		$this->refreshLangSide();
		$this->_cookie->delete();
		$this->_session->delParam($this->_sideSessionName);
	}	
}
?>