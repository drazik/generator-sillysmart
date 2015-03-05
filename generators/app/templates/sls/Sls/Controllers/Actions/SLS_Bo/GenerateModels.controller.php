<?php
class SLS_BoGenerateModels extends SLS_BoControllerProtected 
{	
	public function action()
	{
		set_time_limit(0);
		
		$user = $this->hasAuthorative();
		$errors = array();
		$sql = SLS_Sql::getInstance();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);

		// Get all models
		$models = array();
		$handle = opendir($this->_generic->getPathConfig("models"));
		while (false !== ($file = readdir($handle)))
		{
			if (!is_dir($this->_generic->getPathConfig("models")."/".$file) && substr($file, 0, 1) != ".") 
			{
				$modelExploded = explode(".",$file);
				array_push($models,strtolower($modelExploded[0]).".".$modelExploded[1]);
			}
		}
		
		// If reload
		if ($this->_http->getParam("reload")=="true")
		{
			// Get the tables dude wants to generate
			$tablesG = ($this->_http->getParam("tables")=="") ? array() : $this->_http->getParam("tables");
			
			// Foreach tables, generate model
			foreach($tablesG as $tableG)
			{
				$db = Sls_String::substrBeforeFirstDelimiter($tableG,".");
				$table = Sls_String::substrAfterFirstDelimiter($tableG,".");
				
				// Change db if it's required
				if ($sql->getCurrentDb() != $db)
					$sql->changeDb($db);
				
				// If table exists
				if ($sql->tableExists($table))
				{					
					$columns = $sql->showColumns($table);
					$tableName = $table;
					$currentTable = array("table"=>$db.".".$tableName,"errors"=>array());
					$fieldsOk = true;
					$className = ucfirst($db)."_".SLS_String::tableToClass($tableName);
					$fileName  = ucfirst($db).".".SLS_String::tableToClass($table).".model.php";					
															
					for($i=0 ; $i<$count=count($columns) ; $i++)
					{						
						// Check forbidden chars
						if (SLS_String::removePhpChars($columns[$i]->Field) != $columns[$i]->Field)
						{
							$error = array("column"=>$columns[$i]->Field,"column_clean"=>SLS_String::removePhpChars($columns[$i]->Field));
							array_push($currentTable["errors"],$error);
							$fieldsOk = false;
						}
					}
					
					// If all ok with special chars for the current model
					if ($fieldsOk)
					{
						// Check real fks
						$create = array_shift($sql->select("SHOW CREATE TABLE `".$table."`"));						
						$queries = array_map("trim",explode("\n",$create->{Create." ".Table}));						
						foreach($queries as $query)
						{
							if (SLS_String::startsWith($query,"CONSTRAINT"))
							{
								$tableFk = strtolower($db."_".$tableName);
								$columnFk = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($query,"FOREIGN KEY (`"),"`)");
								$tablePk = $db."_".SLS_String::tableToClass(SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($query,"REFERENCES `"),"`"));
								$onDelete = strtolower(SLS_String::stringToUrl(trim(SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($query,"ON DELETE"),"ON UPDATE")),"_"));
								$labelPk = "";
								$columns = $sql->showColumns(SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($query,"REFERENCES `"),"`"));
								for($i=0 ; $i<$count=count($columns) ; $i++)
								{
									if ($columns[$i]->Key != "PRI" && $columns[$i]->Field != "pk_lang" && SLS_String::contains($columns[$i]->Type,"char"))
									{
										$labelPk = $columns[$i]->Field;
										break;
									}
								}
								if (empty($labelPk))
									$labelPk = SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($query,"REFERENCES `".SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($query,"REFERENCES `"),"`")."` (`"),"`)");
								$xmlNode = '<entry tableFk="'.$tableFk.'" columnFk="'.$columnFk.'" multilanguage="false" ondelete="'.$onDelete.'" labelPk="'.$labelPk.'" tablePk="'.$tablePk.'" />';
								
