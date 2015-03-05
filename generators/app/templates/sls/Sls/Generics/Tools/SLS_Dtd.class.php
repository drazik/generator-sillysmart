<?php
/**
 * Tool SLS_Dtd - Perform a XSL -> Php transformation
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Generics.Tools
 * @since 1.0 
 */ 
class SLS_Dtd
{	
	private $_generic;
	private $_cacheActive;
	private $_viewsBody;
	private $_viewsHeaders;
	private $_xml;
	private $_staticsXsl;
	
	/**
	 * Constructor - get the SLS_Generic instance & cache state & paths
	 *
	 * @access public
	 * @param string $xml the xml flow
	 * @since 1.0
	 */
	public function __construct($xml)
	{
		$this->_generic = SLS_Generic::getInstance();
		$this->_cacheActive = $this->_generic->isCache();
		$this->_viewsBody = ($this->_generic->getSide() == 'user') ? "viewsBody" : "coreViewsBody";
		$this->_viewsHeaders = ($this->_generic->getSide() == 'user') ? "viewsHeaders" : "coreViewsHeaders";
		$this->_xml = $xml;
	}
	
	/**
	 * Get the charset of the application
	 *
	 * @access public
	 * @return string $xslCharset the charset
	 * @since 1.0
	 */
	public function getCharset()
	{
		$xslCharset = ($this->_generic->getSide() == 'user') ? $this->_generic->getSiteConfig('defaultCharset') : $this->_generic->getCoreConfig('charset', 'sls');
		return $xslCharset;
	}
	
	/**
	 * Get the action file (body part) for include
	 *
	 * @access public
	 * @return string $string the action file (body)
	 * @since 1.0
	 */
	public function includeActionFileBody()
	{
		return "<xsl:include href=\"".$this->_generic->getPathConfig($this->_viewsBody).$this->_generic->getGenericControllerName()."/".$this->_generic->getGenericScontrollerName().".xsl\" />";		
	}
	
	/**
	 * Get the action file (header part) for include
	 *
	 * @access public
	 * @return string $string the action file (header)
	 * @since 1.0
	 */
	public function includeActionFileHeader()
	{
		return (is_file($this->_generic->getPathConfig($this->_viewsHeaders).$this->_generic->getGenericControllerName()."/".$this->_generic->getGenericScontrollerName().".xsl")) ? "<xsl:include href=\"".$this->_generic->getPathConfig($this->_viewsHeaders).$this->_generic->getGenericControllerName()."/".$this->_generic->getGenericScontrollerName().".xsl\" />" : "";
	}
	
