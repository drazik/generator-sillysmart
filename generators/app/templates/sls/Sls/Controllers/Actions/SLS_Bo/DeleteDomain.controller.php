<?php
class SLS_BoDeleteDomain extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();		
		$siteXML = $this->_generic->getSiteXML();
		
		$errors = array();
		
		$alias = $this->_http->getParam("alias");
		$resultAlias = $siteXML->getTag("//configs/domainName/domain[@alias='".$alias."']");
		$resultDefault = $siteXML->getTag("//configs/domainName/domain[@alias='".$alias."' and @default='1']");
				
		if (!empty($resultAlias) && empty($resultDefault))
		{
			$siteXML->deleteTags("//configs/domainName/domain[@alias='".$alias."']");						
			$siteXML->saveXML($this->_generic->getPathConfig("configSecure")."site.xml");
		}
		
		$this->_generic->forward("SLS_Bo","GlobalSettings");		
	}
	
}
?>