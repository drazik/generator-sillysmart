<?php
/**
* Class BoSwitchLang into {{USER_BO}} Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.{{USER_BO}}
* @see Mvc.Controllers.{{USER_BO}}.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class {{USER_BO}}BoSwitchLang extends {{USER_BO}}ControllerProtected
{
	public function init()
	{
		parent::init();
	}

	public function action()
	{
		$xml = $this->getXML();
		
		if($this->_http->getParam("Lang") != $this->_lang->getLang())
            $this->_generic->switchLang($this->_http->getParam("Lang"));
        else
            $this->_generic->redirectOnPreviousPage();
		
		$this->saveXML($xml);
	}
}
?>