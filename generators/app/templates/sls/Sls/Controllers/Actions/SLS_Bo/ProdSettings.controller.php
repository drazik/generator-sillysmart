<?php
class SLS_BoProdSettings extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		
		$errors = array();
		
		// Get default values
		$defaultIsProd 				= $this->_generic->getSiteConfig("isProd");
		$defaultActiveCache 		= $this->_generic->getSiteConfig("activeCache");
		$defaultActiveMaintenance 	= $this->_generic->getSiteConfig("activeMaintenance");
		$defaultActiveMonitoring 	= $this->_generic->getSiteConfig("activeMonitoring"); 
		$reload 					= $this->_http->getParam("reload");
				
		if ($reload == "true")
		{
			// Get New Parameters
			$postIsProd 			= $this->_http->getParam("prod", "post");
			$postActiveCache 		= $this->_http->getParam("cache", "post");
			$postActiveMaintenance 	= $this->_http->getParam("maintenance", "post");
			$postActiveMonitoring 	= $this->_http->getParam("monitoring", "post");
			$siteXML = $this->_generic->getSiteXML();
			
			if ($postActiveCache != 0 && $postActiveCache != 1)
				array_push($errors, "Incorrect value for Cache");
			if ($postIsProd != 0 && $postIsProd != 1)
				array_push($errors, "Incorrect value for Production mode");
			if ($postActiveMaintenance != 0 && $postActiveMaintenance != 1)
				array_push($errors, "Incorrect value for Maintenance mode");
			if ($postActiveMonitoring != 0 && $postActiveMonitoring != 1)
				array_push($errors, "Incorrect value for Monitoring mode");
			if (empty($errors))
			{
				if ($defaultActiveCache != $postActiveCache)
					 $siteXML->setTag("//configs/activeCache", $postActiveCache, true);
				if ($defaultIsProd != $postIsProd)
					 $siteXML->setTag("//configs/isProd", $postIsProd, true);
				if ($defaultActiveMaintenance != $postActiveMaintenance)
					 $siteXML->setTag("//configs/activeMaintenance", $postActiveMaintenance, true);
				if ($defaultActiveMonitoring != $postActiveMonitoring)
					 $siteXML->setTag("//configs/activeMonitoring", $postActiveMonitoring, true);
					 
				if (($defaultActiveCache != $postActiveCache) || ($defaultIsProd != $postIsProd) || ($defaultActiveMaintenance != $postActiveMaintenance) || ($defaultActiveMonitoring != $postActiveMonitoring))
				{
					$siteXML->refresh();
					@file_put_contents($this->_generic->getPathConfig("configSecure")."site.xml", $siteXML->getXML());
				}
			}
			else 
			{
				$xml->startTag("errors");
					foreach ($errors as $error)
						$xml->addFullTag("error", $error, true);
				$xml->endTag("errors");
			}
		}
		$this->_generic->eraseCache('Site');
		$xml->startTag("current_values");
			$xml->addFullTag("prod", $this->_generic->getSiteConfig("isProd"), true);
			$xml->addFullTag("cache", $this->_generic->getSiteConfig("activeCache"), true);
			$xml->addFullTag("maintenance", $this->_generic->getSiteConfig("activeMaintenance"), true);
			$xml->addFullTag("monitoring", $this->_generic->getSiteConfig("activeMonitoring"), true);
		$xml->endTag("current_values");
		
		$xml->addFullTag("url_flush_cache",$this->_generic->getFullPath("SLS_Bo","FlushCache",array(array("key"=>"From","value"=>"Full"),array("key"=>"Token","value"=>substr(sha1($this->_generic->getSiteConfig("privateKey")),12,8)))),true);
		
		$this->saveXML($xml);		
	}
	
}
?>