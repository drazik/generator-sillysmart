<?php
/**
* Class Modify{{DB}}_{{TABLE}} into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}Modify{{DB}}_{{TABLE}} extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		$xml = $this->getXML();
				
		# Bo Settings
		$db_alias = strtolower("{{DB}}");
		$table = strtolower("{{TABLE}}");
		# /Bo Settings
		
		$bo = new SLS_BoEdit($xml,$db_alias,$table);
		$xml = $bo->getXML();
		
		$xml = $this->_bo->formatNotif($xml);
		$this->saveXML($xml);
	}
}
?>