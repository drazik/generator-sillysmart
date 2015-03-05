<?php
class SLS_BoAddGeneric extends SLS_BoControllerProtected 
{	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		$xml 	= $this->getXML();
		$xml 	= $this->makeMenu($xml);		
		$errors = array();		
		$generics 	= $this->getAppXsl();
		
		if ($this->_http->getParam("reload") == "true")
		{
			$generic = ucfirst(SLS_String::trimSlashesFromString(SLS_String::stringToUrl($this->_http->getParam("generic_name"),"_")));
			
			if (empty($generic))
				array_push($errors,"You must choose a name for your generic");
			else if (in_array(strtolower($generic),$generics))
				array_push($errors,"This generic name already exists, please choose another one");
				
			if (empty($errors))
			{
				$str =  '<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">'."\n".
						t(1).'<xsl:template name="'.$generic.'">'."\n".
							t(2).''."\n".
						t(1).'</xsl:template>'."\n".
						'</xsl:stylesheet>';
				file_put_contents($this->_generic->getPathConfig("viewsGenerics").$generic.".xsl",$str);
				$this->_generic->goDirectTo("SLS_Bo","Templates");
			}
			else
			{
				$xml->startTag("errors");
				foreach($errors as $error)
					$xml->addFullTag("error",$error,true);
				$xml->endTag("errors");
			}
		}
		
		
		$this->saveXML($xml);
	}	
}
?>