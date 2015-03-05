<?php
class SLS_InitDirRights extends SLS_InitControllerProtected 
{		
	/**
	 * Action Dir Rights
	 *
	 */
	public function action() 
	{
		$this->setInstallationStep(array(0=>"SLS_Init",1=>"Initialization"), array(0=>"DirRights",1=>"Directories"));
		
		// Check files rights
		$pathsToCheck = array(
		"actionsControllers",			"models",		"modelsSql",			"views",				"viewsHeaders",
		"viewsBody",					"viewsGenerics","plugins",				"langs",				"actionLangs",			
		"css",							"scripts",		"js",					"jsStatics",			"jsDyn", 
		"configSecure", 				"configSls",	"genericLangs");
		
		$xmlToolBox = $this->getXML();
		$xmlToolBox->startTag("directories");
		$nextStep = true;
		foreach ($pathsToCheck as $path)
		{	$xmlToolBox->startTag("directory");		
			
			(!is_writable($this->_generic->getPathConfig($path))) ? $xmlToolBox->addFullTag("writable", 0) : $xmlToolBox->addFullTag("writable", 1);
			(!is_readable($this->_generic->getPathConfig($path))) ? $xmlToolBox->addFullTag("readable", 0) : $xmlToolBox->addFullTag("readable", 1);
			(is_dir($this->_generic->getPathConfig($path))) ? $xmlToolBox->addFullTag("path", realpath($this->_generic->getPathConfig($path)), true) : $xmlToolBox->addFullTag("path", SLS_String::substrBeforeLastDelimiter($_SERVER['SCRIPT_FILENAME'],'/').substr($this->_generic->getPathConfig($path), 0, (strlen($this->_generic->getPathConfig($path))-1)), true);
			(!is_writable($this->_generic->getPathConfig($path)) || !is_readable($this->_generic->getPathConfig($path))) ? $nextStep = false : "";
			$xmlToolBox->endTag("directory");		
		}		
		$xmlToolBox->endTag("directories"); 
		if (!$nextStep) 
			$xmlToolBox->addFullTag("next", 0); 
		else
		{
			$xmlToolBox->addFullTag("next", 1);
		}
		$this->_generic->registerLink('authentication', 'SLS_Init', 'Authentication');
		$this->saveXML($xmlToolBox);	
		($nextStep) ? $this->setInstallationStep(array(0=>"SLS_Init",1=>"Initialization"), array(0=>"Authentication",1=>"Authentication")) : false;		
	}
	
}
?>