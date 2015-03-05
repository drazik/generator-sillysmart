<?php
class SLS_BoAddDomain extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 		= $this->hasAuthorative();
		$xml 		= $this->getXML();
		$xml		= $this->makeMenu($xml);
		$siteXML 	= $this->_generic->getSiteXML();
		$langs 		= $this->_lang->getSiteLangs();
		
		$errors = array();
		
		if ($this->_http->getParam("reload") == "true")
		{
			$alias = SLS_String::trimSlashesFromString($this->_http->getParam("alias"));
			$domain = SLS_String::trimSlashesFromString($this->_http->getParam("domain"));
			$lang = SLS_String::trimSlashesFromString($this->_http->getParam("lang"));
			$cdn = $this->_http->getParam("cdn");
			if ($cdn != 'true') 
				$cdn = 'false';
			if (SLS_String::endsWith(trim($domain),"/"))
				$domain = SLS_String::substrBeforeLastDelimiter(trim($domain),"/");
			
			$result = $siteXML->getTag("//configs/domainName/domain[@alias='".$alias."']");
			
			if (empty($alias))
				array_push($errors,"You must choose an alias for your domain name.");
			else if (!empty($result))
				array_push($errors,"This alias is already used by another domain, please choose another.");
			if (empty($domain))
				array_push($errors,"You must fill your domain name.");			
			else if (!SLS_String::isValidUrl("http://".$domain))
				array_push($errors,"This domain is not a valid url.");
				
			if (empty($errors))
			{
				$str_xml = '<domain alias="'.$alias.'" js="true" isSecure="false" cdn="'.$cdn.'" lang="'.$lang.'">'.
								'<![CDATA['.$domain.']]>'.
							'</domain>';
				$siteXML->appendXMLNode("//configs/domainName",$str_xml);
				$siteXML->saveXML($this->_generic->getPathConfig("configSecure")."site.xml");
				$this->_generic->forward("SLS_Bo","GlobalSettings");
			}
			else
			{
				$xml->startTag("domain");
					$xml->addFullTag("alias",$alias,true);
					$xml->addFullTag("domain",$domain,true);
					$xml->addFullTag("cdn",$cdn,true);
					$xml->addFullTag("lang",$lang,true);
				$xml->endTag("domain");
			}
		}
		
		$langsBinded = $siteXML->getTags("//configs/domainName/domain/@lang");		
		foreach($langs as $lang)
			if (in_array($lang,$langsBinded))
				unset($langs[array_shift(array_keys($langs,$lang))]);
		$xml->startTag("langs");		
		foreach($langs as $lang)
			$xml->addFullTag("lang",$lang,true);
		$xml->endTag("langs");
		
		$xml->startTag("errors");
		foreach($errors as $error)
			$xml->addFullTag("error",$error,true);
		$xml->endTag("errors");
		
		$this->saveXML($xml);		
	}
	
}
?>