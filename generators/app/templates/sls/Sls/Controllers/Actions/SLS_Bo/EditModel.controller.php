<?php
class SLS_BoEditModel extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		
		// Objects
		$sql = SLS_Sql::getInstance();		
		$xml = $this->getXML();		
		$xml = $this->makeMenu($xml);
		
		// Actions
		$xml->addFullTag("delete",$this->_generic->getFullPath("SLS_Bo","DeleteModel",array(),false));
		$xml->addFullTag("delete_bearer",$this->_generic->getFullPath("SLS_Bo","DeleteBearerTable",array(),false));
		$xml->addFullTag("delete_type",$this->_generic->getFullPath("SLS_Bo","DeleteType",array(),false));
		$xml->addFullTag("edit_type",$this->_generic->getFullPath("SLS_Bo","EditType",array(),false));
		$xml->addFullTag("delete_fk",$this->_generic->getFullPath("SLS_Bo","DeleteForeignKey",array(),false));
		$xml->addFullTag("edit_fk",$this->_generic->getFullPath("SLS_Bo","EditForeignKey",array(),false));
		$xml->addFullTag("update",$this->_generic->getFullPath("SLS_Bo","UpdateModel",array(),false));		
		$xml->addFullTag("descriptions",$this->_generic->getFullPath("SLS_Bo","UpdateDescription",array(),false));
		
		// Get the table & class name
		$table = SLS_String::substrAfterFirstDelimiter($this->_http->getParam("name"),"_");
		$db	   = SLS_String::substrBeforeFirstDelimiter($this->_http->getParam("name"),"_");
		$class = ucfirst($db)."_".SLS_String::tableToClass($table);
		$file  = ucfirst($db).".".SLS_String::tableToClass($table);
		
		// If current db is not this one
		if ($sql->getCurrentDb() != $db)
			$sql->changeDb($db);
		
		// Get generic object
		$this->_generic->useModel(SLS_String::tableToClass($table),$db,"user");
		$object = new $class();
		
		// Get description's table
		$description = $object->getTableComment($table);	
		$columnsInfos = $sql->showColumns($table);
		
		// Get object's infos
		$columns = array();
		$columnsP = $object->getParams();
		$pk = $object->getPrimaryKey();
		$multilanguage = $object->isMultilanguage();
		$xml->startTag("model");
		$xml->addFullTag("table",$table,true);
		$xml->addFullTag("description",(SLS_String::contains($description,"InnoDB free")) ? SLS_String::substrBeforeFirstDelimiter($description,"; InnoDB free") : $description,true);
		$xml->addFullTag("db",$db,true);
		$xml->addFullTag("class",$class,true);
		$xml->addFullTag("pk",$pk,true);
		$xml->addFullTag("multilanguage",($multilanguage) ? "true" : "false",true);
		$xml->startTag("columns");
		$cursor = 0;
		foreach($columnsP as $column => $value)
		{
			$xml->startTag("column");
			$xml->addFullTag("name",$column,true);
			array_push($columns,$column);
			$fk = "";
			$sType = "";
			$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
			$xmlFk = new SLS_XMLToolbox($pathsHandle);			
			$res = $xmlFk->getTagsByAttributes("//sls_configs/entry",array("tableFk","columnFk"),array($db."_".$table,$column));
			if (!empty($res))
			{
				$tableTmp = substr($res,(strpos($res,'tablePk="')+9),(strpos($res,'"/>')-(strpos($res,'tablePk="')+9)));
				$fk = SLS_String::substrAfterFirstDelimiter($tableTmp,"_");				
			}
			$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
			$xmlType = new SLS_XMLToolbox($pathsHandle);						
			$res = $xmlType->getTagsByAttributes("//sls_configs/entry",array("table","column"),array($db."_".$table,$column));
			if (!empty($res))
			{
				$sType = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($res,'type="'),'"/>');
				// If specific type numeric and native type too
				if (SLS_String::startsWith($sType,"num_") && $this->containsRecursive($columnsInfos[$cursor]->Type,array("int","float","double","decimal","real")))
					$xml->addFullTag("allow_to_delete_type","false",true);
			}
			
			$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
			$xmlFilter = new SLS_XMLToolbox($pathsHandle);			
			$results = $xmlFilter->getTagsAttributes("//sls_configs/entry[@table='".$db."_".$table."' and @column='".$column."']",array("filter","hash"));			
			$xml->startTag("filters");
			for($i=0 ; $i<$count=count($results) ; $i++)
			{
				$filter = $results[$i]["attributes"][0]["value"];
				$result = $results[$i]["attributes"][1]["value"];
				
				$xml->startTag("filter");
					$xml->addFullTag("name",$filter.((!empty($result)) ? ' ['.$result.']' : ''),true);
					$xml->addFullTag("url_delete",$this->_generic->getFullPath("SLS_Bo","DeleteFilter",array(array("key"=>"table","value"=>$this->_http->getParam("name")),array("key"=>"column","value"=>$column),array("key"=>"filter","value"=>$filter))),true);
				$xml->endTag("filter");
			}
			$xml->endTag("filters");
			$xml->addFullTag("fk",$fk,true);
			$xml->addFullTag("type",ucfirst($sType),true);
			$xml->addFullTag("comment",$object->getColumnComment($column),true);
			$xml->endTag("column");
			
			$cursor++;
		}
		$xml->endTag("columns");
		$xml->addFullTag("url_add_type",$this->_generic->getFullPath("SLS_Bo","AddType",array(0=>array("key"=>"name","value"=>$db."_".$table))),true);
		$xml->addFullTag("url_add_filter",$this->_generic->getFullPath("SLS_Bo","AddFilter",array(0=>array("key"=>"name","value"=>$db."_".$table))),true);
		$xml->addFullTag("url_add_fk",$this->_generic->getFullPath("SLS_Bo","AddForeignKey",array(0=>array("key"=>"name","value"=>$db."_".$table))),true);
		
		// Get the source of the current model
		$xml->addFullTag("current_source",str_replace("\t","    ",file_get_contents($this->_generic->getPathConfig("models").$file.".model.php")),true);
		
		// Get the source of the current table
		if (!$sql->tableExists($table))
			$xml->addFullTag("current_table",-1,true);
		else
		{
			$columns = $sql->showColumns($table);					
			$tableName = $table;
			$currentTable = array("table"=>$db.".".$tableName,"errors"=>array());			
			$className = ucfirst($db)."_".SLS_String::tableToClass($tableName);
			$fileName = ucfirst($db).".".SLS_String::tableToClass($table).".model.php";
			$primaryKey = "";
			$multiLanguage = 'false';
			
			$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml");
			$xmlType = new SLS_XMLToolbox($pathsHandle);
			$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml");
			$xmlFk = new SLS_XMLToolbox($pathsHandle);
			$pathsHandle = file_get_contents($this->_generic->getPathConfig("configSls")."/filters.xml");
			$xmlFilter = new SLS_XMLToolbox($pathsHandle);
			
			// Get source
			$contentM = $this->getModelSource($tableName,$db);
			
			// Is data bearer
			$xmlBearer = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bearers.xml"));
			$result = array_shift($xmlBearer->getTagsAttributes("//sls_configs/entry[@tableBearer='".$class."']",array("table1")));
			$xml->addFullTag("is_data_bearer",(!empty($result)) ? $result["attributes"][0]["value"] : "false",true);
			
			// Save the new source
			$xml->addFullTag("current_table",str_replace("\t","    ",$contentM),true);
			$xml->addFullTag("url_data_bearer",$this->_generic->getFullPath("SLS_Bo","AddBearerTable",array(array("key"=>"name","value"=>$this->_http->getParam("name")))),true);
		}		
		$xml->endTag("model");
		$this->saveXML($xml);
	}
	
}
?>