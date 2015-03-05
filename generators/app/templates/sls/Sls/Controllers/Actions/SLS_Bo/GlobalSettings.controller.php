<?php
class SLS_BoGlobalSettings extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		$siteXML = $this->_generic->getSiteXML();
		
		$errors = array();
		$aliases = array();
		$domains = array();
		
		// Prod Deployment		
		$env = $this->_http->getParam("Env");
		if (empty($env))
			$env = "prod";
		$finalFile = ($this->_http->getParam("ProdDeployment") == "true") ? "site_".$env.".xml" : "site.xml";
		$isInBatch = ($this->_http->getParam("CompleteBatch") == "true") ? true : false;
		$xml->addFullTag("is_batch",($isInBatch) ? "true" : "false",true);
		$xml->addFullTag("is_prod",($this->_http->getParam("ProdDeployment") == "true") ? "true" : "false",true);
		$xml->addFullTag("env",$env,true);
		
		// Get default values
		if ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml"))
		{			
			$xmlSite = new SLS_XMLToolbox(file_get_contents($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml"));
			
			$defaultDomain 				= $xmlSite->getTag("//configs/domainName/domain");
			$defaultProject				= $xmlSite->getTag("//configs/projectName");
			$defaultVersion				= $xmlSite->getTag("//configs/versionName");
			$defaultExtension			= $xmlSite->getTag("//configs/defaultExtension");
			$defaultCharset				= $xmlSite->getTag("//configs/defaultCharset");
			$defaultDoctype				= $xmlSite->getTag("//configs/defaultDoctype");
			$timezone_area				= SLS_String::substrBeforeFirstDelimiter($xmlSite->getTag("//configs/defaultTimezone"),"/");
			$timezone_city				= SLS_String::substrAfterFirstDelimiter($xmlSite->getTag("//configs/defaultTimezone"),"/"); 
			$defaultLang 				= $xmlSite->getTag("//configs/defaultLang");
			$defaultdomainSessionShare 	= $xmlSite->getTag("//configs/domainSession");
		}
		else
		{
			$defaultDomain 				= $this->_generic->getSiteConfig("domainName");
			$defaultProject				= $this->_generic->getSiteConfig("projectName");
			$defaultVersion				= $this->_generic->getSiteConfig("versionName"); 
			$defaultExtension			= $this->_generic->getSiteConfig("defaultExtension");
			$defaultCharset				= $this->_generic->getSiteConfig("defaultCharset");
			$defaultDoctype				= $this->_generic->getSiteConfig("defaultDcotype");
			$timezone_area				= SLS_String::substrBeforeFirstDelimiter($this->_generic->getSiteConfig("defaultTimezone"),"/");
			$timezone_city				= SLS_String::substrAfterFirstDelimiter($this->_generic->getSiteConfig("defaultTimezone"),"/"); 
			$defaultLang 				= $this->_generic->getSiteConfig("defaultLang");
			$defaultdomainSessionShare 	= $this->_generic->getSiteConfig("domainSession");
		}
		
		$reload 			= $this->_http->getParam("reload");

		$charsetsXML = new SLS_XMLToolBox(file_get_contents($this->_generic->getPathConfig('configSls')."charset.xml"));
		$charsets = array_map('strtoupper', $charsetsXML->getTags('//sls_configs/charset/code'));
		$handle2 = file_get_contents($this->_generic->getPathConfig("configSls").'timezone.xml');
		$xml->addFullTag("timezones", SLS_String::substrBeforeLastDelimiter(SLS_String::substrAfterFirstDelimiter($handle2, "<sls_configs>"), "</sls_configs>"), false);
		
		$langs = $this->_generic->getSiteXML()->getTags('//configs/langs/name');
			
		if ($reload == "true")
		{			
			$domains = 	$siteXML->getTagsAttributes("//configs/domainName/domain",array("alias"));			
			for($i=0 ; $i<$count=count($domains) ; $i++)
				array_push($aliases,$domains[$i]["attributes"][0]["value"]);
			
			// Get New Parameters
			$exportConfig	= $this->_http->getParam('export');
			
			$domains = array();
			foreach($aliases as $alias)
			{
				$domain = SLS_String::trimSlashesFromString($this->_http->getParam("domain_".$alias, "post"));
				if (SLS_String::endsWith(trim($domain),"/"))
					$domain = SLS_String::substrBeforeLastDelimiter(trim($domain),"/");
				$domains[$alias]= $domain;
			}	
			
			$postProject		= SLS_String::trimSlashesFromString($this->_http->getParam("project", "post"));
			$postVersion		= SLS_String::trimSlashesFromString($this->_http->getParam("version", "post"));
			$postExtension 		= SLS_String::trimSlashesFromString($this->_http->getParam("extension", "post"));
			$postCharset		= SLS_String::trimSlashesFromString($this->_http->getParam("charset", "post"));
			$postDoctype		= SLS_String::trimSlashesFromString($this->_http->getParam("doctype", "post"));
			$timezone_area		= SLS_String::trimSlashesFromString($this->_http->getParam('settings_timezone_area'));
			$timezone_city		= SLS_String::trimSlashesFromString($this->_http->getParam('settings_timezone_area_'.$timezone_area));
			$postLang			= SLS_String::trimSlashesFromString($this->_http->getParam("lang", "post"));
			$domainSessionShare	= SLS_String::trimSlashesFromString($this->_http->getParam("domainSession", "post"));

			if ($this->_http->getParam("domainSessionActive") == "")
				$domainSessionShare = "";		

			foreach($domains as $alias => $domain)
				if (empty($domain))
					array_push($errors, "The Domain name is required for the domain alias ".$alias);
			if (empty($postProject))
				array_push($errors, "The project Name is required");
			if (empty($postVersion))
				array_push($errors, "The version Name is required");
			if (empty($postExtension))
				array_push($errors, "The extension is required");
			if (!in_array($postCharset, $charsets))
				array_push($errors, "The Charset selected is incorrect");
			if (empty($postDoctype))
				array_push($errors, "The doctype is required");
			if (empty($timezone_area) || empty($timezone_city))
				array_push($errors,"You must choose your default timezone");
			if (!in_array($postLang, $langs))
				array_push($errors, "The Default lang selected is incorrect");
			if ($this->_http->getParam("domainSessionActive") != "" && empty($domainSessionShare))
				array_push($errors,"You need to fill the domain pattern from which you want to share session");
			if (empty($errors))
			{
				foreach($domains as $alias => $domain)
					$siteXML->setTag("//configs/domainName/domain[@alias='".$alias."']", $domain, true);
				if ($defaultProject != $postProject)
					 $siteXML->setTag("//configs/projectName", $postProject, true);
				if ($defaultVersion != $postVersion)
					 $siteXML->setTag("//configs/versionName", $postVersion, true);
				if ($defaultExtension != $postExtension)
					 $siteXML->setTag("//configs/defaultExtension", $postExtension, true);
				if ($defaultCharset != $postCharset)
					 $siteXML->setTag("//configs/defaultCharset", $postCharset, true);
				if ($defaultDoctype != $postDoctype)
					 $siteXML->setTag("//configs/defaultDoctype", $postDoctype, true);
				if ($defaultTimezone != $timezone_area."/".$timezone_city)
					 $siteXML->setTag("//configs/defaultTimezone", $timezone_area."/".$timezone_city, true);
				if ($defaultLang != $postLang)
					 $siteXML->setTag("//configs/defaultLang", $postLang, true);
				if ($defaultdomainSessionShare != $domainSessionShare)
					$siteXML->setTag("//configs/domainSession", $domainSessionShare, true);
				if ($exportConfig == "on")
				{
					$date = gmdate('D, d M Y H:i:s');
					header("Content-Type: text/xml"); 
					header('Content-Disposition: attachment; filename='.$finalFile);
					header('Last-Modified: '.$date. ' GMT');
					header('Expires: ' .$date);
					// For This Fuck'in Browser
					if(preg_match('/msie|(microsoft internet explorer)/i', $_SERVER['HTTP_USER_AGENT']))
					{
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
					}
					else
						header('Pragma: no-cache');
					
					print($siteXML->getXML());
					exit; 
				}
				else
				{
					$siteXML->refresh();
					@file_put_contents($this->_generic->getPathConfig("configSecure").$finalFile, $siteXML->getXML());					
					if ($isInBatch)
						$this->_generic->forward("SLS_Bo","DataBaseSettings",array(array("key"=>"ProdDeployment","value"=>"true"),array("key"=>"CompleteBatch","value"=>"true"),array("key"=>"Env","value"=>$env)));
					else if ($this->_http->getParam("ProdDeployment") == "true")
						$this->_generic->forward("SLS_Bo","ProductionDeployment");
				}
			}
			else 
			{
				$xml->startTag('errors');
				foreach ($errors as $error)				
					$xml->addFullTag('error', $error);				
				$xml->endTag('errors');
			}
	
		}
		$this->_generic->eraseCache('Site');
		$xml->startTag("charsets");
		foreach ($charsets as $charset)
			$xml->addFullTag('charset', $charset, true);
		$xml->endTag("charsets");
		
		$xml->startTag("langs");
			foreach ($langs as $lang)
			$xml->addFullTag('lang', $lang, true);
		$xml->endTag("langs");
		
		$xmlSite = (file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml")) ? new SLS_XMLToolbox(file_get_contents($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml")) : null;
		$xml->startTag("current_values");
			if ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml"))
				$domains = 	$xmlSite->getTagsAttributes("//configs/domainName/domain",array("alias","default","lang"));
			else
				$domains = 	$siteXML->getTagsAttributes("//configs/domainName/domain",array("alias","default","lang"));
			
			$xml->startTag("domains");
			for($i=0 ; $i<$count=count($domains) ; $i++)
			{
				$alias = $domains[$i]["attributes"][0]["value"];
				$default = ($domains[$i]["attributes"][1]["value"] == 1) ? true : false;
				$domain_lang = $domains[$i]["attributes"][2]["value"];
				$xml->startTag("domain");
					$xml->addFullTag("alias",$alias,true);
					$xml->addFullTag("default",($default) ? "true" : "false",true);
					$xml->addFullTag("domain",$domains[$i]["value"],true);
					$xml->addFullTag("lang",$domain_lang,true);
					$xml->addFullTag("delete_url",$this->_generic->getFullPath("SLS_Bo","DeleteDomain",array(array("key"=>"alias","value"=>$alias))),true);
				$xml->endTag("domain");
			}
			$xml->endTag("domains");
			$xml->addFullTag("project", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml")) ? $xmlSite->getTag("//configs/projectName") : $this->_generic->getSiteConfig("projectName"), true);
			$xml->addFullTag("version", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml")) ? $xmlSite->getTag("//configs/versionName") : $this->_generic->getSiteConfig("versionName"), true);
			$xml->addFullTag("extension", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml")) ? $xmlSite->getTag("//configs/defaultExtension") :$this->_generic->getSiteConfig("defaultExtension"), true);
			$xml->addFullTag("charset", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml")) ? $xmlSite->getTag("//configs/defaultCharset") :$this->_generic->getSiteConfig("defaultCharset"), true);
			$xml->addFullTag("doctype", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml")) ? $xmlSite->getTag("//configs/defaultDoctype") :$this->_generic->getSiteConfig("defaultDoctype"), true);
			$xml->addFullTag("lang", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml")) ? $xmlSite->getTag("//configs/defaultLang") :$this->_generic->getSiteConfig("defaultLang"), true);
			$xml->addFullTag("domain_session", ($this->_http->getParam("ProdDeployment") == "true" && file_exists($this->_generic->getRoot().$this->_generic->getPathConfig("configSecure")."/site_".$env.".xml")) ? $xmlSite->getTag("//configs/domainSession") : ((is_null($this->_generic->getSiteConfig("domainSession"))) ? "" : $this->_generic->getSiteConfig("domainSession")), true);
			$xml->startTag("timezone");
				$xml->addFullTag("area",$timezone_area,true);
				$xml->addFullTag("city",$timezone_city,true);
			$xml->endTag("timezone");
		$xml->endTag("current_values");
		
		$xml->addFullTag("add_domain_url",$this->_generic->getFullPath("SLS_Bo","AddDomain"),true);
		
		$environments = $this->getEnvironments();
		$xml->startTag("environments");
		foreach($environments as $environment)
			$xml->addFullTag("environment",$environment,true);
		$xml->endTag("environments");
		
		$this->saveXML($xml);		
	}
	
}
?>