<?php
class SLS_BoJSSettings extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user = $this->hasAuthorative();
		$xml = $this->getXML();
		$xml = $this->makeMenu($xml);
		
		$errors = array();
		
		// Get default values
		$defaultLoadStatic 		= $this->_generic->getSiteConfig("defaultLoadStaticsJavascript");
		$defaultLoadDyns		= $this->_generic->getSiteConfig("defaultLoadDynsJavascript"); 
		$defaultVars	 		= $this->_generic->getSiteConfig("defaultBuildConfigsJsVars");
		$defaultLangs			= $this->_generic->getSiteConfig("defaultMultilanguageJavascript");
		$defaultIE6				= $this->_generic->getSiteConfig("defaultLoadIE6Javascript");  
		$reload 				= $this->_http->getParam("reload");
				
		if ($reload == "true")
		{
			// Get New Parameters
			$postLoadStatic 		= $this->_http->getParam("statics", "post");
			$postLoadDyns 			= $this->_http->getParam("dyns", "post");
			$postVars		 		= $this->_http->getParam("vars", "post");
			$postLangs	 			= $this->_http->getParam("langs", "post");
			$postIe6	 			= $this->_http->getParam("ie6", "post");
			
			$siteXML = $this->_generic->getSiteXML();
			
			if ($postLoadStatic != 0 && $postLoadStatic != 1)
				array_push($errors, "Incorrect value for Loading Statics Javascripts");
			if ($postLoadDyns != 0 && $postLoadDyns != 1)
				array_push($errors, "Incorrect value for Loading Additional Javascripts");
			if ($postVars != 0 && $postVars != 1)
				array_push($errors, "Incorrect value to Automatic JavaScript variables builder");
			if ($postLangs != 0 && $postLangs != 1)
				array_push($errors, "Incorrect value for JavaScript Multilanguage");
			if ($postIe6 != 0 && $postIe6 != 1)
				array_push($errors, "Incorrect value for JavaScript IE6 toolbar message");
				
			if ($postVars == 0 && $postLangs == 1)
				array_push($errors, "You cannot turn on JsMultilanguage if you don't turn on Automatic JavaScript Variables Builder");
				
			if (empty($errors))
			{
				if ($defaultLoadStatic != $postLoadStatic)
					$siteXML->setTag("//configs/defaultLoadStaticsJavascript", $postLoadStatic, true);
				if ($defaultLoadDyns != $postLoadDyns)
					$siteXML->setTag("//configs/defaultLoadDynsJavascript", $postLoadDyns, true);
				if ($defaultVars != $postVars)
					$siteXML->setTag("//configs/defaultBuildConfigsJsVars", $postVars, true);
				if ($defaultLangs != $postLangs)
					$siteXML->setTag("//configs/defaultMultilanguageJavascript", $postLangs, true);
				if ($defaultIE6 != $postIe6)
					$siteXML->setTag("//configs/defaultLoadIE6Javascript", $postIe6, true);
					 
				if (($defaultLoadStatic != $postLoadStatic) || ($defaultLoadDyns != $postLoadDyns) || ($defaultVars != $postVars) || ($defaultLangs != $postLangs) || ($defaultIE6 != $postIe6))
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
			$xml->addFullTag("statics", $this->_generic->getSiteConfig("defaultLoadStaticsJavascript"), true);
			$xml->addFullTag("dyns", $this->_generic->getSiteConfig("defaultLoadDynsJavascript"), true);
			$xml->addFullTag("vars", $this->_generic->getSiteConfig("defaultBuildConfigsJsVars"), true);
			$xml->addFullTag("langs", $this->_generic->getSiteConfig("defaultMultilanguageJavascript"), true);
			$xml->addFullTag("ie6", $this->_generic->getSiteConfig("defaultLoadIE6Javascript"), true);
		$xml->endTag("current_values");
		
		$this->saveXML($xml);		
	}
	
}
?>