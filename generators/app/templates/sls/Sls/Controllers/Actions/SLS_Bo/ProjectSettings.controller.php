<?php
class SLS_BoProjectSettings extends SLS_BoControllerProtected 
{
	public function action()
	{
		
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		$this->_generic->loadProjectSettings();
		
		$env = $this->_http->getParam("Env");
		if (empty($env))
			$env = "prod";
		
		if ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/project_".$env.".xml"))					
			$projectXML = new SLS_XMLToolbox(file_get_contents($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/project_".$env.".xml"));
		else
			$projectXML = $this->_generic->getProjectXML();
		
		$errors = array();
		
		// Prod Deployment		
		$finalFile = ($this->_http->getParam("ProdDeployment") == "true") ? "project_".$env.".xml" : "project.xml";
		$isInBatch = ($this->_http->getParam("CompleteBatch") == "true") ? true : false;
		$xml->addFullTag("is_batch",($isInBatch) ? "true" : "false",true);
		$xml->addFullTag("is_prod",($this->_http->getParam("ProdDeployment") == "true") ? "true" : "false",true);		
		$reload = $this->_http->getParam("reload");
		
		if ($reload == "true")
		{
			
			$postProject = SLS_String::trimSlashesFromString($this->_http->getParam("project", "post"));
			$newXML = (SLS_String::startsWith(trim($postProject),"<?xml")) ? new SLS_XMLToolbox($postProject) : new SLS_XMLToolbox("<?xml version=\"1.0\" encoding=\"utf-8\"?>".$postProject);
			
			if (!$this->_generic->isValidXML($newXML->getXML()))
				array_push($errors, "The XML is incorrect");
					
			if (empty($errors))
			{
				if (count($newXML->getTags("//project")) != 1)
					array_push($errors, "The Root node should be called 'project'");
				
				$newXML = $this->checkNode("//project", $newXML);
				
				if (empty($errors))
				{
					$xmlStr = trim($newXML->getXML());
					if (substr($xmlStr, 0, 2)!= "<?")
						$xmlStr = "<?xml version=\"1.0\" encoding=\"utf-8\"?>".$xmlStr;
					$projectXML->refresh();
					@file_put_contents($this->_generic->getPathConfig("configSecure").$finalFile, $xmlStr);
					if ($isInBatch)
						$this->_generic->forward("SLS_Bo","ProductionDeployment");
					else if ($this->_http->getParam("ProdDeployment") == "true")
						$this->_generic->forward("SLS_Bo","ProductionDeployment");
					$projectXML = new SLS_XMLToolbox($xmlStr);
				}
				
			}
			
			if (!empty($errors))
			{ 
				$xml->startTag('errors');
				foreach ($errors as $error)
				{
					$xml->addFullTag('error', $error, true);
				}
				$xml->endTag('errors');
			}
	
		}
		$this->_generic->eraseCache('Project');
		
		$value = (count($projectXML->getTags('//project/*')) == 0) ? "<project>\n</project>" : $projectXML->getXML('noHeader');
		
		$xml->startTag("current_values");
			$xml->addFullTag("project", str_replace(array('<![CDATA[', ']]>'), array('&#139;![CDATA[',']]&#155;'), $value), true);
		$xml->endTag("current_values");
		
		$this->saveXML($xml);		
	}
	
	/**
	 * Recursive XML Nodes Checking
	 *
	 * @param string $path xpath
	 * @param SLS_XMLToolBox $xml
	 */
	private function checkNode($path, $xml)
	{
		if (substr($path, strlen($path)-1, 1) != "/")
			$path .= "/";
		$childs = $xml->returnXpathQuery($path."*");
		//var_dump($childs->item(0)->getAttribute("isSecure")); exit;
		for ($i=0 ; $i<$childs->length ; $i++)		
		{
			$setAtt = array();
			$isSecure = (string)$childs->item($i)->getAttribute('isSecure');
			if ($isSecure != 'true' && $isSecure != 'false')
				$setAtt['isSecure'] = 'true';
				//array_push($arrayErrors, "The node ".$path.(string)$childs->item($i)->nodeName." should have the attribute 'isSecure' set to 'true' or 'false'");
			$js = (string)$childs->item($i)->getAttribute('js');
			if ($js != 'true' && $js != 'false')
				$setAtt['js'] = 'false';
				//array_push($arrayErrors, "The node ".$path.(string)$childs->item($i)->nodeName." should have the attribute 'js' set to 'true' or 'false'");
			
			if (!empty($setAtt))
				$xml->setTagAttributes($path.(string)$childs->item($i)->nodeName, $setAtt);
			
			if ($childs->item($i)->hasChildNodes()) 
			{
				if (is_object($childs->item($i)->childNodes->item(0)))
				{
					//var_dump(get_class($childs->item($i)->childNodes->item(0))); exit;
					if (get_class($childs->item($i)->childNodes->item(0)) !== "DOMText" && get_class($childs->item($i)->childNodes->item(0)) !== "DOMCdataSection")
						$xml = $this->checkNode($path.(string)$childs->item($i)->name, $xml);
				}
			}
		}
		return $xml;
		
	}
}
?>