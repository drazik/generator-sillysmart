<?php
class SLS_BoLogsMenu extends SLS_BoControllerProtected 
{	
	public function action()
	{		
		$xml = $this->getXML();
		$user = $this->hasAuthorative();
		$xml = $this->makeMenu($xml);
		$xml->addFullTag("logs_monitoring",$this->_generic->getFullPath("SLS_Bo","LogsM"),true);
		$xml->addFullTag("logs_app",$this->_generic->getFullPath("SLS_Bo","Logs"),true);		
		$xml->addFullTag("logs_mail",$this->_generic->getFullPath("SLS_Bo","LogsMail"),true);
		$this->saveXML($xml);
	}
	
}
?>