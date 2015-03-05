<?php
class SLS_BoUpdateModel extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$user = $this->hasAuthorative();
				
		// Objects
		$sql = SLS_Sql::getInstance();
		$xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
		$xmlFk = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/fks.xml"));
		$xmlType = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/types.xml"));
		
		// Get the table & class name
		$tableName 	= SLS_String::substrAfterFirstDelimiter($this->_http->getParam("name"),"_");
		$db	   		= SLS_String::substrBeforeFirstDelimiter($this->_http->getParam("name"),"_");
		$className 	= ucfirst($db)."_".SLS_String::tableToClass($tableName);
		$file 		= ucfirst($db).".".SLS_String::tableToClass($tableName);
		$fileName 	= ucfirst($db).".".SLS_String::tableToClass($tableName).".model.php";
		
		// If current db is not this one
		if ($sql->getCurrentDb() != $db)
			$sql->changeDb($db);
		
		// Remind old properties
		$this->_generic->useModel(SLS_String::tableToClass($tableName),ucfirst(strtolower($db)), "user");
		$object = new $className();
		$oldColumns = $object->getColumns();
		
		// Update Model
		$contentM = $this->getModelSource($tableName,$db);
		file_put_contents($this->_generic->getPathConfig("models").$fileName,$contentM);
		
		// Check Bo
		$controllerBo = $this->_generic->getBo();
		if (!empty($controllerBo))
		{
			$boPath = "//sls_configs/entry[@type='table' and @name='".strtolower($className)."']";
			$boExists = $xmlBo->getTag($boPath."/@type");
			if (empty($boExists))
			{
				$boPath = "//sls_configs/entry/entry[@type='table' and @name='".strtolower($className)."']";
				$boExists = $xmlBo->getTag($boPath);
			}
			if (!empty($boExists))
			{
				$columns = $sql->showColumns($tableName);
				$newColumns = array();
				for($i=0 ; $i<$count=count($columns) ; $i++)
					$newColumns[] = $columns[$i]->Field;
				
				$xmlNodes = '';
				foreach($newColumns as $column)
				{
					$columnExists = $xmlBo->getTag($boPath."/columns/column[@table='".strtolower($className)."' and @name='".$column."']/@name");
					if (empty($columnExists))
					{
						// Avoid pk
						$isPk = ($column == $object->getPrimaryKey() || $column == 'pk_lang') ? true : false;
						// Avoid fk
						$fkExist = $xmlFk->getTag("//sls_configs/entry[@tableFk='".strtolower($db."_".$tableName)."' and @columnFk='".$column."']/@tablePk");
						$isFk = (!empty($fkExist)) ? true : false;
						// Avoid quick edit on type file
						$fileExist = $xmlType->getTag("//sls_configs/entry[@table='".strtolower($db."_".$tableName)."' and @column='".$column."' and (@type='file_all' or @type='file_img')]/@column");
						$isFile = (!empty($fileExist)) ? true : false;
						
						$xmlNodes .= '            <column table="'.strtolower($db."_".$tableName).'" name="'.$column.'" multilanguage="'.(($object->isMultilanguage() && !$isPk) ? "true" : "false").'" displayFilter="true" displayList="'.(($isFk) ? "false" : "true").'" allowEdit="'.(($isPk || $isFk || $isFile) ? "false" : "true").'" allowHtml="false" />'."\n";
					}
				}
				if (!empty($xmlNodes))
				{
					$xmlBo->appendXMLNode($boPath."/columns",$xmlNodes);
				}
				$deprecatedColumns = array_diff($oldColumns,$newColumns);
				foreach($deprecatedColumns as $column)
				{
					$xmlBo->deleteTags($boPath."/columns/column[@table='".strtolower($className)."' and @name='".$column."']",1);
				}
				$xmlBo->saveXML($this->_generic->getPathConfig("configSls")."/bo.xml",$xmlBo->getXML());
				$xmlBo->refresh();
			}
		}
		
		$action_id = $this->_http->getParam("Redirect");
		if ($this->_generic->actionIdExists($action_id))
		{
			$infos = $this->_generic->translateActionId($action_id);			
			$this->_generic->redirect($infos['controller']."/".$infos['scontroller']);
		}
		else
		{
			$controllers = $this->_generic->getTranslatedController("SLS_Bo","EditModel");		
			$this->_generic->redirect($controllers['controller']."/".$controllers['scontroller']."/name/".$db."_".$tableName);
		}
	}
	
}
?>