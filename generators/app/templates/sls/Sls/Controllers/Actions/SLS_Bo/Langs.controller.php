<?php
class SLS_BoLangs extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		$xml->addFullTag("delete",$this->_generic->getFullPath("SLS_Bo","DeleteLang",array(),false));
		$xml->addFullTag("default",$this->_generic->getFullPath("SLS_Bo","DefaultLang",array(),false));
		$this->registerLink("ENABLE", "SLS_Bo", "Langs", false);
		$this->_generic->registerLink('Add_Lang', 'SLS_Bo', 'AddLang');		
		$isos = $this->_generic->getObjectLang()->getSiteLangs(true);
		
		// Enable or Disable Lang
		$enable = $this->_http->getParam('Enable');
		$lang = $this->_http->getParam("iso");
		if (($enable == "on" || $enable == "off") && in_array($lang, $isos))
		{
			$xmlLangs = $this->_generic->getSiteXML();
			// Enable Lang
			if ($enable == "on")
				$xmlLangs->setTagAttributes("//configs/langs/name[node()='".$lang."']", array('active' => 'true'));
			else
			{
				if ($this->_generic->getSiteConfig('defaultLang') != $lang)
					$xmlLangs->setTagAttributes("//configs/langs/name[node()='".$lang."']", array('active' => 'false'));
			}
			$xmlLangs->refresh();
			file_put_contents($this->_generic->getPathConfig('configSecure')."site.xml", $xmlLangs->getXML());
			$this->_generic->forward("SLS_Bo","Langs");
		}
		
		$handleLangs = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."international.xml"));
		$isosActives = $this->_generic->getObjectLang()->getSiteLangs(false);
		
		$xml->startTag("langs");
		foreach($isos as $iso)
		{
			$xml->startTag("lang");
			$xml->addFullTag("language",ucwords(array_shift($handleLangs->getTags("//sls_configs/sls_country/sls_country_langs/sls_country_lang[@iso='".$iso."']"))),true);
			$xml->addFullTag("iso",$iso,true);
			$xml->addFullTag("default",($this->_generic->getSiteConfig("defaultLang")==$iso) ? "true" : "false",true);
			$xml->addFullTag("active", (in_array($iso, $isosActives)) ? "true" : "false", true);
			$xml->endTag("lang");
		}
		$xml->endTag("langs");
		
		$this->saveXML($xml);		
	}	
}
?>