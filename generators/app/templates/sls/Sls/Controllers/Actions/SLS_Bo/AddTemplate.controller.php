<?php
class SLS_BoAddTemplate extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml 	= $this->makeMenu($xml);		
		$errors = array();		
		$tpls 	= $this->getAppTpls();
		$siteXML = $this->_generic->getSiteXML();
		$ga = $siteXML->getTag("//configs/google/setting[@name='ua']");
		
		if ($this->_http->getParam("reload") == "true")
		{
			$tpl = SLS_String::trimSlashesFromString(SLS_String::stringToUrl($this->_http->getParam("tpl_name"),"_"));
			$doctype = $this->_http->getParam("doctype");
			
			if (empty($tpl))
				array_push($errors,"You must choose a name for your template");
			else if (in_array($tpl,$tpls))
				array_push($errors,"This template name already exists, please choose another one");
				
			if (empty($errors))
			{
				$this->createXslTemplate($tpl,$doctype,$ga);
				$this->_generic->goDirectTo("SLS_Bo","Templates");
			}
			else
			{
				$xml->startTag("errors");
				foreach($errors as $error)
					$xml->addFullTag("error",$error,true);
				$xml->endTag("errors");
				
				$xml->addFullTag("doctype",(empty($doctype)) ? $this->_generic->getSiteConfig("defaultDoctype") : $doctype,true);
			}
		}
		else
		{
			$xml->addFullTag("doctype",$this->_generic->getSiteConfig("defaultDoctype"),true);
		}
		
		$this->saveXML($xml);
	}	
}
?>