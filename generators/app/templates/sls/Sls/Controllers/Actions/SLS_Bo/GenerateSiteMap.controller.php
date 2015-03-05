<?php
class SLS_BoGenerateSiteMap extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);		
		$cXml 	= $this->_generic->getControllersXML();		
		$langs 	= $this->_generic->getObjectLang()->getSiteLangs();
		$links	= array($this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName"));
		
		foreach($langs as $lang)
		{
			$controllers = $cXml->getTags("//controllers/controller[@side = 'user' and @name != 'Default' and count(@isBo) = 0]/controllerLangs/controllerLang[@lang='".$lang."']");
			
			foreach($controllers as $controller)
			{
				$generic = array_shift($cXml->getTags("//controllers/controller[controllerLangs[controllerLang[@lang='".$lang."']='".$controller."']]/@name"));
				$actions = $cXml->getTags("//controllers/controller[@side = 'user' and @name != 'Default' and count(@isBo) = 0 and @name='".$generic."']/scontrollers/scontroller[@needParam = '0' and scontrollerLangs/scontrollerLang[@lang='".$lang."']]/@name");
				foreach($actions as $action)
					array_push($links,$this->_generic->getFullPath($generic,$action,array(),true,$lang));
			}
		}
		
		$source = 	'<?xml version="1.0" encoding="UTF-8"?>'."\n".
					'<urlset '."\n".
					'    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '."\n".
					'    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '."\n".
					'    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 '."\n".
					'    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\n\n";
					
		foreach($links as $link)
		{
			$priority = ($link == $this->_generic->getSiteConfig("protocol")."://".$this->_generic->getSiteConfig("domainName")) ? "1.00" : "0.64";
			$source .= 	'    <url>'."\n".
						'        <loc>'.$link.'</loc>'."\n".
						'        <priority>'.$priority.'</priority>'."\n".
						'        <changefreq>daily</changefreq>'."\n".
						'    </url>'."\n";
		}
		$source .= '</urlset>';
		
		$xml->addFullTag("site_map",$source,true);
		$this->saveXML($xml);		
	}
	
}
?>