<?php
/**
 * Tool SLS_HttpRequest - Http Request Treatment
 * 
 * @author Laurent Bientz
 * @author Florian Collot
 * @copyright SillySmart
 * @package Sls.Generics.View
 * @since 1.0  
 */
class SLS_View
{
	private $_generic;
	private $_cache;
	private $_xml;	
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param SLS_Generic $generic SLS_Generic instance
	 * @param string $xml xml string to parse
	 * @since 1.0
	 */
	public function __construct($generic,$xml)
	{
		$this->_generic = $generic;
		$this->_cache = $this->_generic->getObjectCache();		
		$this->_xml = $xml;		
		$this->parseToXhtml();
		
		$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_start),"Process finished","","Render");
	}
	
	/**
	 * Parsing: flow XML + XSL => xHTML
	 *
	 * @access private
	 * @since 1.0
	 */
	private function parseToXhtml()
	{
		$this->_generic->_time_checkpoint = microtime(true);
		
		// Xml
		$xmlDoc = new DOMDocument();
		try 
		{
			$xmlDoc->loadXML($this->_xml); 
		}
		catch (Exception $e)
		{
			SLS_Tracing::addTrace(new Exception("Error during XML Parsing"), true, "<h2>".$e->getMessage()."</h2><div style=\"margin: 0 30px;padding: 10px;\"><pre name=\"code\" class=\"brush:xml\">".htmlentities($this->_xml, ENT_QUOTES)."</pre></div>");
		}
		
		// Xsl
		$xslDoc = new DOMDocument();		
		try 
		{
			$xslDoc->loadXML($this->constructGenericXsl()); 
		}
		catch (Exception $e)
		{
			SLS_Tracing::addTrace(new Exception($e->getMessage()));
		}
		
		// Parsing
		$proc = new XSLTProcessor();
		$proc->registerPHPFunctions();
		try 
		{ 
			$proc->importStyleSheet($xslDoc); 
		}
		catch (Exception $e)
		{
			SLS_Tracing::addTrace(new Exception($e->getMessage()));			
		}
		
		// If errors in dev
		if (!SLS_Generic::getInstance()->isProd() && SLS_Tracing::$_exceptionThrown)
			SLS_Tracing::displayTraces();
		else
		{
			// Show xml in source in dev
			/*if ($this->_generic->getSiteConfig("isProd") == 0)							
				echo "<!--[if lt IE 5]><!--<![CDATA[<pre style='display:none;'>\n".$this->_xml." \n</pre>]]>--><![endif]-->\n";*/
			
			// Parse XML/XSL
			$html = $proc->transformToXML($xmlDoc);			
			$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Parsing XML/XSL","","XML/XSL Parsing");
			$this->_generic->_time_checkpoint = microtime(true);
			
			// Parse HTML with SLS_Dtd
			$html = $this->parseHtml($html);
			
			// Sls cached enabled and Action cache enabled ?
			$cacheOptions = $this->_cache->getAction();
			$actionCache = false;
			if ($this->_generic->isCache() && 
				$this->_generic->getSide() == "user" && 
				$this->_generic->getGenericControllerName() != "Default" &&
				is_array($cacheOptions) && 
				count($cacheOptions) == 4)
			{
				$actionCache			= true; 
				$actionCacheVisibility 	= $cacheOptions[0];
				$actionCacheScope 		= $cacheOptions[1];
				$actionCacheResponsive	= $cacheOptions[2];
				$actionCacheExpiration 	= $cacheOptions[3];
				
				// Save Full HTML cached
				if ($actionCacheScope == "full")				
					$this->_cache->saveCacheFull($html,$actionCacheVisibility,$actionCacheResponsive);				
			}

			// Show flash button to copy Xml if developer on user side and not on Bo
			if (SLS_BoRights::isLogged() && SLS_BoRights::getAdminType() == "developer" && $this->_generic->getSide() == "user" && $this->_generic->getGenericControllerName() != $this->_generic->getBo())
				$html = preg_replace('/\<\/head\>/i', "\n".
													  t(1).'<!-- Sls developer Toolbar -->'."\n".
													  t(1).'<script type="text/javascript" src="'.$this->_generic->getProtocol().'://'.$this->_generic->getSiteConfig("domainName")."/".$this->_generic->getPathConfig("coreJsDyn").'ZeroClipboard/ZeroClipboard.js"></script>'."\n".
													  t(1).'<script type="text/javascript">'."\n".
													  t(2).'window.slsBuild.xml = "'.htmlentities(str_replace(array('"',"\n"),array('\"',''),$this->_xml),ENT_COMPAT,"UTF-8").'";'."\n".
													  t(1).'</script>'."\n".
													  t(1).'<!-- /Sls developer Toolbar -->'."\n\n".
													  t(1).'</head>', $html);

			echo $html;
			
			$this->_generic->logTime($this->_generic->monitor($this->_generic->_time_checkpoint),"Parsing HTML","","HTML Parsing");
		}		
	}
	
	/**
	 * Construct generic XML
	 *
	 * @access public	 
	 * @return string $xsl the xsl content
	 * @since 1.0
	 */
	public function constructGenericXsl()
	{		
		if ($this->_generic->getSide() == 'user')		
			$xsl = file_get_contents($this->_generic->getPathConfig("viewsTemplates").$this->_generic->getCurrentTpl().".xsl");		
		else			
			$xsl = file_get_contents($this->_generic->getPathConfig("coreViewsTemplates").$this->_generic->getCurrentTpl().".xsl");
					
		$delimiters = SLS_String::getBoundContent($xsl,"|||sls:","|||");
		$arraySearch = array();
		$arrayReplace = array();
		
		$dtd = new SLS_Dtd($this->_xml);
		
		// Foreach delimiters found, search if we can map a function of SLS_Dtd's class with this delimiter
		foreach($delimiters as $delimiter)
		{
			$dtd_mask = $delimiter;
			$delimiter = explode(":",$delimiter);
			$method = array_shift($delimiter);
			$args = $delimiter;			
						
			$ref = new ReflectionMethod($dtd,$method);			
			$nbRequiredParams = $ref->getNumberOfRequiredParameters();
			$nbMaxParams = $ref->getNumberOfParameters();			
			if ($nbRequiredParams > count($args))			
				SLS_Tracing::addTrace(new Exception("Error: Specific SLS_Dtd function `".$method."` needs at least ".$nbRequiredParams." required parameters"),true);				
			if ($nbMaxParams < count($args))
				SLS_Tracing::addTrace(new Exception("Warning: Specific SLS_Dtd function `".$method."` has only ".$nbMaxParams." parameters, you call it with ".count($args)." parameters"),true);
			
			if (method_exists($dtd,$method))
			{
				array_push($arraySearch,"|||sls:".$dtd_mask."|||");
				array_push($arrayReplace,$ref->invokeArgs($dtd,$args));
			}
			else
				SLS_Tracing::addTrace(new Exception("Warning: the delimiter |||sls:".$delimiter."||| doesn't match with any function of class SLS_Dtd"));
		}
		return str_replace($arraySearch,$arrayReplace,$xsl);
	}
	
	/**
	 * Parse final HTML with SLS_Dtd
	 * 
	 * @access public
	 * @param string $html the HTML content
	 * @return string the HTML content parsed
	 * @since 1.0.8
	 */
	public function parseHtml($html)
	{
		$delimiters = SLS_String::getBoundContent($html,"|||sls:","|||");
		$arraySearch = array();
		$arrayReplace = array();
		
		$dtd = new SLS_Dtd(null);
		
		// Foreach delimiters found, search if we can map a function of SLS_Dtd's class with this delimiter
		foreach($delimiters as $delimiter)
		{
			$dtd_mask = $delimiter;
			$delimiter = explode(":",$delimiter);
			$method = array_shift($delimiter);
			$args = $delimiter;			
			
			$ref = new ReflectionMethod($dtd,$method);			
			$nbRequiredParams = $ref->getNumberOfRequiredParameters();
			$nbMaxParams = $ref->getNumberOfParameters();
			
			if ($nbRequiredParams > count($args))			
				SLS_Tracing::addTrace(new Exception("Error: Specific SLS_Dtd function `".$method."` needs at least ".$nbRequiredParams." required parameters"),true);
			if ($nbMaxParams < count($args))
				SLS_Tracing::addTrace(new Exception("Warning: Specific SLS_Dtd function `".$method."` has only ".$nbMaxParams." parameters, you call it with ".count($args)." parameters"),true);
			
			if (method_exists($dtd,$method) && $nbRequiredParams <= count($args))
			{
				array_push($arraySearch,"|||sls:".$dtd_mask."|||");
				array_push($arrayReplace,$ref->invokeArgs($dtd,$args));
			}
			else
				SLS_Tracing::addTrace(new Exception("Warning: the delimiter |||sls:".$method."||| doesn't match with any function of class SLS_Dtd"));
		}
		return str_replace($arraySearch,$arrayReplace,$html);
	}
}
?>