<?php
class SLS_BoUpdates extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		
		$slsXml = $this->_generic->getCoreXML('sls');
		$syncServer = array_shift($slsXml->getTags("slsnetwork"));
		$slsVersion = array_shift($slsXml->getTags("version"));
		
		$serversJSON = @file_get_contents($syncServer);
		
		if ($serversJSON !== false && !empty($serversJSON))
		{			
			$servers = json_decode($serversJSON);
			$updateServer = array_shift($servers->servers->update);
			
			if ($updateServer === false || empty($updateServer))
			{
				$xml->addFullTag("error_server","Update server can't be found, please retry later");
			}
			else
			{				
				$serverContent = @file_get_contents($updateServer->url);
				$jsonContent = json_decode($serverContent);
				
				if ($jsonContent === false || empty($jsonContent))
				{
					$xml->addFullTag("error_server","Update server not available, please retry later");
				}
				else
				{					
					$currentRls = array_pop($jsonContent->releases);
					$currentSls = $slsVersion;
										
					$xml->startTag("current_release");					
					$xml->addFullTag("version",$currentRls->version,true);
					$xml->endTag("current_release");
					$xml->addFullTag("current_version",$currentSls,true);
					$xml->addFullTag("up_to_date",($currentRls->version != $currentSls) ? "false" : "true",true);
					$xml->addFullTag("url_update",$this->_generic->getFullPath("SLS_Bo","UpdateSLS"),true);
				}
			}
		}
		else
		{
			$xml->addFullTag("error_server","Synchronisation server not available, please retry later");
		}
		
		$this->saveXML($xml);
	}
	
}
?>