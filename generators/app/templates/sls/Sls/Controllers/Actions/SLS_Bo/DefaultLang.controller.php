<?php
class SLS_BoDefaultLang extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		
		// SLS_XmlToolbox objects
		$siteXML = $this->_generic->getSiteXML();
				
		// Get the lang
		$lang = $this->_http->getParam("name");
		$langs = $this->_generic->getObjectLang()->getSiteLangs();
		
		if (in_array($lang,$langs))
		{
			$siteXML->setTag("//configs/defaultLang",$lang);
			$siteXML->saveXML($this->_generic->getPathConfig("configSecure")."site.xml");
		}
		
		$controllers = $this->_generic->getTranslatedController("SLS_Bo","Langs");
		$this->_generic->redirect($controllers['controller']."/".$controllers['scontroller'].".sls");
	}
	
}
?>