	/**
	 * Get the statics files for include
	 *
	 * @access public
	 * @return string $string the statics files
	 * @since 1.0
	 */
	public function includeStaticsFiles()
	{		
		$xmlToolBox = new SLS_XMLToolbox($this->_xml);
		$this->_staticsXsl['unloaded'] = ($this->_cacheActive == 0) ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Unloaded/xsl/file") : ($this->_generic->getSide() == 'user') ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Unloaded/xsl/file") : $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/SlsUnloaded/xsl/file");
		$this->_staticsXsl['loaded']['headers']['file'] = ($this->_cacheActive == 0) ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/Headers/xsl/file") : ($this->_generic->getSide() == 'user') ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/Headers/xsl/file") : $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/SlsHeaders/xsl/file");
		$this->_staticsXsl['loaded']['headers']['name'] = ($this->_cacheActive == 0) ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/Headers/xsl/templateName") : ($this->_generic->getSide() == 'user') ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/Headers/xsl/templateName") : $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/SlsHeaders/xsl/templateName");
		$this->_staticsXsl['loaded']['body']['file'] = ($this->_cacheActive == 0) ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/Body/xsl/file") : ($this->_generic->getSide() == 'user') ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/Body/xsl/file") : $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/SlsBody/xsl/file");
		$this->_staticsXsl['loaded']['body']['name'] = ($this->_cacheActive == 0) ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/Body/xsl/templateName") : ($this->_generic->getSide() == 'user') ? $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/Body/xsl/templateName") : $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/SlsBody/xsl/templateName");
		
		$this->_staticsXsl['coreUnloaded'] = $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/CoreUnloaded/xsl/file");
		$this->_staticsXsl['loaded']['coreHeaders']['file'] = $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/CoreHeaders/xsl/file");
		$this->_staticsXsl['loaded']['coreHeaders']['name'] = $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/CoreHeaders/xsl/templateName");
		$this->_staticsXsl['loaded']['coreBody']['file'] = $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/CoreBody/xsl/file");
		$this->_staticsXsl['loaded']['coreBody']['name'] = $xmlToolBox->getTags("//root/Statics/Sls/XslStatics/files/Loaded/CoreBody/xsl/templateName");
		$files = array_merge($this->_staticsXsl['unloaded'], $this->_staticsXsl['coreUnloaded'], $this->_staticsXsl['loaded']['headers']['file'], $this->_staticsXsl['loaded']['body']['file'], $this->_staticsXsl['loaded']['coreHeaders']['file'], $this->_staticsXsl['loaded']['coreBody']['file']);
		$string = "";
		foreach ($files as $file)
			$string .= "<xsl:include href=\"".$file."\" />\n";
		 
		return $string;
	}
	
	/**
	 * Get the language of the application
	 *
	 * @access public
	 * @return string $string the language
	 * @since 1.0
	 */
	public function getLanguage()
	{
		return $this->_generic->getObjectLang()->getLang();
	}
	
	/**
	 * Get the generics core headers for call-template
	 *
	 * @access public
	 * @return string $string the generics core headers
	 * @since 1.0
	 */
	public function loadCoreHeaders()
	{
		return $this->loadStaticsXsl("coreHeaders");
	}
	
	/**
	 * Get the generics user headers for call-template
	 *
	 * @access public
	 * @deprecated since 1.0.8	 
	 * @return string $string the generics user headers
	 * @since 1.0
	 */
	public function loadUserHeaders()
	{
		return ($this->_generic->getSide() == 'user') ? $this->loadStaticsXsl("headers") : "";
	}
	
	/**
	 * Get the action file (header part) for call-template
	 *
	 * @access public
	 * @return string $string the action file (header)
	 * @since 1.0
	 */
	public function loadActionFileHeader()
	{
		return (is_file($this->_generic->getPathConfig($this->_viewsHeaders).$this->_generic->getGenericControllerName()."/".$this->_generic->getGenericScontrollerName().".xsl")) ? "<xsl:call-template name=\"Header".$this->_generic->getGenericScontrollerName()."\" />" : "";
	}
	
	/**
	 * Get the generics core body for call-template
	 *
	 * @access public
	 * @return string $string the generics core body
	 * @since 1.0
	 */
	public function loadCoreBody()
	{
		return $this->loadStaticsXsl("coreBody");
	}
	
	/**
	 * Get the generics user body for call-template
	 *
	 * @access public
	 * @deprecated since 1.0.8
	 * @return string $string the generics user body
	 * @since 1.0
	 */
	public function loadUserBody()
	{
		return $this->loadStaticsXsl("body");
	}
	
	/**
	 * Get the action file (body part) for call-template
	 *
	 * @access public
	 * @return string $string the action file (body)
	 * @since 1.0
	 */
	public function loadActionFileBody()
	{
		return "<xsl:call-template name=\"".$this->_generic->getGenericScontrollerName()."\" />";
	}	
			
	/**
	 * Build XSL Variable with URLS
	 *
	 * @access public
	 * @return string $string the xsl variables string
	 * @since 1.0
	 */
	public function buildUrlVars()
	{
		$str  = "<xsl:variable name='sls_url_domain'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName")."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_public'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName",($this->_generic->hasCdn()) ? $this->_generic->getCdn() : "")."/".$this->_generic->getPathConfig('public')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_img'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName",($this->_generic->hasCdn()) ? $this->_generic->getCdn() : "")."/".$this->_generic->getPathConfig('img')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_css'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName",($this->_generic->hasCdn()) ? $this->_generic->getCdn() : "")."/".$this->_generic->getPathConfig('css')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_files'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName",($this->_generic->hasCdn()) ? $this->_generic->getCdn() : "")."/".$this->_generic->getPathConfig('files')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_scripts'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName",($this->_generic->hasCdn()) ? $this->_generic->getCdn() : "")."/".$this->_generic->getPathConfig('scripts')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_js'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName",($this->_generic->hasCdn()) ? $this->_generic->getCdn() : "")."/".$this->_generic->getPathConfig('js')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_js_statics'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName",($this->_generic->hasCdn()) ? $this->_generic->getCdn() : "")."/".$this->_generic->getPathConfig('jsStatics')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_js_dyn'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName",($this->_generic->hasCdn()) ? $this->_generic->getCdn() : "")."/".$this->_generic->getPathConfig('jsDyn')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_img_core'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig('coreImg')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_img_core_icons'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig('coreIcons')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_img_core_buttons'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig('coreButtons')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_img_core_backgrounds'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig('coreBackgrounds')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_css_core'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig('coreCss')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_js_core'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig('coreJs')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_js_core_statics'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig('coreJsStatics')."</xsl:variable>";
		$str .= "<xsl:variable name='sls_url_js_core_dyn'>".$this->_generic->getProtocol()."://".$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig('coreJsDyn')."</xsl:variable>";

		foreach($this->_generic->getViewCustomVars() as $key => $value)
			$str .= "<xsl:variable name='".$key."'>".$value."</xsl:variable>";
		
		return $str;
	}
	
	/**
	 * Translate multilanguage properties
	 * 
	 * @access public
	 * @param string $key the key of your lang
	 * @return string $string the value of your lang
	 * @since 1.0.8
	 */
	public function lang($key)
	{
		$value = (isset($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][strtoupper($key)])) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][strtoupper($key)] : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][strtoupper($key)];
		return str_replace(array('\"'),array('"'),htmlspecialchars_decode($value,ENT_NOQUOTES));
	}
	
	/**
	 * Load & call the statics XSL (Head or Body)
	 *
	 * @access private
	 * @param string $type 'headers' or 'body'
	 * @return string xslCode the xsl code to invoke
	 * @since 1.0
	 */
	private function loadStaticsXsl($type="headers") 
	{
		if ($type != "headers" && $type != "body" && $type != "coreHeaders" && $type != "coreBody")
			SLS_Tracing::addTrace(new Exception("Warning, incorrect argument in loadStaticsXsl() View.class.php"));
		
		$string = "";
		
		foreach ($this->_staticsXsl['loaded'][$type]['name'] as $templateName)
		{
			if (!empty($templateName))
				$string .= "<xsl:call-template 
								name=\"".$templateName."\" />\n";
		}
		return $string;
	}
	
	/**
	 * Get the URL of the customer back-office menu
	 * 
	 * @access public
	 * @return string the url of the {{USER_BO}}/BoMenu
	 * @since 1.1
	 */
	public function urlBoMenu()
	{
		$params = array_merge_recursive($_POST,$_GET);
		if (SLS_String::endsWith($params['smode'], SLS_Generic::getInstance()->getSiteConfig('defaultExtension')))		
            $params['smode'] = SLS_String::substrBeforeLastDelimiter($params['smode'], '.'.SLS_Generic::getInstance()->getSiteConfig('defaultExtension'));
		$explode = explode("/", $params['smode']);
		$params['smode'] = array_shift($explode);
		$queryString = "";
		$params = array_chunk($explode, 2);		
		for($i=0 ; $i<$count=count($params) ; $i++)		
			if (count($params[$i]) == 2)
				$queryString .= (($i == 0) ? '' : '&').$params[$i][0].'='.(($params[$i][1] != "|sls_empty|") ? $params[$i][1] : "");
		$queryString = str_replace(array("=","&"),"/",$queryString);
		$controllerBo = $this->_generic->getBo();
		if (!empty($controllerBo))
		{
			$controllers = $this->_generic->translateActionId($this->_generic->getActionId($controllerBo,"BoMenu"));
			return $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")."/".$controllers["controller"]."/".$controllers["scontroller"]."/".$queryString.".".$this->_generic->getSiteConfig("defaultExtension");
		}
		else
			return "";
	}

	public static function boExists()
	{
		return (SLS_Generic::getInstance()->getBo() == "") ? false : true;
	}
	
	/**
	 * Display language variable
	 *
	 * @access public static
	 * @param string $key the lang key
	 * @return the lang sentence
	 * @since 1.0.2
	 */
	public static function displayLang($key)
	{
		return (isset($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][strtoupper($key)])) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS'][strtoupper($key)] : $GLOBALS[$GLOBALS['PROJECT_NAME']]['XSL'][strtoupper($key)];
	}
}
?>