<?php
class SLS_InitGlobalSettings extends SLS_InitControllerProtected 
{		
	/**
	 * Action Home
	 *
	 */
	public function action() 
	{
		$this->secureURL();
		
		$this->_generic->registerLink('GlobalSettings', 'SLS_Init', 'GlobalSettings');
		$handle = file_get_contents($this->_generic->getPathConfig("configSls").'charset.xml');
		$handle2 = file_get_contents($this->_generic->getPathConfig("configSls").'timezone.xml');
		$xml = $this->getXML();
		$xml->addFullTag("charsets", SLS_String::substrBeforeLastDelimiter(SLS_String::substrAfterFirstDelimiter($handle, "<sls_configs>"), "</sls_configs>"), false);
		$xml->addFullTag("timezones", SLS_String::substrBeforeLastDelimiter(SLS_String::substrAfterFirstDelimiter($handle2, "<sls_configs>"), "</sls_configs>"), false);
				
		$errors = array();
		
		$domain 		= SLS_String::trimSlashesFromString($this->_http->getParam('settings_domain'));
		$protocol 		= SLS_String::trimSlashesFromString($this->_http->getParam('settings_protocol'));
		$project 		= SLS_String::trimSlashesFromString($this->_http->getParam('settings_project'));
		$description 	= SLS_String::trimSlashesFromString($this->_http->getParam('settings_description'));
		$keywords 		= SLS_String::trimSlashesFromString($this->_http->getParam('settings_keywords'));
		$author		 	= SLS_String::trimSlashesFromString($this->_http->getParam('settings_author'));
		$copyright	 	= SLS_String::trimSlashesFromString($this->_http->getParam('settings_copyright'));
		$extension 		= ($this->_http->getParam('settings_extension') == "") ? "sls" : SLS_String::trimSlashesFromString($this->_http->getParam('settings_extension'));
		$charset 		= SLS_String::trimSlashesFromString($this->_http->getParam('settings_charset'));
		$doctype 		= SLS_String::trimSlashesFromString($this->_http->getParam('settings_doctype'));
		$bo 			= SLS_String::trimSlashesFromString($this->_http->getParam('settings_bo'));
		$timezone_area	= SLS_String::trimSlashesFromString($this->_http->getParam('settings_timezone_area'));
		$timezone_city	= SLS_String::trimSlashesFromString($this->_http->getParam('settings_timezone_area_'.$timezone_area));
		
		$xmlTmp = new SLS_XMLToolbox($handle);		
		$allowedCharsets = $xmlTmp->getTags("//sls_configs/charset/code");
		
		if ($this->_http->getParam('globalSettings_reload') == "true")
		{	
			if (empty($domain))
				array_push($errors,"You must fill your main domain name");
			if ($protocol != 'http' && $protocol != 'https')
				array_push($errors,"You must choose a correct Protocol");
			if (empty($project))
				array_push($errors,"You must fill your project name");
			if (empty($description))
				array_push($errors,"You must fill your project description");
			if (empty($author))
				$author = $project;
			if (empty($copyright))
				$copyright = $domain;
			if (empty($extension))
				array_push($errors,"You must fill your default extension");
			if (empty($bo))
				array_push($errors,"You must fill your access to your SillySmart's Back-Office");
			if (!in_array($charset,$allowedCharsets))
				array_push($errors,"You must choose a valid charset");
			if (empty($doctype))
				array_push($errors,"You must choose your default doctype");
			if (empty($timezone_area) || empty($timezone_city))
				array_push($errors,"You must choose your default timezone");
							
			if (empty($errors))
			{
				$key = substr(md5($domain).sha1($project).uniqid(microtime()),mt_rand(5,10),mt_rand(20,32));
				$coreXml = $this->_generic->getSiteXML();
				$coreXml->setTag('//configs/domainName', "<domain alias='__default' default='1' js='true' isSecure='false' lang=''><![CDATA[".$domain."]]></domain>", false);
				$coreXml->setTag('//configs/protocol',$protocol);
				$coreXml->setTag('//configs/defaultExtension',$extension);
				$coreXml->setTag('//configs/projectName',$project);
				$coreXml->setTag('//configs/versionName',date("Ymd")."-dev");
				$coreXml->setTag('//configs/metaDescription',$description);
				$coreXml->setTag('//configs/metaKeywords',$keywords);
				$coreXml->setTag('//configs/metaAuthor',$author);
				$coreXml->setTag('//configs/metaCopyright',$copyright);
				$coreXml->setTag('//configs/privateKey',$key);
				$coreXml->setTag('//configs/defaultCharset',strtoupper($charset));
				$coreXml->setTag('//configs/defaultDoctype',$doctype);
				$coreXml->setTag('//configs/defaultTimezone',$timezone_area."/".$timezone_city);
				file_put_contents($this->_generic->getPathConfig("configSecure")."site.xml", $coreXml->getXML());
				$controllersXml = $this->_generic->getControllersXML();
				$controllersXml->setTag("//controllers/controller[@name='SLS_Bo']/controllerLangs/controllerLang",$bo);
				
				$uniqs = array();
				$metas = array();
				
				// Generate Controllers IDS
				$slsControllers = $controllersXml->getTags("//controllers/controller[@side='sls']/@name");	
				$slsLangs = $controllersXml->getTags("//controllers/controller[@side='sls'][1]/scontrollers/scontroller[1]/scontrollerLangs/scontrollerLang/@lang");
				foreach ($slsControllers as $slsController)
				{
					// Take a random id and set it
					$uniq = uniqid("c_");
					while(in_array($uniq, $uniqs))					
						$uniq = uniqid("c_");					
					array_push($uniqs, $uniq);
					$controllersXml->setTagAttributes("//controllers/controller[@name='".$slsController."' and @side='sls']", array("id"=>$uniq));
					
					// Generate Actions IDS
					$slsActions = $controllersXml->getTags("//controllers/controller[@side='sls' and @name='".$slsController."']/scontrollers/scontroller/@name");					
					foreach ($slsActions as $slsAction)
					{
						// Take a random id and set it
						$uniq = uniqid("a_");
						while(in_array($uniq, $uniqs))						
							$uniq = uniqid("a_");						
						array_push($uniqs, $uniq);
						$controllersXml->setTagAttributes("//controllers/controller[@name='".$slsController."' and @side='sls']/scontrollers/scontroller[@name='".$slsAction."']", array("id"=>$uniq));
						
						// Get title attribute, save it into array and delete this attribute
						$tmpArray = array();
						foreach ($slsLangs as $lang)
						{
							$tmpArray[$lang] = array_shift($controllersXml->getTags("//controllers/controller[@name='".$slsController."' and @side='sls']/scontrollers/scontroller[@name='".$slsAction."']/scontrollerLangs/scontrollerLang[@lang='".$lang."']/@title"));
							$controllersXml->deleteTagAttribute("//controllers/controller[@name='".$slsController."' and @side='sls']/scontrollers/scontroller[@name='".$slsAction."']/scontrollerLangs/scontrollerLang[@lang='".$lang."']","title");
						}
						$metas[$uniq] = $tmpArray;
					}	
				}
				
				// Update metas.xml
				$metaXml = '';
				foreach($metas as $key => $value)
				{
					$metaXml .= '<action id="'.$key.'">';
					foreach ($value as $lang => $title)
						$metaXml .=	'<title lang="'.$lang.'"><![CDATA['.$title.']]></title>';
					$metaXml .=	'<robots><![CDATA[noindex, nofollow]]></robots>';
					$metaXml .= '</action>';
				}
				$metaO = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig("configSls")."metas.xml"));
				$metaO->appendXML("//sls_configs",$metaXml);
				$metaO->saveXML($this->_generic->getPathConfig("configSls")."metas.xml");
				
				// Overwrite template __default
				$this->createXslTemplate("__default",$doctype);
				
				file_put_contents($this->_generic->getPathConfig("configSecure")."controllers.xml", $controllersXml->getXML());
				$this->setInstallationStep(array(0=>"SLS_Init",1=>"Initialization"), array(0=>"International",1=>"International"));
				return $this->_generic->dispatch("SLS_Init", "International");
			}
			else
			{
				$xml->startTag("errors");
				foreach ($errors as $error)
					$xml->addFullTag("error", $error, true);
				$xml->endTag("errors");
				
				$xml->addFullTag("domain",$domain,true);
				$xml->addFullTag("protocol",$protocol,true);
				$xml->addFullTag("project",$project,true);
				$xml->addFullTag("description",$description,true);
				$xml->addFullTag("keywords",$keywords,true);
				$xml->addFullTag("author",$author,true);
				$xml->addFullTag("copyright",$copyright,true);
				$xml->addFullTag("extension",$extension,true);
				$xml->addFullTag("charset",$charset,true);
				$xml->addFullTag("doctype",$doctype,true);
				$xml->addFullTag("bo",$bo,true);
				$xml->startTag("timezone");
					$xml->addFullTag("area",$timezone_area,true);
					$xml->addFullTag("city",$timezone_city,true);
				$xml->endTag("timezone");				
			}
		}
		else
		{
			$timezone = date_default_timezone_get();
			$xml->addFullTag("domain",$_SERVER['HTTP_HOST'].(($_SERVER['SCRIPT_NAME'] != "/index.php") ? SLS_String::substrBeforeFirstDelimiter($_SERVER['SCRIPT_NAME'],"/index.php") : ""),true);
			$xml->addFullTag("protocol",(SLS_String::startsWith($_SERVER['SERVER_PROTOCOL'],'HTTPS')) ? 'https' : 'http',true);
			$xml->addFullTag("author","SillySmart",true);
			$xml->addFullTag("extension","sls",true);
			$xml->startTag("timezone");
				$xml->addFullTag("area",(empty($timezone) || !SLS_String::contains($timezone,'/')) ? 'Europe' : SLS_String::substrBeforeFirstDelimiter($timezone,'/'),true);
				$xml->addFullTag("city",(empty($timezone) || !SLS_String::contains($timezone,'/')) ? 'Paris' : SLS_String::substrAfterFirstDelimiter($timezone,'/'),true);
			$xml->endTag("timezone");
			$xml->addFullTag("bo","Manage",true);
		}
		
		$this->saveXML($xml);
	}
	
}
?>