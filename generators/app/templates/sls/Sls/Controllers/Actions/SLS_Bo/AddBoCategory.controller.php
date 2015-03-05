<?php
class SLS_BoAddBoCategory extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$error = '';

		$this->_xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
		
		if ($this->_http->getParam("reload") == "true")
		{
			$category = htmlentities(str_replace("'","-",trim($this->_http->getParam("category"))), ENT_QUOTES);
			if (!empty($category))
			{
				$boExists = $this->_xmlBo->getTag("//sls_configs/entry[@type='category' and @name='".$category."']/@name");
				if (empty($boExists))
				{
					$xmlNew = '<entry type="category" name="'.$category.'"></entry>';
					$this->_xmlBo->appendXMLNode('//sls_configs', $xmlNew);
					$this->_xmlBo->saveXML($this->_generic->getPathConfig("configSls")."/bo.xml");
					$this->_xmlBo->refresh();
					$forward = $this->_http->getParam("name");
					if (!empty($forward))
						$this->_generic->forward("SLS_Bo","EditBo",array("name" => $forward));
					else
						$this->_generic->forward("SLS_Bo","BoMenu");
				}
				else
					$error = 'Bo category name already exists.';
			}
			else
				$error = 'You must fill the bo category name.';
		}

		$xml->addFullTag("error",$error,true);
		
		$this->saveXML($xml);
	}
}
?>