<?php
class SLS_BoPlugins extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		$action = $this->_http->getParam("Action");	
		$plugin_id = $this->_http->getParam("Plugin");	
		$server_id = $this->_http->getParam("Server");	
		
		$errors = array();
		
		// Get Plugins Versions
		$slsXml = $this->_generic->getCoreXML('sls');
		$syncServer = array_shift($slsXml->getTags("slsnetwork"));
		$slsVersion = array_shift($slsXml->getTags("version"));
		$serversJSON = @file_get_contents($syncServer);
		$pluginsAvailable = array();
		if ($serversJSON !== false && !empty($serversJSON))
		{			
			$servers = json_decode($serversJSON);
			$pluginsServers = $servers->servers->plugins;
			$plugins = array();
			
			foreach ($pluginsServers as $pluginsServer)
			{				
				$serverContent = @file_get_contents($pluginsServer->url);
				if ($serverContent !== false && !empty($serverContent))
				{
					$serverContent = json_decode($serverContent);
					$plugs = $serverContent->plugins;
					foreach ($plugs as $plug)
					{
						$pluginsAvailable[$plug->id."_".$plug->code] = array();
						$pluginsAvailable[$plug->id."_".$plug->code]['server'] = $pluginsServer->id;
						$pluginsAvailable[$plug->id."_".$plug->code]['version'] = $plug->version;
						$pluginsAvailable[$plug->id."_".$plug->code]['compatible'] = $plug->compability;
						$pluginsAvailable[$plug->id."_".$plug->code]['doc'] = $pluginsServer->domain.$plug->doc;
						
						if ($action == "Maj" && $plugin_id == $plug->id && $server_id = $pluginsServer->id)
						{
							$this->goDirectTo("SLS_Bo", "DeletePlugin", array(
																	0 => array("key"=>"Way","value"=>"Maj"),
																	1 => array("key"=>"Server","value"=>$server_id),
																	2 => array("key"=>"Plugin","value"=>$plugin_id)
																	));
						}
					}
				}
			}
		}

		// Get All Plugins Installed
		$plugins = $this->_generic->getPluginXML("plugins");
		$plugArr = count($plugins->getTags("//plugins/plugin"));
		
		$edit = $this->_generic->getTranslatedController('SLS_Bo', 'EditPlugin');
		$delete = $this->_generic->getTranslatedController('SLS_Bo', 'DeletePlugin');
		$maj = $this->_generic->getTranslatedController("SLS_Bo", "Plugins");
		for ($i=1;$i<=$plugArr;$i++)
		{
			
			$id = array_shift($plugins->getTags("//plugins/plugin[".$i."]/@id"));
			$code = array_shift($plugins->getTags("//plugins/plugin[".$i."]/@code"));
			$version = array_shift($plugins->getTags("//plugins/plugin[".$i."]/@version"));
			
			$xml->startTag("plugin");
				$xml->addFullTag("name", array_shift($plugins->getTags("//plugins/plugin[".$i."]/name")), true);
				$xml->addFullTag("description", array_shift($plugins->getTags("//plugins/plugin[".$i."]/description")), true);
				$xml->addFullTag("beta", (array_shift($plugins->getTags("//plugins/plugin[".$i."]/@beta")) == 1) ? "1" : "0", true);
				$xml->addFullTag("author", array_shift($plugins->getTags("//plugins/plugin[".$i."]/author")), true);
				$xml->addFullTag("version", array_shift($plugins->getTags("//plugins/plugin[".$i."]/@version")), true);
				$xml->addFullTag("custom", array_shift($plugins->getTags("//plugins/plugin[".$i."]/@customizable")), true);
				$xml->addFullTag("edit", $edit['protocol']."://".$this->_generic->getSiteConfig('domainName')."/".$edit['controller']."/".$edit['scontroller']."/Plugin/".$id.".sls", true);
				$xml->addFullTag("delete", $delete['protocol']."://".$this->_generic->getSiteConfig('domainName')."/".$delete['controller']."/".$delete['scontroller']."/Plugin/".$id.".sls", true);
				if (key_exists($id."_".$code, $pluginsAvailable))
				{
					
					if (((float)$pluginsAvailable[$id."_".$code]['version'] > (float)$version) && ((float)$slsVersion >= (float)$pluginsAvailable[$id."_".$code]['compatible']))
						$xml->addFullTag("uptodate", 'no', true);
					else 
						$xml->addFullTag("uptodate", 'yes', true);
						
					$xml->addFullTag("update", $maj['protocol']."://".$this->_generic->getSiteConfig("domainName")."/".$maj['controller']."/".$maj['scontroller']."/Action/Maj/Plugin/".$id."/Server/".$pluginsAvailable[$id."_".$code]['server'].".sls", true);
					$xml->addFullTag("doc", $pluginsAvailable[$id."_".$code]['doc'], true);
				}
				else 
					$xml->addFullTag("uptodate", 'yes', true);
					
				
			$xml->endTag("plugin");
		}
		$controllers = $this->_generic->getTranslatedController('SLS_Bo', 'SearchPlugin');
		$xml->addFullTag("search", $controllers['protocol']."://".$this->_generic->getSiteConfig("domainName")."/".$controllers['controller']."/".$controllers['scontroller'].".sls");
		$this->saveXML($xml);		
	}
	
}
?>