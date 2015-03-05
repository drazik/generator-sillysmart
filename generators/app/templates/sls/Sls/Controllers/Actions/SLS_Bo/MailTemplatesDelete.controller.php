<?php
class SLS_BoMailTemplatesDelete extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 		= $this->hasAuthorative();
		$mailXML 	= $this->_generic->getMailXML();
		$tpl_id		= strtolower($this->_http->getParam("TplId"));
		
		$result = $mailXML->getTag("//mails/templates/item[@id='".$tpl_id."']/header");
		if (!empty($result))
		{
			$mailXML->deleteTags("//mails/templates/item[@id='".$tpl_id."']");						
			$mailXML->saveXML($this->_generic->getPathConfig("configSecure")."mail.xml");
		}
		
		$this->_generic->forward("SLS_Bo","MailTemplates");
	}
	
}
?>