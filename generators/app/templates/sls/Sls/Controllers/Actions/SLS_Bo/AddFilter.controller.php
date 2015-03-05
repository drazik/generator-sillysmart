<?php
class SLS_BoAddFilter extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$sql = SLS_Sql::getInstance();		
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$errors = array();
		
		// Get the table name
		$table = SLS_String::substrAfterFirstDelimiter($this->_http->getParam("name"),"_");
		$db	   = SLS_String::substrBeforeFirstDelimiter($this->_http->getParam("name"),"_");
		$class = ucfirst($db)."_".SLS_String::tableToClass($table);
		$file  = ucfirst($db).".".SLS_String::tableToClass($table);
		
		// If current db is not this one
		if ($sql->getCurrentDb() != $db)
			$sql->changeDb($db);
		
		if ($sql->tableExists($table))
		{
			if ($this->_http->getParam("reload") == "true")
			{
				$columnWanted = $this->_http->getParam("column");
				$filterWanted = $this->_http->getParam("filter");
				
				$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
				$xmlFk = new SLS_XMLToolbox($pathsHandle);
				$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
				$xmlType = new SLS_XMLToolbox($pathsHandle);
				$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
				$xmlFilter = new SLS_XMLToolbox($pathsHandle);
				
				$result = $xmlFilter->getTags("//sls_configs/entry[@table='".$db."_".$table."' and @column='".$columnWanted."' and @filter='".$filterWanted."']");
				
				// If an entry already exists in the XML, delete this record
				if (!empty($result))
				{
					$xmlTmp = $xmlFilter->deleteTags("//sls_configs/entry[@table='".$db."_".$table."' and @column='".$columnWanted."' and @filter='".$filterWanted."']");					
					$xmlFilter->saveXML($this->_generic->getPathConfig("configSls")."/filters.xml",$xmlTmp);
					$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
					$xmlFilter = new SLS_XMLToolbox($pathsHandle);
				}
				
				if ($filterWanted == "hash")
				{
					$passwordHash = $this->_http->getParam("hash");
					
					// Save it into the XML
					$xmlNode = '<entry table="'.$db.'_'.$table.'" column="'.$columnWanted.'" hash="'.$passwordHash.'" filter="'.$filterWanted.'" />';				
					$xmlFilter->appendXMLNode("//sls_configs",$xmlNode);
					$xmlFilter->saveXML($this->_generic->getPathConfig("configSls")."/filters.xml",$xmlFilter->getXML());
				}				
				else
				{
					// Save it into the XML
					$xmlNode = '<entry table="'.$db.'_'.$table.'" column="'.$columnWanted.'" filter="'.$filterWanted.'" />';				
					$xmlFilter->appendXMLNode("//sls_configs",$xmlNode); 
					$xmlFilter->saveXML($this->_generic->getPathConfig("configSls")."/filters.xml",$xmlFilter->getXML());
				}
				
				// Update model
				$this->_generic->goDirectTo("SLS_Bo","UpdateModel",array(array("key"=>"name","value"=>$this->_http->getParam("name"))));		
			}
			
			// Get generic object
			$this->_generic->useModel($table,$db,"user");
			$object = new $class();
			
			// Get object's infos
			$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
			$xmlFk = new SLS_XMLToolbox($pathsHandle);
			$columnsP = $object->getParams();
			$pk = $object->getPrimaryKey();
			$multilanguage = $object->isMultilanguage();		
			$xml->startTag("model");
			$xml->addFullTag("table",$table,true);
			$xml->addFullTag("db",$db,true);
			$xml->addFullTag("class",$class,true);
			$xml->addFullTag("pk",$pk,true);
			$xml->addFullTag("multilanguage",($multilanguage) ? "true" : "false",true);
			$xml->startTag("columns");
			foreach($columnsP as $column => $value)
			{
				$res = $xmlFk->getTags("//sls_configs/entry[@tableFk='".$db."_".$table."' and @columnFk='".$column."']/@tablePk");				
				if ($object->getPrimaryKey() != $column && $column != "pk_lang" && empty($res))			
					$xml->addFullTag("column",$column,true);
			}
			$xml->endTag("columns");
			$xml->endTag("model");
		}
		else
		{
			$xml->addFullTag("error","Sorry this table doesn't exist anymore",true);
		}
		
		$this->saveXML($xml);
	}
}