								$xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));
								$result = $xmlFk->getTags("//sls_configs/entry[@tableFk='".$tableFk."' and @columnFk='".$columnFk."' and @tablePk='".$tablePk."']");
								if (!empty($result))
								{
									$xmlTmp = $xmlFk->deleteTags("//sls_configs/entry[@tableFk='".$tableFk."' and @columnFk='".$columnFk."' and @tablePk='".$tablePk."']");
									$xmlFk->saveXML($this->_generic->getPathConfig("configSls")."/fks.xml",$xmlTmp);
									$xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));
								}
								
								$xmlFk->appendXMLNode("//sls_configs",$xmlNode);
								$xmlFk->saveXML($this->_generic->getPathConfig("configSls")."/fks.xml",$xmlFk->getXML());
							}
						}
						
						// Generate Model						
						$contentM = $this->getModelSource($tableName,$db);						
						$status = touch($this->_generic->getPathConfig("models")."/".$fileName);
						if ($status)					
							file_put_contents($this->_generic->getPathConfig("models").$fileName,$contentM);
						
						// Create SQL
						$fileNameS = ucfirst($db).".".SLS_String::tableToClass($table).".sql.php";
						$contentS = '<?php'."\n".
								   '/**'."\n".
								   '* Object '.$className.'Sql'."\n".
								   '* @author SillySmart'."\n".
								   '* @copyright SillySmart'."\n".
								   '* @package Mvc.Models.Objects'."\n".
								   '* @see Sls.Models.Core.SLS_FrontModel'."\n".
								   '* @since 1.0'."\n".
								   '*/'."\n".
								   'class '.$className.'Sql extends SLS_FrontModelSql'."\n".
								   '{'."\n".
								   ''."\n".
								   '}'."\n".
								   '?>';
						if ($status)
							$status2 = touch($this->_generic->getPathConfig("modelsSql")."/".$fileNameS);
						if ($status2)					
							file_put_contents($this->_generic->getPathConfig("modelsSql")."/".$fileNameS,$contentS);					 
					}
					else					
						array_push($errors,$currentTable);					
				}
			}
			// If no errors
			if (empty($errors))
			{
				$controllers = $this->_generic->getTranslatedController("SLS_Bo","Models");
				$this->_generic->redirect($controllers['controller']."/".$controllers['scontroller']);
			}
			else
			{	
				// Get all models
				$models = array();
				$handle = opendir($this->_generic->getPathConfig("models"));
				while (false !== ($file = readdir($handle))) 			
					if (!is_dir($this->_generic->getPathConfig("models")."/".$file) && substr($file, 0, 1) != ".") 
					{
						$modelExploded = explode(".",$file);
						array_push($models,strtolower($modelExploded[0]).".".$modelExploded[1]);
					}
					
				// Form errors
				$xml->startTag("errors");
				for($i=0 ; $i<$count=count($errors) ; $i++)
				{
					$xml->startTag("error");
					$xml->addFullTag("table",SLS_String::substrAfterFirstDelimiter($errors[$i]["table"],"."),true);
					$xml->addFullTag("db",SLS_String::substrBeforeFirstDelimiter($errors[$i]["table"],"."),true);
					$xml->startTag("columns");
					for($j=0 ; $j<$count2=count($errors[$i]["errors"]) ; $j++)
					{
						$xml->startTag("column");
						$xml->addFullTag("old",$errors[$i]["errors"][$j]["column"],true);
						$xml->addFullTag("new",$errors[$i]["errors"][$j]["column_clean"],true);
						$xml->endTag("column");
					}
					$xml->endTag("columns");
					$xml->endTag("error");
				}
				$xml->endTag("errors");				
			}
		}
		
		// Foreach db
		$dbs = $sql->getDbs();
		$allDbs = array();	
		
		foreach($dbs as $db)
		{
			$allDbs[$db] = array();
			
			// Change db
			$sql->changeDb($db);
			
			// Get all tables
			$tables = $sql->showTables();						
			for($i=0 ; $i<$count=count($tables) ; $i++)
			{
				$allDbs[$db][$tables[$i]->Name] = array("name" 	=> $tables[$i]->Name,
														"existed" => (in_array($db.".".SLS_String::tableToClass($tables[$i]->Name),$models)) ? 'true' : 'false');				
			}
		}
		
		asort($allDbs,SORT_REGULAR);
		uksort($allDbs,array($this, 'unshiftDefaultDb'));
		
		$xml->startTag("dbs");
		foreach($allDbs as $key => $db)
		{
			asort($db,SORT_REGULAR);
			
			$xml->startTag("db");
			$xml->addFullTag("name",$key,true);
			$xml->startTag("tables");
			foreach($db as $tableCur)
			{
				if (!SLS_String::startsWith(strtolower($tableCur["name"]),"sls_graph"))
				{
					$xml->startTag("table");				
					$xml->addFullTag("name",$tableCur["name"]);
					$xml->addFullTag("existed",$tableCur["existed"]);
					$xml->endTag("table");
				}
			}
			$xml->endTag("tables");
			$xml->endTag("db");
		}
		$xml->endTag("dbs");
			
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