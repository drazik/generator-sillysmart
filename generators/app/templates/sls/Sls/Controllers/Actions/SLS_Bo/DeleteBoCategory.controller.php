<?php
class SLS_BoDeleteBoCategory extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);

		$this->_xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
		$category = htmlentities(str_replace("'","-",trim($this->_http->getParam("category"))), ENT_QUOTES);
		if (!empty($category))
		{
    		header('Content-type: text/html; charset=utf-8');
			$boExists = $this->_xmlBo->getTag("//sls_configs/entry[@type='category' and @name='".$category."']/@name");
			if (!empty($boExists))
			{
				// Has children ?
				$xmlSlice = '';
				$childrenExists = $this->_xmlBo->getTag("//sls_configs/entry[@type='category' and @name='".$category."']/entry[@type='table']");
				if (!empty($childrenExists))
				{	
					$xmlCopy = simplexml_load_string($this->_xmlBo->getXML());
					$children = $xmlCopy->xpath("//sls_configs/entry[@type='category' and @name='".$category."']/*");
					foreach($children as $child)
						$xmlSlice .= '  '.$child->asXML()."\n";
				}
				
				if (!empty($xmlSlice))
					$this->_xmlBo->appendXMLNode("//sls_configs",$xmlSlice);
				
				$this->_xmlBo->deleteTags("//sls_configs/entry[@type='category' and @name='".$category."']");
				$this->_xmlBo->saveXML($this->_generic->getPathConfig("configSls")."/bo.xml");
				$this->_xmlBo->refresh();
			}
		}
		
		$this->_generic->forward("SLS_Bo","BoMenu");
		
		$this->saveXML($xml);
	}
}
?>