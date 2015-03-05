<?php
class SLS_BoEditPluginField extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$plugId = $this->_http->getParam('Plugin');
		$field 	= $this->_http->getParam('Field');
		$action = $this->_http->getParam('Action');
		$controllers = $this->_generic->getTranslatedController('SLS_Bo', 'EditPlugin');
		
		if (empty($plugId) || SLS_PluginsManager::isExists($plugId) === false)
			$this->dispatch('SLS_Bo', 'Plugins');
		
		$plugin = new SLS_PluginsManager($plugId);
		$xmlPlug = $plugin->getXML();
		
		if (empty($field) || empty($action))
			$this->redirect($controllers['controller']."/".$controllers['scontroller']."/Plugin/".$plugId.".sls");
		
				
		$xpath = "//".str_replace("|||", "/", str_replace("|$|", "]", str_replace("$|$", "[", $field)));
		
		$clonable = array_shift($xmlPlug->getTags($xpath."/@clonable"));
		if ($clonable != 1)
			$this->redirect($controllers['controller']."/".$controllers['scontroller']."/Plugin/".$plugId.".sls");
		
		$nodeName = SLS_String::substrBeforeLastDelimiter($xpath, "[");
			
		if ($action == "del" && count($xmlPlug->getTags($nodeName)) > 1)
		{
			$xmlPlug->deleteTags($xpath, 1);
		}
		if ($action == "add")
		{
			$node = new SLS_XMLToolbox($xmlPlug->getNode($xpath));
			$parent = SLS_String::substrBeforeLastDelimiter($xpath, "/");
			$xmlPlug->appendXMLNode($xpath, $node->getXml('noHeader'), 1, "after");
			$newIndex = SLS_String::substrBeforeLastDelimiter(SLS_String::substrAfterLastDelimiter($xpath, "["), "]");
			$newIndex++;
			$newNode = SLS_String::substrBeforeLastDelimiter($xpath, "[")."[".$newIndex."]";
			if ($xmlPlug->countChilds($newNode) == 0)
				$xmlPlug->setTag($newNode, "", false);
			else 
				$xmlPlug = $this->removeRecursiveValues($newNode, $xmlPlug);
			

			
			$xmlPlug->setTagAttributes($newNode, array("alias"=>uniqid()));
			
			
		}
		$plugin->saveXML($xmlPlug);		
		$this->redirect($controllers['controller']."/".$controllers['scontroller']."/Plugin/".$plugId.".sls");
	}
	
	private function removeRecursiveValues($xpath, $xml)
	{
		$childs = $xml->getChilds($xpath);
		$arrayTagNames = array();
		foreach ($childs as $child)
		{
			$tag = array_shift(explode("[", $child));
			if (array_shift($xml->getTags($xpath."/".$child."/@clonable")) == 1)
			{
				if (key_exists($xpath."/".$tag, $arrayTagNames))
					$arrayTagNames[$xpath."/".$tag]++;
				else 
				{	
					$arrayTagNames[$xpath."/".$tag] = 1;
					$xml->setTagAttributes($xpath."/".$child, array("alias"=>uniqid()));
				}
				
			}
			if ($xml->countChilds($xpath."/".$child) == 0)
				$xml->setTag($xpath."/".$child, "", false);
			else
			{
				$xml = $this->removeRecursiveValues($xpath."/".$child, $xml);
			}
			
		}
		foreach ($arrayTagNames as $path=>$nb)
		{
			for ($i=2;$i<=$nb;$i++)
				$xml->deleteTags($path."[2]", 1);
		}

		return $xml;
	}
	
}
?>