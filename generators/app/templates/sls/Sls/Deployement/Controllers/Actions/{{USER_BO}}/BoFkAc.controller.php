<?php
/**
* Class BoFkAc into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoFkAc extends {{USER_BO}}ControllerProtected
{
	public function before()
	{
		parent::before();
	}

	public function action()
	{
		// Params
		$model 	= $this->_http->getParam("Model");
		$db 	= $this->_http->getParam("Db");
		$column = $this->_http->getParam("Column");
		$keyword= $this->_http->getParam("Keyword");
		$lang 	= $this->_http->getParam("Lang");
		$id 	= $this->_http->getParam("Id");

		// Objects
		$this->_generic->useModel(SLS_String::tableToClass($model),$db,"user");
		$class = ucfirst($db)."_".SLS_String::tableToClass($model);
		$currentModel = new $class();

		// Check really fk
		$res = $this->_bo->_xmlFk->getTagsByAttributes("//sls_configs/entry",array("tableFk","columnFk"),array($db."_".$currentModel->getTable(),$column));
		
		// Objects
		$resultFk = array_shift($this->_bo->_xmlFk->getTagsAttribute("//sls_configs/entry[@tableFk='".$db."_".$currentModel->getTable()."' and @columnFk='".$column."' and @multilanguage = 'false']","multilanguage"));
		$labelPk = substr($res,(strpos($res,'labelPk="')+9),(strpos($res,'" tablePk="')-(strpos($res,'labelPk="')+9)));
		$tableTm = substr($res,(strpos($res,'tablePk="')+9),(strpos($res,'"/>')-(strpos($res,'tablePk="')+9)));
		$tablePk = SLS_String::substrAfterFirstDelimiter($tableTm,"_");
		$dbPk 	 = SLS_String::substrBeforeFirstDelimiter($tableTm,"_");
		$this->_generic->useModel($tablePk,$dbPk,"user");
		$classFk = ucfirst($dbPk)."_".SLS_String::tableToClass($tablePk);
		$objectFk = new $classFk();
		$columns = array();
		$columnsLabel = array();
		$clause = array();
		$params = $objectFk->getParams();
		foreach($params as $key => $value)
		{
			unset($params[$key]);
			$params["`".$objectFk->getTable()."`.`".$key."`"] = $value;
		}
		$fks = $objectFk->getFks();
		foreach($fks as $fk)
		{
			$paramsFk = $objectFk->$fk()->getParams();
			foreach($paramsFk as $key => $value)
			{
				unset($paramsFk[$key]);
				$paramsFk["`".$objectFk->$fk()->getTable()."`.`".$key."`"] = $value;
			}
			$params = array_merge($params,$paramsFk);
		}
		uksort($params, create_function('$a,$b', 'return strlen($a) < strlen($b);'));
		foreach($params as $key => $value)
		{
			array_push($columns,$key);
			if (SLS_String::contains($key,"`.`"))
				$key = SLS_String::substrAfterLastDelimiter($key,"`.`");
			$key = str_replace("`","",$key);
			if (SLS_String::contains($labelPk,$key))
			{
				$keys = array_keys($columnsLabel);
				$keyAlreadySet = false;
				foreach($keys as $currentKey)
					if (SLS_String::contains($currentKey,$key))
						$keyAlreadySet = true;
				if (!$keyAlreadySet)
					$columnsLabel[$key] = strpos($labelPk,$key);
			}
		}
		array_multisort($columnsLabel);
		foreach($columnsLabel as $columnLabel => $offset)
			array_push($clause,$columnLabel);
		
		$pattern = str_replace("'","''",$labelPk);
		foreach($clause as $columnC)
			$pattern = str_replace($columnC,"',LOWER(".$columnC."),'",$pattern);
		
		$tables = array_values(array_unique(array_merge(array($objectFk->getTable()),$objectFk->getFks())));
		$results = $currentModel->fkAC($currentModel->getTable(),$tables,$objectFk->getPrimaryKey(),$columns,"CONCAT('".$pattern."')",($objectFk->isMultilanguage()) ? $lang : "",implode('%',explode(' ',trim($keyword))),10,$id);		
		for($i=0 ; $i<$count=count($results) ; $i++)
		{
			$mask = $labelPk;
			$id = 0;
			$label = "";
			foreach($results[$i] as $key => $value)
			{
				if ($key == $objectFk->getPrimaryKey())
					$id = $value;
				if (SLS_String::contains($mask,$key))
					$mask = str_replace($key,$value,$mask);
			}
			$this->_bo->_render["result"][] = array("id"=>$id,"label"=>$mask);
		}

		$this->_bo->_render["status"] = "OK";
		
		// Render
		echo json_encode($this->_bo->_render);
		die();
	}
	
	public function after()
	{
		parent::after();
	}
}
?>