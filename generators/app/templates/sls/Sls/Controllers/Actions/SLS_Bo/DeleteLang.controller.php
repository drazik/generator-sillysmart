<?php
class SLS_BoDeleteLang extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		
		// SLS_XmlToolbox objects		
		$controllerXML = $this->_generic->getControllersXML();
		$metaXML = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."metas.xml"));
		$siteXML = $this->_generic->getSiteXML();
				
		// Get the lang
		$lang = $this->_http->getParam("name");
		$langs = $this->_generic->getObjectLang()->getSiteLangs();
		
		if (in_array($lang,$langs))
		{
			// controllers.xml
			$controllerXML->deleteTags("//controllers/controller[@side!='sls']/controllerLangs/controllerLang[@lang='".$lang."']");
			$controllerXML->deleteTags("//controllers/controller[@side!='sls']/scontrollers/scontroller/scontrollerLangs/scontrollerLang[@lang='".$lang."']");
			$controllerXML->saveXML($this->_generic->getPathConfig("configSecure")."controllers.xml");
			// /controllers.xml
			
			// metas.xml
			$metaXML->deleteTags("//sls_configs/action/title[@lang='".$lang."']");
			$metaXML->deleteTags("//sls_configs/action/description[@lang='".$lang."']");
			$metaXML->deleteTags("//sls_configs/action/keywords[@lang='".$lang."']");
			$metaXML->saveXML($this->_generic->getPathConfig("configSls")."metas.xml");
			// /metas.xml
			
			// site.xml
			$siteXML->deleteTags("//configs/langs/name[".(array_shift(array_keys($langs,$lang))+1)."]");
			$siteXML->saveXML($this->_generic->getPathConfig("configSecure")."site.xml");
			// /site.xml
			
			// generic.iso.lang.php
			@unlink($this->_generic->getPathConfig("coreGenericLangs")."generic.".$lang.".lang.php");
			// /generic.iso.lang.php
			
			// site.iso.lang.php			
			@unlink($this->_generic->getPathConfig("genericLangs")."site.".$lang.".lang.php");
			// /site.iso.lang.php
			
			// Actions langs files
			$this->deleteActionsLang($this->_generic->getPathConfig("actionLangs"),$lang);
			// /Actions langs files
		}
		
		$controllers = $this->_generic->getTranslatedController("SLS_Bo","Langs");
		$this->_generic->redirect($controllers['controller']."/".$controllers['scontroller'].".sls");
	}
	
}
?>