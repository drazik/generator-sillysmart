<?php
/**
* Class BoLike into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoLike extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		# Params
		$dbAlias = $this->_http->getParam("Db");
		$model = $this->_http->getParam("Model");
		$like = strtolower($this->_http->getParam("Like"));		
		# /Params
				
		// Objects
		$likes = array("true","false");
		if (!in_array($like,$likes))
			$like = array_shift($likes);
		
		// Set like/unlike
		$nodeExists = $this->_bo->_xmlRight->getTag("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/action[@role='read' and @entity='".$dbAlias."_".$model."']/@id");
		if (!empty($nodeExists))
		{
			$this->_bo->_xmlRight->setTagAttributes("//sls_configs/entry[@login='".$this->_session->getParam("SLS_BO_USER")."']/action[@role='read' and @entity='".$dbAlias."_".$model."']", array("like" => $like));
			$this->_bo->_xmlRight->saveXML($this->_generic->getPathConfig("configSls")."/rights.xml");
			$this->_bo->_xmlRight->refresh();
			$this->_bo->_render["status"] = "OK";
			$this->_bo->_render["result"] = $like;
		}
		
		// Render
		echo json_encode($this->_bo->_render);
		die();
	}
}
?>