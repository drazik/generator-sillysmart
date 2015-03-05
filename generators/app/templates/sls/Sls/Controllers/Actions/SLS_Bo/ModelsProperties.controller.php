<?php
class SLS_BoModelsProperties extends SLS_BoControllerProtected 
{
	
	public function action() 
	{		
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$user = $this->hasAuthorative();
		
		// Objects
		$sql = SLS_Sql::getInstance();		
		$properties = array();
		$pathsHandleFk = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
		$pathsHandleType = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
		$pathsHandleFilter = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
		$xmlFk = new SLS_XMLToolbox($pathsHandleFk);
		$xmlType = new SLS_XMLToolbox($pathsHandleType);
		$xmlFilter = new SLS_XMLToolbox($pathsHandleFilter);
		
		// Foreach existing models 
		$models = scandir($this->_generic->getPathConfig("models"));
		foreach($models as $file)
		{
			if (!is_dir($this->_generic->getPathConfig("models")."/".$file) && substr($file, 0, 1) != ".") 
			{
				$fileExploded = explode(".",$file);
				if (is_array($fileExploded) && count($fileExploded) == 4)
				{ 
					$db = strtolower($fileExploded[0]);
					$class = $fileExploded[1];
					$className = $db."_".$class;
					$this->_generic->useModel($class,$db,"user");
					$object = new $className();
					$table = $object->getTable();
					
					$properties[$db][$table] = array("types" 	=> array(),
													 "filters" 	=> array(),
													 "fks" 		=> array());
					
					$types = $xmlType->getTagsAttributes("//sls_configs/entry[@table='".$db."_".$table."']",array("column","type","rules"));
					for($i=0 ; $i<$count=count($types) ; $i++)
					{
						$column = $types[$i]["attributes"][0]["value"];
						$type 	= $types[$i]["attributes"][1]["value"];
						$rules 	= $types[$i]["attributes"][2]["value"];
						
						
						if ($type == "complexity" && !empty($rules))
							$type .= " (".$rules.")";
						
						$properties[$db][$table]["types"][$column][] = $type;
					}
					
					$filters = $xmlFilter->getTagsAttributes("//sls_configs/entry[@table='".$db."_".$table."']",array("column","filter","hash"));
					for($i=0 ; $i<$count=count($filters) ; $i++)
					{
						$column = $filters[$i]["attributes"][0]["value"];
						$filter = $filters[$i]["attributes"][1]["value"];
						$hash 	= $filters[$i]["attributes"][2]["value"];
						
						if ($filter == "hash" && !empty($hash))
							$filter .= " (".$hash.")";
							
						$properties[$db][$table]["filters"][$column][] = $filter;
					}
					
					$fks = $xmlFk->getTagsAttributes("//sls_configs/entry[@tableFk='".$db."_".$table."']",array("columnFk","tablePk","labelPk"));
					for($i=0 ; $i<$count=count($fks) ; $i++)
					{
						$column = $fks[$i]["attributes"][0]["value"];
						$classPk = ucfirst($fks[$i]["attributes"][1]["value"]);
						$labelPk = $fks[$i]["attributes"][2]["value"];
						$tablePk = SLS_String::substrAfterFirstDelimiter($classPk,"_");
						
						$this->_generic->useModel($tablePk,$db,"user");
						$objectPk = new $classPk();
							
						$properties[$db][$table]["fks"][$column][] = $objectPk->getTable();
					}
				}
			}
		}

		asort($properties,SORT_REGULAR);
		uksort($properties,array($this, 'unshiftDefaultDb'));

		$xml->startTag("dbs");
		foreach($properties as $db => $tables)
		{
			$xml->startTag("db");
				$xml->addFullTag("name",$db,true);
				$xml->startTag("tables");
				foreach($tables as $table => $infos)
				{
					$columns = array();
					$xml->startTag("table");
						$xml->addFullTag("name",$table,true);
						$xml->startTag("types");
						foreach($infos["types"] as $column => $type)
						{
							$xml->addFullTag("type",implode(", ",$type),true,array("column"=>$column));
							$columns[] = $column;
						}
						$xml->endTag("types");
						$xml->startTag("filters");
						foreach($infos["filters"] as $column =>  $filter)
						{
							$xml->addFullTag("filter",implode(", ",$filter),true,array("column"=>$column));
							$columns[] = $column;
						}
						$xml->endTag("filters");
						$xml->startTag("fks");
						foreach($infos["fks"] as $column =>  $fk)
						{
							$xml->addFullTag("fk",implode(", ",$fk),true,array("column"=>$column));
							$columns[] = $column;
						}
						$xml->endTag("fks");
						$xml->startTag("columns");
						foreach($columns as $column)
							$xml->addFullTag("column",$column,true);
						$xml->endTag("columns");
					$xml->endTag("table");
				}
				$xml->endTag("tables");
			$xml->endTag("db");
		}
		$xml->endTag("dbs");
		
		$xml->addFullTag("url_type",$this->_generic->getFullPath("SLS_Bo","EditType",array(),false),true);
		$xml->addFullTag("url_model",$this->_generic->getFullPath("SLS_Bo","EditModel",array(),false),true);
		$xml->addFullTag("url_fk",$this->_generic->getFullPath("SLS_Bo","EditForeignKey",array(),false),true);
		
		$this->saveXML($xml);
	}
	
	/**
	 * Order array dbs to unshift default db as first offset
	 * 
	 * @param string $a $key db
	 * @param string $b $key db
	 * @return int -1|0|1
	 */
	public function unshiftDefaultDb($a,$b)
	{
		if ($a == $this->_generic->getDbXML()->getTag("//dbs/db[@isDefault='true']/@alias"))
			return -1;			
		if ($b == $this->_generic->getDbXML()->getTag("//dbs/db[@isDefault='true']/@alias"))
			return 1;
		
		return ($a < $b) ? -1 : 1;
	}
}
?>