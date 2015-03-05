<?php
/**
* Class Templates into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoTemplates extends SLS_BoControllerProtected
{
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		
		$xml->startTag("templates");
		$handle = opendir($this->_generic->getPathConfig("viewsTemplates"));
		while($file = readdir($handle))
		{
			if (is_file($this->_generic->getPathConfig("viewsTemplates").$file) && substr($file, 0, 1) != ".")
			{
				$fileName 	= SLS_String::substrBeforeLastDelimiter($file,".");
				$extension 	= SLS_String::substrAfterLastDelimiter($file,".");
				
				if ($extension == "xsl" && $fileName != "__default")
				{
					$xml->startTag("template");
					$xml->addFullTag("name",$fileName,true);					
					$xml->endTag("template");
				}
			}
		}
		closedir($handle);
		$xml->addFullTag("url_add",$this->_generic->getFullPath("SLS_Bo","AddTemplate"),true);
		$xml->addFullTag("url_delete",$this->_generic->getFullPath("SLS_Bo","DeleteTemplate",array(),false),true);
		$xml->endTag("templates");		
		
		$xml->startTag("generics");
		$handle = opendir($this->_generic->getPathConfig("viewsGenerics"));
		while($file = readdir($handle))
		{
			if (is_file($this->_generic->getPathConfig("viewsGenerics").$file) && substr($file, 0, 1) != ".")
			{
				$fileName 	= SLS_String::substrBeforeLastDelimiter($file,".");
				$extension 	= SLS_String::substrAfterLastDelimiter($file,".");
				
				if ($extension == "xsl")
				{
					$xml->startTag("generic");
					$xml->addFullTag("name",$fileName,true);					
					$xml->endTag("generic");
				}
			}
		}
		closedir($handle);
		$xml->addFullTag("url_add",$this->_generic->getFullPath("SLS_Bo","AddGeneric"),true);
		$xml->addFullTag("url_delete",$this->_generic->getFullPath("SLS_Bo","DeleteGeneric",array(),false),true);
		$xml->endTag("generics");		
		
		$this->saveXML($xml);		
	}
}
?>