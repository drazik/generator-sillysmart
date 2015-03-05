<?php
class SLS_BoSettings extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$xml->startTag("settings_menu");			
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "GlobalSettings"));
				$xml->addFullTag("label", "Edit Global Settings");
			$xml->endTag("setting_menu");
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "DataBaseSettings"));
				$xml->addFullTag("label", "Edit DataBases Settings");
			$xml->endTag("setting_menu");
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "MailSettings"));
				$xml->addFullTag("label", "Edit SMTP Emails Settings");
			$xml->endTag("setting_menu");			
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "ProjectSettings"));
				$xml->addFullTag("label", "Edit Personnal Settings");
			$xml->endTag("setting_menu");
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "JSSettings"));
				$xml->addFullTag("label", "Edit Javascript / Ajax Settings");
			$xml->endTag("setting_menu");
			$xml->startTag("setting_menu");
				$xml->addFullTag("link", $this->_generic->getFullPath("SLS_Bo", "GoogleSettings"));
				$xml->addFullTag("label", "Edit Google Settings");
			$xml->endTag("setting_menu");
		$xml->endTag("settings_menu");
		$this->saveXML($xml);		
	}
	
}
?>