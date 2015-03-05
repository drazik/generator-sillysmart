<?php
/**
 * Enter description here...
 *
 */ 
class SLS_InitControllerProtected extends SiteProtected 
{
	public function init()
    {
        parent::init();        
    }
	
	/**
	 * Function which update Installation Step
	 *
	 * @param array $controller array(0=>ControllerName,1=>ControllerLang);
	 * @param array $scontroller array(0=>SControllerName,1=>SControllerLang);
	 */
	protected function setInstallationStep($controller, $scontroller)
	{
		$coreXml = $this->_generic->getCoreXml('sls');
		$coreXml->setTag("installation/controller/name", $controller[0]);
		$coreXml->setTag("installation/controller/lang", $controller[1]);
		$coreXml->setTag("installation/scontroller/name", $scontroller[0]);
		$coreXml->setTag("installation/scontroller/lang", $scontroller[1]);
		file_put_contents($this->_generic->getPathConfig("configSls")."sls.xml", $coreXml->getXML());
	}
	
	/**
	 * Check if The Installation Step is correct
	 *
	 */
	protected function secureURL()
	{
		if ($this->_generic->getSiteConfig("isInstall") == false)
			$this->_generic->redirect("Home/Index.sls");
		else 
		{
			$coreXml = $this->_generic->getCoreXml('sls');
			$installStep = array_shift($coreXml->getTags("installation/scontroller/name"));
			if ($installStep != $this->_generic->getGenericScontrollerName())
				$this->_generic->redirect(array_shift($coreXml->getTags("installation/controller/lang"))."/".$installStep);
		}
		
	}
}
?>