<?php
class SLS_BoEditPlugin extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);		
		
		$plugId = $this->_http->getParam('Plugin');
		$reload = $this->_http->getParam('reload', 'post');
		$controllers = $this->_generic->getTranslatedController('SLS_Bo', 'Plugins');
		
		
		if(SLS_PluginsManager::isExists($plugId) === false)
			$this->redirect($controllers['controller']."/".$controllers['scontroller']);
		
		$plugin = new SLS_PluginsManager($plugId);
		$xmlPlug = $plugin->getXML();
		
		
		if (!$plugin->isCustomizable())
			$this->redirect($controllers['controller']."/".$controllers['scontroller']);
		
		$xml->startTag("plugin_infos");
			$xml->addFullTag('name', $plugin->_name);
			$xml->addFullTag('code', $plugin->_code);
			$xml->addFullTag('id', $plugin->_id);
			$xml->addFullTag('version', $plugin->_version);
		$xml->endTag("plugin_infos");
		
		if ($reload == 'true')
		{
			$errors = array();
			$form_memory = array();
			$form_memory = $this->recoverFormValues("//plugin", $xmlPlug, $form_memory);
			$xml->startTag("memory");
			foreach ($form_memory as $key=>$field)
			{
				$xml->startTag("values");
				$xml->addFullTag("name", $key, true);
				$xml->addFullTag("value", $field['value'], true);
				$xml->endTag("values");
				$xpath = $field['xpath'];
				$index = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterLastDelimiter($xpath, "["), "]");
				$xpathNoIndex = SLS_String::substrBeforeLastDelimiter($xpath, "[");
				if (SLS_String::endsWith($key, "_alias"))
				{
					if (count($xmlPlug->getTags($xpathNoIndex."[position() != ".$index." and @alias='".$field['value']."']")) != 0)
						$errors[] = array_shift($xmlPlug->getTags($xpath."/@label"))." alias ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_UNIQUE'];
					else 
						$xmlPlug->setTagAttributes($xpath, array("alias"=>$field['value']));
				}
				else {
					$is_null = (array_shift($xmlPlug->getTags($xpath."/@null")) == "1") ? true : false;
					switch (array_shift($xmlPlug->getTags($xpath."/@type")))
					{
						case "string" :
							if (!SLS_String::validateString($field['value']) && !$is_null)
								$errors[] = array_shift($xmlPlug->getTags($xpath."/@label"))." ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_TYPE'];
							else 
								$xmlPlug->setTag($xpath, $field['value']);
							break;
						case "password" :
								if (isset($field['value']))
									$xmlPlug->setTag($xpath, SLS_Security::encrypt($field['value'], $this->_generic->getSiteConfig("privateKey")));
							break;
						case "int" : 
							if (!is_int($field['value']) && !$is_null)
								$errors[] = array_shift($xmlPlug->getTags($xpath."/@label"))." ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_TYPE'];
							else 
								$xmlPlug->setTag($xpath, $field['value']);
							break;
						case "float" :
							if (!is_float($field['value']) && !$is_null)
								$errors[] = array_shift($xmlPlug->getTags($xpath."/@label"))." ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_TYPE'];
							else 
								$xmlPlug->setTag($xpath, $field['value']);
							break;
						case "select" : 
							$values = explode("|||", array_shift($xmlPlug->getTags($xpath."/@values")));
							if (!in_array($field['value'], $values))
								$errors[] = array_shift($xmlPlug->getTags($xpath."/@label"))." ".$GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_E_CONTENT'];
							else 
								$xmlPlug->setTag($xpath, $field['value']);
							break;
						default:
							break;
					}
				}
				
			}
			$xml->endTag("memory");
			if (!empty($errors))
			{
				$xml->startTag("errors");
					foreach ($errors as $error)
						$xml->addFullTag("error", $error, true);
				$xml->endTag("errors");
			}
			else 
			{
				$xml->addFullTag("success", "ok", true);
				$plugin->saveXML($xmlPlug);
			}
		}
		
		$xml->addFullTag("fields", $plugin->getFields()->getXML('noHeader'), false);	
				
		$this->saveXML($xml);		
	}
	
	/**
	 * Recursive function to recover Post Plugin parameters
	 *
	 * @param string $xpath
	 * @param SLS_XmlToolbox $xmlToolBox
	 * @param array $form_memory
	 * @return array
	 */
	private function recoverFormValues($xpath, $xmlToolBox, $form_memory)
	{
		$childs = $xmlToolBox->getChilds($xpath);
		$fields = array();		
		foreach ($childs as $child)
		{
			if (array_shift($xmlToolBox->getTags($xpath."/".$child."/@writable")) == 1)
			{
				$type = (array_shift($xmlToolBox->getTags($xpath."/".$child."/@type")) == "") ? "part" : array_shift($xmlToolBox->getTags($xpath."/".$child."/@type"));
				$param = str_replace("/", "|||", str_replace("]", "|#|", str_replace("[", "#|#", str_replace("//", "", $xpath))))."|||".str_replace("]", "|#|", str_replace("[", "#|#", $child));
				if ($type != 'part' && $type != 'password')
				{
					$form_memory[$param] = array();
					$form_memory[$param]['xpath'] = $xpath."/".$child;
					$form_memory[$param]['value'] = $this->_http->getParam($param, 'post');
				}
				else if ($type != 'part' && $type == 'password')
				{
					$form_memory[$param] = array();
					$form_memory[$param]['xpath'] = $xpath."/".$child;
					if (($this->_http->getParam($param, 'post') != "****" && $this->_http->getParam($param."_encrypted", 'post') != '') || $this->_http->getParam($param."_proceed", 'post') == 'proceed')
						$form_memory[$param]['value'] = $this->_http->getParam($param, 'post');
					
				}
				if (array_shift($xmlToolBox->getTags($xpath."/".$child."/@clonable")) == 1)
				{
					$form_memory[$param."_alias"] = array();
					$form_memory[$param."_alias"]['xpath'] = $xpath."/".$child;
					$form_memory[$param."_alias"]['value'] = $this->_http->getParam($param."_alias", 'post');
				}
				
				
				if ($xmlToolBox->countChilds($xpath."/".$child) > 0)
					$form_memory = $this->recoverFormValues($xpath."/".$child, $xmlToolBox, $form_memory);
				
			}
		}
		return $form_memory;
	}
	
}
?>