<?php
class SLS_BoMailTemplatesAdd extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 		= $this->hasAuthorative();
		$xml 		= $this->getXML();
		$xml		= $this->makeMenu($xml);
		$mailXML 	= $this->_generic->getMailXML();
				
		if ($this->_http->getParam("reload") == "true")
		{
			$id = strtolower($this->_http->getParam("tpl_id"));
			$header = SLS_String::trimSlashesFromString($this->_http->getParam("tpl_header"));
			$footer = SLS_String::trimSlashesFromString($this->_http->getParam("tpl_footer"));
			
			$result = $mailXML->getTag("//mails/templates/item[@id='".$id."']/header");
			
			if (empty($result))
			{
				$str_xml = '<item id="'.$id.'" isSecure="false" js="false">
	      <header isSecure="false" js="false"><![CDATA['.$header.']]></header>
	      <footer isSecure="false" js="false"><![CDATA['.$footer.']]></footer>
	    </item>';
				$mailXML->appendXMLNode("//templates",$str_xml);
				$mailXML->saveXML($this->_generic->getPathConfig("configSecure")."mail.xml");
				$this->_generic->forward("SLS_Bo","MailTemplates");
			}
			else
			{
				$xml->addFullTag("error","This name is already use by another template",true);
				$xml->startTag("template");
					$xml->addFullTag("id",$id,true);
					$xml->addFullTag("header",$header,true);
					$xml->addFullTag("footer",$footer,true);					
				$xml->endTag("template");
			}
		}
				
		$this->saveXML($xml);		
	}
	
}
?>