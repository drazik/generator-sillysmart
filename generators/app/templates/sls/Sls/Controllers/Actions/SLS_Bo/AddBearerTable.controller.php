<?php
class SLS_BoAddBearerTable extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
				
		// Objects		
		$xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));
		$xmlBearer = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bearers.xml"));
		$xml = $this->getXML();
		$tables = array();
		
		$xml = $this->makeMenu($xml);
				
		// Get the table & class name
		$table = SLS_String::substrAfterFirstDelimiter($this->_http->getParam("name"),"_");
		$db	   = SLS_String::substrBeforeFirstDelimiter($this->_http->getParam("name"),"_");
		$class = ucfirst($db)."_".SLS_String::tableToClass($table);
		
		$result = array_shift($xmlBearer->getTagsAttributes("//sls_configs/entry[@tableBearer='".$class."']",array("table1")));
		$res = $xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".strtolower($class)."']",array("tablePk","columnFk"));
		if (!empty($result) || count($res) != "2")
			$this->_generic->forward("SLS_Bo","EditModel",array(array("key"=>"name","value"=>$this->_http->getParam("name"))));		
		else
		{
			$xml->startTag("fks");
			for($i=0 ; $i<$count=count($res) ; $i++)
			{
				$className = ucfirst($res[$i]["attributes"][0]["value"]);
				$this->_generic->useModel(SLS_String::substrAfterFirstDelimiter($className,"_"),SLS_String::substrBeforeFirstDelimiter($className,"_"),"user");							
				$object = new $className();
				array_push($tables,$className);
				
				$xml->startTag("fk");
					$xml->addFullTag("class",$className,"true");
					$xml->addFullTag("table",$object->getTable(),"true");
				$xml->endTag("fk");
			}
			$xml->endTag("fks");
		}
		
		$xml->startTag("model");
		$xml->addFullTag("table",$table,true);
		$xml->addFullTag("class",$class,true);
		$xml->endTag("model");
		
		if ($this->_http->getParam("reload") == "true")
		{
			$target_table = SLS_String::trimSlashesFromString($this->_http->getParam("target_table"));
			$tableBearer = $class;
			$table2 = "";
			foreach($tables as $table)			
				if ($table != $target_table)
					$table2 = $table;			
			
			$xmlNode = '<entry tableBearer="'.$tableBearer.'" table1="'.$target_table.'" table2="'.$table2.'" />';				
			$xmlBearer->appendXMLNode("//sls_configs",$xmlNode); 
			$xmlBearer->saveXML($this->_generic->getPathConfig("configSls")."/bearers.xml",$xmlBearer->getXML());
			$this->_generic->forward("SLS_Bo","EditModel",array(array("key"=>"name","value"=>$this->_http->getParam("name"))));
		}
		
		$this->saveXML($xml);
	}
	
}
?>