<?php
class SLS_BoGoogleSettings extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml	= $this->makeMenu($xml);
		$siteXML = $this->_generic->getSiteXML();
		
		$googleSettings = array();
		
		if ($this->_http->getParam("reload") == "true")
		{
			$googleSettings = $this->_http->getParam("ga");
			
			$siteXML->setTag("//configs/google/setting[@name='ua']",trim($googleSettings["ua"]));
			$siteXML->setTag("//configs/google/setting[@name='apiKey']",trim($googleSettings["apiKey"]));
			$siteXML->setTag("//configs/google/setting[@name='clientId']",trim($googleSettings["clientId"]));
			$siteXML->setTag("//configs/google/setting[@name='accountId']",trim($googleSettings["accountId"]));
			$siteXML->saveXML($this->_generic->getPathConfig("configSecure")."site.xml");
			$siteXML->refresh();
			
			if (!empty($googleSettings["ua"]))
			{
				$googleSettings["ua"] = (SLS_String::startsWith(trim(strtolower($googleSettings["ua"])),"ua-")) ? $googleSettings["ua"] : "UA-".$googleSettings["ua"];
				$templates = scandir($this->_generic->getPathConfig("viewsTemplates"));
				foreach($templates as $template)
				{
					if (!SLS_String::startsWith($template,"."))
					{
						$templateContent = file_get_contents($this->_generic->getPathConfig("viewsTemplates").$template);
						
						if (SLS_String::contains($templateContent,"<!-- GA loading -->") && SLS_String::contains($templateContent,"_gaq.push(['_setAccount'"))
						{
							$oldUa = trim(str_replace("'","",SLS_String::substrBeforeFirstDelimiter(SLS_String::substrAfterFirstDelimiter($templateContent,"_gaq.push(['_setAccount',"),"]")));
							if ($oldUa != $googleSettings["ua"])
							{
								$templateContent = str_replace("_gaq.push(['_setAccount', '".$oldUa."']);","_gaq.push(['_setAccount', '".$googleSettings["ua"]."']);",$templateContent);
								file_put_contents($this->_generic->getPathConfig("viewsTemplates").$template,$templateContent);
							}
						}
						else
						{
							$newContent = "";
							$templateLines = explode("\n",$templateContent);
							
							for($i=0 ; $i<$count=count($templateLines) ; $i++)
							{
								$line = $templateLines[$i];
								
								if (SLS_String::contains($line,"</body>"))
								{
									$newContent .= t(4)."<!-- GA loading -->"."\n".
													t(4)."<xsl:if test=\"//Statics/Sls/Configs/site/isProd = '1'\">"."\n".
														t(5)."<script type=\"text/javascript\">"."\n".
															t(6)."var _gaq = _gaq || [];"."\n".
															t(6)."_gaq.push(['_setAccount', '".$googleSettings["ua"]."']);"."\n".
															t(6)."_gaq.push(['_trackPageview']);"."\n".
															t(6)."(function() {"."\n".
																t(7)."var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;"."\n".
																t(7)."ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';"."\n".
																t(7)."var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);"."\n".
															t(6)."})();"."\n".
														t(5)."</script>"."\n".
													t(4)."</xsl:if>"."\n".
													t(4)."<!-- /GA loading -->"."\n\n";
								}
								
								$newContent .= $line."\n";
							}
							
							file_put_contents($this->_generic->getPathConfig("viewsTemplates").$template,$newContent);
						}
					}
				}
			}
			
			$xml->addFullTag("success","Your settings have been saved.",true);
		}
		else
		{
			$googleSettings["ua"] = $siteXML->getTag("//configs/google/setting[@name='ua']");
			$googleSettings["apiKey"] = $siteXML->getTag("//configs/google/setting[@name='apiKey']");
			$googleSettings["clientId"] = $siteXML->getTag("//configs/google/setting[@name='clientId']");
			$googleSettings["accountId"] = $siteXML->getTag("//configs/google/setting[@name='accountId']");
		}
		
		$xml->startTag("google");
		foreach($googleSettings as $key => $value)
			$xml->addFullTag($key,$value,true);
		$xml->endTag("google");
		
		$this->saveXML($xml);		
	}
	
}
?>