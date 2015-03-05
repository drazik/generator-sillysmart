<?php
class SLS_BoMailTemplates extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 		= $this->hasAuthorative();
		$xml 		= $this->getXML();
		$xml		= $this->makeMenu($xml);
		$mailXML 	= $this->_generic->getMailXML();
		$templates  = $mailXML->getTags("//mails/templates/item/@id");
		
		if ($this->_http->getParam("reload") == "true")
		{
			foreach($templates as $template)
			{
				$header = SLS_String::trimSlashesFromString($this->_http->getParam("template_".$template."_header"));
				$footer = SLS_String::trimSlashesFromString($this->_http->getParam("template_".$template."_footer"));
				
				$mailXML->setTag("//mails/templates/item[@id='".$template."']/header",$header);
				$mailXML->setTag("//mails/templates/item[@id='".$template."']/footer",$footer);
			}
			$mailXML->saveXML($this->_generic->getPathConfig("configSecure")."mail.xml");
		}
		
		$xml->startTag("templates");
		for($i=0 ; $i<$count=count($templates) ; $i++)
		{
			$id = $templates[$i];
			$xml->startTag("template");
				$xml->addFullTag("id",$id,true);
				$xml->addFullTag("header",($mailXML->getTag("//mails/templates/item[@id='".$id."']/header")),true);
				$xml->addFullTag("footer",($mailXML->getTag("//mails/templates/item[@id='".$id."']/footer")),true);
				$xml->addFullTag("url_preview",$this->_generic->getFullPath("SLS_Bo","MailTemplatesPreview",array(array("key"=>"TplId","value"=>$id))),true);
				$xml->addFullTag("url_delete",$this->_generic->getFullPath("SLS_Bo","MailTemplatesDelete",array(array("key"=>"TplId","value"=>$id))),true);
			$xml->endTag("template");
		}
		$xml->endTag("templates");
		
		$xml->addFullTag("url_template_add",$this->_generic->getFullPath("SLS_Bo","MailTemplatesAdd"),true);
		
		$this->saveXML($xml);		
	}
	
}
?>