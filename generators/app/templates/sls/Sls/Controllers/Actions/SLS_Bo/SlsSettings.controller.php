<?php
class SLS_BoSlsSettings extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$xml->startTag("settings_menu");			
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "ProdSettings"));
				$xml->addFullTag("label", "Edit Production Settings");
			$xml->endTag("setting_menu");
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "Compressor"));
				$xml->addFullTag("label", "Compress-Uncompress JS / CSS files");
			$xml->endTag("setting_menu");
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "Environments"));
				$xml->addFullTag("label", "Manage deployment environments");
			$xml->endTag("setting_menu");
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "ProductionDeployment"));
				$xml->addFullTag("label", "Deploy files configuration");
			$xml->endTag("setting_menu");
		$xml->endTag("settings_menu");
		$this->saveXML($xml);		
	}
	
}
?>