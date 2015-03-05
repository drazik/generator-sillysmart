<?php
class SLS_InitFinal extends SLS_InitControllerProtected 
{		
	/**
	 * Action Home
	 *
	 */
	public function action() 
	{
		$this->secureURL();
		$this->_generic->registerLink('BackOffice', 'SLS_Bo', 'Index');
		$coreXml = $this->_generic->getCoreXml("sls");
		$coreXml->setTag("installation/step",-1);		
		file_put_contents($this->_generic->getPathConfig("configSls")."sls.xml", $coreXml->getXML());
		$this->_generic->getObjectLang()->resetLang();
	}
	
}
?>