<?php
class SLS_BoEnvironmentsDelete extends SLS_BoControllerProtected 
{
	
	public function action()
	{
		$user 	= $this->hasAuthorative();
		
		$environment = $this->_http->getParam("Item");
				
		$filesToCheck = array("site","db","project","mail");
		if ($handle = opendir($this->_generic->getPathConfig("configSecure")))
		{
			while (false !== ($entry = readdir($handle)))
			{
				foreach($filesToCheck as $file)
				{
			        if ($entry == $file."_".$environment.".xml")
			        {			        	
			        	unlink($this->_generic->getPathConfig("configSecure").$entry);
			        }
				}
			}
		}
		
		$this->_generic->forward("SLS_Bo","Environments");
				
		$this->saveXML($xml);		
	}
	
}
?>