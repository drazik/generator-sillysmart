<?php
class SLS_BoBoMenu extends SLS_BoControllerProtected 
{
	
	public function action() 
	{		
		// Objects
		$xml = $this->getXML();
		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		
		$this->_xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
		$menuCategories = $this->_xmlBo->getTags("//sls_configs/entry[@type='category']/@name");
		$xml->startTag("categories");
		for($i=0 ; $i<$count=count($menuCategories) ; $i++)
			$xml->addFullTag("category",$menuCategories[$i],true);
		$xml->endTag("categories");
		
		$xml->addFullTag("url_add_category",$this->_generic->getFullPath("SLS_Bo","AddBoCategory"),true);
		$xml->addFullTag("url_delete_category",$this->_generic->getFullPath("SLS_Bo","DeleteBoCategory",array(),false),true);
		
		$this->saveXML($xml);
	}
	
}
?>