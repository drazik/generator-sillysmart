<?php
class SLS_BoEditForeignKey extends SLS_BoControllerProtected 
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
		$column= $this->_http->getParam("column");		
		$class = ucfirst($db)."_".SLS_String::tableToClass($table);
		$file  = ucfirst($db).".".SLS_String::tableToClass($table);
		
		// If current db is not this one
		if ($sql->getCurrentDb() != $db)
			$sql->changeDb($db);
		
		if ($sql->tableExists($table))
		{
			if ($this->_http->getParam("reload") == "true")
			{
				$replacements = array('&amp;','&gt;','&lt;','&#61;','"',"'");
				$masks = array('&','>','<','=','','','');
				
				$columnWanted 	= $this->_http->getParam("column");
				$tableWanted 	= $this->_http->getParam("table");
				$labelWanted 	= $this->_http->getParam($tableWanted.'_fkLabel');
				$labelSpecified = SLS_String::trimSlashesFromString($this->_http->getParam("fkLabel_specified"));				
				$multilang 		= $this->_http->getParam("multilanguage");
				$onDelete 		= $this->_http->getParam("ondelete");
								
				$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
				$xmlFk = new SLS_XMLToolbox($pathsHandle);
				$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
				$xmlType = new SLS_XMLToolbox($pathsHandle);
				$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
				$xmlFilter = new SLS_XMLToolbox($pathsHandle);
				if (!empty($labelSpecified))
					$labelSpecified = str_replace(array('='),array('&#61;'),htmlentities(strtolower($labelSpecified),ENT_QUOTES,"UTF-8"));				
				$result = $xmlFk->getTags("//sls_configs/entry[@tableFk='".$db."_".$table."' and @columnFk='".$columnWanted."' and @tablePk='".$tableWanted."']");
				
				// If an entry already exists in the XML, delete this record
				if (!empty($result))
				{
					$xmlTmp = $xmlFk->deleteTags("//sls_configs/entry[@tableFk='".$db."_".$table."' and @columnFk='".$columnWanted."' and @tablePk='".$tableWanted."']");					
					$xmlFk->saveXML($this->_generic->getPathConfig("configSls")."/fks.xml",$xmlTmp);
					$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
					$xmlFk = new SLS_XMLToolbox($pathsHandle);
				}
				
				// Save it into the XML
				$xmlNode = '<entry tableFk="'.$db."_".$table.'" columnFk="'.$columnWanted.'" multilanguage="'.$multilang.'" ondelete="'.$onDelete.'" labelPk="'.(empty($labelSpecified) ? $labelWanted : $labelSpecified).'" tablePk="'.$tableWanted.'" />';					
				$xmlFk->appendXMLNode("//sls_configs",$xmlNode);
				$xmlFk->saveXML($this->_generic->getPathConfig("configSls")."/fks.xml",$xmlFk->getXML());
				
				// Update model
				$this->_generic->goDirectTo("SLS_Bo","UpdateModel",array(array("key"=>"name","value"=>$this->_http->getParam("name"))));
			}
			
			// Get generic object
			$this->_generic->useModel(SLS_String::tableToClass($table),$db,"user");
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
			foreach($columnsP as $key => $value)
			{							
				if ($object->getPrimaryKey() != $key && $key != "pk_lang")			
					$xml->addFullTag("column",$key,true);
			}
			$xml->endTag("columns");
						
			$attributes = array_shift($xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".strtolower($db."_".$table)."' and @ columnFk='".$column."']",array("multilanguage","labelPk","tablePk","ondelete")));
			$this->_generic->useModel(SLS_String::substrAfterFirstDelimiter($attributes["attributes"][2]["value"],"_"),ucfirst(SLS_String::substrBeforeFirstDelimiter($attributes["attributes"][2]["value"],"_")),"user");
			$className = ucfirst($attributes["attributes"][2]["value"]);
			$objectN = new $className();
			$columns = $objectN->getColumns();
			$specificPattern = true;
			foreach($columns as $key)
				if ($key == $attributes["attributes"][1]["value"])
					$specificPattern = false;
			
			$xml->startTag("current_values");
				$xml->addFullTag("tableFk",$db."_".SLS_String::tableToClass($table),true);
				$xml->addFullTag("columnFk",$column,true);
				$xml->addFullTag("multilanguage",$attributes["attributes"][0]["value"],true);
				$xml->addFullTag("labelPk",$attributes["attributes"][1]["value"],true);
				$xml->addFullTag("tablePk",$attributes["attributes"][2]["value"],true);
				$xml->addFullTag("ondelete",$attributes["attributes"][3]["value"],true);
				$xml->addFullTag("specific_pattern",($specificPattern) ? "true" : "false",true);
			$xml->endTag("current_values");
			
			$tables = $this->getAllModels();
			
			sort($tables,SORT_REGULAR);			
				
			$xml->startTag("tables");			
			for($i=0 ; $i<$count=count($tables) ; $i++)
			{
				if (SLS_String::startsWith($tables[$i],$db))
				{
					$xml->startTag("table");
					$xml->addFullTag("name",SLS_String::substrAfterFirstDelimiter($tables[$i],"."));
					$xml->addFullTag("db",SLS_String::substrBeforeFirstDelimiter($tables[$i],"."));
					$tableN = SLS_String::substrAfterFirstDelimiter($tables[$i],".");
					$dbN = SLS_String::substrBeforeFirstDelimiter($tables[$i],".");
					$classN = ucfirst($dbN)."_".SLS_String::tableToClass($tableN);								
					$this->_generic->useModel($tableN,$dbN,"user");				
					$obj = new $classN();
					$properties = $obj->getParams();
					$xml->startTag("columns");
					foreach($properties as $key => $value)
						if ($key != "pk_lang")
							$xml->addFullTag("column",$key,true);
					$xml->endTag("columns");
					$xml->endTag("table");
				}
			}
				
			$xml->endTag("tables");	
			$xml->endTag("model");
		}
		else
		{
			$xml->addFullTag("error","Sorry this table doesn't exist anymore",true);
		}
		
		$this->saveXML($xml);
	}
}