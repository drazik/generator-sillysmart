<?php
/**
* Class BoProjectSettings into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoProjectSettings extends {{USER_BO}}ControllerProtected
{
	private $_xmlProject = null;
	
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		$this->_xmlProject = $this->_generic->getProjectXML();
		
		if ($this->_http->getParam("reload-edit") == "true")
		{	
			$settings = $this->_http->getParam("settings");
			$xmlChange = false;
			foreach($settings as $xpath => $value)
			{
				$xpath = str_replace(array("openbracket","closebracket"),array("[","]"),$xpath);
				if ($value != $this->_xmlProject->getTag($xpath))
				{
					$this->_xmlProject->setTag($xpath,$value);
					$xmlChange = true;
				}
			}
			if ($xmlChange)
			{
				$this->_xmlProject->saveXML($this->_generic->getPathConfig("configSecure")."project.xml");
				$this->_xmlProject->refresh();
				$this->_bo->pushNotif("success",$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_EDIT']);
			}
		}
		
		$nodes = SLS_XMLToArray::createArray($this->_xmlProject ->getXML());
		$xml->startTag("settings");
			$xml = $this->recursiveParser($nodes,$xml);
		$xml->endTag("settings");
		
		$xml = $this->_bo->formatNotif($xml);
		$this->saveXML($xml);
	}
	
	public function recursiveParser($array,$xml)
	{
		foreach($array as $key => $value)
		{
			if (is_array($value))
			{
				if (array_key_exists("@value",$value) && $key != "project")
				{
					$label = str_replace("/"," - ",SLS_String::substrAfterFirstDelimiter($value["@xpath"],"project/"));
					$attributes = $value["@attributes"];
					if (is_array($attributes))
					{
						if (array_key_exists("js",$attributes))
							unset($attributes["js"]);
						if (array_key_exists("isSecure",$attributes))
							unset($attributes["isSecure"]);
							
						if (!empty($attributes))
						{
							if (SLS_String::contains($label,"["))
							{
								$label = SLS_String::substrBeforeLastDelimiter($label,"[");
							}
							$label .= " (";
							foreach($attributes as $attKey => $attValue)
								$label .= $attKey." = '".$attValue."', ";
							$label = trim($label);
							$label = trim(SLS_String::substrBeforeLastDelimiter($label,','));
							$label .= ")";
						}
					}
					$xml->startTag("setting");
						$xml->addFullTag("key",$label,true);
						$xml->addFullTag("value",$value["@value"],true);
						$xml->addFullTag("xpath",str_replace(array("[","]"),array("openbracket","closebracket"),$value["@xpath"]),true);
					$xml->endTag("setting");
				}
				else
					$xml = $this->recursiveParser($value,$xml);
			}
		}
		
		return $xml;
	}
}
?>