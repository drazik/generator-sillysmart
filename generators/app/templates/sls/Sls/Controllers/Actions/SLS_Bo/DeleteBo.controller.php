<?php
class SLS_BoDeleteBo extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$xmlBo = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."/bo.xml"));
		
		$param = $this->_http->getParam("name");
		$model = SLS_String::substrAfterFirstDelimiter($param,"_");
		$alias = SLS_String::substrBeforeFirstDelimiter($param,"_");
		
		if ($this->_http->getParam("type") == "")	
			$actionsTypes = array("list","add","modify","delete","clone");
		else
			$actionsTypes = array($this->_http->getParam("type"));
		foreach($actionsTypes as $actionType)
			$this->deleteActionBo($model,$actionType,$alias);
		
		# Node into bo.xml
		$boPath = "//sls_configs/entry[@type='table' and @name='".strtolower($alias."_".$model)."']";
		$boExists = $xmlBo->getTag($boPath."/@type");
		if (empty($boExists))		
			$boPath = "//sls_configs/entry/entry[@type='table' and @name='".strtolower($alias."_".$model)."']";
		$boExists = $xmlBo->getTag($boPath);
		if (!empty($boExists))
		{
			$xmlBo->deleteTags($boPath, count($xmlBo->getTags($boPath)));
			$xmlBo->saveXML($this->_generic->getPathConfig("configSls")."/bo.xml",$xmlBo->getXML());
			$xmlBo->refresh();	
		}
		# /Node into bo.xml
			
		$controllers = $this->_generic->getTranslatedController("SLS_Bo","Bo");
		$this->_generic->redirect($controllers["controller"]."/".$controllers["scontroller"]);
	}
	
}
?>