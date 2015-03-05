<?php
class SLS_InitIndex extends SLS_InitControllerProtected 
{	
	/**
	 * Action Home
	 *
	 */
	public function action() 
	{
		$coreXML = $this->_generic->getCoreXML('sls');
		$serverPres = $coreXML->getTags("//sls_configs/prerequies/server[@name='apache']/mod");
		$apache = apache_get_modules();
		foreach ($serverPres as $serverPre)
		{
			if (!in_array($serverPre, $apache))
				SLS_Tracing::addTrace(new Exception("You need apache module: ".$serverPre));
		}
		$phpPres = $coreXML->getTags("//sls_configs/prerequies/php/mod");
		$php = get_loaded_extensions();
		foreach ($phpPres as $phpPre)
		{
			if (!in_array($phpPre, $php))
				SLS_Tracing::addTrace(new Exception("You need PHP Extension : ".$phpPre));
		}
		$this->_generic->registerLink('dircheck', 'SLS_Init', 'DirRights');		
	}
	
}
?>