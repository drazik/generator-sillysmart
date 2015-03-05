<?php
/**
* Class BoUnique into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoUnique extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		// Params
		$model 	= $this->_http->getParam("Model");
		$db 	= $this->_http->getParam("Db");
		$column = $this->_http->getParam("Column");
		$value 	= $this->_http->getParam("Value");
		$lang 	= $this->_http->getParam("Lang");
		$id 	= $this->_http->getParam("Id");

		// Objects
		$this->_generic->useModel(SLS_String::tableToClass($model),$db,"user");
		$class = ucfirst($db)."_".SLS_String::tableToClass($model);
		$currentModel = new $class();
		$lang = (in_array($lang,$this->_bo->_langs)) ? $lang : $this->_bo->_defaultLang;
		if ($currentModel->isMultilanguage())
			$currentModel->setModelLanguage($lang);
		$excludedColumn = "";
		$excludedValue = ""; 
		
		if ($currentModel->getModel($id) === true)
		{
			$excludedColumn = $currentModel->getPrimaryKey();
			$excludedValue = $id;
		}
		
		$typePositionExists = $this->_bo->_xmlType->getTag("//sls_configs/entry[@table='".$this->_db_alias."_".$table."' and @column='".$column["name"]."' and @type='position']/@type");
		
		$isUnique = $currentModel->isUnique($column,$value,$currentModel->getTable(),$excludedColumn,$excludedValue);
		$this->_bo->_render["status"] = "OK";
		$this->_bo->_render["result"] = array("unique" => (empty($typePositionExists)) ? $isUnique : false);
		
		// Render
		echo json_encode($this->_bo->_render);
		die();
	}
}
?>